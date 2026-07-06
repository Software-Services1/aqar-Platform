<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /** كل الصلاحيات المتاحة في النظام (تُدار ديناميكياً من واجهة الأدوار) */
    public const PERMISSIONS = [
        'view-reports'          => 'عرض التقارير',
        'manage-contracts'      => 'إدارة العقود',
        'create-subcontract'    => 'إنشاء عقد فرعي',
        'manage-licenses'       => 'إدارة كل التراخيص',
        'manage-employees'      => 'إدارة الموظفين',
        'manage-representatives' => 'إدارة المناديب',
        'manage-external-companies' => 'إدارة الشركات الخارجية',
        'manage-roles'          => 'إدارة الأدوار والصلاحيات',
        'manage-settings'       => 'إعدادات النظام',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISSIONS) as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(array_keys(self::PERMISSIONS));

        // الموظف: لا صلاحيات إدارية (يُنشئ ترخيصه ويرى بياناته فقط)
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    }
}
