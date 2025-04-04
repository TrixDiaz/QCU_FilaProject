<?php

namespace App\Http\Controllers;

use App\Models\AssetGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function pullOutAsset(Request $request)
    {
        $assetGroupId = $request->input('asset_group_id');
        $assetGroup = AssetGroup::with('assets')->find($assetGroupId);

        if (!$assetGroup) {
            return response()->json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        try {
            DB::beginTransaction();

            // Get the asset directly
            $asset = $assetGroup->assets;

            // Update the asset status to pulled_out if it exists
            if ($asset) {
                $asset->status = 'pulled_out';
                $asset->save();
            }

            // Delete the asset group
            $assetGroup->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Asset successfully pulled out']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to pull out asset: ' . $e->getMessage()], 500);
        }
    }
}
