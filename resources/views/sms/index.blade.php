@extends('layouts.app')

@section('title', 'SMS მენეჯერი')

@section('page-header')
    <div>
        <h1 class="text-2xl font-bold text-white">SMS მენეჯერი</h1>
        <p class="text-dark-400 mt-1">შაბლონები და გაგზავნის ისტორია</p>
    </div>
@endsection

@section('content')
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <p class="text-dark-400 text-sm">გაგზავნილი</p>
            <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['total_sent'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">შეცდომები</p>
            <p class="text-2xl font-bold text-red-400 mt-1">{{ $stats['total_failed'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">დღეს</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['today'] }}</p>
        </div>
        <div class="stat-card">
            <p class="text-dark-400 text-sm">SMS ლიმიტი</p>
            <p class="text-2xl font-bold text-primary-400 mt-1">
                {{ $stats['balance'] !== null ? number_format($stats['balance']) : 'N/A' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- SMS Templates -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">SMS შაბლონები</h2>

            <div class="space-y-4">
                @foreach($templates as $template)
                    <div class="p-4 rounded-lg bg-dark-800/50">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-sm font-medium text-primary-400">{{ $template->description ?? $template->status_key }}</span>
                            <button onclick="editTemplate({{ $template->id }}, '{{ $template->status_key }}')"
                                class="text-sm text-dark-400 hover:text-primary-400">
                                რედაქტირება
                            </button>
                        </div>
                        <p class="text-sm text-dark-300" id="template-text-{{ $template->id }}">{{ $template->template_text }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Manual SMS Send -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">SMS გაგზავნა</h2>

            <form method="POST" action="{{ route('sms.send') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">ტელეფონი</label>
                    <input type="text" name="phone" class="form-input w-full" placeholder="995XXXXXXXXX" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-2">შეტყობინება</label>
                    <textarea name="message" rows="4" class="form-input w-full" maxlength="500" required></textarea>
                    <p class="text-xs text-dark-500 mt-1">მაქს. 500 სიმბოლო</p>
                </div>

                <button type="submit" class="btn-primary w-full">
                    გაგზავნა
                </button>
            </form>
        </div>
    </div>

    <!-- SMS Logs -->
    <div class="glass-card p-6 mt-6" x-data="{ showClearConfirm: false }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">გაგზავნის ისტორია</h2>
            @if($logs->total() > 0)
                <button type="button" @click="showClearConfirm = true"
                    class="px-4 py-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    გასუფთავება
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-dark">
                <thead>
                    <tr>
                        <th class="text-left">თარიღი</th>
                        <th class="text-left">ტელეფონი</th>
                        <th class="text-left">შეტყობინება</th>
                        <th class="text-center">სტატუსი</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-dark-400 text-sm">{{ $log->formatted_date }}</td>
                            <td class="text-white font-mono text-sm">{{ $log->phone }}</td>
                            <td class="text-dark-300 text-sm">{{ $log->short_message }}</td>
                            <td class="text-center">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium 
                                    {{ $log->status == 'sent' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-dark-500">ჩანაწერები არ არის</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>

        <!-- Clear All Confirmation Modal -->
        <div x-show="showClearConfirm" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            @click.self="showClearConfirm = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-dark-800 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white">დადასტურება</h3>
                        <p class="text-dark-400 text-sm mt-1">ნამდვილად გსურთ ყველა გაგზავნის ისტორიის წაშლა?</p>
                        <p class="text-dark-500 text-xs mt-2">ეს ოპერაცია შეუქცევადია!</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" @click="showClearConfirm = false"
                        class="px-4 py-2 rounded-lg bg-dark-700 text-dark-300 hover:bg-dark-600 transition-colors">
                        არა
                    </button>
                    <form method="POST" action="{{ route('sms.clear-all-logs') }}" class="inline-block">
                        @csrf
                        <button type="submit" @click="showClearConfirm = false"
                            class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors">
                            კი, წაშლა
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Template Modal -->
    <div id="template-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-dark-900 rounded-xl p-6 w-full max-w-lg mx-4">
            <h3 class="text-lg font-semibold text-white mb-4">შაბლონის რედაქტირება</h3>
            <form id="template-form" method="POST">
                @csrf
                @method('PUT')
                <textarea name="template_text" id="modal-template-text" rows="4" class="form-input w-full mb-4"></textarea>
                <p class="text-xs text-dark-500 mb-4">ცვლადები: [მანქანა] [წელი] [ვინ] [ლოტი] [კონტეინერი] [კლიენტი]</p>
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closeModal()" class="btn-secondary">გაუქმება</button>
                    <button type="submit" class="btn-primary">შენახვა</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function editTemplate(id, key) {
                const text = document.getElementById(`template-text-${id}`).textContent;
                document.getElementById('modal-template-text').value = text;
                document.getElementById('template-form').action = `/sms/templates/${id}`;
                document.getElementById('template-modal').classList.remove('hidden');
                document.getElementById('template-modal').classList.add('flex');
            }

            function closeModal() {
                document.getElementById('template-modal').classList.add('hidden');
                document.getElementById('template-modal').classList.remove('flex');
            }
        </script>
    @endpush
@endsection