@extends('layouts.app')

@section('title', 'ინვოისები')

@section('page-header')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-white">ინვოისები</h1>
        <p class="text-dark-400 mt-1">სულ: {{ $invoices->total() }} ინვოისი</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        ახალი ინვოისი
    </a>
</div>
@endsection

@section('content')
@if($invoices->count() > 0)
<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">ინვოისის ნომერი</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">ტიპი</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">კლიენტი</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">VIN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">თანხა</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">თარიღი</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-dark-400 uppercase tracking-wider">მოქმედებები</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($invoices as $invoice)
                <tr class="hover:bg-dark-800/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-white">{{ $invoice->invoice_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-primary-500/20 text-primary-400">
                            {{ $invoice->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-white">{{ $invoice->client_name ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-mono text-dark-300">{{ $invoice->vin ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-white">{{ $invoice->formatted_total }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-dark-300">{{ $invoice->created_at->format('d.m.Y') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('invoices.show', $invoice) }}" target="_blank" 
                               class="text-primary-400 hover:text-primary-300 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" 
                                  onsubmit="return confirm('დარწმუნებული ხართ რომ გსურთ წაშლა?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-white/10">
        {{ $invoices->links() }}
    </div>
</div>
@else
<div class="glass-card p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <h3 class="text-lg font-medium text-white mb-2">ინვოისები არ არის</h3>
    <p class="text-dark-400 mb-6">დაიწყეთ ახალი ინვოისის შექმნით</p>
    <a href="{{ route('invoices.create') }}" class="btn-primary inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        ახალი ინვოისი
    </a>
</div>
@endif
@endsection
