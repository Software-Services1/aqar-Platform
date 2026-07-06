<?php

use App\Http\Controllers\AdLicenseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// المصادقة
Route::get('login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'login'])->middleware('guest');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // الملف الشخصي (لكل المستخدمين)
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    // العقود
    Route::resource('contracts', ContractController::class);
    // العقود الفرعية (بصلاحية create-subcontract)
    Route::get('contracts/{contract}/sub', [ContractController::class, 'createSub'])->name('contracts.sub.create');
    Route::post('contracts/{contract}/sub', [ContractController::class, 'storeSub'])->name('contracts.sub.store');

    // التراخيص (لكل موظف ترخيص واحد لكل عقد) + تعديل/حذف
    Route::get('contracts/{contract}/license/create', [AdLicenseController::class, 'create'])->name('licenses.create');
    Route::post('contracts/{contract}/license', [AdLicenseController::class, 'store'])->name('licenses.store');
    Route::get('licenses', [AdLicenseController::class, 'index'])->name('licenses.index');
    Route::get('licenses/{license}/edit', [AdLicenseController::class, 'edit'])->name('licenses.edit');
    Route::put('licenses/{license}', [AdLicenseController::class, 'update'])->name('licenses.update');
    Route::delete('licenses/{license}', [AdLicenseController::class, 'destroy'])->name('licenses.destroy');

    // الإشعارات
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // التقارير
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // الإدارة (محميّة بالصلاحيات داخل المتحكمات)
    Route::resource('employees', EmployeeController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('representatives', RepresentativeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('external-companies', \App\Http\Controllers\ExternalCompanyController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('platforms', PlatformController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});
