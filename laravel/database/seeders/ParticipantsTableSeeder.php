<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('participants')->insert([
            [
                'id' => 101,
                'dni' => '12345678A',
                'first_name' => 'Ana',
                'last_name' => 'Dev',
                'phone' => '+34600111222',
                'emergency_phone' => '+34600999999',
                'status' => 'not_presented',
                'lunch_sandwich' => true,
                'dinner_sandwich' => true,
            ],
            [
                'id' => 102,
                'dni' => '87654321B',
                'first_name' => 'Juan',
                'last_name' => 'Dev',
                'phone' => '+34600222333',
                'emergency_phone' => '+34600998888',
                'status' => 'not_presented',
                'lunch_sandwich' => true,
                'dinner_sandwich' => false,
            ],
            [
                'id' => 103,
                'dni' => '56781234C',
                'first_name' => 'Marta',
                'last_name' => 'Dev',
                'phone' => '+34600333444',
                'emergency_phone' => '+34600997777',
                'status' => 'not_presented',
                'lunch_sandwich' => false,
                'dinner_sandwich' => true,
            ],
        ]);
    }
}
