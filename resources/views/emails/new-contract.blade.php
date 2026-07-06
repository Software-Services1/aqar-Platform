<x-mail::message>
# عقد جديد بانتظار ترخيصك

مرحباً،

تم إنشاء عقد جديد في النظام، ويمكنك إنشاء ترخيصك الإعلاني الخاص به:

- **المشروع:** {{ $contract->project_name }}
- **المطوّر:** {{ $contract->developer_name }}
- **رقم العقد:** {{ $contract->contract_number }}
- **نوع العقد:** {{ $contract->type_label }}
- **الحي:** {{ $contract->neighborhood ?? '—' }}

<x-mail::button :url="route('contracts.show', $contract)">
عرض العقد وإنشاء الترخيص
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
