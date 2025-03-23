<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // First check if columns don't exist before adding them
            if (!Schema::hasColumn('tickets', 'type')) {
                $table->string('type')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('tickets', 'subtype')) {
                $table->string('subtype')->nullable()->after('type');
            }
            if (!Schema::hasColumn('tickets', 'ticket_type')) {
                $table->string('ticket_type')->nullable()->after('subtype');
            }
            if (!Schema::hasColumn('tickets', 'ticket_number')) {
                $table->string('ticket_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('tickets', 'start_time')) {
                $table->timestamp('start_time')->nullable();
            }
            if (!Schema::hasColumn('tickets', 'end_time')) {
                $table->timestamp('end_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['type', 'subtype', 'ticket_type', 'ticket_number', 'start_time', 'end_time']);
        });
    }
};
