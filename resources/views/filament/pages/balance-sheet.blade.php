<x-filament-panels::page>
    {{-- Inline CSS for tooltips --}}
    <style>
        .account-tooltip {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            margin-left: 6px;
            border-radius: 50%;
            background-color: rgba(59, 130, 246, 0.2);
            color: rgb(59, 130, 246);
            font-size: 10px;
            font-weight: 600;
            cursor: help;
            transition: all 0.2s ease;
        }
        .account-tooltip:hover {
            background-color: rgba(59, 130, 246, 0.3);
            transform: scale(1.1);
        }
        .account-tooltip .tooltip-text {
            visibility: hidden;
            opacity: 0;
            width: 280px;
            background-color: #1f2937;
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 10px 12px;
            position: absolute;
            z-index: 1000;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            font-weight: 400;
            line-height: 1.5;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }
        .account-tooltip .tooltip-text::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -6px;
            border-width: 6px;
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }
        .account-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>

    <div class="space-y-6">
        {{-- Header --}}
        <x-filament::section>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Neraca</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Per tanggal: {{ $reportData['date'] ?? now()->format('d F Y') }}</p>
                </div>
                <x-filament::button wire:click="refresh">
                    Refresh
                </x-filament::button>
            </div>
        </x-filament::section>

        @if(!empty($reportData))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Assets Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    ASET
                </x-slot>

                <table class="w-full">
                    <tbody>
                        @forelse($reportData['assets'] as $asset)
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 text-gray-600 dark:text-gray-400">
                                <span class="flex items-center">
                                    {{ $asset['code'] }} - {{ $asset['name'] }}
                                    @if(!empty($asset['tooltip']))
                                    <span class="account-tooltip">
                                        <span>i</span>
                                        <span class="tooltip-text">{{ $asset['tooltip'] }}</span>
                                    </span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($asset['balance'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="py-2 text-gray-500 dark:text-gray-400">Tidak ada data aset</td>
                        </tr>
                        @endforelse
                        <tr class="font-bold bg-blue-50 dark:bg-blue-900/20">
                            <td class="py-3 text-blue-700 dark:text-blue-400">Total Aset</td>
                            <td class="py-3 text-right text-blue-700 dark:text-blue-400">Rp {{ number_format($reportData['totalAssets'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </x-filament::section>

            {{-- Liabilities & Equity Section --}}
            <x-filament::section>
                <x-slot name="heading">
                    KEWAJIBAN & MODAL
                </x-slot>

                <div class="space-y-4">
                    {{-- Liabilities --}}
                    <div>
                        <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Kewajiban</h4>
                        <table class="w-full">
                            <tbody>
                                @forelse($reportData['liabilities'] as $liability)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-600 dark:text-gray-400">
                                        <span class="flex items-center">
                                            {{ $liability['code'] }} - {{ $liability['name'] }}
                                            @if(!empty($liability['tooltip']))
                                            <span class="account-tooltip">
                                                <span>i</span>
                                                <span class="tooltip-text">{{ $liability['tooltip'] }}</span>
                                            </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($liability['balance'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada kewajiban</td>
                                </tr>
                                @endforelse
                                <tr class="font-semibold bg-orange-50 dark:bg-orange-900/20">
                                    <td class="py-2 text-orange-700 dark:text-orange-400">Total Kewajiban</td>
                                    <td class="py-2 text-right text-orange-700 dark:text-orange-400">Rp {{ number_format($reportData['totalLiabilities'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Equity --}}
                    <div>
                        <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Modal</h4>
                        <table class="w-full">
                            <tbody>
                                @forelse($reportData['equity'] as $eq)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2 text-gray-600 dark:text-gray-400">
                                        <span class="flex items-center">
                                            {{ $eq['code'] }} - {{ $eq['name'] }}
                                            @if($eq['isDynamic'] ?? false)
                                                <span class="text-xs text-blue-500 ml-1">(Otomatis)</span>
                                            @endif
                                            @if(!empty($eq['tooltip']))
                                            <span class="account-tooltip">
                                                <span>i</span>
                                                <span class="tooltip-text">{{ $eq['tooltip'] }}</span>
                                            </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($eq['balance'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="py-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada modal</td>
                                </tr>
                                @endforelse
                                <tr class="font-semibold bg-purple-50 dark:bg-purple-900/20">
                                    <td class="py-2 text-purple-700 dark:text-purple-400">Total Modal</td>
                                    <td class="py-2 text-right text-purple-700 dark:text-purple-400">Rp {{ number_format($reportData['totalEquity'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Total Liabilities & Equity --}}
                    <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600">
                        <table class="w-full">
                            <tr class="font-bold text-lg">
                                <td class="py-2 text-gray-900 dark:text-white">Total Kewajiban & Modal</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($reportData['totalLiabilitiesAndEquity'], 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Balance Check --}}
        <x-filament::section>
            @if(abs($reportData['totalAssets'] - $reportData['totalLiabilitiesAndEquity']) < 0.01)
            <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                <x-heroicon-o-check-circle class="w-5 h-5" />
                <span class="font-semibold">Neraca Seimbang (Balance)</span>
            </div>
            @else
            <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                <x-heroicon-o-x-circle class="w-5 h-5" />
                <span class="font-semibold">Neraca Tidak Seimbang! Selisih: Rp {{ number_format(abs($reportData['totalAssets'] - $reportData['totalLiabilitiesAndEquity']), 0, ',', '.') }}</span>
            </div>
            @endif
        </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
