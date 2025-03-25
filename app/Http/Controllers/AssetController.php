<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function downloadExampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="example.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // Add CSV header
            fputcsv($handle, ['serial_number', 'name', 'asset_code', 'status', 'expiry_date', 'brand', 'category']);

            // Add an example row
            fputcsv($handle, ['SN12345', 'Laptop', 'AC001', 'available', '2025-12-31', 'Dell', 'Electronics']);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
