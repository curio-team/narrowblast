<?php

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSlide extends EditRecord
{
    protected static string $resource = SlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // If the slide is not approved, check if we're on screens. If so, remove those screen slides.
        if ($this->record->approved_at === null) {
            $this->record->screenSlides()->delete();
        }
    }
}
