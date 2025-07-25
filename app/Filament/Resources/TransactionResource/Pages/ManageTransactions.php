<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\Account;
use Filament\Forms\Get;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Support\RawJs;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\View\View;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\TransactionResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use App\Filament\Resources\TransactionResource\Widgets\TransactionOverview;

class ManageTransactions extends ManageRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('lihat_akun')
                ->label('Lihat Akun')
                ->modalHeading('Lihat Semua Akun')
                ->stickyModalHeader()
                // ->closeModalByClickingAway(false)
                ->slideOver()
                ->stickyModalFooter()
                ->modalSubmitAction(false)
                ->modalCancelAction(fn(StaticAction $action) => $action->label('Kembali'))
                ->modalContent(fn(): View => view(
                    'filament.pages.actions.lihat-akun',
                    ['records' => Account::where('user_id', Auth::user()->id)->get()],
                ))
                ->outlined()
                ->modalWidth(MaxWidth::ThreeExtraLarge)
                ->color('info')
                ->icon('heroicon-o-wallet'),

            Actions\Action::make('transfer')
                ->label('Transfer')
                ->outlined()
                ->icon('heroicon-o-arrows-right-left')
                ->form([


                    Grid::make(2)->schema([
                        Select::make('account_id_send')
                            ->label('Akun Pengirim')
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
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih akun'),

                        Select::make('account_id_recived')
                            ->label('Akun Penerima')
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
                                modifyQueryUsing: function ($query, Get $get) {
                                    $query->where('user_id', Auth::id())->orderBy('sort');

                                    // Filter agar tidak bisa memilih akun yang sama dengan pengirim
                                    if ($get('account_id_send')) {
                                        $query->where('id', '!=', $get('account_id_send'));
                                    }
                                }
                            )
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih akun'),
                    ]),
                    TextInput::make('amount')
                        ->label('Jumlah Transfer')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix('Rp ')
                        ->numeric()
                        ->columnSpanFull()
                        ->required(),

                    DatePicker::make('date')
                        ->label('Tanggal Transaksi')
                        ->default(Carbon::now())
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->reactive()
                        ->afterStateHydrated(
                            fn($component, $state) =>
                            $component->state(Carbon::parse($state)->toDateString())
                        )
                        ->dehydrateStateUsing(fn($state) => Carbon::parse($state)->toDateString()),

                ])
                ->action(function (array $data) {
                    // Bersihkan jumlah transfer (misal: dari "5.000.000" menjadi 5000000)
                    $amount = (int) str_replace(['.', ','], '', $data['amount']);

                    // Ambil akun
                    $accounts = Account::where('user_id', Auth::id())->get();
                    $accountSend = $accounts->find($data['account_id_send']);
                    $accountRecived = $accounts->find($data['account_id_recived']);

                    // Cek kategori
                    $kategoriKeluar = Category::where('name', 'Transfer keluar')->first();
                    $kategoriMasuk = Category::where('name', 'Transfer masuk')->first();

                    // Hitung saldo akun pengirim
                    $transaksi = Transaction::where('user_id', Auth::id());
                    $transaksiIncome = $transaksi->clone()
                        ->where('account_id', $accountSend->id)
                        ->where('tipe_transaksi', 'Pemasukan')
                        ->sum('amount');

                    $transaksiExpense = $transaksi->clone()
                        ->where('account_id', $accountSend->id)
                        ->where('tipe_transaksi', 'Pengeluaran')
                        ->sum('amount');

                    $saldoAkhir = $accountSend->starting_balance + ($transaksiIncome + $transaksiExpense);
                    $totalAccountSend = $saldoAkhir - $amount;

                    if ($totalAccountSend < 0) {
                        Notification::make()
                            ->title('Transfer gagal')
                            ->body('Saldo tidak mencukupi dari akun ' . $accountSend->name . ' sisa saldo Rp ' . number_format($saldoAkhir, 2, ',', '.'))
                            ->warning()
                            ->duration(10000) // tampil selama 10 detik
                            ->send();

                        $this->halt();
                    } else {
                        $userId = Auth::user()->id;


                        $dateWithTime = Carbon::parse($data['date'])
                            ->setTimeFrom(Carbon::now()); // ambil jam, menit, detik saat ini

                        Transaction::create([
                            'user_id' => $userId,
                            'account_id' => $data['account_id_send'],
                            'category_id' => $kategoriKeluar->id,
                            'tipe_transaksi' => 'Pengeluaran',
                            'amount' => -$data['amount'],
                            'date' => $dateWithTime,
                            'is_transfer' => true,
                            'description' => 'Kirim ke ' . $accountRecived->name,
                        ]);

                        sleep(1); // jeda 1 detik

                        $dateWithTime = Carbon::parse($data['date'])
                            ->setTimeFrom(Carbon::now()); // ambil ulang jam sekarang

                        Transaction::create([
                            'user_id' => $userId,
                            'account_id' => $data['account_id_recived'],
                            'category_id' => $kategoriMasuk->id,
                            'tipe_transaksi' => 'Pemasukan',
                            'amount' => $data['amount'],
                            'date' => $dateWithTime,
                            'is_transfer' => true,
                            'description' => 'Diterima dari ' . $accountSend->name,
                        ]);


                        Notification::make()
                            ->title('Transfer berhasil')
                            ->body('Transfer berhasil dilakukan dari ' . $accountSend->name . ' ke ' . $accountRecived->name . ' sebanyak Rp ' . number_format($data['amount'], 2, ',', '.'))
                            ->success()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::user()->id;

                    return $data;
                })
                ->icon('heroicon-o-plus')
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make()
                // ->badge(count(Transaction::where('user_id', Auth::user()->id)->get()))
                ->icon('heroicon-o-rectangle-stack')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)),
            'Pengeluaran' => Tab::make()
                // ->badge(count(Transaction::where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pengeluaran')->get()))
                ->icon('heroicon-o-arrow-down-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pengeluaran')),
            'Pemasukan' => Tab::make()
                // ->badge(count(Transaction::where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pemasukan')->get()))
                ->icon('heroicon-o-arrow-up-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id)->where('tipe_transaksi', 'Pemasukan')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return TransactionResource::getWidgets();
    }
}
