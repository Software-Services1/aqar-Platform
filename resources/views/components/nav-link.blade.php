@props(['href' => '#', 'active' => false, 'icon' => 'dot'])
@php
$icons = [
  'grid'  => 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
  'doc'   => 'M6 2h9l5 5v15H6zM15 2v5h5',
  'badge' => 'M12 2l2.4 4.8L20 7.6l-4 3.9.9 5.5L12 14.8 7.1 17l.9-5.5-4-3.9 5.6-.8z',
  'bell'  => 'M6 8a6 6 0 1112 0c0 7 3 7 3 9H3c0-2 3-2 3-9M9 21a3 3 0 006 0',
  'user'  => 'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21a8 8 0 0116 0',
  'users' => 'M9 12a4 4 0 100-8 4 4 0 000 8zM2 21a7 7 0 0114 0M17 11a3 3 0 100-6M22 21a6 6 0 00-8-5',
  'layers'=> 'M12 2l9 5-9 5-9-5zM3 12l9 5 9-5M3 17l9 5 9-5',
  'gear'  => 'M12 9a3 3 0 100 6 3 3 0 000-6zM19 12a7 7 0 00-.1-1l2-1.6-2-3.4-2.4 1a7 7 0 00-1.7-1L14.5 2h-4l-.3 2.4a7 7 0 00-1.7 1l-2.4-1-2 3.4L4 10a7 7 0 000 2l-2 1.6 2 3.4 2.4-1a7 7 0 001.7 1l.3 2.4h4l.3-2.4a7 7 0 001.7-1l2.4 1 2-3.4-2-1.6c.07-.3.1-.7.1-1z',
  'chart' => 'M3 3v18h18M7 14l3-4 3 3 4-6',
  'idcard'=> 'M3 5h18v14H3zM7 9h4M7 13h7M16 9a2 2 0 11-2 2',
  'shield'=> 'M12 2l8 3v6c0 5-3.5 8-8 11-4.5-3-8-6-8-11V5z',
  'chat'  => 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
  'building'=> 'M4 21V5a1 1 0 011-1h9a1 1 0 011 1v16M15 21V9h4a1 1 0 011 1v11M8 8h3M8 12h3M8 16h3',
  'dot'   => 'M12 12h.01',
];
@endphp
<a href="{{ $href }}" wire:navigate
   class="nav-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-white/65 transition hover:bg-white/5 hover:text-white {{ $active ? 'active' : '' }}">
    <svg class="ico h-[18px] w-[18px] shrink-0 text-white/45 group-hover:text-white/80" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
        <path d="{{ $icons[$icon] ?? $icons['dot'] }}"/>
    </svg>
    <span>{{ $slot }}</span>
</a>
