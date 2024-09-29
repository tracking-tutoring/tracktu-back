<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StatisticController extends Controller
{
    protected $msg;
    protected $data;
    protected $validation_errors;

    public function __construct()
    {
        $this->data = config('utilities.httpKeyResponse.data');
    }

    public function getStatisticsForCards()  
    {
        $number_of_tutors = User::where('role', 'tutor')->count();
        $number_of_admins = User::where('role', 'tracking')->count();
        $number_of_modules = Module::count();
        $number_of_sessions = Session::count();
        
        return response()->json([
            "{$this->data}" => [
                'tutors' => $number_of_tutors,
                'admins' => $number_of_admins,
                'modules' => $number_of_modules,
                'sessions' => $number_of_sessions,
            ],
        ]);

    }

    public function getHoursDone(string $type, int $id) 
    {
        $availableParameters = ['module', 'tutor', 'group'];

        if (!in_array($type, $availableParameters)) {
            throw new InvalidArgumentException('Invalid type provided');
        }
        switch ($type) {
            case 'module':
                $module = Module::findOrFail($id);
                $totalHoursDone = $module->tutors()->sum('module_tutor.hours_done');
                return response()->json([
                    "{$this->data}" => $totalHoursDone
                ]);
                break;

            case 'tutor':
                $tutor = User::findOrFail($id);
                $totalHoursDone = $tutor->modules()->sum('module_tutor.hours_done');
                return response()->json([
                    "{$this->data}" => $totalHoursDone
                ]);

            case 'group':
                $totalHoursDone = DB::table('affectations')
                ->join('module_tutor', function($join) {
                    $join->on('affectations.module_id', '=', 'module_tutor.module_id')
                        ->on('affectations.tutor_id', '=', 'module_tutor.tutor_id');
                })
                ->where('affectations.group_id', $id)
                ->sum('module_tutor.hours_done');

                return response()->json([
                    "{$this->data}" => $totalHoursDone
                ]);
                break;
            
        }
    }

    public function getHoursNotDone(string $type, int $id) 
    {
        $availableParameters = ['module', 'tutor', 'group'];

        if (!in_array($type, $availableParameters)) {
            throw new InvalidArgumentException('Invalid type provided');
        }
        switch ($type) {
            case 'module':
                $module = Module::findOrFail($id);
                $totalHoursNotDone = $module->tutors()->sum('module_tutor.hours_not_done');
                return response()->json([
                    "{$this->data}" => $totalHoursNotDone
                ]);
                break;

            case 'tutor':
                $tutor = User::findOrFail($id);
                $totalHoursNotDone = $tutor->modules()->sum('module_tutor.hours_not_done');
                return response()->json([
                    "{$this->data}" => $totalHoursNotDone
                ]);

            case 'group':
                $totalHoursNotDone = DB::table('affectations')
                ->join('module_tutor', function($join) {
                    $join->on('affectations.module_id', '=', 'module_tutor.module_id')
                        ->on('affectations.tutor_id', '=', 'module_tutor.tutor_id');
                })
                ->where('affectations.group_id', $id)
                ->sum('module_tutor.hours_not_done');

                return response()->json([
                    "{$this->data}" => $totalHoursNotDone
                ]);
                break;
            
        }
    }
}
