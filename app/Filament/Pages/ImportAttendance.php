<?php

namespace App\Filament\Pages;

use App\Models\CreditLog;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImportAttendance extends Page
{
    const CSV_COLUMNS = [
        'group_code',
        'name',
        'id',
        'attendance_type',
        'attendance_count',
    ];

    protected static string $view = 'filament.pages.import-attendance';

    protected static ?string $navigationGroup = 'Credits';

    private static function getLineColumnByName(array $line, string $name): string {
        // remove hidden characters from the line
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $line[array_search($name, self::CSV_COLUMNS)]);
    }

    private static function findUser(string $id): ?User {
        $prefixes = ['d', 'i'];

        foreach ($prefixes as $prefix) {
            $user = User::where('id', $prefix . $id)->first();
            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }

    public function importAction(): Action
    {
        // Aantal_registraties_per_persoon_van_groep2.csv example data:
        // groep_code,deel_naam_opgemaakt,Deel_ovnr,TypeOmschrijving,textbox2,textbox3,Textbox8
        // TTSDB-sd4o23a,Oliver Johnson,205878,Present,10,13.53,13.75
        // TTSDB-sd4o23a,Oliver Johnson,205878,Regulier absent,0,0.00,13.75
        // TTSDB-sd4o23a,Oliver Johnson,205878,Te laat,1,0.22,13.75
        // TTSDB-sd4o23a,Oliver Johnson,205878,Ziek,0,0.00,13.75
        // TTSDB-sd4o23a,Isabella Davis,203186,Present,10,13.75,13.75
        // TTSDB-sd4o23a,Isabella Davis,203186,Regulier absent,0,0.00,13.75
        // TTSDB-sd4o23a,Isabella Davis,203186,Te laat,,,13.75
        // TTSDB-sd4o23a,Isabella Davis,203186,Ziek,0,0.00,13.75
        return Action::make('import')
            ->form([
                Forms\Components\FileUpload::make('file')
                    ->label(__('crud.user.attendance_file'))
                    ->preserveFilenames()
                    ->storeFiles(false)
                    // ->acceptedFileTypes(['text/csv', 'text/plain','application/csv','text/comma-separated-values','text/anytext','application/octet-stream','application/txt'])
                    ->required(),
            ])
            ->action(function(array $data) {
                /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile */
                $file = $data['file'];

                $creditsForFullAttendance = 100;
                $positiveAttendanceTypes = ['Present', 'Regulier absent'];

                $users = [];

                // Iterate all rows, counting the credits for each user based on their positive attendance.
                // If a user has a negative attendance, the credits will be set to 0.

                $currentUserId = null;
                $currentUserCredits = 100;
                $hasStarted = false;

                foreach (file($file->getRealPath()) as $line) {
                    $line = str_getcsv($line, ',', '"');

                    // If it's an empty line, skip it.
                    if (count($line) <= 1) {
                        continue;
                    }

                    // Skip the first line, which contains the column names.
                    if (self::getLineColumnByName($line, 'groep_code') === 'groep_code') {
                        $hasStarted = true;
                        continue;
                    }

                    if (!$hasStarted) {
                        Notification::make()
                            ->title(__('crud.user.import_attendance_invalid_file'))
                            ->danger()
                            ->send();
                        return;
                    }

                    // Check if this is a new user, if so save the credits of the previous user.
                    if ($currentUserId !== self::getLineColumnByName($line, 'id')) {
                        // If this is a new user, save the credits of the previous user.
                        if ($currentUserId !== null) {
                            $users[] = [
                                'credits' => $currentUserCredits,
                                'name' => self::getLineColumnByName($line, 'name'),
                                'id' => $currentUserId,
                            ];
                        }

                        // Reset the credits for the new user.
                        $currentUserId = self::getLineColumnByName($line, 'id');
                        $currentUserCredits = $creditsForFullAttendance;
                    }

                    // Check if the attendance is negative, if so and the attendance_count are not empty, set the credits to 0.
                    if (!in_array(self::getLineColumnByName($line, 'attendance_type'), $positiveAttendanceTypes)) {
                        $value = self::getLineColumnByName($line, 'attendance_count');
                        if ($value !== '' && $value !== '0') {
                            $currentUserCredits = 0;
                        }
                    }
                }

                // Create a query that upserts the users, adding to their credits, or creating the users if they dont exist. Their type will be student and their email is 'd<theirid>@curio.nl'.
                \DB::transaction(function() use ($users) {
                    foreach ($users as $userData) {
                        $user = static::findUser($userData['id']);

                        if ($user === null) {
                            // Let's not create a user for now. Just let users be created when they log in and then start earning credits.
                            // $user = new User();
                            // $user->name = $userData['name'];
                            // $user->email = 'd' . $userData['id'] . '@curio.nl';
                            // $user->type = 'student';
                            continue;
                        }

                        CreditLog::mutateWithTransaction(
                            receiver: $user,
                            sender: null,
                            amount: (int) $userData['credits'],
                            reason: 'attendance import by ' . auth()->user()->name . ' @ ' . now()->format('Y-m-d H:i:s'),
                            mutator: function() use($user, $userData) {
                                $user->credits += $userData['credits'];
                                $user->credits = max(0, min($user->credits, PHP_INT_MAX));
                                $user->save();
                            }
                        );
                    }
                });

                Notification::make()
                    ->title(__('crud.user.import_attendance_success'))
                    ->success()
                    ->send();
            });
    }
}
