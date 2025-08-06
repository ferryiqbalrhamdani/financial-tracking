<?php

namespace App\Filament\Resources\DebtResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\DebtResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDebt extends CreateRecord
{
    protected static string $resource = DebtResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        if ($data['type'] === 'hutang') {
            $data['amount'] = abs($data['amount']);
        } elseif ($data['type'] === 'piutang') {
            $data['amount'] = -abs($data['amount']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->data;
        $tipeTransaksi = null;
        $description = null;
        $kategori =  Category::all();
        $dateWithTime = Carbon::parse($data['start_date'])
            ->setTimeFrom(Carbon::now()); // ambil jam, menit, detik saat ini

        if ($record['type'] === 'hutang') {
            $kategori = $kategori->where('name', 'Transfer masuk')->first();
            $description = 'Hutang ke ' . $record['name'];
            $tipeTransaksi = 'Pemasukan';
        } elseif ($record['type'] === 'piutang') {
            $kategori = $kategori->where('name', 'Transfer keluar')->first();
            $description = 'Piutang ke ' . $record['name'];
            $tipeTransaksi = 'Pengeluaran';
        }

        $record->transactions()->create([
            'user_id' => $record['user_id'],
            'account_id' => $record['account_id'],
            'category_id' => $kategori->id,
            'tipe_transaksi' => $tipeTransaksi,
            'amount' => $record['amount'],
            'description' => $description,
            'date' => $dateWithTime,
        ]);
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
