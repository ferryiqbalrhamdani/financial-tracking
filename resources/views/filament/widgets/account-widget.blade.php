<x-filament-widgets::widget>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .card-container {
            cursor: grab;
        }

        .card-container:active {
            cursor: grabbing;
        }
    </style>
    <div x-data="{
            scrollContainer: null,
            isDragging: false,
            startX: 0,
            scrollLeft: 0,
            
            scrollLeftBtn() {
                this.scrollContainer.scrollBy({ left: -300, behavior: 'smooth' });
            },
            scrollRightBtn() {
                this.scrollContainer.scrollBy({ left: 300, behavior: 'smooth' });
            },
            
            init() {
                this.scrollContainer = this.$refs.cardContainer;
                
                // Mouse drag scroll
                this.scrollContainer.addEventListener('mousedown', (e) => {
                    this.isDragging = true;
                    this.startX = e.pageX - this.scrollContainer.offsetLeft;
                    this.scrollLeft = this.scrollContainer.scrollLeft;
                    this.scrollContainer.style.cursor = 'grabbing';
                });
                
                document.addEventListener('mouseup', () => {
                    this.isDragging = false;
                    this.scrollContainer.style.cursor = 'grab';
                });
                
                document.addEventListener('mousemove', (e) => {
                    if(!this.isDragging) return;
                    e.preventDefault();
                    const x = e.pageX - this.scrollContainer.offsetLeft;
                    const walk = (x - this.startX) * 2; // Adjust scroll speed
                    this.scrollContainer.scrollLeft = this.scrollLeft - walk;
                });
            }
        }" x-init="init()" class="relative w-full">

        <!-- Kontainer Card -->
        <div x-ref="cardContainer" class="flex space-x-4 overflow-x-auto px-12 scrollbar-hide card-container">
            @foreach ($this->getAccounts() as $account)
            <div x-data="{
                        showSaldo: localStorage.getItem('widget_showSaldo_{{ $account->id }}') === 'true',
                        toggleSaldo() {
                            this.showSaldo = !this.showSaldo;
                            localStorage.setItem('widget_showSaldo_{{ $account->id }}', this.showSaldo);
                        }
                    }"
                class="shrink-0 w-72 bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-shadow select-none">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nama Akun</div>
                        <div class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
                            {{ $account->name }}
                        </div>
                    </div>
                    @if ($account->exclude_from_total ?? true)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        Tidak Termasuk Total
                    </span>
                    @endif
                </div>

                <div class="flex items-center justify-between mb-1">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Saldo Akhir</div>
                    <button @click.stop.prevent="toggleSaldo()"
                        class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none"
                        title="Lihat atau Sembunyikan Saldo">
                        <svg x-show="!showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showSaldo" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.98 9.98 0 012.093-3.368m1.977-1.846A9.955 9.955 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.977 9.977 0 01-4.112 5.225M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3" />
                        </svg>
                    </button>
                </div>

                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    <span x-show="showSaldo">
                        Rp {{ number_format($account->starting_balance + $account->transactions->sum('amount'), 2, ',',
                        '.') }}
                    </span>
                    <span x-show="!showSaldo">••••••••</span>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</x-filament-widgets::widget>