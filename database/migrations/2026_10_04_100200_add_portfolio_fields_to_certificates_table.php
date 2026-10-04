<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('issuer')->nullable();
            $table->date('issued_at')->nullable();
            $table->string('image')->nullable();
            $table->string('description', 300)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['issuer', 'issued_at', 'image', 'description', 'sort_order']);
        });
    }
};
