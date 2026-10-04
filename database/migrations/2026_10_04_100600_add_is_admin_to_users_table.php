<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds explicit administrator authorization.
 *
 * DECISION — why every existing row is marked as an admin:
 *
 * Before this column existed, authorization was effectively "is authenticated".
 * Every account that exists today therefore already had full portfolio write and
 * delete access, and the dashboard is a single-owner application. Marking existing
 * rows as admins preserves exactly the access they already have, so nobody is
 * locked out by this migration. Rows created *after* the migration default to
 * non-admin, which is the safe direction.
 *
 * No email address is guessed and no row is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        // Preserve the pre-existing access level of every current account.
        DB::table('users')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
