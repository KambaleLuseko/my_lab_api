<?php

namespace App\Http\Controllers;

use App\Models\Salles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SallesController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all buildings from the database
        $buildings = Salles::all();

        // Return a JSON response
        return response()->json(['data'=>$buildings], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'opened_at' => ['nullable', 'string'],
            'closed_at' => ['nullable', 'string'],
            'status' => ['string', Rule::in(['Active', 'Inactive', 'Maintenance'])],
        ]);
        
        $validatedData['uuid'] = Controller::uuidGenerator('RM');
        $building = Salles::create($validatedData);

        return response()->json($building, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function show(Request $building)
    {
        // Laravel's Route Model Binding automatically finds the building for you.
        // It returns a 404 if not found.
        
        // Return a JSON response
        return response()->json($building, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'opened_at' => ['nullable', 'string'],
            'closed_at' => ['nullable', 'string'],
            'status' => ['string', Rule::in(['Active', 'Inactive', 'Maintenance'])],
        ]);
         $checkDuplicate = Salles::where('name', $request->name)->where('id', '!=', $id)->first();
        //  print($user);
        if ($checkDuplicate) {
            return response()->json(['message' => 'Room with this name already exists'], 403);
        }
         unset($validatedData['uuid']);
        
        $data=Salles::find($id)->update($validatedData);

        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Building  $building
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salles $building)
    {
        // Delete the building using the model binding instance
        $building->delete();

        // Return a 204 No Content status for a successful deletion
        return response()->json(null, 204);
    }
}
