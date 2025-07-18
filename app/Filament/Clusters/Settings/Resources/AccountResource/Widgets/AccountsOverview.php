<?php

namespace App\Filament\Clusters\Settings\Resources\AccountResource\Widgets;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class AccountsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2; // Tambahkan ini agar size tiap stat mengecil (default biasanya 3)
    }

    protected function getStats(): array
    {
        $allAccounts = Account::where('user_id', Auth::user()->id)->with('transactions')->get();

        $includedAccounts = $allAccounts->where('exclude_from_total', false);
        $excludedAccounts = $allAccounts->where('exclude_from_total', true);

        // Total Saldo Akhir (Include)
        $includeStartingBalance = $includedAccounts->sum('starting_balance');
        $includePemasukan = $includedAccounts->flatMap->transactions
            ->where('tipe_transaksi', 'Pemasukan')
            ->sum('amount');
        $includePengeluaran = $includedAccounts->flatMap->transactions
            ->where('tipe_transaksi', 'Pengeluaran')
            ->sum('amount');
        $includeTotal = $includeStartingBalance + ($includePemasukan + $includePengeluaran);

        // Total Saldo Akhir (Exclude)
        $excludeStartingBalance = $excludedAccounts->sum('starting_balance');
        $excludePemasukan = $excludedAccounts->flatMap->transactions
            ->where('tipe_transaksi', 'Pemasukan')
            ->sum('amount');
        $excludePengeluaran = $excludedAccounts->flatMap->transactions
            ->where('tipe_transaksi', 'Pengeluaran')
            ->sum('amount');
        $excludeTotal = $excludeStartingBalance + ($excludePemasukan - $excludePengeluaran);

        
        return [
            Stat::make('Saldo Termasuk (Include)', 'Rp ' . number_format($includeTotal, 2, ',', '.'))
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->extraAttributes([
                'class' => 'cursor-pointer text-xs',
            ]), // kecilkan font,
            
            Stat::make('Saldo Tidak Dihitung (Exclude)', 'Rp ' . number_format($excludeTotal, 2, ',', '.'))
            ->icon('heroicon-o-eye-slash')
            ->color('gray')
            ->extraAttributes(['class' => 'cursor-pointer']), // kecilkan font,
        ];
    }


}
