<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Helper\generateSessions;
use App\Models\Affectation;
use App\Models\Group;
use App\Models\Module;
use App\Models\Session;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $tutor1 = User::factory()->state(['role' => 'tutor'])->create();
        $tutor2 = User::factory()->state(['role' => 'tutor'])->create();

        $tracking1 = User::factory()->state(['role' => 'tracking'])->create();
        $tracking2 = User::factory()->state(['role' => 'tracking'])->create();
        $tracking3 = User::factory()->state(['role' => 'tracking'])->create();

        $module1 = Module::factory()->state(['name' => 'Module 1', 'weeks_duration' => 10, 'user_id' => $tracking1->id])->create();
        $module2 = Module::factory()->state(['name' => 'Module 2', 'weeks_duration' => 10, 'user_id' => $tracking2->id])->create();

        $group1 = Group::factory()
                        ->state(['user_id' => $tracking1->id])
                        ->has(Student::factory()->count(6))
                        ->create();

        $group2 = Group::factory()
                        ->state(['user_id' => $tracking2->id])
                        ->has(Student::factory()->count(6))
                        ->create();

        $group3 = Group::factory()
                        ->state(['user_id' => $tracking3->id])
                        ->has(Student::factory()->count(6))
                        ->create();

        $students = Student::factory()->count(30)->create();


         // Affectation des tuteurs aux modules et groupes
         $affectation1 = Affectation::create([
            'tutor_id' => $tutor1->id,
            'module_id' => $module1->id,
            'group_id' => $group1->id,
            'assigned_by' => $tracking1->id
        ]);

        $affectation2 = Affectation::create([
            'tutor_id' => $tutor1->id,
            'module_id' => $module2->id,
            'group_id' => $group2->id,
            'assigned_by' => $tracking2->id
        ]);

        $affectation3 = Affectation::create([
            'tutor_id' => $tutor2->id,
            'module_id' => $module1->id,
            'group_id' => $group3->id,
            'assigned_by' => $tracking3->id
        ]);

        // Lier les tuteurs aux modules via la table intermédiaire
        DB::table('module_tutor')->insert([
            ['tutor_id' => $tutor1->id, 'module_id' => $module1->id, 'assigned_by' => $tracking1->id],
            ['tutor_id' => $tutor1->id, 'module_id' => $module2->id, 'assigned_by' => $tracking2->id],
            ['tutor_id' => $tutor2->id, 'module_id' => $module1->id, 'assigned_by' => $tracking3->id],
        ]);

        // Génération automatique des séances pour chaque affectation
        $generate_session_instance1 = new generateSessions($affectation1->tutor_id, $module1);
        $generate_session_instance2 = new generateSessions($affectation2->tutor_id, $module2);
        $generate_session_instance3 = new generateSessions($affectation3->tutor_id, $module1);
        
        $generate_session_instance1->generateSessionsForModule();
        $generate_session_instance2->generateSessionsForModule();
        $generate_session_instance3->generateSessionsForModule();
        
    }
}
