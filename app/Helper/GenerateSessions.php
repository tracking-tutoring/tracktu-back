<?php

namespace App\Helper;

use App\Models\Affectation;
use App\Models\Module;
use App\Models\Session;
use App\Models\User;
use Carbon\Carbon;

class generateSessions {

    
    public function __construct(
        private Affectation $affectation,
        private Module $module,
        // private User $tracking

    ) {
       
    }

    // Méthode pour générer automatiquement les séances pour un module
    public function generateSessionsForModule()
    {
        // Générer une séance par semaine pendant la durée du module
        for ($i = 0; $i < $this->module->weeks_duration; $i++) {
            Session::create([
                'module_id' => $this->module->id,
                'tutor_id' => $this->affectation->tutor_id,
                'group_id' => $this->affectation->group_id,
                // 'marked_by' => $this->tracking->id,
                'start_time' => Carbon::now()->addWeeks($i),  // Séances hebdomadaires
                'end_time' => Carbon::now()->addWeeks($i)->addHours(2),
                // 'status' => 'non_effectuee' 
            ]);
        }
    }
}