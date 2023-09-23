<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\CreditLog;
use App\Models\ShopItem;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->disabled()
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->disabled()
                    ->readOnly()
                    ->required(),
                Forms\Components\Repeater::make('shopItemUsers')
                    ->relationship()
                    ->orderColumn(false)
                    ->schema(function(User $user) {
                        return [
                            Forms\Components\Select::make('shop_item_id')
                                ->relationship('shopItem', 'name')
                                ->searchable(['name'])
                                ->preload()
                                ->required(),
                        ];
                    })
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['cost_in_credits'] = 0;

                        return $data;
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('credits')
                    ->numeric(0, ',', '.')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('giveCredits')
                    ->label(ucfirst(__('crud.user.change_credits')))
                    ->icon('heroicon-o-currency-dollar')
                    ->form(function(User $record) {
                        return [
                            Forms\Components\TextInput::make('changeCredits')
                                ->label(__('crud.user.change_in_credits', [
                                    'user' => $record->name,
                                ]))
                                ->required(),
                            Forms\Components\Placeholder::make('tip')
                                ->hint(__('crud.user.change_in_credits_description'))
                        ];
                    })
                    ->requiresConfirmation()
                    ->action(function (array $data, User $record): void {
                        CreditLog::mutateWithTransaction(
                            receiver: $record,
                            sender: auth()->user(),
                            amount: (int)$data['changeCredits'],
                            reason: 'administrator panel change',
                            mutator: function() use($record, $data) {
                                $record->credits += $data['changeCredits'];
                                $record->credits = max(0, min($record->credits, PHP_INT_MAX));
                                $record->save();
                            });
                        }
                    ),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->emptyStateActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
