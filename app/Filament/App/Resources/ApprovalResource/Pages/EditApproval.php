<?php

namespace App\Filament\App\Resources\ApprovalResource\Pages;

use App\Filament\App\Resources\ApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApproval extends EditRecord
{
    protected static string $resource = ApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
