<?php
// File: app/Models/LicenseKey.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // <-- Tambahkan use statement ini
use Spatie\Activitylog\LogOptions;

class LicenseKey extends Model
{
    use HasFactory, LogsActivity;

    // Tambahkan 'key' agar bisa diisi secara massal
    protected $fillable = [
        'product_id',
        'customer_id',
        'status',
        'expires_at',
        'activations_limit',
        'key', // Tambahkan ini
    ];

    // Tambahkan properti casts
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Untuk lisensi, kita lacak semua perubahan
            ->logOnlyDirty() // <-- PENTING
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "The license {$this->key} has been {$eventName}")
            ->useLogName('License Key');
    }

    // --- LOGIKA UTAMA ADA DI SINI ---
    protected static function boot(): void
    {
        parent::boot();
        // Event ini akan terpanggil SETIAP KALI data baru akan dibuat
        static::creating(function (LicenseKey $licenseKey) {
            // Jika key belum diisi, buatkan key baru
            if (empty($licenseKey->key)) {
                $licenseKey->key = self::generateKey();
            }
        });
    }

    /**
     * Membuat string unik untuk kunci lisensi.
     * Format: KGS-XXXX-XXXX-XXXX-XXXX
     */
    public static function generateKey(): string
    {
        // "KGS" = Key Generation System (bisa diganti)
        // Menghasilkan 16 karakter acak dan membaginya ke dalam 4 blok
        return 'KGS-' . strtoupper(implode('-', str_split(bin2hex(random_bytes(8)), 4)));
    }
    // --- AKHIR DARI LOGIKA UTAMA ---


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }
}
