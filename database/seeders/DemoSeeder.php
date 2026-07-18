<?php

namespace Database\Seeders;

use App\Models\AdLicense;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Platform;
use App\Models\Representative;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $manager = Employee::firstOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'مدير النظام', 'password' => 'password', 'phone' => '0500000000']
        );
        $manager->assignRole('manager');

        $employees = collect([
            ['name' => 'سارة العتيبي', 'email' => 'sara@example.com'],
            ['name' => 'فهد القحطاني',  'email' => 'fahad@example.com'],
            ['name' => 'نورة الزهراني', 'email' => 'noura@example.com'],
        ])->map(function ($e) {
            $emp = Employee::firstOrCreate(['email' => $e['email']], ['name' => $e['name'], 'password' => 'password']);
            $emp->assignRole('employee');
            return $emp;
        });

        // دور مخصّص «مدير عقود» يملك صلاحية إدارة العقود (لعرض كل العقود دون دور مدير)
        $contractsManager = Role::firstOrCreate(['name' => 'مدير عقود', 'guard_name' => 'web']);
        $contractsManager->syncPermissions(['manage-contracts', 'view-reports']);
        $employees[1]->syncRoles(['مدير عقود']); // فهد يرى كل العقود بكل حالاتها

        // منح سارة صلاحية إنشاء عقد فرعي مباشرةً (لتجربة الميزة)
        $employees[0]->givePermissionTo('create-subcontract');

        $reps = collect([
            ['name' => 'خالد المطيري', 'phone' => '0551111111'],
            ['name' => 'عبدالله السبيعي', 'phone' => '0552222222'],
        ])->map(fn ($r) => Representative::firstOrCreate(['name' => $r['name']], $r));

        $responsibles = ['أحمد الغامدي', 'محمد السالم', 'وليد الدوسري', 'سعود الشمري'];
        $allPlatforms = Platform::pluck('name')->values()->all();
        $mk = fn (array $names) => collect($names)->map(fn ($n) => [
            'name' => $n,
            'url'  => 'https://ads.example.com/' . urlencode($n) . '/' . rand(1000, 9999),
        ])->all();

        // [مشروع, مطوّر, حالة, نوع, صفقة, حي, نهاية]
        $samples = [
            ['مشروع الواحة',  'شركة درّة',   'approved',  'brokerage', 'sale', 'النرجس',   now()->addDays(40)],
            ['برج اللؤلؤة',   'مطور النخبة', 'approved',  'exclusive', 'rent', 'الياسمين', now()->addDays(4)],
            ['حدائق الريان',  'دار البناء',  'pending',   'marketing', 'sale', 'الملقا',   now()->addDays(20)],
            ['أبراج السلام',  'إعمار الشرق', 'expired',   'brokerage', 'rent', 'الصحافة',  now()->subDays(10)],
            ['فلل المروج',    'ركن التطوير', 'cancelled', 'exclusive', 'sale', 'العارض',   now()->addDays(15)],
            ['ضاحية النخيل',  'بناء المستقبل','finished',  'marketing', 'rent', 'النرجس',   now()->subDays(2)],
            ['ربوة المدينة',  'تعمير',       'approved',  'brokerage', 'sale', 'القيروان', now()->addDays(70)],
        ];

        $first = null;
        foreach ($samples as $i => [$project, $dev, $status, $type, $deal, $hood, $end]) {
            $contract = Contract::create([
                'contract_number'   => '62' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'project_name'      => $project,
                'developer_name'    => $dev,
                'developer_phone'   => '0539' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'neighborhood'      => $hood,
                'contract_type'     => $type,
                'transaction_type'  => $deal,
                'responsible_name'  => $responsibles[$i % count($responsibles)],
                'responsible_phone' => '0561' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'representative_id' => $reps[$i % $reps->count()]->id,
                'created_by'        => $manager->id,
                'start_date'        => now()->subDays(30),
                'end_date'          => $end,
                'approval_status'   => $status,
            ]);
            $first ??= $contract;

            if ($project === 'مشروع الواحة') {
                AdLicense::create([
                    'contract_id' => $contract->id, 'employee_id' => $employees[0]->id,
                    'license_number' => '72' . str_pad((string) ($i * 10 + 1), 5, '0', STR_PAD_LEFT),
                    'issue_date' => now()->subDays(5), 'expiry_date' => now()->addDays(60),
                    'platforms' => $mk($allPlatforms), 'status' => 'complete',
                ]);
                AdLicense::create([
                    'contract_id' => $contract->id, 'employee_id' => $employees[1]->id,
                    'license_number' => '72' . str_pad((string) ($i * 10 + 2), 5, '0', STR_PAD_LEFT),
                    'issue_date' => now()->subDays(3), 'expiry_date' => now()->addDays(5),
                    'platforms' => $mk(array_slice($allPlatforms, 0, 1)), 'status' => 'created_unpublished',
                ]);
            }

            if ($project === 'برج اللؤلؤة') {
                AdLicense::create([
                    'contract_id' => $contract->id, 'employee_id' => $employees[2]->id,
                    'license_number' => '72' . str_pad((string) ($i * 10 + 1), 5, '0', STR_PAD_LEFT),
                    'issue_date' => now()->subDays(2), 'expiry_date' => now()->addDays(30),
                    'platforms' => [], 'status' => 'created_unpublished',
                ]);
            }
        }

        // مثال عقد فرعي مشتق من العقد الأول (لشركة أخرى) — بدون ترخيص
        $extCompany = \App\Models\ExternalCompany::firstOrCreate(
            ['name' => 'شركة الأفق العقارية'],
            ['contact_person' => 'ماجد الحربي', 'phone' => '0567778888', 'is_active' => true]
        );

        Contract::create([
            'contract_number'  => '62F0001',
            'project_name'     => $first->project_name,
            'developer_name'   => 'شركة الشريك الخارجي',
            'developer_phone'  => $first->developer_phone,
            'neighborhood'     => $first->neighborhood,
            'contract_type'    => $first->contract_type,
            'transaction_type' => $first->transaction_type,
            'responsible_name' => 'مندوب الشركة الخارجية',
            'responsible_phone' => '0569990000',
            'representative_id' => $first->representative_id,
            'created_by'       => $employees[0]->id,
            'parent_id'        => $first->id,
            'external_company_id' => $extCompany->id,
            'start_date'       => now()->subDays(10),
            'end_date'         => now()->addDays(50),
            'approval_status'  => 'pending',
        ]);

        // إسناد الرؤية: العقد الأول يظهر لسارة وفهد
        $first->assignedEmployees()->sync([$employees[0]->id, $employees[1]->id]);

        // مثال عقد مسودة (بيانات ناقصة)
        Contract::create([
            'project_name'    => 'مشروع قيد الإدخال',
            'contract_type'   => 'brokerage',
            'transaction_type' => 'sale',
            'approval_status' => 'pending',
            'is_draft'        => true,
            'created_by'      => $manager->id,
        ]);

        // رسائل تجريبية بين الموظفين
        \App\Models\Message::insert([
            ['sender_id' => $employees[0]->id, 'receiver_id' => $manager->id,      'body' => 'السلام عليكم، هل تم اعتماد عقد مشروع الواحة؟', 'read_at' => null, 'created_at' => now()->subMinutes(30), 'updated_at' => now()->subMinutes(30)],
            ['sender_id' => $manager->id,      'receiver_id' => $employees[0]->id, 'body' => 'وعليكم السلام، نعم تم الاعتماد. باشري بالترخيص.',   'read_at' => now(), 'created_at' => now()->subMinutes(28), 'updated_at' => now()->subMinutes(28)],
            ['sender_id' => $employees[1]->id, 'receiver_id' => $employees[0]->id, 'body' => 'أرسلي لي رقم الترخيص لو سمحتِ.', 'read_at' => null, 'created_at' => now()->subMinutes(10), 'updated_at' => now()->subMinutes(10)],
        ]);
    }
}
