<?php
// File: app/Filament/Resources/LicenseKeyResource/RelationManagers/ActivationsRelationManager.php

namespace App\Filament\Resources\LicenseKeyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivationsRelationManager extends RelationManager
{
    protected static string $relationship = 'activations';
    protected static ?string $title = 'Device Activations'; // Judul yang akan ditampilkan di halaman

    // Form ini tidak kita perlukan karena admin tidak seharusnya membuat aktivasi manual.
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('device_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_id')
            ->columns([
                // Kolom untuk ID unik perangkat
                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable(),

                // Kolom untuk Alamat IP
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address'),

                // Kolom untuk waktu aktivasi
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Activated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Tampilkan yang terbaru di atas
            ->filters([
                //
            ])
            // Kita tidak ingin admin bisa membuat aktivasi manual dari sini
            ->headerActions([])
            // Aksi yang bisa dilakukan per baris
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            // Aksi yang bisa dilakukan secara massal
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Memastikan admin tidak bisa meng-attach aktivasi yang sudah ada
    public function isReadOnly(): bool
    {
        return false;
    }
}
