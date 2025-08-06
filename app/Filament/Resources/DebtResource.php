<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Debt;
use Filament\Tables;
use App\Models\Account;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DebtResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DebtResource\RelationManagers;
use App\Filament\Resources\DebtResource\Widgets\DebtsOverview;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\ToggleButtons::make('type')
                            ->label('Tipe')
                            ->inline()
                            ->grouped()
                            ->options([
                                'hutang' => 'Hutang',
                                'piutang' => 'Piutang',
                            ])
                            ->icons([
                                'hutang' => 'heroicon-o-arrow-up-circle',
                                'piutang' => 'heroicon-o-arrow-down-circle',
                            ])
                            ->default('hutang')
                            ->required()
                            ->colors([
                                'hutang' => 'success',
                                'piutang' => 'danger',
                            ])
                            ->helperText('Hutang: uang yang masuk, Piutang: uang yang keluar')
                            ->reactive(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama orang/pihak')
                            ->required()
                            ->placeholder('Contoh: John Doe')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Hutang/Piutang')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->placeholder('Contoh: 1,000,000')
                            ->prefix('Rp '),
                        Forms\Components\Select::make('account_id')
                            ->label('Akun')
                            ->options(Account::all()->where('user_id', Auth::id())->pluck('name', 'id'))
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih akun'),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now())
                            ->placeholder('Pilih tanggal mulai'),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->helperText('Kosongkan jika tidak ada'),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Orang/Pihak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'hutang' => 'success',
                        'piutang' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Hutang/Piutang')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (Debt $record) {
                        // Ambil semua transaksi
                        $transactions = $record->transactions;
                        $pemasukan = $transactions->where('tipe_transaksi', 'Pemasukan')->sum('amount');
                        $pengeluaran = $transactions->where('tipe_transaksi', 'Pengeluaran')->sum('amount');
                        $status = '';

                        $total = $pemasukan + $pengeluaran;
                        if ($record->type == 'hutang') {
                            if ($total > 0) {
                                $status = 'Belum Lunas';
                            } else {
                                $status = 'Lunas';
                            }
                        } else if ($record->type == 'piutang') {
                            if ($total < 0) {
                                $status = 'Belum Lunas';
                            } else {
                                $status = 'Lunas';
                            }
                        }

                        return $status;
                    })
                    ->alignment(Alignment::Center)
                    ->color(fn($state) => $state === 'Lunas' ? 'info' : 'danger'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->alignment(Alignment::Right)
                    ->sortable()
                    ->description(function (Debt $record) {
                        // Ambil semua transaksi
                        $transactions = $record->transactions;
                        $pemasukan = $transactions->where('tipe_transaksi', 'Pemasukan')->sum('amount');
                        $pengeluaran = $transactions->where('tipe_transaksi', 'Pengeluaran')->sum('amount');

                        $total = $pemasukan + $pengeluaran;

                        return 'Sisa: Rp ' . number_format($total, 2, ',', '.');
                    }),
                //                Tables\Columns\TextColumn::make('jumlah_dibayar')
                //                    ->label('Jumlah Dibayar')
                //                    ->alignment(Alignment::Right)
                //                    ->money('IDR')
                //                    ->state(function (Debt $record) {
                //                        // Ambil semua transaksi
                //                        $transactions = $record->transactions;
                //
                //                        // return 'mantap';
                //
                //                        if ($record->type === 'hutang') {
                //                            return $transactions
                //                                ->where('tipe_transaksi', 'Pengeluaran')
                //                                ->sum('amount');
                //                        }
                //
                //                        if ($record->type === 'piutang') {
                //                            return $transactions
                //                                ->where('tipe_transaksi', 'Pemasukan')
                //                                ->sum('amount');
                //                        }
                //
                //                        return 0;
                //                    }),
            ])
            ->filters([
                DateRangeFilter::make('start_date')
                    ->label('Tanggal Hutang/Piutang')
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('payment')
                        ->label('Pembayaran')
                        ->icon('heroicon-o-credit-card')
                        ->fillForm(fn(Debt $record): array => [
                            'accountId' => $record->account->id,
                            'amount' => abs($record->amount),
                            'date' => now(),
                        ])
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah Hutang/Piutang')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->placeholder('Contoh: 1,000,000')
                                ->prefix('Rp '),
                            Forms\Components\Select::make('accountId')
                                ->label('Akun')
                                ->options(Account::query()->pluck('name', 'id'))
                                ->required(),
                            Forms\Components\DatePicker::make('date')
                                ->label('Tanggal Transaksi')
                                ->required(),
                        ])
                        ->action(function (array $data, Debt $record): void {
                            // $transaction = $record->transactions->first();
                            // dd($transaction, $data);
                            // $record->account()->associate($data['accountId']);
                            // $record->save();

                            $kategori =  Category::all();
                            $dateWithTime = Carbon::parse($data['date'])
                                ->setTimeFrom(Carbon::now()); // ambil jam, menit, detik saat ini

                            if ($record['type'] === 'hutang') {
                                $kategori = $kategori->where('name', 'Tagihan & utilitas')->first();
                                $description = 'Pembayaran Hutang ke ' . $record['name'];
                                $tipeTransaksi = 'Pengeluaran';
                                $record['amount'] = -abs($record['amount']);
                            } elseif ($record['type'] === 'piutang') {
                                $kategori = $kategori->where('name', 'Transfer masuk')->first();
                                $description = 'Penerimaan atas piutang dari ' . $record['name'];
                                $tipeTransaksi = 'Pemasukan';
                                $record['amount'] = abs($record['amount']);
                            }

                            // dd($data['accountId'], $record['user_id'], $kategori->id, $tipeTransaksi, $record['amount'], $description, $dateWithTime, $record['type']);

                            $record->transactions()->create([
                                'user_id' => $record['user_id'],
                                'account_id' => $data['accountId'],
                                'category_id' => $kategori->id,
                                'tipe_transaksi' => $tipeTransaksi,
                                'amount' => $record['amount'],
                                'description' => $description,
                                'date' => $dateWithTime,
                            ]);

                            Notification::make()
                                ->title('Pembayaran berhasil')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDebts::route('/'),
            'create' => Pages\CreateDebt::route('/create'),
            'edit' => Pages\EditDebt::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Hutang & Piutang';
    }

    public static function getModelLabel(): string
    {
        return 'Hutang & Piutang';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Hutang & Piutang';
    }

    public function getTitle(): string
    {
        return 'Hutang & Piutang';
    }

    public static function getWidgets(): array
    {
        return [
            DebtsOverview::class,
        ];
    }
}
