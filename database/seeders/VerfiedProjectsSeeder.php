<?php

namespace Database\Seeders;

use App\Models\VerifiedProject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VerfiedProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VerifiedProject::truncate();

        $projects = [
            [
                'blockchain_id' => 1,
                'identifier' => 'GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ',
                'asset_code' => 'TKG',
                'name' => 'TokenGlade',
                'status' => 1,
            ],

            [
                'blockchain_id' => 1,
                'identifier' => 'GBNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67AQUA',
                'asset_code' => 'AQUA',
                'name' => 'Aquarius',
                'status' => 1,
            ],
        ];

        foreach ($projects as $project) {
            VerifiedProject::create($project);
        }
    }
}
