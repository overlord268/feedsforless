<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('delivery_zip', 20);
            $table->boolean('requires_liftgate')->default(false);
            $table->boolean('requires_appointment')->default(false);
            $table->decimal('total_estimated_cost', 12, 2)->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};