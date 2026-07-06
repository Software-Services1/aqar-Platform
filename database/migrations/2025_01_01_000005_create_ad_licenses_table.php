<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_licenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            // الموظف الذي أنشأ الترخيص (لكل موظف ترخيص واحد فقط لنفس العقد)
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // رقم الترخيص يبدأ بـ 72 ويُولَّد تلقائياً
            $table->string('license_number')->unique();

            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->json('platforms')->nullable();                       // المنصات (multi-select) كـ JSON
            $table->enum('status', ['pending', 'created_unpublished', 'complete'])->default('pending');
            $table->unsignedSmallInteger('platform_count')->default(0); // عدد المنصات المنشور عليها
            $table->text('notes')->nullable();
            $table->timestamps();

            // موظف واحد = ترخيص واحد لكل عقد
            $table->unique(['contract_id', 'employee_id']);
            $table->index('platform_count');
            $table->index('status');
            $table->index('issue_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_licenses');
    }
};
