<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('tax_year');
            $table->string('title')->nullable();
            $table->string('married_status');
            $table->json('personal_details')->nullable();
            // เก็บผล ณ เวลาบันทึก เพื่อไม่ให้ประวัติเปลี่ยนตามข้อมูลหลัก
            $table->decimal('total_income', 15, 2);
            $table->decimal('exempt_income', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2);
            $table->decimal('total_deduction', 15, 2);
            $table->decimal('donation_allowed', 15, 2)->default(0);
            $table->decimal('net_income', 15, 2);
            $table->decimal('step_tax', 15, 2);
            $table->decimal('minimum_tax', 15, 2)->default(0);
            $table->decimal('tax_payable', 15, 2);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->decimal('half_year_tax', 15, 2)->default(0);
            $table->decimal('additional_tax', 15, 2)->default(0);
            $table->decimal('refund', 15, 2)->default(0);
            $table->string('calculation_version')->default('1');
            // ไม่ใช้ unique เพราะปีเดียวกันบันทึกได้หลายครั้ง
            $table->index(['user_id', 'tax_year']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_records');
    }
};
