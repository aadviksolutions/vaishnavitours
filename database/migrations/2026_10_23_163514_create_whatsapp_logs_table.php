<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_key');
            $table->string('recipient_type'); // 'customer' | 'admin'
            $table->string('to_number');
            $table->text('message');
            $table->string('status')->default('pending'); // pending | sent | failed
            $table->text('response')->nullable();
            $table->nullableMorphs('related'); // morphs to Booking, Enquiry, etc.
            $table->timestamps();

            $table->index('event_key');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
    }
};
