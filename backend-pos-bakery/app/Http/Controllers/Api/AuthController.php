<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kasir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login Kasir menggunakan Kode Kasir & PIN (Layar 01 Login)
     */
    public function login(Request $request)
    {
        $request->validate([
            'kode_kasir' => 'required|string',
            'pin'        => 'required|string',
        ]);

        $kasir = Kasir::where('kode_kasir', $request->kode_kasir)->first();

        if (! $kasir || ! Hash::check($request->pin, $kasir->pin)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode kasir atau PIN salah.',
            ], 401);
        }

        $token = $kasir->createToken('pos-tablet-token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login kasir berhasil.',
            'data'    => [
                'kasir' => $kasir,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout Kasir (Layar 14 Akhiri Shift / Logout)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil keluar dari sesi kasir.',
        ]);
    }
}