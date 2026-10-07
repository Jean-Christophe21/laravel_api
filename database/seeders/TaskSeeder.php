<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\task;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'title' => 'Tâche de test API',
            'description' => 'Vérifier le bon fonctionnement des endpoints REST',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        Task::factory()->count(50)->create();
    }
}
