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

            // رقم العقد (من منصة أخرى) — قد يكون فارغاً في المسودة
            $table->string('contract_number')->nullable()->unique();

            $table->string('project_name')->nullable();
            $table->string('developer_name')->nullable();
            $table->string('developer_phone')->nullable();
            $table->string('neighborhood')->nullable();          // الحي (لفلاتر التقارير)

            // نوع العقد: حصري | وساطة | تسويق
            $table->enum('contract_type', ['exclusive', 'brokerage', 'marketing'])->default('brokerage');
            // نوع الصفقة: إيجار | بيع
            $table->enum('transaction_type', ['rent', 'sale'])->default('sale');
            // العقد الأصل (للعقود الفرعية لشركات أخرى)
            $table->unsignedBigInteger('parent_id')->nullable();
            // الشركة الخارجية (للعقود الفرعية)
            $table->unsignedBigInteger('external_company_id')->nullable();

            // المسؤول عن العقد (اسم حر — قد لا يكون مستخدماً في النظام)
            $table->string('responsible_name')->nullable();
            $table->string('responsible_phone')->nullable();
            // المندوب
            $table->foreignId('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            // منشئ العقد (المشرف/المدير) — قد لا يكون معنيّاً بالتراخيص
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // حالة العقد: انتظار الموافقة | تمت الموافقة | انتهت المدة دون موافقة | ملغي
            $table->enum('approval_status', ['pending', 'approved', 'finished', 'expired', 'cancelled'])->default('pending');
            // مسودة: العقد ناقص البيانات
            $table->boolean('is_draft')->default(false);

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('approval_status');
            $table->index('end_date');
            $table->index('contract_type');
            $table->index('neighborhood');
            $table->index('parent_id');
            $table->index('external_company_id');
            $table->index('transaction_type');
            $table->index('is_draft');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
