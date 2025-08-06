<?php

namespace App\Filament\Resources\DebtResource\Widgets;

use App\Models\Debt;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DebtsOverview extends BaseWidget
{
    protected function getColumns(): int
    {
        return 2; // Tambahkan ini agar size tiap stat mengecil (default biasanya 3)
    }

    protected function getStats(): array
    {
        $data = Debt::with('transactions') // penting agar relasi di-load
            ->where('user_id', Auth::id())
            ->get();

        $hutang = $data->where('type', 'hutang');
        $piutang = $data->where('type', 'piutang');

        // Ambil semua transaksi dari hutang dan piutang
        $totalHutang = $hutang->flatMap(function ($debt) {
            return $debt->transactions;
        })->where('tipe_transaksi', 'Pengeluaran')->sum('amount');

        $totalPiutang = $piutang->flatMap(function ($debt) {
            return $debt->transactions;
        })->where('tipe_transaksi', 'Pemasukan')->sum('amount');

        return [
            Stat::make('Total Hutang', 'Rp ' . number_format(
                $hutang->sum('amount') + $totalHutang,
                2,
                ',',
                '.'
            ))
                ->icon('heroicon-o-arrow-down-circle'),

            Stat::make('Total Piutang', 'Rp ' . number_format(
                $piutang->sum('amount') + $totalPiutang,
                2,
                ',',
                '.'
            ))
                ->icon('heroicon-o-arrow-up-circle'),
        ];
    }
}
