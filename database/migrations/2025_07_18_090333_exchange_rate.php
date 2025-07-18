<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('', function (Blueprint $table) {
            $table->id();
            $table->string('currency_from')->default('EUR');
            $table->string('currency_to')->index();
            $table->decimal('rate', 16, 8);
            $table->timestamp('retrieved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('');
    }
};
