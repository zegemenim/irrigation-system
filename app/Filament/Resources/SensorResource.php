<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SensorResource\Pages;
use App\Models\Relay;
use App\Models\Sensor;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SensorResource extends Resource
{
    protected static ?string $model = Sensor::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Relay System';

    protected static ?string $navigationLabel = 'Hareket Sensörleri';

    protected static ?string $modelLabel = 'Hareket Sensörü';

    protected static ?string $pluralModelLabel = 'Hareket Sensörleri';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('sensor_number')
                    ->label('Sensör No')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('name')
                    ->label('İsim')
                    ->required()
                    ->maxLength(255),
                CheckboxList::make('relays')
                    ->label('Bu sensör hareket algılayınca açılacak projektörler')
                    ->relationship('relays', 'name')
                    ->options(
                        Relay::query()
                            ->orderBy('relay_number')
                            ->pluck('name', 'id')
                    )
                    ->columns(2)
                    ->helperText('Birden fazla projektör seçebilirsiniz.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sensor_number')
                    ->label('Sensör No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('İsim')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('relays.name')
                    ->label('Eşlenen Projektörler')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('sensor_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSensors::route('/'),
            'edit' => Pages\EditSensor::route('/{record}/edit'),
        ];
    }
}
