<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Actions\CreateAction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Resources\Pages\CreateRecord::disableCreateAnother();
        \Filament\Actions\CreateAction::configureUsing(fn(CreateAction $action) => $action->createAnother(false));
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        \Filament\Notifications\Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }
}
