<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
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

    public function getTutorModules()
    {
        /** @var \App\Models\User $user **/
        $user = auth()->user();
        $modules = $user->modules;
        $modules->transform(function ($module) {
            $module->picture = $module->getImageUrl();
            return $module;
        });

        return response()->json([
            "{$this->data}" => $modules,
        ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modules = Module::paginate();
        $modules->getCollection()->transform(function ($module) {
            $module->picture = $module->getImageUrl();
            return $module;
        });
        return response()->json([
            "{$this->data}" => $modules
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = Gate::inspect('create');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'weeks_duration' => ['required', 'numeric',],
            'picture' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module = new Module();
        $module->name = $request->name;
        $module->description = $request->description;
        $module->weeks_duration = $request->weeks_duration;
        $module->user_id = $request->user()->id;

        if ($request->has('picture')) {
            $path_module_img = $request->file('picture')->store('image_module', 'public');
            $module->picture = $path_module_img;
        }

        $module->save();

        return response()->json([
            "{$this->msg}" => 'Module enregistré.',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $module = Module::findOrFail($id);

        return response()->json([
            "{$this->data}" => $module
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Module $module)
    {
        $response = Gate::inspect('update');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'string'],
            'picture' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "{$this->validation_errors}" => $validator->errors(),
            ], 422);
        }

        $module->name = $request->name;
        $module->description = $request->description;

        if ($request->has('picture')) {
            if ($module->picture != '' || $module->picture != null) {
                Storage::disk('public')->delete($module->picture);
            }

            $path_module_img = $request->file('picture')->store('image_module', 'public');
            $module->picture = $path_module_img;
        }

        $module->save();

        return response()->json([
            "{$this->msg}" => 'Module mis à jour avec succès.'
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Module $module)
    {
        $response = Gate::inspect('delete');

        if (!$response->allowed()) {
            return response()->json([
                "{$this->msg}" => $response->message(),
            ], 403);
        };

        $module->delete();

        return response()->json([
            "{$this->msg}" => 'Module supprimé.'
        ]);
    }
}
