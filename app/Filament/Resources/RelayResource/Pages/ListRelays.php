<?php

namespace App\Filament\Resources\RelayResource\Pages;

use App\Filament\Resources\RelayResource;
use Filament\Resources\Pages\ListRecords;

class ListRelays extends ListRecords
{
    protected static string $resource = RelayResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
