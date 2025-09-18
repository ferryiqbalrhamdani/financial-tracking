<?php

namespace App\Filament\Clusters\Settings\Resources\AccountResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Localization;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tipe_transaksi')
                    ->required()
                    ->maxLength(255),
            ]);
    }

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
            ->recordTitleAttribute('tipe_transaksi')
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->description ?? 'Tidak ada deskripsi'),
                Tables\Columns\TextColumn::make('account.name')
                    ->label('Akun')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->badge()
                    ->alignment(Alignment::Center)
                    ->color(fn(string $state): string => match ($state) {
                        'Simpanan' => 'info',
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Simpanan' => 'heroicon-o-wallet',
                        'Pemasukan' => 'heroicon-o-arrow-up-circle',
                        'Pengeluaran' => 'heroicon-o-arrow-down-circle',
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal Transaksi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah Transaksi')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->tipe_transaksi == 'Pengeluaran') {
                            return 'danger';
                        } else if ($record->tipe_transaksi == 'Pemasukan') {
                            return 'success';
                        } else {
                            return 'gray';
                        }
                    })
                    ->alignment(Alignment::Right)
                    ->money($currency)
                    ->summarize(
                        Sum::make()
                            ->label('')
                            ->money('IDR')
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('date')
                    ->label('Tanggal Transaksi')
                    ->defaultCustom(
                        $startDate,
                        $endDate
                    ),
                Tables\Filters\Filter::make('akun')
                    ->form([
                        Forms\Components\Select::make('akun')
                            ->label('Tampilkan Akun')
                            ->options([
                                false => 'Masukkan Dalam Total',
                                true => 'Kecualikan Dari Total',
                            ])
                            ->default(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('account', function ($q) use ($data) {
                            if (isset($data['akun'])) {
                                $q->where('exclude_from_total', $data['akun']);
                            }
                        });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (isset($data['akun'])) {
                            $label = $data['akun'] ? 'Kecualikan Dari Total' : 'Masukkan Dalam Total';
                            $indicators[] = 'Akun: ' . $label;
                        }

                        return $indicators;
                    }),
                Tables\Filters\Filter::make('is_transfer')
                    ->form([
                        Forms\Components\Select::make('is_transfer')
                            ->label('Tampilkan Transfer')
                            ->options([
                                true => 'Iya',
                                false => 'Tidak',
                            ])
                            ->placeholder('Semua') // <- Ini penting agar null saat tidak dipilih
                            ->nullable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Tampilkan semua jika filter tidak dipilih
                        if (array_key_exists('is_transfer', $data) && $data['is_transfer'] !== null) {
                            $query->where('is_transfer', $data['is_transfer']);
                        } else {
                            $query->whereIn('is_transfer', [true, false]);
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        if (!array_key_exists('is_transfer', $data) || $data['is_transfer'] === null) {
                            return [];
                        }

                        return [
                            $data['is_transfer'] ? 'Hanya Transfer' : 'Tanpa Transfer',
                        ];
                    }),



            ], layout: FiltersLayout::Modal)
            ->deferLoading()
            // ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
            ->defaultSort('date', 'desc')
            ->actions([])
            ->bulkActions([])
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
