<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('path');
            $table->string('repo')->nullable();
            $table->enum('type', ['php', 'laravel', 'wordpress'])->index();
            $table->string('php')->default(env('DEFAULT_PHP_VERSION'))->index();
            $table->timestamps();
			
			$table->engine = 'InnoDB';
			$table->charset = 'utf8mb4';
			$table->collation = 'utf8mb4_unicode_520_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
