<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum AssetStatus: string implements HasLabel, HasColor, HasIcon
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DEPLOY = 'deploy';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::INACTIVE => 'inactive',
            self::DEPLOY => 'deploy',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIVE => 'primary',
            self::INACTIVE => 'gray',
            self::DEPLOY => 'secondary',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-m-bolt',
            self::INACTIVE => 'heroicon-m-bolt-slash',
            self::DEPLOY => 'heroicon-m-computer-desktop',
        };
    }
}
