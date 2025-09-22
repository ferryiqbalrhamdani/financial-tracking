<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Category;
use Filament\Tables\Table;
use App\Models\Localization;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Widgets\TableWidget as BaseWidget;

class CategoryExpanses extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;
    public function table(Table $table): Table
    {
        $dayStart = Localization::where('user_id', Auth::id())->value('monthly_period_start_day') ?? 1;

        $today = Carbon::today();

        $currency = Localization::where('user_id', Auth::id())->value('currency') ?? 'IDR';

        if ($today->day >= $dayStart) {
            // Sudah lewat tanggal start → ambil bulan ini sampai tanggal start bulan depan - 1
            $startDate = Carbon::create($today->year, $today->month, $dayStart);
            $endDate = $startDate->copy()->addMonth()->subDay();
        } else {
            // Belum sampai tanggal start → ambil tanggal start bulan lalu sampai kemarin tanggal start - 1
            $startDate = Carbon::create($today->year, $today->month, $dayStart)->subMonth();
            $endDate = $startDate->copy()->addMonth()->subDay();
        }
        
       return $table
        ->query(
            Category::query()
                ->where('user_id', Auth::id())
                ->where('tipe_transaksi', 'Pengeluaran')
                ->withSum(['transactions as pengeluaran' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('date', [$startDate, $endDate]);
                }], 'amount')
        )
        ->heading('Pengeluaran ' . $startDate->format('d M') . ' – ' . $endDate->format('d M'))
        ->paginated(false)
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Nama Kategori'),
            Tables\Columns\TextColumn::make('pengeluaran')
                ->alignment(Alignment::Right)
                ->money($currency)
                ->color('danger')
                ->default(0)
                // ->getStateUsing(fn ($record) => abs($record->pengeluaran ?? 0)), // ubah ke positif,
        ])
        ->defaultSort('pengeluaran', 'asc');
    }
}
