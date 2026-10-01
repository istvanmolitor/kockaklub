<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Kulcs')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->helperText('A sablonokban ezzel a kulccsal lehet hivatkozni az értékre, pl. setting(\'kulcs\').'),
                TextInput::make('label')
                    ->label('Megnevezés'),
                TextInput::make('value')
                    ->label('Érték'),
            ]);
    }
}
