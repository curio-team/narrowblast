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
    public bool $isFinalized;

    public function mount(bool $isApproved = false, bool $isFinalized = true): void
    {
        $this->isApproved = $isApproved;
        $this->isFinalized = $isFinalized;
    }

    public function table(Table $table): Table
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $query = $user->slides()->getQuery();

        if ($this->isApproved) {
            $query->whereNotNull('approved_at');
        } else {
            $query->whereNull('approved_at');
        }

        if ($this->isFinalized) {
            $query->whereNotNull('finalized_at');
        } else {
            $query->whereNull('finalized_at');
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
                ...(
                    $this->isFinalized === false
                    ?   [
                        Tables\Actions\Action::make('ask_for_approval')
                            ->icon('heroicon-o-check-badge')
                            ->color('secondary')
                            ->label(ucfirst(__('crud.slides.ask_for_approval')))
                            ->action(function (Slide $record) {
                                $record->finalized_at = now();
                                $record->save();

                                return redirect(route('slides.manage') . '?tab=2#your_slides');
                            }),
                        ]
                    : []
                ),
                ...(
                    $this->isFinalized === true && $this->isApproved === false
                    ?   [
                        Tables\Actions\Action::make('revoke_ask_for_approval')
                            ->icon('heroicon-o-check-badge')
                            ->color('secondary')
                            ->label(ucfirst(__('crud.slides.revoke_ask_for_approval')))
                            ->action(function (Slide $record) {
                                $record->finalized_at = null;
                                $record->save();

                                return redirect(route('slides.manage') . '?tab=1#your_slides');
                            }),
                        ]
                    : []
                ),
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('secondary')
                    ->label(ucfirst(__('crud.slides.preview')))
                    ->url(fn (Slide $record): string => route('slides.preview', $record))
                    ->openUrlInNewTab(),
                ...(
                    $this->isApproved === false
                    ? [ Tables\Actions\DeleteAction::make() ]
                    : []
                )
            ]);
    }

    public function render(): View
    {
        return view('livewire.list-slides');
    }
}
