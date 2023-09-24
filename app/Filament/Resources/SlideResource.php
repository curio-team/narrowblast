<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlideResource\Pages;
use App\Filament\Resources\SlideResource\RelationManagers;
use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static ?string $navigationGroup = 'Narrowcasting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->autofocus()
                    ->required(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(['name', 'id'])
                    ->preload()
                    ->required(),

                Forms\Components\FileUpload::make('path')
                    ->disk(Slide::STORAGE_DISK)
                    ->directory(Slide::FILE_DIRECTORY)
                    ->acceptedFileTypes(Slide::ACCEPTED_FILE_TYPES)
                    ->required()
                    ->downloadable(),

                Forms\Components\Toggle::make('approved_at')
                    ->default(false)
                    ->live()
                    ->formatStateUsing(function (?Slide $record): ?string {
                        return $record?->approved_at !== null;
                    })
                    // Frustratingly doesn't work on create, so lets just hide the approved button for now
                    ->hidden(function (Get $get):bool {
                        return $get('id') === null;
                    })
                    ->dehydrateStateUsing(function (?Slide $record, ?bool $state) {
                        $record->approved_at = $state ? now() : null;
                        $record->approver_id = $state ? auth()->id() : null;
                    }),

                // Allows adding to screens immediately
                Forms\Components\Repeater::make('screenSlides')
                    ->relationship()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Select::make('screen_id')
                            ->relationship('screen', 'name')
                            ->searchable(['name'])
                            ->preload()
                            ->required(),
                    ])
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['activator_id'] = auth()->id();

                        return $data;
                    })
                    ->hidden(function (Get $get):bool {
                        return $get('approved_at') == false;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(ucfirst(__('crud.slides.title')))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(ucfirst(__('validation.attributes.created_at')))
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(ucfirst(__('validation.attributes.username')))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('approved')
                    ->label(ucfirst(__('crud.slides.approved')))
                    ->sortable()
                    ->state(function(Slide $record) {
                        return $record->approved_at !== null;
                    })
                    ->updateStateUsing(function(Slide $record, bool $state) {
                        $record->approved_at = $state ? now() : null;
                        $record->approver_id = $state ? auth()->id() : null;

                        if (!$state) {
                            $record->screenSlides()->delete();
                        }

                        $record->save();

                        return $record;
                    }),
                Tables\Columns\TextColumn::make('screen_count')
                    ->label(ucfirst(__('crud.slides.screen_count')))
                    ->sortable()
                    ->state(function (Slide $record) {
                        return $record->screens()->count();
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')
                    ->label(__('crud.slides.approved'))
                    ->query(function (Builder $query) {
                        return $query->whereNotNull('approved_at');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('secondary')
                    ->label(ucfirst(__('crud.slides.preview')))
                    ->url(fn (Slide $record): string => route('slides.preview', $record).'?cachebust='.time())
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
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
