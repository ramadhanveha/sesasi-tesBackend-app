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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->foreignId('verified_by')->nullable()->constrained(
                    table: 'users', indexName: 'verified_user_id');
            // $table->foreignId('verified_by')->constrained(
            //     table: 'users', indexName: 'posts_user_id'
            // )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('verified_by');
        });
    }
};
