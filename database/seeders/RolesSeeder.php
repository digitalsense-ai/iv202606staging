<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::firstOrCreate(['name' => 'list clients']);
        Permission::firstOrCreate(['name' => 'show client']);
        Permission::firstOrCreate(['name' => 'add client']);
        Permission::firstOrCreate(['name' => 'edit client']);      
        Permission::firstOrCreate(['name' => 'delete client']);

        Permission::firstOrCreate(['name' => 'list vat registration']);
        Permission::firstOrCreate(['name' => 'show vat return']);
        Permission::firstOrCreate(['name' => 'add vat registration']);
        Permission::firstOrCreate(['name' => 'edit vat registration']);        
        Permission::firstOrCreate(['name' => 'delete vat registration']);

        // create roles and assign created permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin'])
        			->givePermissionTo(Permission::all());

        $companyAdmin = Role::firstOrCreate(['name' => 'company-admin'])
            		->givePermissionTo(['show client', 'list vat registration', 'show vat return', 'add vat registration', 'edit vat registration', 'delete vat registration']);

        $teamUser = Role::firstOrCreate(['name' => 'team-user'])
        			->givePermissionTo(['show client', 'list vat registration', 'show vat return']);
        			    		
        $clientUser = Role::firstOrCreate(['name' => 'client-user']);
     
		// Assign role to user
		User::find(1)->assignRole(['super-admin']);
		//User::find(2)->assignRole(['Admin']);   
    }
}
