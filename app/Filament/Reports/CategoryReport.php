<?php

namespace App\Filament\Reports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tag;
use Filament\Forms\Form;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Rename the class and properties to reflect the new purpose
class CategoryReport extends Report
{
    protected static bool $shouldRegisterNavigation = false;
    public ?string $heading = "Asset Count Report";
    public ?string $icon = 'heroicon-o-clipboard-document-list';

    // Update header text
    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Asset Count Report')->title(),
                                Text::make('This report shows asset counts by brand and tag')->subtitle(),
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
            ->schema([
                Footer\Layout\FooterRow::make()
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

    // Update filter form
    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('brand')
                    ->label('Brand')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(\App\Models\Brand::pluck('name', 'id')),

                Select::make('tags')
                    ->label('Tags')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(\App\Models\Tag::pluck('name', 'id')),

                Actions::make([
                    Action::make('reset')
                        ->label('Reset Filters')
                        ->color('danger')
                        ->action(function (Form $form) {
                            $form->fill([
                                'brand' => null,
                                'tags' => null,
                            ]);
                        }),
                ]),
            ]);
    }

    // Update report method
    public function categoryReport(?array $filters = []): Collection
    {
        // Count by Brand
        $brandQuery = Asset::query()
            ->with(['brand'])
            ->select('brand_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('brand_id');

        if (!empty($filters['brand'])) {
            $brandQuery->whereIn('brand_id', $filters['brand']);
        }

        $brandCounts = $brandQuery->get();

        // Count by Tag - Updated table name from 'asset_tag' to 'asset_tags'
        $tagQuery = Asset::query()
            ->join('asset_tags', 'assets.id', '=', 'asset_tags.asset_id')
            ->join('tags', 'asset_tags.asset_tag_id', '=', 'tags.id') // Updated column name
            ->select('tags.id', 'tags.name', DB::raw('COUNT(*) as total_count'))
            ->groupBy('tags.id', 'tags.name');

        if (!empty($filters['tags'])) {
            $tagQuery->whereIn('tags.id', $filters['tags']);
        }

        $tagCounts = $tagQuery->get();

        if ($brandCounts->isEmpty() && $tagCounts->isEmpty()) {
            return collect([
                [
                    'column1' => 'No assets found',
                    'column2' => '',
                    'column3' => '',
                ]
            ]);
        }

        $result = collect([
            [
                'column1' => 'Type',
                'column2' => 'Name',
                'column3' => 'Total Count',
            ]
        ]);

        // Add brand counts
        foreach ($brandCounts as $brand) {
            $result->push([
                'column1' => 'Brand',
                'column2' => $brand->brand->name ?? 'Unbranded',
                'column3' => $brand->total_count,
            ]);
        }

        // Add tag counts
        foreach ($tagCounts as $tag) {
            $result->push([
                'column1' => 'Tag',
                'column2' => $tag->name,
                'column3' => $tag->total_count,
            ]);
        }

        return $result;
    }
}
