<?php
// File: app/Filament/Resources/LicenseKeyResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseKeyResource\Pages;
use App\Models\LicenseKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LicenseKeyResource extends Resource
{
    protected static ?string $model = LicenseKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('License Details')
                    ->schema([
                        // Dropdown untuk memilih Customer, menampilkan nama dan email
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} - {$record->email}")
                            ->searchable(['name', 'email'])
                            ->required(),

                        // Dropdown untuk memilih Product
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        // Dropdown untuk Status
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expired',
                                'revoked' => 'Revoked',
                            ])
                            ->required()
                            ->default('active'),

                        // Input Angka untuk Batas Aktivasi
                        Forms\Components\TextInput::make('activations_limit')
                            ->required()
                            ->numeric()
                            ->default(1),

                        // Input Tanggal untuk Kadaluwarsa
                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Expires At'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom untuk Kunci Lisensi, bisa disalin
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('License key copied!')
                    ->label('License Key'),

                // Kolom untuk Nama Pelanggan
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                // Kolom untuk Produk
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),

                // Kolom untuk Status dengan warna
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => fn ($state) => in_array($state, ['expired', 'revoked']),
                    ]),

                // Kolom untuk Tanggal Kadaluwarsa
                Tables\Columns\TextColumn::make('expires_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // Filter berdasarkan status
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                        'revoked' => 'Revoked',
                    ]),
                // Filter berdasarkan produk
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenseKeys::route('/'),
            'create' => Pages\CreateLicenseKey::route('/create'),
            'edit' => Pages\EditLicenseKey::route('/{record}/edit'),
        ];
    }
}
