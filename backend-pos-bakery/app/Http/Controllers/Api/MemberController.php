<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Cek Member berdasarkan Nomor HP atau Kode Member (Skenario 1 Langkah 4-5, Layar 05 & 06A)
     */
    public function cekMember(Request $request)
    {
        $request->validate([
            'identitas' => 'required|string', // Nomor HP atau Kode Member
        ]);

        $identitas = $request->identitas;

        $member = Member::where('no_hp', $identitas)
            ->orWhere('kode_member', $identitas)
            ->first();

        if (! $member) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Member tidak ditemukan. Tawarkan pendaftaran member baru kepada pelanggan.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Member ditemukan.',
            'data'    => [
                'id_member'      => $member->id_member,
                'kode_member'    => $member->kode_member,
                'nama_member'    => $member->nama_member,
                'no_hp'          => $member->no_hp,
                'tanggal_daftar' => $member->tanggal_daftar->format('Y-m-d'),
                'saldo_poin'     => $member->saldo_poin,
            ],
        ]);
    }

    /**
     * Pendaftaran Member Baru Langsung dari Kasir (Skenario 1 Langkah 4, Layar 06B)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_member' => 'required|string|max:100',
            'no_hp'       => 'required|string|max:20|unique:member,no_hp',
        ]);

        // Generate kode_member otomatis (contoh: MBR-003)
        $urutTerakhir = Member::max('id_member') ?? 0;
        $kodeMember = 'MBR-' . str_pad($urutTerakhir + 1, 3, '0', STR_PAD_LEFT);

        $member = Member::create([
            'kode_member'    => $kodeMember,
            'nama_member'    => $request->nama_member,
            'no_hp'          => $request->no_hp,
            'tanggal_daftar' => Carbon::today(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran member baru berhasil.',
            'data'    => $member,
        ], 201);
    }
}