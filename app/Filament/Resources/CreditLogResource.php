<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditLogResource\Pages;
use App\Filament\Resources\CreditLogResource\RelationManagers;
use App\Models\CreditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditLogResource extends Resource
{
    protected static ?string $model = CreditLog::class;

    protected static ?string $navigationGroup = 'Credits';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // User $receiver, ?User $sender, int $amount, string $reason
                Tables\Columns\TextColumn::make('receiver.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sender.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(0, ',', '.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListCreditLogs::route('/'),
        ];
    }
}
