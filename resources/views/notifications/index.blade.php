@extends('layouts.app')

@section('title', 'შეტყობინებები')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">შეტყობინებები</h1>
        <p class="text-dark-400 mt-1">წაუკითხავი: {{ $unreadCount }}</p>
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
        @csrf
        <button type="submit" class="btn-secondary">ყველა წაკითხულად</button>
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="glass-card overflow-hidden">
    @if($notifications->count() > 0)
    <div class="divide-y divide-white/5">
        @foreach($notifications as $notification)
        <div class="p-4 hover:bg-dark-800/50 transition-colors {{ !$notification->is_read ? 'bg-primary-500/5' : '' }}">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-primary-600/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white {{ !$notification->is_read ? 'font-medium' : '' }}">
                        {{ $notification->message }}
                    </p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-sm text-dark-500">{{ $notification->time_ago }}</span>
                        @if($notification->car)
                        <a href="{{ route('cars.show', $notification->car) }}" class="text-sm text-primary-400 hover:text-primary-300">
                            მანქანის ნახვა
                        </a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(!$notification->is_read)
                    <form method="POST" action="{{ route('notifications.mark-read', $notification) }}">
                        @csrf
                        <button type="submit" class="p-2 text-dark-400 hover:text-primary-400" title="წაკითხულად მონიშვნა">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-dark-400 hover:text-red-400" title="წაშლა">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-dark-400">შეტყობინებები არ არის</p>
    </div>
    @endif
</div>

<div class="mt-6">{{ $notifications->links() }}</div>
@endsection
