<?php

namespace App\Filament\Resources\RelayResource\Pages;

use App\Filament\Resources\RelayResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRelay extends EditRecord
{
    protected static string $resource = RelayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
