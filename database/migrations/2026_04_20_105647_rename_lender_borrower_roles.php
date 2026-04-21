<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildUsersTableForSqlite(
                fromRoleA: 'lender',
                toRoleA: 'institution',
                fromRoleB: 'borrower',
                toRoleB: 'individual',
                defaultRole: 'individual'
            );

            return;
        }

        DB::table('users')
            ->where('role', 'lender')
            ->update(['role' => 'institution']);

        DB::table('users')
            ->where('role', 'borrower')
            ->update(['role' => 'individual']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildUsersTableForSqlite(
                fromRoleA: 'institution',
                toRoleA: 'lender',
                fromRoleB: 'individual',
                toRoleB: 'borrower',
                defaultRole: 'lender'
            );

            return;
        }

        DB::table('users')
            ->where('role', 'institution')
            ->update(['role' => 'lender']);

        DB::table('users')
            ->where('role', 'individual')
            ->update(['role' => 'borrower']);
    }

    private function rebuildUsersTableForSqlite(
        string $fromRoleA,
        string $toRoleA,
        string $fromRoleB,
        string $toRoleB,
        string $defaultRole
    ): void {
        DB::statement('PRAGMA foreign_keys=OFF');

        Schema::create('users_tmp_role_migration', function ($table) use ($defaultRole) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'institution', 'individual'])->default($defaultRole);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement(
            "INSERT INTO users_tmp_role_migration (id, name, email, email_verified_at, password, role, remember_token, created_at, updated_at)
             SELECT id, name, email, email_verified_at, password,
                    CASE
                        WHEN role = '{$fromRoleA}' THEN '{$toRoleA}'
                        WHEN role = '{$fromRoleB}' THEN '{$toRoleB}'
                        ELSE role
                    END,
                    remember_token, created_at, updated_at
             FROM users"
        );

        Schema::drop('users');
        Schema::rename('users_tmp_role_migration', 'users');

        DB::statement('PRAGMA foreign_keys=ON');
    }
};
