@extends('layouts.app')
@section('title', 'الموظفون')
@section('content')
<div class="mb-4 flex justify-end">
    <a href="{{ route('employees.create') }}" class="rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">+ موظف جديد</a>
</div>
<div class="overflow-hidden rounded-2xl bg-white shadow-card">
    <table class="w-full text-sm">
        <thead><tr class="border-b border-ink/8 text-right text-[12px] text-ink-muted">
            <th class="py-3 pr-5">الاسم</th><th class="px-3 py-3">البريد</th><th class="px-3 py-3">الجوال</th>
            <th class="px-3 py-3">الدور</th><th class="px-3 py-3">الحالة</th><th class="px-3 py-3">عقود مسؤول عنها</th><th class="px-3 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-ink/5">
            @foreach ($employees as $emp)
                <tr class="hover:bg-paper/60">
                    <td class="py-3 pr-5 font-semibold text-ink">{{ $emp->name }}</td>
                    <td class="px-3 py-3 text-ink-muted" dir="ltr">{{ $emp->email }}</td>
                    <td class="px-3 py-3 text-ink-muted" dir="ltr">{{ $emp->phone ?: '—' }}</td>
                    <td class="px-3 py-3">
                        @forelse ($emp->roles as $role)
                            <span class="rounded-full px-2.5 py-0.5 text-[12px] font-semibold {{ $role->name === 'manager' ? 'bg-ink/10 text-ink' : 'bg-brass/10 text-brass' }}">{{ $role->name }}</span>
                        @empty
                            <span class="text-ink-muted">—</span>
                        @endforelse
                    </td>
                    <td class="px-3 py-3">
                        <span class="{{ $emp->is_active ? 'text-ok' : 'text-gone' }} text-[12px] font-semibold">{{ $emp->is_active ? 'نشط' : 'موقوف' }}</span>
                    </td>
                    <td class="px-3 py-3 tabular-nums">{{ $emp->contracts_count }}</td>
                    <td class="px-3 py-3 text-left">
                        <a href="{{ route('employees.edit', $emp) }}" class="text-[13px] font-semibold text-brass hover:underline">تعديل</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="border-t border-ink/5 px-4 py-3">{{ $employees->links() }}</div>
</div>
@endsection
