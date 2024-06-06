<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $dbName;

    /**
     * Setup contructor.
     */
    public function __construct()
    {
        $this->dbName = env( 'DB_DATABASE', 'dev.test' );
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP DATABASE `{$this->dbName}`");
    }
};
