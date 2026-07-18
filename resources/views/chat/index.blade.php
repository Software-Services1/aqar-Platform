@extends('layouts.app')
@section('title', 'الرسائل')
@section('subtitle', 'محادثات بين الموظفين')
@section('content')
    @livewire('chat')
@endsection
@push('scripts')
<script>
    const initChatScroll = () => {
        const scroll = () => { const el = document.getElementById('msgs'); if (el) el.scrollTop = el.scrollHeight; };
        scroll();
        if (!window.__chatHooked) {
            window.__chatHooked = true;
            Livewire.hook('morphed', scroll);
        }
    };
    document.addEventListener('livewire:initialized', initChatScroll);
    document.addEventListener('livewire:navigated', initChatScroll);
</script>
@endpush
