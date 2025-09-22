<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Localization;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use App\Filament\Resources\TransactionResource;
use Filament\Widgets\TableWidget as BaseWidget;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class CurrentMonthTransactionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 6;
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
            ->headerActions([
                Tables\Actions\Action::make('lihat_semua')
                    ->label('Lihat Selengkapnya')
                    ->url(fn() => TransactionResource::getUrl('index'))
                    ->outlined()
                    ->icon('heroicon-o-eye')
                    ->color('info'),
            ])
            ->query(
                TransactionResource::getEloquentQuery()
                    ->where('user_id', Auth::user()->id)
                    ->whereBetween('date', [
                        $startDate->startOfDay(),
                        $endDate->endOfDay()
                    ])
                    ->where('is_transfer', false)
                    ->orderBy('date', 'desc')

            )
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->description(fn($record): string => $record->description ?? 'Tidak ada deskripsi'),
                // Tables\Columns\TextColumn::make('account.name')
                //     ->label('Akun')
                //     ->alignment(Alignment::Center)
                //     ->badge(),
                // Tables\Columns\TextColumn::make('tipe_transaksi')
                //     ->label('Tipe Transaksi')
                //     ->badge()
                //     ->alignment(Alignment::Center)
                //     ->color(fn(string $state): string => match ($state) {
                //         'Simpanan' => 'info',
                //         'Pemasukan' => 'success',
                //         'Pengeluaran' => 'danger',
                //         default => 'gray',
                //     })
                //     ->icon(fn(string $state): string => match ($state) {
                //         'Simpanan' => 'heroicon-o-wallet',
                //         'Pemasukan' => 'heroicon-o-arrow-up-circle',
                //         'Pengeluaran' => 'heroicon-o-arrow-down-circle',
                //     }),
                // Tables\Columns\TextColumn::make('date')
                //     ->label('Tanggal Transaksi')
                //     ->date(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Transaksi')
                    ->numeric()
                    ->color(function ($record) {
                        if ($record->tipe_transaksi == 'Pengeluaran') {
                            return 'danger';
                        } else if ($record->tipe_transaksi == 'Pemasukan') {
                            return 'success';
                        } else {
                            return 'gray';
                        }
                    })
                    ->description(fn($record): string => $record->account->name ?? 'Tidak ada akun')
                    ->alignment(Alignment::Right)
                    ->money($currency)
                    ->summarize(
                        Sum::make()
                            ->label('')
                            ->money('IDR')
                    ),

            ])
            ->deferLoading()
            ->heading('Periode ' . $startDate->format('d M') . ' – ' . $endDate->format('d M'))
            ->description('Transaksi anda dalam periode ini, transfer tidak ditampilkan.')
            ->paginated(false)
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label('Tanggal Transaksi')
                    ->collapsible()
                    ->orderQueryUsing(fn($query,) =>
                    $query->orderBy("date", "desc"))
                    ->date(),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('date');
    }
}
