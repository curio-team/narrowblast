<?php

namespace App\Filament\Resources\CreditLogResource\Pages;

use App\Filament\Resources\CreditLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditLog extends EditRecord
{
    protected static string $resource = CreditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
