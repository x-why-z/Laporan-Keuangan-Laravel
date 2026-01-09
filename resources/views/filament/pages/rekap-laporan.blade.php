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
        
        {{-- Executive Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-{{ $reportData['showOperationalExpenses'] ? '5' : '3' }} gap-4">
            {{-- Total Omset --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Omset</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($reportData['totalRevenue'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                </div>
            </div>

            {{-- Total HPP --}}
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Total HPP</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($reportData['totalHPP'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <x-heroicon-o-cube class="w-6 h-6" />
                    </div>
                </div>
            </div>

            {{-- Laba Kotor --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Laba Kotor</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($reportData['labaKotor'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <x-heroicon-o-chart-bar class="w-6 h-6" />
                    </div>
                </div>
            </div>

            @if($reportData['showOperationalExpenses'])
            {{-- Beban Operasional --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Beban Operasional</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($reportData['totalOperationalExpenses'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <x-heroicon-o-receipt-percent class="w-6 h-6" />
                    </div>
                </div>
            </div>

            {{-- Laba Bersih --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Laba Bersih</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($reportData['labaBersih'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3">
                        <x-heroicon-o-trophy class="w-6 h-6" />
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Financial Summary --}}
        <x-filament::section>
            <x-slot name="heading">
                Rekap Laporan Keuangan
            </x-slot>
            <x-slot name="description">
                Periode: {{ \Carbon\Carbon::parse($reportData['startDate'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($reportData['endDate'])->format('d M Y') }}
            </x-slot>

            <div class="space-y-6">
                {{-- Revenue Section --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">PENDAPATAN</h3>
                    <table class="w-full">
                        <tbody>
                            @forelse($reportData['revenues'] as $revenue)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $revenue['code'] }} - {{ $revenue['name'] }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($revenue['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-gray-500 dark:text-gray-400">Tidak ada data pendapatan</td>
                            </tr>
                            @endforelse
                            <tr class="font-semibold bg-green-50 dark:bg-green-900/20">
                                <td class="py-2 text-green-700 dark:text-green-400">Total Pendapatan</td>
                                <td class="py-2 text-right text-green-700 dark:text-green-400">Rp {{ number_format($reportData['totalRevenue'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- HPP Section --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">HARGA POKOK PENJUALAN (HPP)</h3>
                    <table class="w-full">
                        <tbody>
                            @forelse($reportData['hppList'] as $hpp)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $hpp['code'] }} - {{ $hpp['name'] }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($hpp['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-gray-500 dark:text-gray-400">Tidak ada data HPP</td>
                            </tr>
                            @endforelse
                            <tr class="font-semibold bg-red-50 dark:bg-red-900/20">
                                <td class="py-2 text-red-700 dark:text-red-400">Total HPP</td>
                                <td class="py-2 text-right text-red-700 dark:text-red-400">Rp {{ number_format($reportData['totalHPP'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Laba Kotor --}}
                <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600">
                    <table class="w-full">
                        <tbody>
                            <tr class="text-lg font-bold {{ $reportData['labaKotor'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                <td class="py-3">LABA KOTOR</td>
                                <td class="py-3 text-right">Rp {{ number_format($reportData['labaKotor'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Operational Expenses (Owner Only) --}}
                @if($reportData['showOperationalExpenses'])
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">BEBAN OPERASIONAL</h3>
                    <table class="w-full">
                        <tbody>
                            @forelse($reportData['operationalExpenses'] as $expense)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $expense['code'] }} - {{ $expense['name'] }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-gray-500 dark:text-gray-400">Tidak ada beban operasional</td>
                            </tr>
                            @endforelse
                            <tr class="font-semibold bg-orange-50 dark:bg-orange-900/20">
                                <td class="py-2 text-orange-700 dark:text-orange-400">Total Beban Operasional</td>
                                <td class="py-2 text-right text-orange-700 dark:text-orange-400">Rp {{ number_format($reportData['totalOperationalExpenses'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Laba Bersih --}}
                <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600">
                    <table class="w-full">
                        <tbody>
                            <tr class="text-xl font-bold {{ $reportData['labaBersih'] >= 0 ? 'text-purple-600 dark:text-purple-400' : 'text-red-600 dark:text-red-400' }}">
                                <td class="py-3">{{ $reportData['labaBersih'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td>
                                <td class="py-3 text-right">Rp {{ number_format(abs($reportData['labaBersih']), 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Order Statistics --}}
        <x-filament::section>
            <x-slot name="heading">
                Statistik Pesanan
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $reportData['orderStats']['total'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Pesanan</p>
                </div>
                <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-green-700 dark:text-green-400">{{ $reportData['orderStats']['paid'] }}</p>
                    <p class="text-sm text-green-600 dark:text-green-500">Lunas</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900/30 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-400">{{ $reportData['orderStats']['partial'] }}</p>
                    <p class="text-sm text-yellow-600 dark:text-yellow-500">Sebagian Dibayar</p>
                </div>
                <div class="bg-red-100 dark:bg-red-900/30 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-red-700 dark:text-red-400">{{ $reportData['orderStats']['unpaid'] }}</p>
                    <p class="text-sm text-red-600 dark:text-red-500">Belum Dibayar</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Orders Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Detail Pesanan Periode Ini
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">No. Order</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-900 dark:text-white">Pelanggan</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Total</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Dibayar</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">Status Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordersData as $order)
                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $order['order_number'] }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $order['order_date'] }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $order['customer_name'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($order['paid_amount'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @switch($order['payment_status'])
                                    @case('paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Lunas
                                        </span>
                                        @break
                                    @case('partial')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Sebagian
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Belum Dibayar
                                        </span>
                                @endswitch
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada pesanan dalam periode ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
