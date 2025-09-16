<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index()
    {
   
        if (Auth::check()) {
            $role = Auth::user()->role;

            switch ($role) {
                case 'etudiant':
                  
                    return view('etudiant.index');

                case 'enseignant':
                    return view('enseignant.index');

                case 'visiteur':
                    return view('visiteur.index');

                case 'admin':
                  
                    return view('admin.index');
                        
                default:
                    return redirect()->route('login');
            }
        }

        return redirect()->route('login');
    
    }

}