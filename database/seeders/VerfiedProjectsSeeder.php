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

            [
                'blockchain_id' => 1,
                'identifier' => 'GDSTRSHXHGJ7ZIVRBXEYE5Q74XUVCUSEKEBR7UCHEUUEK72N7I7KJ6JH',
                'asset_code' => 'SHX',
                'name' => 'Stronghold',
                'status' => 1,
            ],

            [
                'blockchain_id' => 1,
                'identifier' => 'GAB7STHVD5BDH3EEYXPI3OM7PCS4V443PYB5FNT6CFGJVPDLMKDM24WK',
                'asset_code' => 'LSP',
                'name' => 'Lumenswap',
                'status' => 1,
            ],

            [
                'blockchain_id' => 1,
                'identifier' => 'GBHFGY3ZNEJWLNO4LBUKLYOCEK4V7ENEBJGPRHHX7JU47GWHBREH37UR',
                'asset_code' => 'SSLX',
                'name' => 'Cassator Corp',
                'status' => 1,
            ],
        ];

        foreach ($projects as $project) {
            VerifiedProject::create($project);
        }
    }
}
