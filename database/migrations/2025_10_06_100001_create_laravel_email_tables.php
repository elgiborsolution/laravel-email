<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('le_email_templates', function(Blueprint $t){
            $t->id();
            $t->string('name')->unique();
            $t->string('subject');
            $t->text('html')->nullable();
            $t->text('text')->nullable();
            $t->string('from_email')->nullable();
            $t->string('from_name')->nullable();
            $t->timestamps();
        });

        Schema::create('le_broadcasts', function(Blueprint $t){
            $t->id();
            $t->string('name');
            $t->foreignId('template_id')->constrained('le_email_templates')->cascadeOnDelete();
            $t->string('provider_key')->nullable();
            $t->string('status')->default('draft');
            $t->json('headers')->nullable();
            $t->json('custom_args')->nullable();
            $t->timestamps();
            $t->index(['status']);
        });

        Schema::create('le_broadcast_recipients', function(Blueprint $t){
            $t->id();
            $t->foreignId('broadcast_id')->constrained('le_broadcasts')->cascadeOnDelete();
            $t->string('email')->index();
            $t->string('name')->nullable();
            $t->string('token')->unique();
            $t->timestamp('sent_at')->nullable();
            $t->string('provider_message_id')->nullable();
            $t->timestamp('opened_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->timestamp('bounced_at')->nullable();
            $t->timestamps();
        });

        Schema::create('le_email_events', function(Blueprint $t){
            $t->id();
            $t->foreignId('broadcast_id')->nullable()->constrained('le_broadcasts')->nullOnDelete();
            $t->foreignId('recipient_id')->nullable()->constrained('le_broadcast_recipients')->nullOnDelete();
            $t->string('event');
            $t->string('provider')->default('sendgrid');
            $t->json('payload')->nullable();
            $t->timestamps();
            $t->index(['event','provider']);
        });

        Schema::create('le_suppressions', function(Blueprint $t){
            $t->id();
            $t->string('email')->unique();
            $t->enum('reason', ['unsubscribe','bounce','spam','manual'])->default('unsubscribe');
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('le_suppressions');
        Schema::dropIfExists('le_email_events');
        Schema::dropIfExists('le_broadcast_recipients');
        Schema::dropIfExists('le_broadcasts');
        Schema::dropIfExists('le_email_templates');
    }
};
