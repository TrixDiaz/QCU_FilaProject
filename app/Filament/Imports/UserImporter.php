<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('Full Name'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('John Doe'),
            ImportColumn::make('email')
                ->label(__('Email Address'))
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('johndoe@qcu.edu.ph'),
            ImportColumn::make('password')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('password123'),
            ImportColumn::make('approval_status')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean'])
                ->example('1'),
        ];
    }

    public function resolveRecord(): ?User
    {
        // Check if user with this email already exists
        return User::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public function mutateRecord(User $user): User
    {
        // Set basic user attributes
        $user->name = $this->data['name'];
        $user->email = $this->data['email'];
        $user->password = Hash::make($this->data['password']);
        $user->approval_status = $this->data['approval_status'] ? true : false;

        // Save the user first to ensure it exists before assigning roles
        $user->save();

        // Optionally assign a default role
        // $user->assignRole('professor');

        return $user;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
