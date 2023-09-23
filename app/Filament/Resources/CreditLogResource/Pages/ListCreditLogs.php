<?php

namespace App\Filament\Resources\CreditLogResource\Pages;

use App\Filament\Resources\CreditLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditLogs extends ListRecords
{
    protected static string $resource = CreditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
