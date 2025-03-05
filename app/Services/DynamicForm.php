<?php

namespace App\Services;

final class DynamicForm
{
    public static function schema($model): array
    {
        return [
            \Filament\Forms\Components\Grid::make(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, \Filament\Forms\Set $set) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }),

                    \Filament\Forms\Components\TextInput::make('slug')
                        ->extraAttributes(['x-model' => "name.replace(/\s+/g, '-').toLowerCase()"])
                        ->unique($model, 'slug', ignoreRecord: true)
                        ->dehydrated()
                        ->required()
                        ->disabled(),
                ]),
        ];
    }
}
