<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <form wire:submit="filter">
                {{ $this->form }}
                <div class="mt-4">
                    <x-filament::button type="submit">
                        Generate Laporan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Report Content --}}
        @if(!empty($reportData))
        <x-filament::section>
            <x-slot name="heading">
                Analisis Margin Laba per Produk
            </x-slot>
            <x-slot name="description">
                Periode: {{ \Carbon\Carbon::parse($reportData['startDate'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($reportData['endDate'])->format('d M Y') }}
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Produk</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Qty</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Pendapatan</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Harga Dasar</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Harga Jual Avg</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Margin %</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Kontribusi %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['products'] as $product)
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product['unit'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($product['totalQty'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($product['totalRevenue'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">Rp {{ number_format($product['basePrice'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($product['avgPrice'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="{{ $product['marginPercent'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-semibold">
                                    {{ number_format($product['marginPercent'], 1) }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min($product['contribution'], 100) }}%"></div>
                                    </div>
                                    <span class="text-gray-900 dark:text-white">{{ number_format($product['contribution'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data penjualan untuk periode ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($reportData['products']) > 0)
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                            <td class="px-4 py-3 text-gray-900 dark:text-white">TOTAL</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format(collect($reportData['products'])->sum('totalQty'), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($reportData['totalRevenue'], 0, ',', '.') }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>

        {{-- Summary Cards --}}
        @if(count($reportData['products']) > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Produk Terlaris</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $reportData['products'][0]['name'] ?? '-' }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($reportData['products'][0]['totalQty'] ?? 0, 0, ',', '.') }} unit</p>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Produk Terjual</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ count($reportData['products']) }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">jenis produk</p>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Margin</p>
                    @php
                        $avgMargin = count($reportData['products']) > 0 
                            ? collect($reportData['products'])->avg('marginPercent') 
                            : 0;
                    @endphp
                    <p class="text-xl font-bold {{ $avgMargin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ number_format($avgMargin, 1) }}%
                    </p>
                </div>
            </x-filament::section>
        </div>
        @endif
        @endif
    </div>
</x-filament-panels::page>
