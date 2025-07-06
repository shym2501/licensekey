<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    // Fungsi untuk kustomisasi log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) // Kolom yang ingin dilacak
            ->logOnlyDirty() // <-- PENTING: Hanya catat jika ada perubahan
            ->dontSubmitEmptyLogs() // Jangan buat log jika tidak ada yang berubah
            ->setDescriptionForEvent(fn(string $eventName) => "Customer '{$this->name}' has been {$eventName}")
            ->useLogName('Customer');
    }
    public function licenseKeys()
    {
        return $this->hasMany(LicenseKey::class);
    }
}
