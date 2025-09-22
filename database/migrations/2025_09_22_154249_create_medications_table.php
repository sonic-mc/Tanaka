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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
        
            // Basic info
            $table->string('name'); // e.g. "Paracetamol"
            $table->string('brand')->nullable(); // e.g. "Panado"
            $table->string('dosage_form'); // e.g. "Tablet", "Syrup"
            $table->string('strength')->nullable(); // e.g. "500mg"
        
            // Inventory management
            $table->integer('quantity')->default(0); // stock level
            $table->integer('reorder_level')->default(10); // threshold for low stock alerts
            $table->decimal('unit_price', 10, 2)->nullable(); // cost per unit
        
            // Expiry tracking
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
        
            // Supplier details
            $table->string('manufacturer')->nullable();
            $table->string('supplier')->nullable();
        
            // Status
            $table->enum('status', ['available', 'out_of_stock', 'expired'])->default('available');
        
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
