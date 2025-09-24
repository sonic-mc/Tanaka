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
        Schema::create('backups', function (Blueprint $table) {
        $table->id();
    
        $table->string('file_path'); // Full path or relative reference
        $table->string('filename')->nullable(); // Optional filename for display
        $table->enum('type', ['database', 'files', 'full'])->default('full'); // Backup scope
        $table->enum('status', ['pending', 'completed', 'failed', 'restored'])->default('pending'); // Lifecycle state
        $table->text('notes')->nullable(); // Optional admin notes or context
    
        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Creator
        $table->timestamp('created_at')->useCurrent(); // Creation timestamp
        $table->timestamp('restored_at')->nullable(); // If restored, when
    
        $table->ipAddress('origin_ip')->nullable(); // Who triggered it
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
