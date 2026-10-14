<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type_name');
            $table->string('method');
            $table->decimal('rate', 7, 4)->nullable(); // อัตรา ณ วันที่บันทึก
            $table->decimal('amount', 15, 2); // ค่าใช้จ่ายที่กรอกหรือยอดเหมา
            $table->decimal('allowed_amount', 15, 2); // ยอดที่หักได้จริง
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
