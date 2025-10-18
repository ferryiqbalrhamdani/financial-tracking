<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Event;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\EventResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EventResource\Pages\EditEvent;
use App\Filament\Resources\EventResource\Pages\ViewEvent;
use App\Filament\Resources\EventResource\RelationManagers;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 5;

      public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Nama Acara')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('end')
                    ->label('Tanggal Selesai Acara'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Acara')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('diterima')
                    ->label('Diterima')
                    ->default(0)
                    ->money('idr', locale: 'id')
                     ->getStateUsing(function ($record) {
                        return $record->transactions()->where('tipe_transaksi', 'Pemasukan')->sum('amount') + $record->transactions()->where('tipe_transaksi', 'Pengeluaran')->sum('amount');
                     }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

      public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Acara')
                ->description('Informasi lengkap tentang acara ini.')
                ->columns([
                    'sm' => 1,
                    'xl' => 3,
                    '2xl' => 3,
                ])
                ->schema([
                    TextEntry::make('title')
                        ->label('Nama Acara'),
                    TextEntry::make('end')
                        ->label('Tanggal Selesai Acara')
                        ->date(),
                    IconEntry::make('is_active')
                        ->label('Status Aktif')
                        ->boolean(),
                ]),
            ]);
    }


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewEvent::class,
            EditEvent::class,
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Acara';
    }

    public static function getModelLabel(): string
    {
        return 'Acara';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Acara';
    }

    public function getTitle(): string
    {
        return 'Acara';
    }
}
