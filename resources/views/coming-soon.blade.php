@extends('layouts.app')

@section('title', 'მალე დაემატება')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-primary-500/20 flex items-center justify-center">
            <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">მალე დაემატება</h1>
        <p class="text-dark-400 mb-6">ეს გვერდი დამუშავების პროცესშია</p>
        <a href="{{ route('dashboard') }}" class="btn-primary inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            დაბრუნება მთავარზე
        </a>
    </div>
</div>
@endsection
