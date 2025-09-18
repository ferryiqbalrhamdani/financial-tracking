<?php

namespace App\Filament\Clusters\Settings\Resources\CategoryResource\Pages;

use Filament\Actions;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Clusters\Settings\Resources\CategoryResource;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'semua data' => Tab::make()
            //     ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id))
            //     ->badge(count(Category::where('user_id', Auth::user()->id)->get())),
            'Pengeluaran' => Tab::make()
            ->badge(count(Category::where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pengeluaran')->get()))
            ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pengeluaran')),
            'Pemasukan' => Tab::make()
                ->badge(count(Category::where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pemasukan')->get()))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pemasukan')),
        ];
    }
}
