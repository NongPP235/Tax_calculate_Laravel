<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_type_id')->constrained()->restrictOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unsignedSmallInteger('tax_year');
            $table->string('method'); // flat หรือ actual
            $table->decimal('rate', 7, 4)->nullable(); // 0.50 หมายถึง 50%
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->string('limit_group')->nullable(); // กลุ่มที่ใช้เพดานร่วมกัน
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['tax_year', 'code']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_types');
    }
};
