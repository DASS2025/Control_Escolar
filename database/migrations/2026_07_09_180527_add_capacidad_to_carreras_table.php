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
        Schema::table('Carreras', function (Blueprint $table) {
            $table->unsignedSmallInteger('capacidad')->default(300)->after('total_creditos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Carreras', function (Blueprint $table) {
            $table->dropColumn('capacidad');
        });
    }
};
