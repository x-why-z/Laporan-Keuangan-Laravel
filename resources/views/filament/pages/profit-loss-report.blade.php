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
                Laporan Laba Rugi
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

                {{-- Expense Section --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">BEBAN</h3>
                    <table class="w-full">
                        <tbody>
                            @forelse($reportData['expenses'] as $expense)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ $expense['code'] }} - {{ $expense['name'] }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-2 text-gray-500 dark:text-gray-400">Tidak ada data beban</td>
                            </tr>
                            @endforelse
                            <tr class="font-semibold bg-red-50 dark:bg-red-900/20">
                                <td class="py-2 text-red-700 dark:text-red-400">Total Beban</td>
                                <td class="py-2 text-right text-red-700 dark:text-red-400">Rp {{ number_format($reportData['totalExpense'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Net Profit Section --}}
                <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600">
                    <table class="w-full">
                        <tbody>
                            <tr class="text-xl font-bold {{ $reportData['netProfit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                <td class="py-3">{{ $reportData['netProfit'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td>
                                <td class="py-3 text-right">Rp {{ number_format(abs($reportData['netProfit']), 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
