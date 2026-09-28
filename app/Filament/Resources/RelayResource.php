<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelayResource\Pages;
use App\Models\Relay;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RelayResource extends Resource
{
    protected static ?string $model = Relay::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Relay System';

    protected static ?string $navigationLabel = 'Projektörler';

    protected static ?string $modelLabel = 'Projektör';

    protected static ?string $pluralModelLabel = 'Projektörler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('relay_number')
                    ->label('Röle No')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('relay_number')
                    ->label('Röle No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('İsim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Durum')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_activated_at')
                    ->label('Son Açılma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('relay_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRelays::route('/'),
            'edit' => Pages\EditRelay::route('/{record}/edit'),
        ];
    }
}
