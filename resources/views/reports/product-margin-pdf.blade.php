<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Analisis Margin Produk - Percetakan Mutiara Rizki</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            padding: 15px 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-address {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 5px 0;
            color: #2d3748;
        }
        
        .report-period {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        
        /* Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .report-table th {
            background-color: #4a5568;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        
        .report-table th.center {
            text-align: center;
        }
        
        .report-table th.right {
            text-align: right;
        }
        
        .report-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .report-table td.center {
            text-align: center;
        }
        
        .report-table td.right {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .report-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        
        .report-table tr.total-row {
            background-color: #2d3748;
            color: white;
            font-weight: bold;
        }
        
        .report-table tr.total-row td {
            border: none;
            color: white;
        }
        
        /* Margin Indicators */
        .margin-positive {
            color: #22543d;
            font-weight: bold;
        }
        
        .margin-negative {
            color: #742a2a;
            font-weight: bold;
        }
        
        .margin-neutral {
            color: #666;
        }
        
        /* Contribution Bar */
        .contribution-bar {
            width: 100%;
            height: 12px;
            background-color: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .contribution-fill {
            height: 100%;
            background-color: #4299e1;
        }
        
        /* Summary Box */
        .summary-box {
            background-color: #ebf8ff;
            border: 1px solid #4299e1;
            padding: 12px;
            margin: 15px 0;
        }
        
        .summary-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
            color: #2b6cb0;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #bee3f8;
        }
        
        .summary-item:last-child {
            border-right: none;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .summary-label {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Top Products */
        .top-products-box {
            background-color: #f0fff4;
            border: 1px solid #38a169;
            padding: 12px;
            margin: 15px 0;
        }
        
        .top-products-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            color: #22543d;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-section {
            width: 100%;
            margin-top: 20px;
        }
        
        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 8px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 120px;
            margin: 50px auto 5px auto;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 10px;
        }
        
        .signature-title {
            font-size: 9px;
            color: #666;
        }
        
        .print-date {
            text-align: right;
            font-size: 8px;
            color: #666;
            margin-top: 15px;
        }
        
        /* Rankings */
        .rank-badge {
            display: inline-block;
            width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 9px;
        }
        
        .rank-1 { background-color: #f6e05e; color: #744210; }
        .rank-2 { background-color: #cbd5e0; color: #2d3748; }
        .rank-3 { background-color: #ed8936; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">Percetakan Mutiara Rizki</div>
            <div class="company-address">
                Jl. Contoh Alamat No. 123, Kota, Provinsi<br>
                Telp: (021) 1234567 | Email: info@mutiaraizki.com
            </div>
        </div>
        
        <!-- Report Title -->
        <div class="report-title">ANALISIS MARGIN PRODUK</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($data['startDate'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($data['endDate'])->format('d F Y') }}
        </div>
        
        <!-- Summary Box -->
        <div class="summary-box">
            <div class="summary-title">RINGKASAN PENJUALAN</div>
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center; width: 33%;">
                        <div class="summary-value">{{ count($data['products']) }}</div>
                        <div class="summary-label">Produk Terjual</div>
                    </td>
                    <td style="text-align: center; width: 34%;">
                        <div class="summary-value">Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</div>
                        <div class="summary-label">Total Pendapatan</div>
                    </td>
                    <td style="text-align: center; width: 33%;">
                        <div class="summary-value">
                            @php
                                $totalMargin = collect($data['products'])->sum('totalMargin');
                            @endphp
                            Rp {{ number_format($totalMargin, 0, ',', '.') }}
                        </div>
                        <div class="summary-label">Total Margin</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Product Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Produk</th>
                    <th class="center">Satuan</th>
                    <th class="right">Harga Dasar</th>
                    <th class="right">Harga Jual Rata²</th>
                    <th class="center">Qty</th>
                    <th class="right">Total Penjualan</th>
                    <th class="right">Margin/Unit</th>
                    <th class="center">Margin %</th>
                    <th class="center">Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['products'] as $index => $product)
                <tr>
                    <td class="center">
                        @if($index < 3)
                            <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </td>
                    <td><strong>{{ $product['name'] }}</strong></td>
                    <td class="center">{{ $product['unit'] }}</td>
                    <td class="right">Rp {{ number_format($product['basePrice'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['avgPrice'], 0, ',', '.') }}</td>
                    <td class="center">{{ number_format($product['totalQty'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['totalRevenue'], 0, ',', '.') }}</td>
                    <td class="right {{ $product['marginPerUnit'] >= 0 ? 'margin-positive' : 'margin-negative' }}">
                        Rp {{ number_format($product['marginPerUnit'], 0, ',', '.') }}
                    </td>
                    <td class="center {{ $product['marginPercent'] >= 0 ? 'margin-positive' : 'margin-negative' }}">
                        {{ number_format($product['marginPercent'], 1) }}%
                    </td>
                    <td class="center">{{ number_format($product['contribution'], 1) }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: #666; padding: 20px;">
                        Tidak ada data penjualan pada periode ini
                    </td>
                </tr>
                @endforelse
                
                @if(count($data['products']) > 0)
                <tr class="total-row">
                    <td colspan="5"><strong>TOTAL</strong></td>
                    <td class="center"><strong>{{ number_format(collect($data['products'])->sum('totalQty'), 0, ',', '.') }}</strong></td>
                    <td class="right"><strong>Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</strong></td>
                    <td class="right"><strong>Rp {{ number_format(collect($data['products'])->sum('totalMargin'), 0, ',', '.') }}</strong></td>
                    <td colspan="2"></td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Top Products Highlight -->
        @if(count($data['products']) >= 3)
        <div class="top-products-box">
            <div class="top-products-title">🏆 TOP 3 PRODUK TERLARIS</div>
            <table style="width: 100%; font-size: 10px;">
                @foreach(array_slice($data['products'], 0, 3) as $index => $product)
                <tr>
                    <td style="width: 20px; font-weight: bold;">{{ $index + 1 }}.</td>
                    <td><strong>{{ $product['name'] }}</strong></td>
                    <td style="text-align: right;">Rp {{ number_format($product['totalRevenue'], 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #22543d;">{{ number_format($product['contribution'], 1) }}% kontribusi</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
        
        <!-- Footer with Signature -->
        <div class="footer">
            <table class="signature-section">
                <tr>
                    <td></td>
                    <td>
                        <div>{{ now()->format('d F Y') }}</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Pemilik</div>
                        <div class="signature-title">Percetakan Mutiara Rizki</div>
                    </td>
                </tr>
            </table>
            
            <div class="print-date">
                Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
