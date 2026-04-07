<?php

namespace App\Models\Holdings\Campus\Siakads\Students;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    // Menggunakan koneksi database campus
    protected $connection = 'sccr_campus';

    // Table
    protected $table = 'students';

    // Primary key auto increment
    protected $primaryKey = 'id';

    public $incrementing = true;

    // Fillable (wajib untuk Livewire)
    protected $fillable = [

        // SSO
        'user_id',

        // Identitas dasar
        'nim', 'no_ektp', 'nisn', 'nama_lengkap',
        'jenis_kelamin', 'agama', 'gol_darah',
        'tempat_lahir', 'tanggal_lahir',

        // Kontak
        'email_private', 'email_campus', 'no_hp',
        'alamat_domisili', 'kota_domisili',

        // Akademik
        'kelas_id', 'prodi_id', 'fakultas_id',
        'tahun_masuk', 'jenjang', 'student_status',
        'asal_sekolah',

        // Orang tua
        'nama_ayah', 'nama_ibu', 'no_hp_parent',
        'alamat_asal', 'kota_asal', 'propinsi_asal',

        // Dokumen
        'photo_file', 'kk_file', 'ektp_file', 'ijazah_file',

        // Lainnya
        'notes', 'no_virtual_account', 'nama_bank',
    ];

    // --- RELASI SSO (ke DB utama sccr_db) ---
    // public function user()
    // {
    //     return $this->setConnection('sccr_db')
    //                 ->belongsTo(\App\Models\User::class, 'user_id', 'id');
    // }

    // ---- RELASI AKADEMIK (semua berada di DB campus) ----
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_id');
    }

    // Contoh: relasi ke KRS, Nilai, Presensi jika nanti dibuat
    public function krs()
    {
        return $this->hasMany(Krs::class, 'student_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'student_id');
    }
}
