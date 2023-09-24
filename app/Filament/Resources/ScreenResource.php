<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScreenResource\Pages;
use App\Filament\Resources\ScreenResource\RelationManagers;
use App\Helpers\DateHelpers;
use App\Models\Screen;
use App\Models\ScreenSlide;
use DateTime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScreenResource extends Resource
{
    protected static ?string $model = Screen::class;

    protected static ?string $navigationGroup = 'Narrowcasting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->autofocus()
                    ->required(),

                // This code is disabled because:
                // Besides making screenslides, it should also add some data to the shopitemuser (see CustomSlideTime.php)
                //// Forms\Components\Repeater::make('slides')
                ////     ->relationship()
                ////     ->orderColumn('slide_order')
                ////     ->schema(function(Screen $screen) {
                ////         $defaultUntil = now()->addDays(7);
                ////         return [
                ////             Forms\Components\Select::make('slide_id')
                ////                 ->relationship('slide', 'title', function (Builder $query, ?ScreenSlide $record) use($screen) {
                ////                     $alreadySelectedExceptSelf = $screen->screenSlides->pluck('slide_id');

                ////                     if($record !== null) {
                ////                         $alreadySelectedExceptSelf = $alreadySelectedExceptSelf->filter(fn ($id) => $id !== $record->slide_id);
                ////                     }

                ////                     return $query->onlyApproved()
                ////                         ->whereNotIn('id', $alreadySelectedExceptSelf);
                ////                 })
                ////                 ->searchable(['title'])
                ////                 ->preload()
                ////                 ->required(),

                ////             Forms\Components\DateTimePicker::make('displays_from')
                ////                 ->label(ucfirst(__('crud.slides.displays_from')))
                ////                 ->default(now())
                ////                 ->minDate(function (?ScreenSlide $record) {
                ////                     if ($record?->isClean('displays_from')) {
                ////                         return null;
                ////                     }

                ////                     return now()->subMinute(5);
                ////                 })
                ////                 ->displayFormat(DateHelpers::getDateFormat())
                ////                 ->seconds(false)
                ////                 ->native(false)
                ////                 ->closeOnDateSelection()
                ////                 ->required(),

                ////             Forms\Components\Checkbox::make('display_forever')
                ////                 ->label(ucfirst(__('crud.slides.display_forever')))
                ////                 ->live()
                ////                 ->formatStateUsing(function (?ScreenSlide $record, ?bool $state): ?bool {
                ////                     return $record?->displays_until === null;
                ////                 })
                ////                 ->afterStateUpdated(function (Set $set, ?bool $state) use($defaultUntil) {
                ////                     $set('displays_until', $state ? null : $defaultUntil);
                ////                 })
                ////                 ->default(false),

                ////             Forms\Components\DateTimePicker::make('displays_until')
                ////                 ->label(ucfirst(__('crud.slides.displays_until')))
                ////                 ->live()
                ////                 ->default($defaultUntil)
                ////                 ->minDate(function (?ScreenSlide $record) {
                ////                     if ($record?->isClean('displays_until')) {
                ////                         return null;
                ////                     }

                ////                     return now();
                ////                 })
                ////                 ->afterStateUpdated(function (Set $set, ?string $state) {
                ////                     if ($state) {
                ////                         $set('display_forever', false);
                ////                     }
                ////                 })
                ////                 ->displayFormat(DateHelpers::getDateFormat())
                ////                 ->seconds(false)
                ////                 ->native(false)
                ////                 ->closeOnDateSelection()
                ////                 ->required(fn (Get $get): bool => $get('display_forever') === false),
                ////                 //->visible(fn (Get $get): bool => $get('display_forever') === false), // With this line uncommented the $set wont save :/ (it should.......)

                ////             Forms\Components\TextInput::make('slide_duration')
                ////                 ->label(ucfirst(__('crud.slides.slide_duration')))
                ////                 ->numeric()
                ////                 ->default(10)
                ////                 ->minValue(1)
                ////                 ->maxValue(60),
                ////         ];
                ////     })
                ////     ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                ////         $data['activator_id'] = auth()->id();

                ////         return $data;
                ////     })
                ////     ->columns([
                ////         'lg' => 5,
                ////         'md' => 2,
                ////     ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(ucfirst(__('validation.attributes.name')))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slides_count')
                    ->label(ucfirst(__('crud.slides.slides_count')))
                    ->sortable(true, function (Builder $query, string $direction) {
                        return $query->withCount('screenSlides')->orderBy('screen_slides_count', $direction);
                    })
                    ->state(function (Screen $record) {
                        return $record->screenSlides->count();
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(ucfirst(__('validation.attributes.updated_at')))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->color('secondary')
                    ->label(ucfirst(__('crud.screens.view')))
                    ->url(fn (Screen $record): string => route('slides.slideShow', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListScreens::route('/'),
            'create' => Pages\CreateScreen::route('/create'),
            'edit' => Pages\EditScreen::route('/{record}/edit'),
        ];
    }
}
