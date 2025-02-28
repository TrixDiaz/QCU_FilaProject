<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\ToModel;

class AssetImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Asset([
            'category'     => $row[0], 
            'brand'        => $row[1], 
            'name'         => $row[2],
            'slug'         => $row[3],
            'asset_code'   => $row[4],
            'serial_number'=> $row[5], 
            'expiry_date'  => $row[6], 
            'status'       => $row[7], 
            //
        ]);
    }
}
