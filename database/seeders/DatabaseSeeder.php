<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        User::factory()->create([
             'name' => 'Maximilian Schulze',
             'email' => 'm.schulze@ass-maschinenbau.de',
        ]);

        User::factory()->create([
            'name' => 'Tom Schmitz',
            'email' => 't.schmitz@ass-maschinenbau.de',
        ]);

        User::factory()->create([
            'name' => 'Kay Federwisch',
            'email' => 'k.federwisch@ass-maschinenbau.de',
        ]);

        User::factory()->create([
            'name' => 'Max Pustelnik',
            'email' => 'm.pustelnik@ass-maschinenbau.de',
        ]);

        User::factory()->create([
            'name' => 'Ralf Roderwieser',
            'email' => 'r.roderwieser@ass-maschinenbau.de',
        ]);
    }
}
