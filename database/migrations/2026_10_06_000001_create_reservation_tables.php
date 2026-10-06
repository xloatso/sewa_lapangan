<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Futsal', 'Badminton', 'Basket']);
            $table->unsignedInteger('price_per_hour');
            $table->text('description');
            $table->string('image')->nullable();
            $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone', 25);
            $table->date('booking_date')->index();
            $table->unsignedInteger('total_price');
            $table->enum('status', ['Pending', 'Lunas', 'Batal'])->default('Pending');
            $table->timestamps();
        });
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->time('start_time');
            $table->unsignedInteger('duration_hours');
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_details');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('courts');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
