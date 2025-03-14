<?php

namespace App\Services;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

final class DynamicForm
{
    public static function schema($model): array
    {
        return [
            Grid::make(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(function ($state, $set) {
                            if (!empty($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    TextInput::make('slug')
                        ->required()
                        ->unique($model, 'slug', ignoreRecord: true)
                        ->dehydrated()
                        ->readOnly(),
                ]),
        ];
    }
}
