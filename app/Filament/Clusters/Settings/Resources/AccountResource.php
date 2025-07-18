<?php

namespace App\Filament\Clusters\Settings\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Account;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use App\Filament\Clusters\Settings;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Count;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\Settings\Resources\AccountResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Clusters\Settings\Resources\AccountResource\RelationManagers;
use App\Filament\Clusters\Settings\Resources\AccountResource\Widgets\AccountsOverview;
use App\Filament\Clusters\Settings\Resources\AccountResource\Widgets\AccountsTotalOverview;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Settings::class;
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Akun'),
                Forms\Components\Toggle::make('exclude_from_total')
                    ->inline(false)
                    ->label('Kecualikan dari total'),
                Forms\Components\TextInput::make('starting_balance')
                    ->label('Saldo Awal')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp '),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('starting_balance')
                //     ->label('Saldo Awal')
                //     ->money('IDR', locale: 'id')
                //     ->sortable(),
                Tables\Columns\IconColumn::make('exclude_from_total')
                    ->label('Tidak termasuk total')
                    ->alignment(Alignment::Center)
                    ->boolean(),
                    Tables\Columns\TextColumn::make('saldo_akhir')
                    ->label('Saldo Akhir')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignment(Alignment::Right)
                    ->getStateUsing(function ($record) {
                        $startingBalance = $record->starting_balance;
                
                        $pemasukan = $record->transactions
                            ->where('tipe_transaksi', 'Pemasukan')
                            ->sum('amount');
                
                        $pengeluaran = $record->transactions
                            ->where('tipe_transaksi', 'Pengeluaran')
                            ->sum('amount');
                
                        $saldoAkhir = $startingBalance + ($pemasukan + $pengeluaran);
                
                        return $saldoAkhir;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
            ])
            ->defaultSort('exclude_from_total', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('exclude_from_total')
                    ->getDescriptionFromRecordUsing(function ($record) {
                            if($record->exclude_from_total == false) {
                                return 'Masukkan dalam total';
                            } else {
                                return 'Kecualikan dari total';
                            }
                        }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            AccountsTotalOverview::class,
            AccountsOverview::class,
        ];
    }
}
