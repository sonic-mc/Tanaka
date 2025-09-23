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
    {Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->string('action'); // e.g. login, logout, created patient
        $table->string('module')->nullable(); // e.g. patients, billing
        $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
        $table->text('description')->nullable();
        $table->timestamp('timestamp')->useCurrent();
    });
    
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
