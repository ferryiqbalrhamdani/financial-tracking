<?php

namespace App\Filament\Resources\DebtResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Account;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Hutang/Piutang')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->placeholder('Contoh: 1,000,000')
                            ->prefix('Rp '),
                        Forms\Components\Select::make('accountId')
                            ->label('Akun')
                            ->options(Account::where('user_id', Auth::id())
                                ->orderBy('sort', 'asc')
                                ->pluck('name', 'id'))
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
                            ->reactive()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Transaksi')
                            ->required(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->deferLoading()
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
                    ->money('IDR')
                    ->summarize(
                        Sum::make()
                            ->label('')
                            ->money('IDR')
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()
                //     ->label('Tambah Transaksi'),
            ])
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
            ]);
    }
}
