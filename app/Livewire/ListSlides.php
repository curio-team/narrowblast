<?php

namespace App\Livewire;

use App\Models\ShopItem;
use App\Models\Slide;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListSlides extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public bool $isApproved;

    public function mount(bool $isApproved = false): void
    {
        $this->isApproved = $isApproved;
    }

    public function table(Table $table): Table
    {
        $query = Slide::query();

        if ($this->isApproved) {
            $query->whereNotNull('approved_at');
        } else {
            $query->whereNull('approved_at');
        }

        $customSlideColumns = ShopItem::callUserShopItemMethods('getCustomSlideColumns', $this->isApproved)
            ->filter(fn ($column) => $column !== false)
            ->flatten()
            ->toArray();

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                ...(
                    $this->isApproved
                        ?   [
                                // Tables\Columns\TextColumn::make('approver_id')
                                //     ->searchable(),
                                Tables\Columns\TextColumn::make('approved_at')
                                    ->dateTime()
                                    ->sortable(),
                            ]
                        :   []
                ),
                ...$customSlideColumns,
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('secondary')
                    ->label(ucfirst(__('crud.slides.preview')))
                    ->url(fn (Slide $record): string => route('slides.preview', $record))
                    ->openUrlInNewTab(),
                // Tables\Actions\DeleteAction::make(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-slides');
    }
}
