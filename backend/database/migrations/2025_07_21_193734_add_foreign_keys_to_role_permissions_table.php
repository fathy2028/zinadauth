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
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign(['role_id'], 'role_permissions_ibfk_1')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['permission_id'], 'role_permissions_ibfk_2')->references(['id'])->on('permissions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('role_permissions_ibfk_1');
            $table->dropForeign('role_permissions_ibfk_2');
        });
    }
};
