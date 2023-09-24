<?php

namespace App\Livewire;

use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class UploadSlide extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('path')
                    ->disk(Slide::STORAGE_DISK)
                    ->directory(Slide::FILE_DIRECTORY)
                    ->acceptedFileTypes(Slide::ACCEPTED_FILE_TYPES)
                    ->required()
                    ->downloadable()
                    ->columnSpan(2),
            ])
            ->statePath('data')
            ->columns(3)
            ->model(Slide::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = Slide::create(array_merge($data, [
            'user_id' => auth()->id(),
        ]));

        $this->form->model($record)->saveRelationships();
        $this->redirect(route('slides.manage').'?tab=1#your_slides');
    }

    public function render(): View
    {
        return view('livewire.upload-slide');
    }
}
