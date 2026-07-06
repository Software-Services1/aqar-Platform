<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول إشعارات مخصص (يطابق المخطط المطلوب)
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['new_contract', 'expiring_license']);
            $table->string('message');
            $table->nullableMorphs('notifiable'); // ربط اختياري بالعقد/الترخيص
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
