<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employees')->insert([
            ['nip' => '202509010002',
                'nama' => 'Dwi Sulyanto',
                'gelar_depan' => null,
                'gelar_belakang' => 'A.Md.Kom',
                'user_id' => 1,
                'holding_id' => 1,
                'department_id' => 2,
                'division_id' => 3,
                'position_id' => 4,
                'employee_code' => 'SCCR',
                'status' => 'PKWT',
                'pendidikan' => 'Manajemen Informatika',
                'alamat_asal' => 'Perum Depsos Blok B7, Kel. Telaga Asih, Kec. Cikarang Barat, Kab. Bekasi, Jawa Barat',
                'kota_asal' => 'Bekasi',
                'alamat_domisili' => 'Kost Samping SCCR',
                'kota_domisili' => 'Semarang',
                'jenis_kelamin' => 'Laki-laki',
                'status_perkawinan' => 'Kawin',
                'agama' => 'Islam',
                'tempat_lahir' => 'Mojokerto',
                'tanggal_lahir' => '1975-02-05',
                'tanggal_join' => '2025-09-01',
                'email' => 'otnaylus.sccr@gmail.com',
                'no_hp' => '081929295060',
                'no_ektp' => '3216080502750013',
                'kis' => '123456',
                'bpjs_tk' => '789012',
                'no_rekening' => '57080',
                'pemilik_rekening' => 'Dwi Sulyanto',
                'nama_bank' => 'BCA',
                'foto' => '202509010002.jpg',
                'job_title_id' => 2],
        ]);
    }
}
