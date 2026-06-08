<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use App\Models\Valve;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Irrigation';

    protected static ?string $navigationLabel = 'Schedules';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('valve_id')
                    ->label('Valve')
                    ->options(fn (): array => Valve::query()
                        ->orderBy('valve_number')
                        ->get()
                        ->mapWithKeys(fn (Valve $valve): array => [
                            $valve->id => "Valve {$valve->valve_number} - {$valve->name}",
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                CheckboxList::make('days_of_week')
                    ->label('Days of Week')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ])
                    ->columns(2)
                    ->gridDirection('row')
                    ->required(),
                TimePicker::make('start_time')
                    ->label('Start Time')
                    ->seconds(false)
                    ->native(false)
                    ->required(),
                TextInput::make('duration_minutes')
                    ->label('Duration Minutes')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(1440)
                    ->step(1)
                    ->required(),
                TextInput::make('target_hz')
                    ->label('Target Hz')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(55)
                    ->step(0.1),
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('valve.valve_number')
                    ->label('Valve')
                    ->formatStateUsing(fn (int|string|null $state, Schedule $record): string => "Valve {$state} - {$record->valve?->name}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('days_of_week')
                    ->label('Days')
                    ->getStateUsing(fn (Schedule $record): string => collect($record->days_of_week ?? [])
                        ->map(fn (string $day): string => ucfirst($day))
                        ->implode(', ') ?: collect($record->cycle_valve_order ?? [])
                        ->map(fn (int|string $valveNumber): string => "Valve {$valveNumber}")
                        ->implode(' → '))
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_hz')
                    ->label('Hz')
                    ->suffix(' Hz')
                    ->placeholder('Default')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Enabled'),
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
            ->defaultSort('start_time');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
