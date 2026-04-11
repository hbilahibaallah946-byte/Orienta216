<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FieldSeeder extends Seeder
{
    public function run(): void
    {
        $file = Storage::get('fields.csv');
        $lines = explode("\n", $file);

        foreach ($lines as $line) {
            if(trim($line) == '') continue;

            $data = str_getcsv($line);

            DB::table('fields')->insert([
                'name' => $data[0],
                'university_id' => $data[1],
                'bac_type_required' => $data[2],
                'min_score' => $data[3],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
