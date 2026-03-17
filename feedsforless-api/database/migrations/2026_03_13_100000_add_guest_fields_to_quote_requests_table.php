<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('guest_email', 255)->nullable()->after('request_by_id');
            $table->string('guest_company_name', 255)->nullable()->after('guest_email');
            $table->string('guest_contact_name', 255)->nullable()->after('guest_company_name');
            $table->string('guest_phone', 50)->nullable()->after('guest_contact_name');
            $table->string('guest_destination_address', 500)->nullable()->after('guest_phone');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropForeign(['request_by_id']);
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE quote_requests MODIFY request_by_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('request_by_id')->nullable()->change();
            });
        }
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreign('request_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropForeign(['request_by_id']);
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE quote_requests MODIFY request_by_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('request_by_id')->nullable(false)->change();
            });
        }
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreign('request_by_id')->references('id')->on('users')->cascadeOnDelete();
        });
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['guest_email', 'guest_company_name', 'guest_contact_name', 'guest_phone', 'guest_destination_address']);
        });
    }
};
