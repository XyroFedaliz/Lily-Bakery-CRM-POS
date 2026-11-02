<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $daftarMember = [
            [
                'kode_member'    => 'MBR-001',
                'no_hp'          => '081234567890',
                'nama_member'    => 'Andi Pratama',
                'tanggal_daftar' => '2026-09-01',
            ],
            [
                'kode_member'    => 'MBR-002',
                'no_hp'          => '081298765432',
                'nama_member'    => 'Rina Wulandari',
                'tanggal_daftar' => '2026-10-01',
            ],
        ];

        foreach ($daftarMember as $member) {
            Member::updateOrCreate(
                ['kode_member' => $member['kode_member']],
                $member
            );
        }
    }
}