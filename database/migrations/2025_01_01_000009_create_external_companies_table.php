<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الشركات الخارجية التي تُنشأ لها عقود وساطة فرعية.
 * كيان مُدار (قابل لإعادة الاستخدام عبر عدة عقود فرعية) وقابل للفلترة في التقارير.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // اسم الشركة
            $table->string('contact_person')->nullable(); // الشخص المسؤول عن العقد
            $table->string('phone')->nullable();          // رقم الجوال
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_companies');
    }
};
