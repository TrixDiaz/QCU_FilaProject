<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, modify the column to VARCHAR to avoid data loss
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type VARCHAR(20)");

        // Update existing values
        DB::statement("UPDATE tickets SET ticket_type = 'request' WHERE ticket_type IN ('asset_request', 'classroom_request', 'general_inquiry')");
        DB::statement("UPDATE tickets SET ticket_type = 'incident' WHERE ticket_type NOT IN ('request')");

        // Now convert to ENUM with the correct values
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type ENUM('incident', 'request') NOT NULL");
    }

    public function down()
    {
        // Convert back to VARCHAR in case we need to rollback
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_type VARCHAR(20)");
    }
};
