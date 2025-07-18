<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->unique(Team::class, 'slug', ignorable: fn(?Team $record) => $record),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        if (!Auth::check() || !Auth::user()->is_team_creator) {
            throw new \Exception('Unauthorized action.');
        }

        $team = Team::create($data);

        $team->users()->attach(auth()->user());

        return $team;
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->is_team_creator;
    }


}
