<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Filament\Resources\TransactionResource\Pages\ManageTransactions;

class TransactionOverview extends BaseWidget
{
    use InteractsWithPageTable;

    // protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ManageTransactions::class;
    }
    
    protected function getHeading(): ?string
    {
        return 'Ringkasan Transaksi';
    }

    protected function getDescription(): ?string
    {
        return 'Laporan singkat pemasukan, pengeluaran, dan saldo akhir Anda.';
    }


    protected function getStats(): array
    {
        // $query = $this->getPageTableQuery();
        $query = Transaction::where('user_id', Auth::user()->id);
        $accounts = Account::where('user_id', Auth::id())->where('exclude_from_total', false)->get();
        $transactions = $query->where('user_id', Auth::id())
                                    ->whereHas('account', function ($query) {
                                        $query->where('exclude_from_total', false);
                                    })
                                    ->get();

        $totalAccountBalance = $accounts->sum('starting_balance');
        $totalIn = $transactions->where('tipe_transaksi', 'Pemasukan')->sum('amount');
        $totalEx = $transactions->where('tipe_transaksi', 'Pengeluaran')->sum('amount');
        
        $totalBalance = $totalAccountBalance + ($totalIn + $totalEx);
        return [
            Stat::make('Total Balance', 'Rp ' . number_format($totalBalance, 2, ',', '.'))
                // ->description($this->getDesc($totalIncome))
                // ->descriptionIcon($this->getDescriptionIcon($totalIncome))
                ->chart($this->getMonthlyBalanceChart()) // <- DIUBAH DI SINI
                ->color('success')
                ->icon('heroicon-o-wallet')
                ->extraAttributes([
                    'class' => 'shadow-lg',
                ]),

            Stat::make('Total Expense', 'Rp ' . number_format($totalEx, 2, ',', '.'))
                ->description('Arus Keluar')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->icon('heroicon-o-arrow-down-circle')
                ->extraAttributes([
                    'class' => 'shadow-lg',
                ]),

            Stat::make('Total Income', 'Rp ' . number_format($totalIn, 2, ',', '.'))
                ->description('Arus Masuk')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-arrow-up-circle'),
        ];
    }

    // ✅ DITAMBAHKAN: Ambil saldo bulanan (12 bulan terakhir)
    protected function getMonthlyBalanceChart(): array
    {
        $userId = Auth::id();
        $months = collect(range(0, 11))
            ->map(fn($i) => Carbon::now()->subMonths($i)->startOfMonth())
            ->reverse();

        $accounts = Account::where('user_id', $userId)
            ->where('exclude_from_total', false)
            ->get();

        $startingBalance = $accounts->sum('starting_balance');
        $currentBalance = $startingBalance;
        $balances = [];

        foreach ($months as $startOfMonth) {
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $income = Transaction::where('user_id', $userId)
                ->where('tipe_transaksi', 'Pemasukan')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $expense = Transaction::where('user_id', $userId)
                ->where('tipe_transaksi', 'Pengeluaran')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $currentBalance += $income + $expense;
            $balances[] = $currentBalance;
        }

        return $balances;
    }



    protected function getDesc(float $value): string
    {
        if ($value >= 0 && $value < 1000000) {
            return 'Stabil';
        } elseif ($value >= 1000000) {
            return 'Meningkat pesat';
        } else {
            return 'Menurun';
        }
    }

    protected function getDescriptionIcon(float $value): string
    {
        if ($value >= 0 && $value < 1000000) {
            return 'heroicon-m-arrows-right-left';
        } elseif ($value >= 1000000) {
            return 'heroicon-m-arrow-trending-up';
        } else {
            return 'heroicon-m-arrow-trending-down';
        }
    }

}
