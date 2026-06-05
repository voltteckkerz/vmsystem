<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_visitors', function (Blueprint $table) {
            $table->string('visitor_name')->nullable()->after('visitor_id');
            $table->string('visitor_company')->nullable()->after('visitor_name');
        });

        // Back-fill existing rows with the current visitor name / company
        DB::statement("
            UPDATE visit_visitors vv
            JOIN visitors v ON v.id = vv.visitor_id
            JOIN companies c ON c.id = v.company_id
            SET vv.visitor_name    = v.name,
                vv.visitor_company = c.name
        ");
    }

    public function down(): void
    {
        Schema::table('visit_visitors', function (Blueprint $table) {
            $table->dropColumn(['visitor_name', 'visitor_company']);
        });
    }
};
