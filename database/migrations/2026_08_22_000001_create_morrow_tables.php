<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('destination');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status');
            $table->text('summary');
            $table->unsignedInteger('budget_cents');
            $table->char('currency', 3)->default('EUR');
            $table->string('hero_tone')->default('moss');
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('trip_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('title');
            $table->string('subtitle');
            $table->unsignedTinyInteger('position');
            $table->string('neighborhood');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['trip_id', 'position']);
        });

        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_day_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('provider');
            $table->string('title');
            $table->string('confirmation_code')->nullable();
            $table->string('status');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('location');
            $table->unsignedInteger('price_cents')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('travelers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('initials', 3);
            $table->string('email');
            $table->string('role');
            $table->string('status');
            $table->timestamp('last_seen_at')->nullable();
            $table->string('color');
            $table->timestamps();
        });

        Schema::create('trip_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->string('actor_name');
            $table->string('action');
            $table->string('subject');
            $table->timestamp('occurred_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_activities');
        Schema::dropIfExists('travelers');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('trip_days');
        Schema::dropIfExists('trips');
    }
};
