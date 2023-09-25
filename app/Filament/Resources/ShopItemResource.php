<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopItemResource\Pages;
use App\Filament\Resources\ShopItemResource\RelationManagers;
use App\Models\ShopItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShopItemResource extends Resource
{
    protected static ?string $model = ShopItem::class;

    protected static ?string $navigationGroup = 'Credits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->autofocus()
                    ->required(),

                Forms\Components\TextInput::make('unique_id')
                    ->maxLength(255)
                    ->unique('shop_items', 'unique_id', function (?ShopItem $record) {
                        return $record;
                    })
                    ->disabled(function (?ShopItem $record) {
                        return $record?->exists ?? false;
                    })
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->maxLength(1024)
                    ->required(),

                Forms\Components\FileUpload::make('image_path')
                    ->disk(ShopItem::STORAGE_DISK)
                    ->directory(ShopItem::FILE_DIRECTORY)
                    ->image()
                    ->required()
                    ->downloadable(),

                Forms\Components\TextInput::make('cost_in_credits')
                    ->label(ucfirst(__('crud.shop_items.cost_in_credits')))
                    ->numeric()
                    ->default(100)
                    ->minValue(0)
                    ->maxValue(9999999999)
                    ->required(),

                Forms\Components\Checkbox::make('limit_purchases')
                    ->label(ucfirst(__('crud.shop_items.limit_purchases')))
                    ->live()
                    ->formatStateUsing(function (?ShopItem $record, ?bool $state): ?bool {
                        return $record?->max_per_user !== null;
                    })
                    ->afterStateUpdated(function (Set $set, ?bool $state) {
                        $set('max_per_user', $state ? 1 : null);
                    })
                    ->default(false),

                Forms\Components\TextInput::make('max_per_user')
                    ->label(ucfirst(__('crud.shop_items.max_per_user')))
                    ->numeric()
                    ->default(null)
                    ->minValue(0)
                    ->maxValue(9999999999)
                    ->required(fn (Get $get): bool => $get('limit_purchases') === true),

                Forms\Components\Select::make('required_type')
                    ->label(ucfirst(__('crud.shop_items.required_type')))
                    ->placeholder(ucfirst(__('crud.shop_items.required_type_options.no_restriction')))
                    ->options([
                        'student' => ucfirst(__('crud.shop_items.required_type_options.student')),
                        'teacher' => ucfirst(__('crud.shop_items.required_type_options.teacher')),
                    ])
                    ->default(null),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk(ShopItem::STORAGE_DISK)
                    ->label(ucfirst(__('validation.attributes.image'))),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->formatStateUsing(function (string $state): string {
                        return \Str::limit($state, 50);
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('required_type')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListShopItems::route('/'),
            'create' => Pages\CreateShopItem::route('/create'),
            'edit' => Pages\EditShopItem::route('/{record}/edit'),
        ];
    }
}
