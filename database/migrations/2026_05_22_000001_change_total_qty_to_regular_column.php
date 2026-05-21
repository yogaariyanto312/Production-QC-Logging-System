<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus generated column, ganti dengan kolom integer biasa
        DB::statement('ALTER TABLE production_logs DROP COLUMN total_qty');
        DB::statement('ALTER TABLE production_logs ADD COLUMN total_qty INT NOT NULL DEFAULT 0 AFTER shift3_qty');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE production_logs DROP COLUMN total_qty');
        DB::statement('ALTER TABLE production_logs ADD COLUMN total_qty INT GENERATED ALWAYS AS (shift1_qty + shift2_qty + shift3_qty) STORED AFTER shift3_qty');
    }
};
