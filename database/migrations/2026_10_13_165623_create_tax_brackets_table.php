<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tax_year');
            $table->decimal('lower_bound', 15, 2);
            $table->decimal('upper_bound', 15, 2)->nullable(); // null คือไม่มีเพดาน
            $table->decimal('rate', 7, 4);
            $table->unique(['tax_year', 'lower_bound']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_brackets');
    }
};
