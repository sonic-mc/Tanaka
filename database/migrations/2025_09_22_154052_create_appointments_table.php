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
    {Schema::create('appointments', function (Blueprint $table) {
        $table->id();
    
        // Relationships
        $table->foreignId('patient_id')->constrained()->onDelete('cascade');
        $table->foreignId('staff_id')->constrained('users')->onDelete('cascade'); // psychiatrist/nurse/doctor
        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // admin/receptionist
        
        // Appointment details
        $table->date('date');                  // e.g. 2025-09-22
        $table->time('start_time');            // Start time of appointment
        $table->time('end_time')->nullable();  // Optional if not fixed
        $table->enum('type', [
            'consultation',
            'therapy_session',
            'follow_up',
            'emergency',
            'assessment',
        ])->default('consultation');
        
        // Status handling
        $table->enum('status', [
            'scheduled',   // confirmed booking
            'pending',     // waiting approval/confirmation
            'cancelled',   // cancelled by patient/staff
            'completed',   // finished session
            'no_show'      // patient didn’t attend
        ])->default('pending');
    
        // Meta information
        $table->string('location')->nullable();   // clinic/room/ward or "virtual"
        $table->string('meeting_link')->nullable(); // telehealth sessions
        $table->text('notes')->nullable();        // optional staff notes
        
        // Audit trail
        $table->timestamps();   // created_at, updated_at
        $table->softDeletes();  // deleted_at for safe recovery
    });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
