<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $msg;
    protected $data;
    protected $validation_errors;

    public function __construct()
    {
        $this->msg = config('utilities.httpKeyResponse.message');
        $this->data = config('utilities.httpKeyResponse.data');
        $this->validation_errors = config('utilities.httpKeyResponse.validation_errors');
    }

    public function getTutorgroups(?int $moduleId = null) {
        /** @var \App\Models\User $user **/
        $user = auth()->user();

        if (is_null($moduleId)) {
            $groups = $user->groups()->with('students')->get();

            return response()->json([
                "{$this->data}" => $groups,
            ]);
        }

        $groups = $user->groups()->with('students')->where('module_id', $moduleId)->get();

        return response()->json([
            "{$this->data}" => $groups,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
