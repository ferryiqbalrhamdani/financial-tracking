<?php

namespace App\Filament\Clusters\Settings\Resources\BudgetResource\Pages;

use App\Filament\Clusters\Settings\Resources\BudgetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudget extends EditRecord
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
