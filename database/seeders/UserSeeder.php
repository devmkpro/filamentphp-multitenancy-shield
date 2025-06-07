<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::first();
        
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_team_creator' => true,
        ]);

        $user->teams()->attach($team, [
            'team_id' => $team->id,
        ]);

        $superAdminRole = Role::where('name', 'super_admin')
            ->where('team_id', $team->id)
            ->where('guard_name', 'web')
            ->first();

        if ($superAdminRole) {
            $user->roles()->attach($superAdminRole->id, [
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'team_id' => $team->id,
            ]);
        }
    }
}
