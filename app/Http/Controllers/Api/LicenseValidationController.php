<?php
// File: app/Http/Controllers/Api/LicenseValidationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LicenseKey;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LicenseValidationController extends Controller
{
    public function validateLicense(Request $request)
    {
        // 1. Validasi input dari klien
        $validated = $request->validate([
            'license_key' => 'required|string',
            'device_id'   => 'required|string', // ID unik perangkat, misalnya MAC address atau ID hardware
        ]);

        $licenseKey = $validated['license_key'];
        $deviceId = $validated['device_id'];

        // 2. Cari lisensi di database
        $license = LicenseKey::with('activations')->where('key', $licenseKey)->first();

        // ------------------ Logika Validasi ------------------

        // Jika lisensi tidak ditemukan
        if (!$license) {
            return response()->json(['status' => 'invalid', 'message' => 'License key not found.'], 404);
        }

        // Jika statusnya bukan 'active'
        if ($license->status !== 'active') {
            return response()->json(['status' => 'invalid', 'message' => 'License key is not active. Current status: ' . $license->status], 403);
        }

        // Jika lisensi sudah kedaluwarsa
        if ($license->expires_at && Carbon::now()->isAfter($license->expires_at)) {
            // Otomatis update status di database menjadi 'expired'
            $license->update(['status' => 'expired']);
            return response()->json(['status' => 'invalid', 'message' => 'License key has expired.'], 403);
        }

        // Cek apakah perangkat ini sudah pernah diaktifkan sebelumnya
        $existingActivation = $license->activations->firstWhere('device_id', $deviceId);

        if ($existingActivation) {
            // Perangkat ini sudah ter-otorisasi, tidak perlu cek limit lagi.
            return response()->json([
                'status'  => 'valid',
                'message' => 'License key is valid for this device.',
                'plan'    => $license->product->name, // Contoh data tambahan
                'expires_at' => $license->expires_at,
            ]);
        }

        // Jika ini aktivasi baru, cek apakah limit sudah tercapai
        if ($license->activations->count() >= $license->activations_limit) {
            return response()->json(['status' => 'invalid', 'message' => 'Activation limit reached.'], 403);
        }

        // ------------------ Akhir Logika Validasi ------------------

        // Jika semua lolos, daftarkan perangkat ini sebagai aktivasi baru
        $license->activations()->create([
            'device_id'  => $deviceId,
            'ip_address' => $request->ip(),
        ]);

        // Berikan respons sukses
        return response()->json([
            'status'  => 'valid',
            'message' => 'License key has been successfully activated on this device.',
            'plan'    => $license->product->name, // Contoh data tambahan
            'expires_at' => $license->expires_at,
        ]);
    }
}
