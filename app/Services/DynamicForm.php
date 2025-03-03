<?php

namespace App\Services;

use Filament\Forms;
use App\Models\Brand;
use App\Models\Category;
use App\Models\AssetTag;

final class DynamicForm
{
    public static function schema($model): array
    {
        $baseSchema = [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->required(),
        ];

        // Add model-specific fields
        if ($model === Brand::class) {
            $baseSchema[] = Forms\Components\Textarea::make('description')
                ->maxLength(255);
        } elseif ($model === Category::class) {
            $baseSchema[] = Forms\Components\Textarea::make('description')
                ->maxLength(255);
        } elseif ($model === AssetTag::class) {
            $baseSchema[] = Forms\Components\ColorPicker::make('color')
                ->required();
        }

        return $baseSchema;
    }

    public static function getEditData($record): array
    {
        return [
            'name' => $record->name,
            'is_active' => $record->is_active,
            'description' => $record->description ?? null,
            'color' => $record->color ?? null,
        ];
    }
}
