<?php

namespace App\Services;

use Filament\Forms;

final class DeployComputerSet
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    // Required Columns in AssetGroup Table
                    // Classroom ID
                    // Asset ID, sa Asset since hindi existing sa asset it should be create new para dito
                    // name
                    // code ito yung para sa Terminal No. - Unique dapat ito pero classroom
                    // status set into active 
                ]),
        ];
    }
}
