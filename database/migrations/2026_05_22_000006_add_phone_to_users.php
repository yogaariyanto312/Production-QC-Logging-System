<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER department');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP COLUMN phone');
    }
};
