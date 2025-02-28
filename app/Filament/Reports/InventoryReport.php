<?php

namespace App\Filament\Reports;

use App\Models\Asset;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class InventoryReport extends Report
{

    public ?string $heading = "Inventory Report";
    public ?string $icon = 'heroicon-o-archive-box';

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Inventory Report')->title(),
                                Text::make('This report shows all Inventory records in the system')->subtitle(),
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
                            ->data(fn(?array $filters) => $this->inventorySummary($filters)),
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
                    \Filament\Forms\Components\Select::make('status')
                    ->label('Status')
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
                                'status' => null,
                                'date_from' => null,
                                'date_to' => null,
                            ]);
                        })
                ]),
            ]);
    }
    public function inventorySummary(?array $filters = []): Collection
    {
        $query = Asset::query()
            ->with(['brand', 'category', 'classroom'])
            ->select([
                'assets.*',
            ]);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('assets.name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('assets.serial_number', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('assets.asset_code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['asset_status']) && $filters['asset_status'] !== 'all') {
            $query->where('assets.status', $filters['asset_status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('assets.created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('assets.created_at', '<=', $filters['date_to']);
        }

        $assets = $query->latest('assets.created_at')->get();

        return collect([
            [
                'column1' => 'Name',
                'column2' => 'Category',
                'column3' => 'Brand',
                'column4' => 'Serial Number',
                'column5' => 'Asset Code',
                'column6' => 'Expiry Date',
                'column7' => 'Status',
                'column8' => 'Classroom',
                'column9' => 'Created At',
                'column10' => 'Updated At',
            ]
        ])->concat($assets->map(function ($asset) {
            return [
                'column1' => $asset->name,
                'column2' => $asset->category->name ?? 'Unknown',
                'column3' => $asset->brand->name ?? 'Unknown',
                'column4' => $asset->serial_number,
                'column5' => $asset->asset_code,
                'column6' => $this->formatDate($asset->expiry_date),
                'column7' => ucfirst($asset->status),
                'column8' => optional($asset->assetGroup?->classroom)->name ?? 'Unassigned',
                'column9' => $this->formatDate($asset->created_at),
                'column10' => $this->formatDate($asset->updated_at),
            ];
        }));
    }

    private function formatDate($date): string
    {
        return $date ? Carbon::parse($date)->format('F d, Y') : 'N/A';
    }
}
