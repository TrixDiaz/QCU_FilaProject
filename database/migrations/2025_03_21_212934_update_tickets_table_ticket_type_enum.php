<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First convert to VARCHAR to avoid data loss
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type VARCHAR(255)");
        
        // Update existing records if needed
        DB::statement("UPDATE tickets SET ticket_type = 'request' WHERE ticket_type IN ('asset_request', 'classroom_request', 'inquiry')");
        DB::statement("UPDATE tickets SET ticket_type = 'incident' WHERE ticket_type NOT IN ('request')");
        
        // Convert back to ENUM with new values
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type ENUM('incident', 'request') NOT NULL DEFAULT 'incident'");
    }

    public function down()
    {
        // Convert back to original ENUM values if needed to rollback
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type VARCHAR(255)");
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type ENUM('incident', 'asset_request', 'classroom_request', 'inquiry') NOT NULL DEFAULT 'incident'");
    }
};