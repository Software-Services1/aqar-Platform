<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            // رقم العقد يبدأ بـ 62 ويُولَّد تلقائياً
            $table->string('contract_number')->unique();

            $table->string('project_name');
            $table->string('developer_name');
            $table->string('developer_phone')->nullable();
            $table->string('neighborhood')->nullable();          // الحي (لفلاتر التقارير)

            // نوع العقد: حصري | وساطة | تسويق
            $table->enum('contract_type', ['exclusive', 'brokerage', 'marketing'])->default('brokerage');

            // المسؤول عن العقد (موظف)
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            // المندوب
            $table->foreignId('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            // منشئ العقد (المشرف/المدير) — قد لا يكون معنيّاً بالتراخيص
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            // حالة العقد: انتظار الموافقة | تمت الموافقة | انتهت المدة دون موافقة | ملغي
            $table->enum('approval_status', ['pending', 'approved', 'finished', 'expired', 'cancelled'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('approval_status');
            $table->index('end_date');
            $table->index('contract_type');
            $table->index('neighborhood');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
