<?php

namespace App\Filament\Resources\UserResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Account;
use App\Models\Category;
use App\Filament\Resources\UserResource;
use App\Models\Localization;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeCreate(): void
    {
        // dd($this->data);
    }

    protected function afterCreate(): void
    {
        Account::insert([
            [
                'name' => 'Dompet saya',
                'user_id' => $this->record['id'],
                'starting_balance' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'BCA',
                'user_id' => $this->record['id'],
                'starting_balance' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        Category::insert([
            [
                'tipe_transaksi' => 'Pengeluaran',
                'name' => '⬆️ Transfer keluar',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pengeluaran',
                'name' => '🥗 Makanan, Minuman & Belanja',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pengeluaran',
                'name' => '🧾 Tagihan & utilitas',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pengeluaran',
                'name' => '🚗 Transportasi',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pengeluaran',
                'name' => '⚽ Olahraga',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pemasukan',
                'name' => '⬇️ Transfer masuk',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'tipe_transaksi' => 'Pemasukan',
                'name' => '💸 Gaji',
                'user_id' => $this->record['id'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        Localization::create([
            'user_id' => $this->record['id'],
        ]);
    }
}
