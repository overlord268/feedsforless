<?php

use App\Domains\Quotes\Models\QuoteRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->string('guest_first_name', 100)->nullable()->after('guest_contact_name');
            $table->string('guest_last_name', 100)->nullable()->after('guest_first_name');
        });

        QuoteRequest::query()
            ->whereNotNull('guest_contact_name')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $parts = preg_split('/\s+/', trim((string) $row->guest_contact_name), 2);
                    $row->update([
                        'guest_first_name' => $parts[0] ?? null,
                        'guest_last_name' => $parts[1] ?? null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropColumn(['guest_first_name', 'guest_last_name']);
        });
    }
};
