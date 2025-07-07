<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Spatie\Activitylog\Models\Activity; // <-- Arahkan ke model dari package

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class; // <-- Arahkan ke model dari package

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Admin'; // Grup menu baru
    protected static ?int $navigationSort = 3; // Urutan menu

    // Kita tidak akan membuat form karena log tidak untuk diedit
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section untuk menampilkan nilai lama (sebelum diubah)
                Section::make('Old Values')
                    ->schema([
                        KeyValue::make('properties.old')
                            ->label('Data before changes')
                            ->columnSpanFull(),
                    ])
                    // Hanya tampilkan section ini jika eventnya 'updated' atau 'deleted'
                    ->hidden(fn(Activity $record) => !in_array($record->event, ['updated', 'deleted'])),

                // Section untuk menampilkan nilai baru (setelah diubah)
                Section::make('New Values')
                    ->schema([
                        KeyValue::make('properties.attributes')
                            ->label('Data after changes')
                            ->columnSpanFull(),
                    ])
                    // Hanya tampilkan section ini jika eventnya 'updated' atau 'created'
                    ->hidden(fn(Activity $record) => !in_array($record->event, ['updated', 'created'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->badge()
                    ->colors([
                        'success' => fn($record): bool => $record->event === 'created',
                        'warning' => fn($record): bool => $record->event === 'updated',
                        'danger'  => fn($record): bool => $record->event === 'deleted',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable(),

                // 'causer' adalah user yang melakukan aksi
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User'),

                // 'subject' adalah data yang diubah
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Model')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Timestamp'),
            ])
            ->defaultSort('created_at', 'desc') // Urutkan dari yang terbaru
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]); // Tidak ada bulk action
    }

    // Fungsi ini untuk memastikan resource ini read-only
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
