<?php


namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Team profile';
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
}