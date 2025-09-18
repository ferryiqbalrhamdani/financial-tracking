<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }


    protected static function booted()
    {
        static::created(function ($user) {
            // Tambahkan akun default
            \App\Models\Account::insert([
                [
                    'name' => 'Dompet saya',
                    'user_id' => $user->id,
                    'starting_balance' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'BCA',
                    'user_id' => $user->id,
                    'starting_balance' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);

            // Tambahkan kategori default
            \App\Models\Category::insert([
                [
                    'tipe_transaksi' => 'Pengeluaran',
                    'name' => '⬆️ Transfer keluar',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pengeluaran',
                    'name' => '🥗 Makanan, Minuman & Belanja',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pengeluaran',
                    'name' => '🧾 Tagihan & utilitas',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pengeluaran',
                    'name' => '🚗 Transportasi',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pengeluaran',
                    'name' => '⚽ Olahraga',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pemasukan',
                    'name' => '⬇️ Transfer masuk',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'tipe_transaksi' => 'Pemasukan',
                    'name' => '💸 Gaji',
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);

            // Tambahkan localization default
            \App\Models\Localization::create([
                'user_id' => $user->id,
            ]);
        });
    }
}
