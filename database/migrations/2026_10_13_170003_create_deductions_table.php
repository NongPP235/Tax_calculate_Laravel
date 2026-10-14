<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type_name');
            $table->decimal('amount', 15, 2);
            $table->decimal('allowed_amount', 15, 2);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
