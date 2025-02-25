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
use Carbon\Carbon; 

class AssetReport extends Report
{
    public ?string $heading = "Asset Report";
    public ?string $icon = 'heroicon-o-clipboard-document-list';

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Asset Report')->title(),
                                Text::make('This report shows all assets in the system')->subtitle(),
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
                            ->data(fn(?array $filters) => $this->assetSummary($filters)),
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

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('search')
                    ->placeholder('Search')
                    ->autofocus(),
                \Filament\Forms\Components\Select::make('asset_status')
                    ->label('Asset Status')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                \Filament\Forms\Components\DatePicker::make('date_from')
                    ->label('Date From')
                    ->placeholder('Start Date')
                    ->timezone('Asia/Manila')
                    ->displayFormat('F d, Y')
                    ->maxDate(now())
                    ->native(false),
                \Filament\Forms\Components\DatePicker::make('date_to')
                    ->label('Date To')
                    ->placeholder('End Date')
                    ->timezone('Asia/Manila')
                    ->displayFormat('F d, Y')
                    ->maxDate(now())
                    ->native(false),
                \Filament\Forms\Components\Actions::make([
                    \Filament\Forms\Components\Actions\Action::make('reset')
                        ->label('Reset Filters')
                        ->color('danger')
                        ->action(function (Form $form) {
                            $form->fill([
                                'search' => null,
                                'asset_status' => null,
                                'date_from' => null,
                                'date_to' => null,
                            ]);
                        })
                ]),
            ]);
    }

    public function assetSummary(?array $filters = []): Collection
{
    $query = Asset::query()->with(['brand', 'category']);

    $filtersApplied = false;

    if (!empty($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%');
        });
        $filtersApplied = true;
    }

    if (!empty($filters['asset_status']) && $filters['asset_status'] !== 'all') {
        $query->where('status', $filters['asset_status']);
        $filtersApplied = true;
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('created_at', '>=', $filters['date_from']);
        $filtersApplied = true;
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('created_at', '<=', $filters['date_to']);
        $filtersApplied = true;
    }

    $assets = $query->latest('created_at')->get();

    // If no assets match the filter (e.g., no inactive assets), return a "Nothing to show" message.
    if ($assets->isEmpty()) {
        return collect([
            [
                'column1' => 'Nothing to show',
                'column2' => '',
                'column3' => '',
                'column4' => '',
                'column5' => '',
                'column6' => '',
                'column7' => '',
            ]
        ]);
    }

    return collect([
        [
            'column1' => 'Name',
            'column2' => 'Brand & Category',
            'column3' => 'Serial Number',
            'column4' => 'Expiry Date',
            'column5' => 'Status',
            'column6' => 'Created At',
            'column7' => 'Updated At',
        ]
    ])->concat($assets->map(function ($asset) {
        return [
            'column1' => $asset->name,
            'column2' => ($asset->brand->name ?? '') . ' - ' . ($asset->category->name ?? ''),
            'column3' => $asset->serial_number,
            'column4' => $this->formatDate($asset->expiry_date),
            'column5' => $asset->status,
            'column6' => $this->formatDate($asset->created_at),
            'column7' => $this->formatDate($asset->updated_at),
        ];
    }));
}


    private function formatDate($date): string
    {
        if (!$date) {
            return 'N/A';
        }

        try {
            return Carbon::parse($date)->format('F d, Y');
        } catch (\Exception $e) {
            return 'Invalid Date';
        }
    }
}
