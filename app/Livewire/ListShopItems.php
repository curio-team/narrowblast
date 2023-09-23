<?php

namespace App\Livewire;

use App\Models\CreditLog;
use App\Models\ShopItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListShopItems extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(ShopItem::query())
            ->columns([
                Tables\Columns\ImageColumn::make('image_path'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->formatStateUsing(function (string $state): string {
                        return \Str::limit($state, 20);
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('cost_in_credits')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_per_user')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('purchaseItem')
                    ->label(ucfirst(__('crud.shop_items.purchase')))
                    ->icon('heroicon-o-currency-dollar')
                    ->requiresConfirmation()
                    ->action(function (ShopItem $record): void {
                        $mutation = -$record->cost_in_credits;
                        /** @var \App\Models\User */
                        $user = auth()->user();

                        CreditLog::mutateWithTransaction(
                            receiver: $user,
                            sender: null,
                            amount: $mutation,
                            reason: __('crud.shop_items.purchase_log_reason', ['item' => $record->name]),
                            mutator: function() use($user, $mutation) {
                                $user->credits += $mutation;
                                $user->credits = max(0, min($user->credits, PHP_INT_MAX));
                                $user->save();
                            });
                        }
                    ),
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
