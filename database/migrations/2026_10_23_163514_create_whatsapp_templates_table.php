<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // e.g. 'new_booking', 'booking_confirmed'
            $table->string('event_label');         // Human-readable label
            $table->text('template_body');         // Message body with {{variables}}
            $table->boolean('is_active')->default(true);
            $table->boolean('send_to_customer')->default(true);
            $table->boolean('send_to_admin')->default(true);
            $table->json('available_variables')->nullable(); // info about replaceable vars
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
