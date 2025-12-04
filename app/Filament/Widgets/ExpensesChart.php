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
    public ?string $filter = 'year';

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

        $baseQuery = Transaction::where('user_id', $userId)
            ->where('tipe_transaksi', 'Pengeluaran')
            ->whereBetween('date', [$start, $end])
            ->whereHas('account', function ($q) {
                $q->where('exclude_from_total', false);
            });

        if ($activeFilter === 'today') {

            $rows = $baseQuery
                ->selectRaw('EXTRACT(HOUR FROM "date") as period, SUM(amount) as total')
                ->groupBy(DB::raw('EXTRACT(HOUR FROM "date")'))
                ->pluck('total', 'period');

            for ($h = 0; $h <= 23; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $values[] = abs((float) ($rows[$h] ?? 0));
            }

        } elseif ($activeFilter === 'week' || $activeFilter === 'month') {

            $period = CarbonPeriod::create($start->startOfDay(), $end->startOfDay());
            $rows = $baseQuery
                ->selectRaw('CAST("date" AS DATE) as period, SUM(amount) as total')
                ->groupBy(DB::raw('CAST("date" AS DATE)'))
                ->pluck('total', 'period');

            foreach ($period as $day) {
                $key = $day->toDateString();
                $labels[] = $day->format('d M');
                $values[] = abs((float) ($rows[$key] ?? 0));
            }

        } else { // year

            $rows = $baseQuery
                ->selectRaw('EXTRACT(MONTH FROM "date") as period, SUM(amount) as total')
                ->groupBy(DB::raw('EXTRACT(MONTH FROM "date")'))
                ->pluck('total', 'period');

            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create($start->year, $m, 1)->format('M');
                $values[] = abs((float) ($rows[$m] ?? 0));
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran',
                    'data' => $values,
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
        return match ($filter) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
            ],
            default => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
            ],
        };
    }

    protected function getType(): string
    {
        return 'line';
    }
}
