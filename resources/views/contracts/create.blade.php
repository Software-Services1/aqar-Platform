@extends('layouts.app')
@section('title', 'عقد جديد')
@section('subtitle', 'إضافة عقد وساطة جديد — سيُشعَر جميع الموظفين لإنشاء تراخيصهم')
@section('content')
<form method="POST" action="{{ route('contracts.store') }}" class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-card">
    @csrf
    @include('contracts._form')
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ العقد</button>
        <a href="{{ route('contracts.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
<style>.lbl{display:block;margin-bottom:.35rem;font-size:.8rem;font-weight:600;color:#22324d}.inp{width:100%;border:1px solid rgba(27,42,65,.12);border-radius:.75rem;padding:.6rem .75rem;font-size:.875rem;outline:none}.inp:focus{border-color:#A77C3C;box-shadow:0 0 0 3px rgba(167,124,60,.15)}.err{color:#dc2626;font-size:.75rem;margin-top:.25rem}</style>
@endsection
