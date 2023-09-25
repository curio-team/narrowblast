<?php

namespace App\Livewire;

use App\Models\CreditLog;
use App\Models\ShopItem;
use Filament\Tables\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListShopItems extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $query = ShopItem::query()
            ->where(function (Builder $query) {
                $query->whereNull('required_type')
                    ->orWhere('required_type', auth()->user()->type);
            });

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->visibleFrom('md'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->visibleFrom('lg')
                    ->formatStateUsing(function (string $state): string {
                        return \Str::limit($state, 20);
                    })
                    ->tooltip(function (string $state): string {
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('cost_in_credits')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('max_per_user')
                //     ->numeric()
                //     ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('purchaseItem')
                    ->label(function (ShopItem $record): string {
                        if ($record->cost_in_credits > auth()->user()->credits) {
                            return ucfirst(__('crud.shop_items.purchase_disabled_reasons.insufficient_credits'));
                        }

                        if ($record->userHasMaximum(auth()->user())) {
                            return ucfirst(__('crud.shop_items.purchase_disabled_reasons.max_per_user'));
                        }

                        return ucfirst(__('crud.shop_items.purchase'));
                    })
                    ->icon(function (ShopItem $record): string|null {
                        if ($record->cost_in_credits <= auth()->user()->credits
                            && !$record->userHasMaximum(auth()->user())) {
                            return 'heroicon-o-currency-dollar';
                        }

                        return null;
                    })
                    ->color(function (ShopItem $record): string|null {
                        if ($record->cost_in_credits <= auth()->user()->credits
                        && !$record->userHasMaximum(auth()->user())) {
                            return 'primary';
                        }

                        return 'secondary';
                    })
                    ->disabled(function(ShopItem $record) {
                        if ($record->cost_in_credits > auth()->user()->credits) {
                            return true;
                        }

                        if ($record->userHasMaximum(auth()->user())) {
                            return true;
                        }

                        return false;
                    })
                    ->requiresConfirmation()
                    ->modalHeading(ucfirst(__('crud.shop_items.purchase_confirmation')))
                    ->modalDescription(function(ShopItem $record): string {
                        return __('crud.shop_items.purchase_confirmation_description', [
                            'item' => $record->name,
                            'credits' => $record->cost_in_credits,
                        ]);
                    })
                    ->modalSubmitActionLabel(__('crud.shop_items.purchase_confirmation_button'))
                    ->action(function (ShopItem $record): void {
                        $mutation = -$record->cost_in_credits;
                        /** @var \App\Models\User */
                        $user = auth()->user();

                        if ($user->credits < $record->cost_in_credits) {
                            Notification::make()
                                ->title(__('crud.shop_items.purchase_failed', ['reason' => __('crud.shop_items.purchase_failed_reasons.insufficient_credits')]))
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($record->userHasMaximum(auth()->user())) {
                            Notification::make()
                                ->title(__('crud.shop_items.purchase_failed', ['reason' => __('crud.shop_items.purchase_failed_reasons.max_per_user')]))
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($record->required_type !== null && $record->required_type !== $user->type) {
                            Notification::make()
                                ->title(__('crud.shop_items.purchase_failed', ['reason' => __('crud.shop_items.required_type_options.' . $record->required_type)]))
                                ->danger()
                                ->send();
                            return;
                        }

                        CreditLog::mutateWithTransaction(
                            receiver: $user,
                            sender: null,
                            amount: $mutation,
                            reason: __('crud.shop_items.purchase_log_reason', ['item' => $record->name]),
                            mutator: function() use($user, $record, $mutation) {
                                $user->credits += $mutation;
                                $user->credits = max(0, min($user->credits, PHP_INT_MAX));
                                $user->save();

                                $this->dispatch('shop-item-purchased', shopItem: $record);

                                $record->purchaseFor($user);

                                Notification::make()
                                    ->title(__('crud.shop_items.purchase_success', ['item' => $record->name]))
                                    ->success()
                                    ->send();
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-shop-items');
    }
}
