<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pengeluaran';
    protected static ?string $maxHeight = '300px';
    protected static string $color = 'danger';
    public ?string $filter = 'year'; // Default filter set to 'year'

    protected static ?int $sort = 3;

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
                ->where('tipe_transaksi', 'Pengeluaran')
                ->where('ex_report', false) // hanya ambil yang ex_report = false
                ->whereBetween('date', [$start, $end])
                ->groupBy(DB::raw('EXTRACT(HOUR FROM "date")'))
                ->pluck('total', 'period');

            for ($h = 0; $h <= 23; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $val = isset($rows[$h]) ? (float) $rows[$h] : 0;
                $values[] = abs($val);
            }
        } elseif ($activeFilter === 'week' || $activeFilter === 'month') {
            // per hari between start..end
            $period = CarbonPeriod::create($start->startOfDay(), $end->startOfDay());
            $rows = Transaction::selectRaw('CAST("date" AS DATE) as period, SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('tipe_transaksi', 'Pengeluaran')
                ->whereBetween('date', [$start, $end])
                 ->where('ex_report', false) // filter ex_report
                ->groupBy(DB::raw('CAST("date" AS DATE)'))
                ->pluck('total', 'period');

            foreach ($period as $day) {
                $key = $day->toDateString(); // 'YYYY-MM-DD'
                $labels[] = $day->format('d M'); // e.g. 12 Sep
                $val = isset($rows[$key]) ? (float) $rows[$key] : 0;
                $values[] = abs($val);
            }
        } else { // year
            // per bulan (1..12)
            $year = $start->year;
            $rows = Transaction::selectRaw('EXTRACT(MONTH FROM "date") as period, SUM(amount) as total')
                ->where('user_id', $userId)
                ->where('tipe_transaksi', 'Pengeluaran')
                 ->where('ex_report', false) // filter ex_report
                ->whereYear('date', $year)
                ->groupBy(DB::raw('EXTRACT(MONTH FROM "date")'))
                ->pluck('total', 'period');

            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create($year, $m, 1)->format('M'); // Jan, Feb, ...
                $val = isset($rows[$m]) ? (float) $rows[$m] : 0;
                $values[] = abs($val);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran',
                    'data' => $values, // numeric (positive)
                    'fill' => true,
                    'tension' => 0.3,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
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
            return Carbon::parse($date)->format('M'); // bulan
        } elseif ($filter === 'today') {
            return Carbon::parse($date)->format('H:i'); // jam
        }

        return Carbon::parse($date)->format('d M'); // tanggal
    }

    protected function getType(): string
    {
        return 'line';
    }
}
