<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('controls')->insert([
            [
                'name' => 'Start',
                'km_point' => 0.0,
                'responsible' => 'Javi Ramos',
                'phone' => '+34660887081',
                'status' => 'preparing',
            ],
            [
                'name' => 'Snack 1',
                'km_point' => 20.0,
                'responsible' => 'Josep Massana',
                'phone' => '+34600112233',
                'status' => 'preparing',
            ],
            [
                'name' => 'Snack 2',
                'km_point' => 40.0,
                'responsible' => 'Rubén Fernández',
                'phone' => '+34600998877',
                'status' => 'preparing',
            ],
            [
                'name' => 'Finish',
                'km_point' => 74.0,
                'responsible' => 'Àngels Rebollo',
                'phone' => '+34600665544',
                'status' => 'preparing',
            ],
        ]);
    }
}
