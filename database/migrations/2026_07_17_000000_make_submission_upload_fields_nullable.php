<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('judul_laporan')->nullable()->change();
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('submissions')->whereNull('file_path')->delete();

        Schema::table('submissions', function (Blueprint $table) {
            $table->string('judul_laporan')->nullable(false)->change();
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
