@extends('layouts.app')
@section('title', 'تعديل العقد')
@section('content')
<form method="POST" action="{{ route('contracts.update', $contract) }}" class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-card">
    @csrf @method('PUT')
    @include('contracts._form')
    <div class="mt-6 flex gap-3 border-t border-ink/8 pt-5">
        <button class="rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">حفظ التعديلات</button>
        <button type="submit" name="save_as_draft" value="1" formnovalidate class="rounded-xl border border-brass/40 px-5 py-2.5 text-sm font-semibold text-brass hover:bg-brass/10">حفظ كمسودة</button>
        <a href="{{ route('contracts.show', $contract) }}" class="rounded-xl px-5 py-2.5 text-sm font-medium text-ink-muted hover:bg-paper">إلغاء</a>
    </div>
</form>
@endsection
