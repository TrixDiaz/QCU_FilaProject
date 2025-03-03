<?php

namespace App\Filament\Reports;

use App\Models\Asset;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoryReport extends Report
{
    protected static bool $shouldRegisterNavigation = false;
    public ?string $heading = "Category Report";

    public ?string $icon = 'heroicon-o-clipboard-document-list';

    public function header(Header $header): Header
    {
        return $header
            ->schema([Header\Layout\HeaderRow::make()
            ->schema([
                Header\Layout\HeaderColumn::make()
                    ->schema([
                        Text::make('Category Report')->title(),
                        Text::make('This report shows all asset counts in the system')->subtitle(),
                    ]),
                Header\Layout\HeaderColumn::make()
                    ->schema([
                        Text::make(now()->format('F, d Y'))->subtitle(),
                    ])->alignRight(),
            ])
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Body\Table::make()
                            ->data(fn(?array $filters) => $this->categoryReport($filters)),
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([Footer\Layout\FooterRow::make()
            ->schema([
                Footer\Layout\FooterColumn::make()
                    ->schema([
                        Text::make(config('app.name', 'Laravel'))->title()->primary(),
                        Text::make("All Rights Reserved")->subtitle(),
                    ]),
                Footer\Layout\FooterColumn::make()
                    ->schema([
                        Text::make("Generated on: " . now()->format('F d, Y')),
                    ])
                    ->alignRight(),
            ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('name')
                ->label('Name')
                ->multiple()
                ->searchable()
                ->preload()
                ->options([
                    'asset_1' => 'Asset 1',
                    'asset_2' => 'Asset 2',
                    'asset_3' => 'Asset 3',
                ]),

            Select::make('category')
                ->label('Category')
                ->multiple()
                ->searchable()
                ->preload()
                ->options([
                    'hardware' => 'Hardware',
                    'software' => 'Software',
                    'license' => 'License',
                    'components' => 'Components',
                ]),

            Select::make('brand')
                ->label('Brand')
                ->multiple()
                ->searchable()
                ->preload()
                ->options([
                    'apple' => 'Apple',
                    'dell' => 'Dell',
                    'asus' => 'Asus',
                    'lenovo' => 'Lenovo',
                ]),

            Actions::make([
                Action::make('reset')
                    ->label('Reset Filters')
                    ->color('danger')
                    ->action(function (Form $form) {
                        $form->fill([
                            'name' => null,
                            'category' => null,
                            'brand' => null,
                        ]);
                    }),
            ]),
            ]);
    }

    public function categoryReport(?array $filters = []): Collection
{
    $query = Asset::query()->with(['brand', 'category']);

    $filtersApplied = false;

    // Filter by name (if multiple names are selected)
    if (!empty($filters['name'])) {
        $query->whereIn('id', $filters['name']);
        $filtersApplied = true;
    }

    // Filter by category (if multiple categories are selected)
    if (!empty($filters['category'])) {
        $query->whereIn('category_id', $filters['category']);
        $filtersApplied = true;
    }

    // Filter by brand (if multiple brands are selected)
    if (!empty($filters['brand'])) {
        $query->whereIn('brand_id', $filters['brand']);
        $filtersApplied = true;
    }

    // Fetch grouped results with total count per category
    $assets = $query->select([
            'name',
            'brand_id',
            'category_id',
            DB::raw('COUNT(*) as total_count')
        ])
        ->groupBy('name', 'brand_id', 'category_id')
        ->get();

    // If no matching records are found, return a "Nothing to show" message
    if ($assets->isEmpty()) {
        return collect([
            [
                'column1' => 'Nothing to show',
                'column2' => '',
                'column3' => '',
                'column4' => '',
            ]
        ]);
    }

    // Return formatted collection for the report
    return collect([
        [
            'column1' => 'Name',
            'column2' => 'Brand',
            'column3' => 'Category',
            'column4' => 'Total Count',
        ]
    ])->concat($assets->map(function ($asset) {
        return [
            'column1' => $asset->name,
            'column2' => $asset->brand->name ?? 'N/A',
            'column3' => $asset->category->name ?? 'N/A',
            'column4' => $asset->total_count,
        ];
    }));
}

}
