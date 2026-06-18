<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('quote_request_id')
                ->nullable()
                ->after('user_id')
                ->constrained('quote_requests')
                ->nullOnDelete();

            $table->unique('quote_request_id');
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->string('message_type', 30)->default('text')->after('sender_user_id');
            $table->json('metadata')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropColumn(['message_type', 'metadata']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quote_request_id');
        });
    }
};
