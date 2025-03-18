<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class ReportBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = "Reports";
    
    protected static string $view = 'filament.app.pages.report-builder';
}
