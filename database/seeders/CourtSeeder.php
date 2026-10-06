<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Futsal Arena 01', 'type' => 'Futsal', 'price_per_hour' => 150000, 'description' => 'Lapangan indoor dengan rumput sintetis. Tersedia area tunggu dan ruang ganti.'],
            ['name' => 'Futsal Arena 02', 'type' => 'Futsal', 'price_per_hour' => 120000, 'description' => 'Lapangan vinyl untuk latihan dan pertandingan bersama tim kamu.'],
            ['name' => 'Badminton Court 01', 'type' => 'Badminton', 'price_per_hour' => 50000, 'description' => 'Lapangan indoor dengan karpet olahraga dan pencahayaan yang nyaman.'],
            ['name' => 'Basket Court 01', 'type' => 'Basket', 'price_per_hour' => 175000, 'description' => 'Lapangan basket untuk latihan tim, sparring, dan permainan bersama.'],
        ] as $court) {
            Court::firstOrCreate(['name' => $court['name']], $court);
        }
    }
}
