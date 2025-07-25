<?php

namespace App\Filament\Clusters\Settings\Resources\CategoryResource\Pages;

use App\Filament\Clusters\Settings\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn($record): bool => $record->name !== 'Transfer keluar' && $record->name !== 'Transfer masuk'),
        ];
    }
}
