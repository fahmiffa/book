<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First convert existing string data to numbers to avoid SQL errors
        DB::table('bookings')->whereIn('status', ['confirmed', 'pending'])->update(['status' => '3']);
        DB::table('bookings')->where('status', 'completed')->update(['status' => '2']);
        
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('status')->default(3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
