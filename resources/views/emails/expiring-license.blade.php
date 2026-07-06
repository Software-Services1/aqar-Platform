<x-mail::message>
# تنبيه: ترخيص يقترب من الانتهاء

مرحباً {{ $license->employee->name }}،

ترخيصك الإعلاني التالي على وشك الانتهاء، يرجى اتخاذ الإجراء اللازم:

- **المشروع:** {{ $license->contract->project_name }}
- **رقم الترخيص:** {{ $license->license_number }}
- **رقم العقد:** {{ $license->contract->contract_number }}
- **ينتهي في:** {{ optional($license->expiry_date)->format('Y-m-d') }}
- **المتبقّي:** {{ max((int) $license->days_remaining, 0) }} يوم/أيام

<x-mail::button :url="route('licenses.index')" color="error">
مراجعة التراخيص
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
