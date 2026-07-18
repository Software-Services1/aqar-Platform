<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** الموظفون المصرّح لهم برؤية العقد (يحدّدهم منشئ العقد). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['contract_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_employee');
    }
};
