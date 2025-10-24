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
        Schema::create('invoicess', function (Blueprint $table) {
            $table->id();
        
            // Relationships
            $table->foreignId('patient_id')->constrained('patient_details')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        
            // Invoice details
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_due', 12, 2)->default(0.00);
        
            // Status lifecycle
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
        
            // Dates
            $table->date('issue_date')->default(now());
            $table->date('due_date')->nullable();
        
            // Optional notes
            $table->text('notes')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoicess');
    }
};
