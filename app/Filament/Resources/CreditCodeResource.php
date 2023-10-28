<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditCodeResource\Pages;
use App\Models\CreditCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;

class CreditCodeResource extends Resource
{
    protected static ?string $model = CreditCode::class;

    protected static ?string $navigationGroup = 'Credits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('credits')
                    ->label('Credit value for code')
                    ->minValue(1)
                    ->maxValue(10000)
                    ->default(100)
                    ->numeric()
                    ->autofocus()
                    ->required(),
                Forms\Components\TextInput::make('amount_of_codes')
                    ->label('Amount of Codes')
                    ->minValue(1)
                    ->maxValue(1000)
                    ->default(1)
                    ->numeric()
                    ->autofocus()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credits')
                    ->numeric(0, ',', '.')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemer.id')
                    ->default('nobody')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('redeemed_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('printed_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                Tables\Actions\BulkAction::make('createCodes')
                    ->label('Print Credit Codes')
                    ->icon('heroicon-o-printer')
                    ->action(function (Collection $records) {
                        DB::beginTransaction();

                        $time = now();

                        $records->each(function ($record) use ($time) {
                            $record->printed_at = $time;
                            $record->save();
                        });

                        DB::commit();

                        return response()->streamDownload(function () use ($records) {
                            echo Pdf::loadHtml(
                                Blade::render('exports.credit-codes', ['creditCodes' => $records])
                            )
                            ->stream();
                        }, $time . '.pdf');
                    })
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(function (CreditCode $record) {
                return $record->redeemed_at === null
                    && $record->printed_at === null;
            })
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
            'index' => Pages\ListCreditCodes::route('/'),
        ];
    }
}
