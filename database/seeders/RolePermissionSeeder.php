<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Création des rôles 
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrateur']
        );

        $etudiant = Role::firstOrCreate(
            ['name' => 'etudiant'],
            ['description' => 'Étudiant']
        );

        $visiteur = Role::firstOrCreate(
            ['name' => 'visiteur'],
            ['description' => 'Visiteur']
        );

        // Création des permissions 
        $manageUsers = Permission::firstOrCreate(
            ['name' => 'manage_users'],
            ['description' => 'Gérer les utilisateurs']
        );

        $reserveSlot = Permission::firstOrCreate(
            ['name' => 'reserve_slot'],
            ['description' => 'Réserver un créneau']
        );

        $viewLab = Permission::firstOrCreate(
            ['name' => 'view_lab'],
            ['description' => 'Consulter infos du laboratoire']
        );

        // Associer permissions aux rôles
        $admin->permissions()->syncWithoutDetaching([$manageUsers->id, $reserveSlot->id, $viewLab->id]);
        $etudiant->permissions()->syncWithoutDetaching([$reserveSlot->id, $viewLab->id]);
        $visiteur->permissions()->syncWithoutDetaching([$viewLab->id]);

        // un admin par défaut 
        if (User::count() === 0) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0000000000',
                'role' => 'admin',
                'status' => 'Active'
            ]);

            // Lui attribuer le rôle admin
            $user->roles()->syncWithoutDetaching([$admin->id]);
        }
    }
}
