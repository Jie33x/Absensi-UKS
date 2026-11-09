<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Buat akun admin default: sekretaris / pmr123
        $admin = User::firstOrCreate(
            ['username' => 'sekretaris'],
            [
                'email' => 'sekretariat@pmrwira.org',
                'password' => Hash::make('pmr123'),
            ]
        );

        AdminProfile::firstOrCreate(
            ['user_id' => $admin->id],
            ['username' => 'sekretaris']
        );
    }
}
