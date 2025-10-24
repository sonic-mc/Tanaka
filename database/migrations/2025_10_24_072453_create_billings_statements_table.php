<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billings_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_details')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('outstanding_balance', 10, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings_statements');
    }
};
