<?php

namespace App\Filament\Clusters\Settings\Resources\AccountResource\Pages;

use App\Filament\Clusters\Settings\Resources\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return AccountResource::getWidgets();
    }
}
