<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ValveResource\Pages;
use App\Models\Valve;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ValveResource extends Resource
{
    protected static ?string $model = Valve::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Irrigation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('valve_number')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue((int) config('irrigation.valve_count', 4))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('valve_number')
                    ->label('Valve')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_activated_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('valve_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListValves::route('/'),
            'edit' => Pages\EditValve::route('/{record}/edit'),
        ];
    }
}
