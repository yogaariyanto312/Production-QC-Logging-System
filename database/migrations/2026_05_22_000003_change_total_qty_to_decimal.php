<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE production_logs MODIFY COLUMN total_qty DECIMAL(8,1) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE production_logs MODIFY COLUMN total_qty INT NOT NULL DEFAULT 0');
    }
};
