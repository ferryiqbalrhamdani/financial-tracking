<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Account;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\RawJs;
use App\Models\Localization;
use App\Enums\Settings\DayStart;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Resources\TransactionResource\Widgets\TransactionOverview;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1;


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('tipe_transaksi')
                    ->inline()
                    ->grouped()
                    ->options([
                        'Pengeluaran' => 'Pengeluaran',
                        'Pemasukan' => 'Pemasukan',
                    ])
                    ->icons([
                        'Pengeluaran' => 'heroicon-o-arrow-down-circle',
                        'Pemasukan' => 'heroicon-o-arrow-up-circle',
                    ])
                    ->default('Pengeluaran')
                    ->required()
                    ->colors([
                        'Pengeluaran' => 'danger',
                        'Pemasukan' => 'success',
                    ])
                    ->reactive(),

                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->required()
                    ->options(function (callable $get) {
                        $tipeTransaksi = $get('tipe_transaksi');
                        return Category::query()
                            ->where('tipe_transaksi', $tipeTransaksi)
                            ->where('user_id', Auth::user()->id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->reactive()
                    ->disabled(fn(callable $get) => empty($get('tipe_transaksi')))
                    ->placeholder('Pilih kategori'),
                Forms\Components\Select::make('account_id')
                    ->label('Akun')
                    ->helperText(function ($state) {
                        if (!$state) return null;

                        // Ambil akun beserta total pemasukan dan pengeluaran
                        $account = Account::where('id', $state)
                            ->withSum([
                                'transactions as pemasukan' => fn($query) => $query->where('tipe_transaksi', 'Pemasukan'),
                                'transactions as pengeluaran' => fn($query) => $query->where('tipe_transaksi', 'Pengeluaran'),
                            ], 'amount')
                            ->first();

                        if (!$account) return null;

                        $balance = $account->starting_balance + ($account->pemasukan ?? 0) + ($account->pengeluaran ?? 0);

                        return 'Saldo: Rp ' . number_format($balance, 2, ',', '.');
                    })
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->where('user_id', Auth::id())->orderBy('sort')
                    )
                    ->reactive()
                    ->required()
                    ->placeholder('Pilih akun'),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal Transaksi')
                    ->default(Carbon::now())
                    ->native(false)
                    ->required()
                    ->closeOnDateSelection()
                    ->reactive()
                    // ->afterStateHydrated(function ($component, $state) {
                    //     // Saat edit, pastikan hanya tanggal saja yang ditampilkan
                    //     $component->state(Carbon::parse($state)->toDateString());
                    // })
                    ->dehydrateStateUsing(function ($state) {
                        // Saat menyimpan, gabungkan tanggal dengan waktu sekarang
                        return Carbon::parse($state);
                    }),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->prefix('Rp ')
                    ->numeric()
                    ->columnSpanFull()
                    ->required()
                    ->dehydrateStateUsing(function ($state, callable $get) {
                        $amount = floatval(str_replace(',', '', $state)); // Ubah string jadi angka
                        return $get('tipe_transaksi') === 'Pengeluaran'
                            ? -abs($amount)
                            : abs($amount);
                    }),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull()
                    ->rows(5),
            ]);
    }

    public static function table(Table $table): Table
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
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->description ?? 'Tidak ada deskripsi'),
                // Tables\Columns\TextColumn::make('account.name')
                //     ->label('Akun')
                //     ->alignment(Alignment::Center)
                //     ->badge()
                //     ->sortable(),
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
                //     ->date()
                //     ->sortable(),
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
                    ->description(fn($record): string => $record->account->name ?? 'Tidak ada akun')
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



            ], layout: FiltersLayout::AboveContent)
            ->deferLoading()
            ->filtersFormColumns(3)
            ->filtersFormWidth(MaxWidth::FourExtraLarge)
            ->defaultSort('date', 'desc')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label('Tanggal Transaksi')
                    ->collapsible()
                    ->orderQueryUsing(
                        fn($query) =>
                        $query
                            ->orderBy('date', 'desc')      // urutkan group utama berdasarkan tanggal transaksi
                            ->orderBy('created_at', 'desc') // dalam setiap group, urutkan berdasarkan waktu input
                    )
                    ->date(),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('date')
        ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TransactionOverview::class,
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Transaksi';
    }

    public static function getModelLabel(): string
    {
        return 'Transaksi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Transaksi';
    }

    public function getTitle(): string
    {
        return 'Transaksi';
    }
}
