<?php

namespace App\Http\Controllers;

use App\Models\InviteSystem;
use App\Models\ShopItemUser;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class InviteController extends Controller
{
    /**
     * Activates JavaScript capabilities for the given slide
     */
    public function inviteActivate(Request $request)
    {
        $request->validate([
            'slide_id' => [
                'required',
                ValidationRule::exists('slides', 'id')->where(function ($query) {
                    $query->whereIn('id', auth()->user()->approvedSlides()->pluck('id'));
                }),
            ],
            'shop_item_user_id' => [
                'required',
                'exists:shop_item_user,id',
            ],
            'invite_system_title' => [
                'required',
                'string'
            ],
            'invite_system_description' => [
                'required',
                'string'
            ],
            'invite_system_invitee_slots' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'invite_system_entry_fee_in_credits' => [
                'required',
                'integer',
                'min:0',
                'max:1000',
            ],
        ]);

        // Check that the user owns the given shop item
        $shopItemUser = auth()->user()->shopItemUsers()->find($request->shop_item_user_id);

        if (!$shopItemUser) {
            return redirect()->back()->withErrors([
                'shop_item_user_id' => __('You do not own this item'),
            ]);
        }

        if ($shopItemUser->shopItem->unique_id !== 'slide_invite_system') {
            return redirect()->back()->withErrors([
                'shop_item_user_id' => __('This item is not a slide invite system'),
            ]);
        }

        $slide = Slide::find($request->slide_id);
        $result = $shopItemUser->shopItem->callShopItemMethod(
            'onUse',
            $shopItemUser,
            $slide,
            $request->invite_system_title,
            $request->invite_system_description,
            $request->invite_system_invitee_slots,
            $request->invite_system_entry_fee_in_credits
        );

        if ($result !== null) {
            return $result;
        }

        $shopItemUser->save();

        return redirect()->back()->with('success', 'Invite System op slide geactiveerd!');
    }

    /**
     * Display the invite code entry page
     */
    public function inviteEnter(string $inviteCode = null)
    {
        // If we got here through a QR-code, we'll go straight through to the inviteProcess confirmation page
        if ($inviteCode !== null) {
            $inviteSystem = \App\Models\InviteSystem::where('latest_code', $inviteCode)->firstOrFail();

            return view('app.slides.invite-confirm', [
                'inviteSystem' => $inviteSystem,
            ]);
        }

        return view('app.slides.invite-enter');
    }

    private function sanitizeInviteCode(Request $request)
    {
        // Remove dashes from the code
        if ($request->has('invite_code')) {
            $request->merge(['invite_code' => str_replace('-', '', $request->invite_code)]);
        }
    }

    /**
     * Process the invite code entry by checking which invite_system the code belongs to.
     * Once found, show that invite_system's 'authorization' page, which explains the
     * invitee the entry fee and the amount of slots available.
     */
    public function inviteProcess(Request $request)
    {
        $this->sanitizeInviteCode($request);

        $request->validate([
            'invite_code' => [
                'required',
                'exists:invite_systems,latest_code',
            ],
        ]);

        $inviteSystem = \App\Models\InviteSystem::where('latest_code', $request->invite_code)->firstOrFail();

        return view('app.slides.invite-confirm', [
            'inviteSystem' => $inviteSystem,
        ]);
    }

    /**
     * Confirm entry to the invite system, creating a InviteSystemInvitee for the user and
     * deducting the entry fee from the user's credits (if it's not a preview invite system)
     */
    public function inviteConfirm(Request $request)
    {
        $request->validate([
            'invite_code' => [
                'required',
                'exists:invite_systems,latest_code',
            ],
        ]);

        $inviteSystem = \App\Models\InviteSystem::where('latest_code', $request->invite_code)->firstOrFail();

        // Check if the user has enough credits to enter
        if (!$inviteSystem->isPreview() && auth()->user()->credits < $inviteSystem->entry_fee_in_credits) {
            return redirect()->back()->withErrors([
                'invite_code' => __('You do not have enough credits to enter this screen'),
                'csrfToken' => csrf_token(),
            ]);
        }

        // Check if there are still slots available
        if ($inviteSystem->invitee_slots !== null && $inviteSystem->invitee_slots <= $inviteSystem->invitees()->count()) {
            return redirect()->back()->withErrors([
                'invite_code' => __('There are no slots available for this screen'),
                'csrfToken' => csrf_token(),
            ]);
        }

        // Check if the user has already entered this screen, sending them through
        if (!$inviteSystem->isPreview() && $inviteSystem->invitees()->where('user_id', auth()->user()->id)->exists()) {
            return redirect()->route('slides.inviteeInteract', $inviteSystem);
        }

        // Create the invitee
        $invitee = new \App\Models\InviteSystemInvitee;
        $invitee->user_id = auth()->user()->id;
        $invitee->invite_system_id = $inviteSystem->id;
        $invitee->reserved_entry_fee_in_credits = $inviteSystem->entry_fee_in_credits;
        $invitee->save();

        // Deduct the entry fee from the user's credits
        if (!$inviteSystem->isPreview()) {
            auth()->user()->credits -= $inviteSystem->entry_fee_in_credits;
            auth()->user()->save();
        }

        return redirect()->route('slides.inviteeInteract', $inviteSystem);
    }

    /**
     * Shows the shop item interaction screen for the given invite system and logged in user
     */
    public function inviteeInteract(InviteSystem $inviteSystem)
    {
        $invitees = $inviteSystem->invitees;

        // Ensure the user has entered this screen
        if ($invitees->where('user_id', auth()->user()->id)->count() === 0) {
            return redirect()->route('slides.inviteEnter')->withErrors([
                'invite_code' => __('You have not entered this screen yet'),
                'csrfToken' => csrf_token(),
            ]);
        }

        $shopItemUser = $inviteSystem->shopItemUser;

        if (!$inviteSystem->isPreview()) {
            // Since it's a real shop item, find the slide that has the same shop item
            $slide = Slide::where('data->invite_system_shop_item_user_id', $shopItemUser->id)->firstOrFail();
        } else {
            // Since it's a preview, find the slide that has the same invite system
            $slide = Slide::where('data->invite_system_is_previewing_id', $inviteSystem->id)->firstOrFail();
        }

        if ($inviteSystem->isPreview()) {
            $localKey = isset($_GET['localKey']) ? $_GET['localKey'] : 0;
            $localId = self::makePreviewId(auth()->user()->id, $localKey);

            return view('app.slides.invitee-interact', [
                'publicPath' => $slide->extractPreviewToPublic(),
                'localId' => $localId,
                'slide' => $slide,
                'inviteCode' => $inviteSystem->latest_code,
                'isPreview' => true,
            ]);
        } else {
            return view('app.slides.invitee-interact', [
                'publicPath' => $slide->getKnownUrl(),
                'localId' => auth()->user()->id,
                'slide' => $slide,
                'inviteCode' => $inviteSystem->latest_code,
                'isPreview' => false,
            ]);
        }
    }

    /**
     * API endpoint to return a new invite code for the user, if no secret tick key is provided, creates
     * a preview invite code
     */
    public function inviteCodeRequest(Request $request)
    {
        $secretTickKey = $request->header('X-Secret-Tick-Key');

        $request->validate([
            'slide_id' => [
                'required',
            ],
        ]);

        $slide = Slide::findOrfail($request->slide_id);

        if ($secretTickKey != null) {
            if ($secretTickKey !== config('app.slide_show_secret_tick_key')) {
                return response()->json([
                    'error' => 'Invalid secret tick key',
                    'csrfToken' => csrf_token(),
                ], 403);
            }

            // $request->validate([
            //     'screen_id' => [
            //         'required',
            //     ],
            // ]);

            $shopItemUserId = $slide->data['invite_system_shop_item_user_id'];
            $shopItemUser = ShopItemUser::findOrFail($shopItemUserId);

            // TODO: Test this
            $inviteSystem = new \App\Models\InviteSystem;
            $inviteSystem->title = $shopItemUser->data['invite_system_title'];
            $inviteSystem->description = $shopItemUser->data['invite_system_description'];
            $inviteSystem->latest_code = $inviteSystem->generateCode();
            $inviteSystem->invitee_slots = isset($shopItemUser->data['invite_system_invitee_slots']) ? $shopItemUser->data['invite_system_invitee_slots'] : null;
            $inviteSystem->entry_fee_in_credits = $shopItemUser->data['invite_system_entry_fee_in_credits'];
            $inviteSystem->user_id = $shopItemUser->user_id;
            $inviteSystem->shop_item_user_id = $shopItemUser->id;
            $inviteSystem->save();
        } else {
            // Destroy existing preview invite systems for this user
            auth()->user()->inviteSystems()->where('shop_item_user_id', null)->delete();

            $inviteSystem = new \App\Models\InviteSystem;
            $inviteSystem->title = 'Preview';
            $inviteSystem->description = 'Preview';
            $inviteSystem->latest_code = $inviteSystem->generateCode();
            $inviteSystem->invitee_slots = null; // unlimited
            $inviteSystem->entry_fee_in_credits = 100;
            $inviteSystem->user_id = auth()->user()->id;
            $inviteSystem->save();
            $slide->setData('invite_system_is_previewing_id', $inviteSystem->id);
        }

        $writer = new PngWriter();

        $qrCode = QrCode::create(route('slides.inviteEnter', $inviteSystem->latest_code))
             ->setEncoding(new Encoding('UTF-8'))
             ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
             ->setSize(300)
             ->setMargin(5)
             ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
             ->setForegroundColor(new Color(0, 0, 0))
             ->setBackgroundColor(new Color(255, 255, 255));

        $logo = Logo::create(resource_path('images/emoji_rocket_1f680_padded.png'))
            ->setResizeToWidth(50)
            ->setPunchoutBackground(true);

        $result = $writer->write($qrCode, $logo);
        $qrCodeDataUri = $result->getDataUri();

        return response()->json([
            'inviteCode' => $inviteSystem->formatCode(),
            'inviteCodeQr' => $qrCodeDataUri,
            'csrfToken' => csrf_token(),
            'publicPath' => $slide->getKnownUrl(),
        ]);
    }

    public static function makePreviewId($id, $key)
    {
        return 'preview-'.$id.'-'.$key;
    }

    private function checkSecretTickKey(Request $request, $inviteSystem)
    {
        $secretTickKey = $request->header('X-Secret-Tick-Key');

        if (!$inviteSystem->isPreview()) {
            if ($secretTickKey !== config('app.slide_show_secret_tick_key')) {
                // return response()->json([
                //     'error' => 'Invalid secret tick key',
                //     'csrfToken' => csrf_token(),
                // ], 403);
                abort(403, 'Invalid secret tick key');
            }
        }

    }

    /**
     * API endpoint to update the invite code for the user, if no secret tick key is provided, updates
     * the preview invite code.
     * Sends updates on which invitees are currently in the screen.
     */
    public function inviteCodeUpdate(Request $request)
    {
        $this->sanitizeInviteCode($request);

        $request->validate([
            'invite_code' => [
                'required',
            ],
        ]);

        $inviteSystem = \App\Models\InviteSystem::with('shopItemUser')
            ->where('latest_code', $request->invite_code)
            ->firstOrFail();
        $this->checkSecretTickKey($request, $inviteSystem);

        $invitees = $inviteSystem->invitees()->with('user')->get()->map(function ($invitee) {
            return [
                'id' => $invitee->user->id,
                'name' => $invitee->user->name,
                'entryFee' => $invitee->reserved_entry_fee_in_credits,
            ];
        });

        if ($inviteSystem->isPreview()) {
            // For previews we allow the invitee to join multiple times, so we'll adjust their id to be unique
            $invitees = $invitees->map(function ($invitee, $key) {
                $invitee['id'] = self::makePreviewId($invitee['id'], $key);
                return $invitee;
            });
        } else {
            $publicPath = Slide::findOrFail($inviteSystem->shopItemUser->data['slide_id'])->getKnownUrl();
        }

        return response()->json([
            'invitees' => $invitees,
            'interactionData' => $inviteSystem->data,
            'csrfToken' => csrf_token(),
            'publicPath' => isset($publicPath) ? $publicPath : null,
        ]);
    }

    /**
     * Endpoint to request to redistribute wealth. All users that are currently in the invite system
     * must be present in the request. The sum of the credits must match the total entry fee pool.
     * All wealth must be redistributed, so no credits may be left over.
     */
    function inviteRedistributeRequest(Request $request)
    {
        $this->sanitizeInviteCode($request);

        $request->validate([
            'invite_code' => [
                'required',
            ],
            'redistributed_balance' => [
                'required',
            ],
        ]);

        $inviteSystem = \App\Models\InviteSystem::where('latest_code', $request->invite_code)->firstOrFail();
        $this->checkSecretTickKey($request, $inviteSystem);

        \DB::beginTransaction();

        $invitees = $inviteSystem->invitees()->with('user')->get();
        $inviteeIds = $invitees->pluck('user_id')->map(function ($id, $key) use ($inviteSystem) {
            if ($inviteSystem->isPreview()) {
                return self::makePreviewId($id, $key);
            }
            return $id;
        })->toArray();

        // Check if the sum of the redistributed balance matches the total entry fee pool
        $redistributedBalance = 0;
        $totalEntryFeePool = $inviteSystem->entry_fee_in_credits * $invitees->count();

        // Iterate redistributed_balance and check the balance for each id, ensure each id is present
        foreach ($request->redistributed_balance as $id => $balance) {
            if (!in_array($id, $inviteeIds)) {
                return response()->json([
                    'error' => 'The redistributed balance contains an invalid id',
                    'csrfToken' => csrf_token(),
                ], 400);
            }

            $redistributedBalance += $balance;
        }

        if ($redistributedBalance != $totalEntryFeePool) {
            return response()->json([
                'error' => 'The redistributed balance does not match the total entry fee pool',
                'csrfToken' => csrf_token(),
            ], 400);
        }

        if (!$inviteSystem->isPreview()) {
            foreach ($request->redistributed_balance as $id => $balance) {
                $user = \App\Models\User::findOrFail($id);
                $user->credits += $balance;
                $user->save();
            }

            $publicPath = Slide::findOrFail($inviteSystem->shopItemUser->data['slide_id'])->getKnownUrl();
        }

        // Remove the invite system
        $inviteSystem->delete();

        \DB::commit();

        return response()->json([
            'wasSuccesful' => true,
            'csrfToken' => csrf_token(),
            'publicPath' => isset($publicPath) ? $publicPath : null,
        ]);
    }

    /**
     * Sets interaction data on the invite system
     */
    public function inviteRequestSetInteractionData(Request $request)
    {
        $this->sanitizeInviteCode($request);

        $request->validate([
            'invite_code' => [
                'required',
            ],
            'interaction_data' => [
                'required',
            ],
        ]);

        $inviteSystem = \App\Models\InviteSystem::where('latest_code', $request->invite_code)->firstOrFail();
        $this->checkSecretTickKey($request, $inviteSystem);

        $inviteSystem->data = $request->interaction_data;
        $inviteSystem->save();

        if (!$inviteSystem->isPreview()) {
            $publicPath = Slide::findOrFail($inviteSystem->shopItemUser->data['slide_id'])->getKnownUrl();
        }

        return response()->json([
            'wasSuccesful' => true,
            'csrfToken' => csrf_token(),
            'publicPath' => isset($publicPath) ? $publicPath : null,
        ]);
    }
}
