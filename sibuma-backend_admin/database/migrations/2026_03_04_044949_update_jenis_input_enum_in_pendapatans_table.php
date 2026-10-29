<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pendapatans MODIFY COLUMN jenis_input ENUM('omzet', 'nilai_produk', 'nilai_jasa') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pendapatans MODIFY COLUMN jenis_input ENUM('omzet', 'nilai_produk') NOT NULL");
    }
};