<?php

namespace App\Filament\Reports;

use App\Models\Asset;
use App\Models\Ticket;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use Carbon\Carbon; 

class MaintenanceReport extends Report
{
    public ?string $heading = "Maintenance Report";
    public ?string $icon = 'heroicon-o-wrench-screwdriver';

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Maintenance Report')->title(),
                                Text::make('This report shows all maintenance records in the system')->subtitle(),
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
                        ->data(fn(?array $filters) => $this->maintenanceSummary($filters)),
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
                \Filament\Forms\Components\Select::make('priority')
                    ->label('Priority')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),
                    \Filament\Forms\Components\Select::make('status')
                    ->label('Status')
                    ->native(false)
                    ->options([
                        'all' => 'All',
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Unassigned' => 'Unassigned',
                        'Resolved' => 'Resolved',
                    ]),                
                \Filament\Forms\Components\Select::make('section')
                ->label('Section')
                ->native(false)
                ->options(\App\Models\Section::pluck('name', 'id')->toArray()), // Fetch sections dynamically
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
                                'priority' => null,
                                'status' => null,
                                'section' => null,
                                'date_from' => null,
                                'date_to' => null,
                            ]);
                        })
                ]),
            ]);
    }
    public function maintenanceSummary(?array $filters = []): Collection
{
    $query = Ticket::query()
        ->with(['asset', 'section'])
        ->where('ticket_type', 'incident') // Only fetch "incident" type tickets
        ->select([
            'tickets.id as ticket_id',
            'tickets.title as ticket_title',
            'tickets.description as ticket_description',
            'assets.name as asset_name',
            'assets.serial_number as asset_serial',
            'tickets.priority',
            'tickets.status',
            'sections.name as section_name',
            'tickets.created_at',
            'tickets.updated_at',
        ])
        ->join('assets', 'tickets.asset_id', '=', 'assets.id')
        ->join('sections', 'tickets.section_id', '=', 'sections.id');

    $filtersApplied = false;

    // Search Filter
    if (!empty($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('tickets.title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('tickets.description', 'like', '%' . $filters['search'] . '%')
                ->orWhere('assets.name', 'like', '%' . $filters['search'] . '%');
        });
        $filtersApplied = true;
    }

    // ✅ Apply Priority Filter
    if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
        $query->where('tickets.priority', $filters['priority']);
        $filtersApplied = true;
    }

    // ✅ Fix Status Filter (Ensure Exact Match)
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
       $query->whereRaw('LOWER(tickets.status) = ?', [strtolower($filters['status'])]);
       $filtersApplied = true;
    }


    // ✅ Apply Section Filter
    if (!empty($filters['section'])) {
        $query->where('tickets.section_id', $filters['section']);
        $filtersApplied = true;
    }

    // Date From Filter
    if (!empty($filters['date_from'])) {
        $query->whereDate('tickets.created_at', '>=', $filters['date_from']);
        $filtersApplied = true;
    }

    // Date To Filter
    if (!empty($filters['date_to'])) {
        $query->whereDate('tickets.created_at', '<=', $filters['date_to']);
        $filtersApplied = true;
    }

    $tickets = $query->latest('tickets.created_at')->get();

    // If no tickets match the filter, return "Nothing to show"
    if ($tickets->isEmpty()) {
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
            'column1' => 'Ticket Information',
            'column2' => 'Asset',
            'column3' => 'Priority',
            'column4' => 'Status',
            'column5' => 'Section',
            'column6' => 'Created At',
            'column7' => 'Updated At',
        ]
    ])->concat($tickets->map(function ($ticket) {
        return [
            'column1' => "[#{$ticket->ticket_id}] {$ticket->ticket_title} - {$ticket->ticket_description}",
            'column2' => "{$ticket->asset_name} (SN: {$ticket->asset_serial})",
            'column3' => ucfirst($ticket->priority),
            'column4' => ucfirst($ticket->status),
            'column5' => $ticket->section_name,
            'column6' => $this->formatDate($ticket->created_at),
            'column7' => $this->formatDate($ticket->updated_at),
        ];
    }));
}


// Helper function for formatting dates
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
