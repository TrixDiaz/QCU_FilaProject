<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\AssetResource\Pages;
use App\Filament\App\Resources\AssetResource\RelationManagers;
use App\Models\Asset;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;




class AssetResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'publish'
        ];
    }

    protected static ?string $model = Asset::class;

    protected static ?string $navigationGroup = 'Assets';
    protected static ?string $navigationLabel = 'Asset';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of active Asset';


    public static function generateUniqueCode()
    {
        do {
            $code = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10));
        } while (\Illuminate\Support\Facades\DB::table('assets')->where('asset_code', $code)->exists());

        return $code;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->createOptionForm(\App\Services\DynamicForm::schema(\App\Models\Category::class))
                            ->editOptionForm(function ($record) {
                                return \App\Services\DynamicForm::schema(\App\Models\Category::class);
                            }),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->createOptionForm(\App\Services\DynamicForm::schema(\App\Models\Brand::class))
                            ->editOptionForm(function ($record) {
                                return \App\Services\DynamicForm::schema(\App\Models\Brand::class);
                            }),
                        Forms\Components\Select::make('asset_tag_id')
                            ->relationship('assetTags', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->native(false)
                            ->createOptionForm(\App\Services\DynamicForm::schema(\App\Models\AssetTag::class))
                            ->editOptionForm(function ($record) {
                                // Always return the form schema, even for inactive/deleted records
                                return \App\Services\DynamicForm::schema(\App\Models\AssetTag::class);
                            }),
                        Forms\Components\Toggle::make('show_expiry_date')
                            ->label('Add Expiry Date')
                            ->reactive(),
                    ])->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                $set('asset_code', \Illuminate\Support\Str::slug($state) . '-' . self::generateUniqueCode());;
                            })
                            ->required(),
                        Forms\Components\TextInput::make('asset_code')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->unique(\App\Models\Asset::class, 'asset_code', ignoreRecord: true),

                        Forms\Components\TextInput::make('serial_number')
                            ->required()
                            ->minLength(8)
                            ->maxLength(20)
                            ->extraAlpineAttributes([
                                'style' => 'text-transform: uppercase;',
                                'class' => 'uppercase',
                                '@input' => "serial_number = serial_number.toUpperCase()"

                            ]),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->native(false)
                            ->visible(fn(Forms\Get $get) => $get('show_expiry_date')),

                        Forms\Components\Select::make('status')
                            ->options(\App\Enums\AssetStatus::class)
                            ->native(false)
                            ->extraAttributes(['style' => 'text-transform:uppercase'])
                            ->visibleOn(['edit', 'view'])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->description(fn($record): string => $record->asset_code)
                    ->searchable(['name', 'asset_code'])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand & Category')
                    ->description(function ($record): string {
                        if (!$record->category) {
                            return 'No Category';
                        }
                        return $record->category->name;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->extraAttributes(['style' => 'text-transform:uppercase'])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assetTags.name')
                    ->label('Tags')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->limitList(1)
                    ->expandableLimitedList(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->extraAttributes(['style' => 'text-transform:uppercase'])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('assign')
                    ->label('Assign')
                    ->button()
                    ->color('secondary')
                    ->visible(fn($record) => $record->status === 'active')
                    ->form(function ($record) {
                        return \App\Services\AssignAssetForm::schema($record);
                    })
                    ->action(function (array $data, $record) {
                        \App\Models\AssetGroup::create([
                            'asset_id' => $record->id,
                            'classroom_id' => $data['classroom'],
                            'name' => $data['name'],
                            'code' => $data['code'],
                            'status' => 'active',
                            'created' => now(),
                            'updated' => now(),
                        ]);

                        // Update the status of the asset to 'deploy'
                        $record->update(['status' => 'deploy']);

                        // Optionally, you can add a notification here
                        \Filament\Notifications\Notification::make()
                            ->title('Asset Assigned')
                            ->body('The asset has been successfully assigned.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('View'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit')
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Archive')
                        ->tooltip('Archive')
                        ->modalHeading('Archive Asset'),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make()
                        ->color('secondary'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->except(["id", "name", "brand_id"])
                            ->withFilename(date('Y-m-d') . '-Assets.xlsx'),

                        ExcelExport::make()
                            ->fromTable()
                            ->only([
                                'name',
                                'serial_number',
                                'assetTags.name',
                                'expiry_date',
                                'status',
                                'created_at',
                                'updated_at',
                            ])
                            ->withFilename(date('Y-m-d') . '-Filtered-Assets.xlsx'),
                    ])

                ]),
            ])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
        ];
    }
}
