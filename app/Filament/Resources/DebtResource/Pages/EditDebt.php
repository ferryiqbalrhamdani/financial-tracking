<?php

namespace App\Filament\Resources\DebtResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Category;
use App\Filament\Resources\DebtResource;
use Filament\Resources\Pages\EditRecord;

class EditDebt extends EditRecord
{
    protected static string $resource = DebtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        $tipeTransaksi = null;
        $description = null;
        $kategori =  Category::all();
        $dateWithTime = Carbon::parse($data['start_date'])->setTimeFrom(Carbon::now());

        // Sesuaikan nilai amount dan tipe transaksi berdasarkan jenis
        if ($record->type === 'hutang') {
            $kategori = $kategori->where('name', 'Transfer masuk')->first();
            $description = 'Hutang ke ' . $record->name;
            $tipeTransaksi = 'Pemasukan';
            $amount = abs($record->amount);
        } elseif ($record->type === 'piutang') {
            $kategori = $kategori->where('name', 'Tagihan & utilitas')->first();
            $description = 'Piutang ke ' . $record->name;
            $tipeTransaksi = 'Pengeluaran';
            $amount = -abs($record->amount);
        }

        // Ambil transaksi pertama terkait dan update
        $transaction = $record->transactions();


        if ($transaction) {
            $transaction->update([
                'user_id' => $record->user_id,
                'category_id' => $kategori->id,
                'account_id' => $record->account_id,
                'tipe_transaksi' => $tipeTransaksi,
                'amount' => $amount,
                'description' => $description,
                'date' => $dateWithTime,
            ]);
        }
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
