<?php

namespace App\Filament\Resources\CreditCodeResource\Pages;

use App\Filament\Resources\CreditCodeResource;
use App\Models\CreditCode;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditCodes extends ListRecords
{
    protected static string $resource = CreditCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createCodes')
                    ->label('Create Credit Codes')
                    ->icon('heroicon-o-currency-dollar')
                    ->form(function() {
                        return [
                            Forms\Components\TextInput::make('credits')
                                ->label('Credits')
                                ->hint('The amount of credits each code should give')
                                ->numeric()
                                ->default(100)
                                ->minValue(1)
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->hint('The amount of codes to generate')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                        ];
                    })
                    ->requiresConfirmation()
                    ->action(function (array $data): void {
                        $amount = (int)$data['amount'];
                        $credits = (int)$data['credits'];

                        for ($i = 0; $i < $amount; $i++) {
                            $code = new CreditCode();
                            $code->code = CreditCode::generateCode();
                            $code->credits = $credits;
                            $code->created_by = auth()->user()->id;
                            $code->save();
                        }
                    }),
        ];
    }
}
