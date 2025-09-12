<?php

namespace App\Http\Controllers;

use App\Models\Salles;
use App\Models\Services;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    //Afficher tous les services disponibles 
    public function index()
    {
       
        $services = Services::with('salle')->get();
        return response()->json($services);
    }

     //Formulaire de creation     
    public function create()
    {
    
        $salles = Salles::all();
        return view('services.create', compact('salles'));
    }


     //Enregistremnt d'un nouveau service
    public function store(Request $request)
    {
    
       $validated= $request->validate([
            'name'  => 'required|string|max:255',
            'description' => 'nullable|string',
            'salles_id' => 'required|exists:salles,id',


        ]);
        $validated  ['uuid']=Controller::uuidGenerator('SRV');
        $services = Services::create($validated);

        return response()->json([
            'message' => 'Service crée avec succès',
            'service' => $services
        ]);
    }

  

     //Afficher un message spécifique
    public function show(Services $services)
    {
        return response()->json($services->load('salles'));
    }

   //Formulaire d'edition
   function edit(string $id)
    {
         $salles = Salles::all();
        return view('services.edit', compact('services', 'salles'));
    }

    //Mise à jour d'un service
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'salles_id' => 'required|exists:salles,id',
        ]);


        $service = Services::findOrFail($id);


        $service->update($request->all());

        return response()->json([
            'message' => 'Service mis à jour avec succès',
            'service' => $service
        ]);
    }

  
  // Supprimer un service
    public function destroy(Services $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service supprimé']);
    }
}
