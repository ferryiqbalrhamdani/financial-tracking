<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pemasukan';
    protected static ?string $maxHeight = '300px';
    protected static string $color = 'success'; // hijau untuk pemasukan
    public ?string $filter = 'year'; // Default filter set to 'year'

    protected static ?int $sort = 4; // tampil setelah ExpensesChart

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari Ini',
            'week' => 'Minggu Terakhir',
            'month' => 'Bulan Terakhir',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter ?? 'year';
        $dateRange = $this->getDateRangeByFilter($activeFilter);
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $userId = Auth::id();

        $labels = [];
        $values = [];

        if ($activeFilter === 'today') {
            // per jam: 0..23
            $rows = Transaction::selectRaw('EXTRACT(HOUR FROM "date") as period, SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('tipe_transaksi', 'Pemasukan')
                ->whereBetween('date', [$start, $end])
                ->groupBy(DB::raw('EXTRACT(HOUR FROM "date")'))
                ->pluck('total', 'period');

            for ($h = 0; $h <= 23; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $val = isset($rows[$h]) ? (float) $rows[$h] : 0;
                $values[] = $val;
            }
        } elseif ($activeFilter === 'week' || $activeFilter === 'month') {
            // per hari
            $period = CarbonPeriod::create($start->startOfDay(), $end->startOfDay());
            $rows = Transaction::selectRaw('CAST("date" AS DATE) as period, SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('tipe_transaksi', 'Pemasukan')
                ->whereBetween('date', [$start, $end])
                ->groupBy(DB::raw('CAST("date" AS DATE)'))
                ->pluck('total', 'period');

            foreach ($period as $day) {
                $key = $day->toDateString();
                $labels[] = $day->format('d M');
                $val = isset($rows[$key]) ? (float) $rows[$key] : 0;
                $values[] = $val;
            }
        } else { // year
            $year = $start->year;
            $rows = Transaction::selectRaw('EXTRACT(MONTH FROM "date") as period, SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('tipe_transaksi', 'Pemasukan')
                ->whereYear('date', $year)
                ->groupBy(DB::raw('EXTRACT(MONTH FROM "date")'))
                ->pluck('total', 'period');

            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create($year, $m, 1)->format('M');
                $val = isset($rows[$m]) ? (float) $rows[$m] : 0;
                $values[] = $val;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pemasukan',
                    'data' => $values,
                    'fill' => true,
                    'tension' => 0.3,
                    'borderColor' => '#22c55e', // hijau
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)', // hijau transparan
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDateRangeByFilter(string $filter): array
    {
        switch ($filter) {
            case 'today':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
            case 'year':
            default:
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear(),
                ];
        }
    }

    protected function formatLabel(string $date, string $filter): string
    {
        if ($filter === 'year') {
            return Carbon::parse($date)->format('M');
        } elseif ($filter === 'today') {
            return Carbon::parse($date)->format('H:i');
        }

        return Carbon::parse($date)->format('d M');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
