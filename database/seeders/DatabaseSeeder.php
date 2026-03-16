<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\Location::create(['name' => 'Kantor Pusat', 'address' => 'Jakarta']);
        \App\Models\Location::create(['name' => 'Cabang Bandung', 'address' => 'Bandung']);

        \App\Models\Service::create(['name' => 'Klaim JHT']);
        \App\Models\Service::create(['name' => 'Koreksi Data']);
        \App\Models\Service::create(['name' => 'Pendaftaran']);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@jmo',
            'location_id' => 1,
            'password' => 'rahasia',
            'role' => 0
        ]);
    }
}
