<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('invoice_id')->constrained('invoicess')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('patient_details')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');

            // Payment details
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'card', 'mobile_money', 'bank_transfer'])->default('cash');
            $table->string('transaction_ref')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
