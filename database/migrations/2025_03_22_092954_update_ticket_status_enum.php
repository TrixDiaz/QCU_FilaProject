<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First convert to VARCHAR to avoid data loss
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_status VARCHAR(20)");
        
        // Update existing statuses if needed
        DB::statement("UPDATE tickets SET ticket_status = 'closed' WHERE ticket_status = 'resolved'");
        
        // Convert back to ENUM with all possible values
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_status 
            ENUM('open', 'in_progress', 'resolved', 'closed', 'archived') 
            NOT NULL DEFAULT 'open'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE tickets MODIFY COLUMN ticket_status 
            ENUM('open', 'in_progress', 'closed', 'archived') 
            NOT NULL DEFAULT 'open'");
    }
};
