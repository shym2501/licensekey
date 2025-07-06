<?php
// File: app/Models/LicenseActivation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key_id',
        'device_id',
        'ip_address',
    ];

    public function licenseKey()
    {
        return $this->belongsTo(LicenseKey::class);
    }
}
