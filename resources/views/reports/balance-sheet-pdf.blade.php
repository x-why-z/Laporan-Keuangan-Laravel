<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Neraca - Percetakan Mutiara Rizki</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            padding: 20px 30px;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-address {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0 5px 0;
            color: #2d3748;
        }
        
        .report-period {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        
        /* Tables */
        .balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .balance-table th {
            background-color: #2d3748;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .balance-table th.amount {
            text-align: right;
            width: 150px;
        }
        
        .balance-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .balance-table td.amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .balance-table tr.total-row {
            background-color: #edf2f7;
            font-weight: bold;
        }
        
        .balance-table tr.total-row td {
            border-top: 2px solid #2d3748;
            border-bottom: 2px solid #2d3748;
        }
        
        .section-header {
            background-color: #4a5568;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .subsection-header {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .account-description {
            font-size: 9px;
            color: #718096;
            font-style: italic;
            display: block;
            margin-top: 2px;
        }
        
        /* Two Column Layout */
        .two-column {
            width: 100%;
        }
        
        .two-column td {
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }
        
        .column-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        /* Balance Check */
        .balance-check {
            margin-top: 20px;
            padding: 12px;
            border: 2px solid;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .balance-check.balanced {
            border-color: #38a169;
            background-color: #f0fff4;
            color: #22543d;
        }
        
        .balance-check.unbalanced {
            border-color: #e53e3e;
            background-color: #fff5f5;
            color: #742a2a;
        }
        
        /* Accounting Equation */
        .equation-box {
            background-color: #ebf8ff;
            border: 1px solid #4299e1;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
            font-size: 12px;
            color: #2b6cb0;
        }
        
        .equation-box strong {
            font-size: 14px;
        }
        
        /* Footnotes */
        .footnotes {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .footnotes-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            color: #2d3748;
        }
        
        .footnote-item {
            font-size: 9px;
            color: #4a5568;
            margin-bottom: 4px;
            padding-left: 15px;
            text-indent: -15px;
        }
        
        .footnote-code {
            font-weight: bold;
            color: #2d3748;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-section {
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            width: 150px;
            margin: 60px auto 5px auto;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 11px;
        }
        
        .signature-title {
            font-size: 10px;
            color: #666;
        }
        
        .print-date {
            text-align: right;
            font-size: 9px;
            color: #666;
            margin-top: 20px;
        }
        
        /* Grand Total */
        .grand-total-row {
            background-color: #2d3748 !important;
            color: white;
        }
        
        .grand-total-row td {
            color: white;
            font-weight: bold;
            border: none !important;
        }
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
        <div class="report-title">LAPORAN NERACA (BALANCE SHEET)</div>
        <div class="report-period">Per Tanggal: {{ $date }}</div>
        
        <!-- Accounting Equation -->
        <div class="equation-box">
            <strong>Persamaan Akuntansi:</strong> ASET = KEWAJIBAN + MODAL
            <br>
            <span style="font-size: 11px;">
                Rp {{ number_format($data['totalAssets'], 0, ',', '.') }} = 
                Rp {{ number_format($data['totalLiabilities'], 0, ',', '.') }} + 
                Rp {{ number_format($data['totalEquity'], 0, ',', '.') }}
            </span>
        </div>
        
        <!-- Two Column Balance Sheet -->
        <table class="two-column">
            <tr>
                <!-- Left Column: ASET -->
                <td>
                    <table class="balance-table">
                        <thead>
                            <tr>
                                <th colspan="2" style="background-color: #2b6cb0;">ASET</th>
                            </tr>
                            <tr style="background-color: #4a5568;">
                                <th>Nama Akun</th>
                                <th class="amount">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['assets'] as $asset)
                            <tr>
                                <td>
                                    <strong>{{ $asset['code'] }}</strong> - {{ $asset['name'] }}
                                    @if(!empty($asset['tooltip']))
                                    <span class="account-description">{{ $asset['tooltip'] }}</span>
                                    @endif
                                </td>
                                <td class="amount">Rp {{ number_format($asset['balance'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" style="text-align: center; color: #666;">Tidak ada data aset</td>
                            </tr>
                            @endforelse
                            <tr class="total-row">
                                <td><strong>TOTAL ASET</strong></td>
                                <td class="amount"><strong>Rp {{ number_format($data['totalAssets'], 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                
                <!-- Right Column: KEWAJIBAN & MODAL -->
                <td>
                    <!-- Kewajiban -->
                    <table class="balance-table">
                        <thead>
                            <tr>
                                <th colspan="2" style="background-color: #dd6b20;">KEWAJIBAN</th>
                            </tr>
                            <tr style="background-color: #4a5568;">
                                <th>Nama Akun</th>
                                <th class="amount">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['liabilities'] as $liability)
                            <tr>
                                <td>
                                    <strong>{{ $liability['code'] }}</strong> - {{ $liability['name'] }}
                                    @if(!empty($liability['tooltip']))
                                    <span class="account-description">{{ $liability['tooltip'] }}</span>
                                    @endif
                                </td>
                                <td class="amount">Rp {{ number_format($liability['balance'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" style="text-align: center; color: #666;">Tidak ada kewajiban</td>
                            </tr>
                            @endforelse
                            <tr class="total-row" style="background-color: #feebc8;">
                                <td><strong>Total Kewajiban</strong></td>
                                <td class="amount"><strong>Rp {{ number_format($data['totalLiabilities'], 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Modal -->
                    <table class="balance-table" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th colspan="2" style="background-color: #805ad5;">MODAL</th>
                            </tr>
                            <tr style="background-color: #4a5568;">
                                <th>Nama Akun</th>
                                <th class="amount">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['equity'] as $eq)
                            <tr>
                                <td>
                                    <strong>{{ $eq['code'] }}</strong> - {{ $eq['name'] }}
                                    @if($eq['isDynamic'] ?? false)
                                    <span style="color: #3182ce; font-size: 9px;">(Otomatis)</span>
                                    @endif
                                    @if(!empty($eq['tooltip']))
                                    <span class="account-description">{{ $eq['tooltip'] }}</span>
                                    @endif
                                </td>
                                <td class="amount">Rp {{ number_format($eq['balance'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" style="text-align: center; color: #666;">Tidak ada modal</td>
                            </tr>
                            @endforelse
                            <tr class="total-row" style="background-color: #e9d8fd;">
                                <td><strong>Total Modal</strong></td>
                                <td class="amount"><strong>Rp {{ number_format($data['totalEquity'], 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Total Kewajiban & Modal -->
                    <table class="balance-table" style="margin-top: 10px;">
                        <tbody>
                            <tr class="grand-total-row">
                                <td><strong>TOTAL KEWAJIBAN & MODAL</strong></td>
                                <td class="amount"><strong>Rp {{ number_format($data['totalLiabilitiesAndEquity'], 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- Balance Check -->
        @if(abs($data['totalAssets'] - $data['totalLiabilitiesAndEquity']) < 0.01)
        <div class="balance-check balanced">
            ✓ NERACA SEIMBANG (BALANCE)
        </div>
        @else
        <div class="balance-check unbalanced">
            ✗ NERACA TIDAK SEIMBANG - Selisih: Rp {{ number_format(abs($data['totalAssets'] - $data['totalLiabilitiesAndEquity']), 0, ',', '.') }}
        </div>
        @endif
        
        <!-- Footnotes -->
        <div class="footnotes">
            <div class="footnotes-title">Catatan Kaki - Penjelasan Akun:</div>
            @foreach($data['assets'] as $asset)
                @if(!empty($asset['tooltip']))
                <div class="footnote-item">
                    <span class="footnote-code">[{{ $asset['code'] }}]</span> {{ $asset['name'] }}: {{ $asset['tooltip'] }}
                </div>
                @endif
            @endforeach
            @foreach($data['liabilities'] as $liability)
                @if(!empty($liability['tooltip']))
                <div class="footnote-item">
                    <span class="footnote-code">[{{ $liability['code'] }}]</span> {{ $liability['name'] }}: {{ $liability['tooltip'] }}
                </div>
                @endif
            @endforeach
            @foreach($data['equity'] as $eq)
                @if(!empty($eq['tooltip']))
                <div class="footnote-item">
                    <span class="footnote-code">[{{ $eq['code'] }}]</span> {{ $eq['name'] }}: {{ $eq['tooltip'] }}
                </div>
                @endif
            @endforeach
        </div>
        
        <!-- Footer with Signature -->
        <div class="footer">
            <table class="signature-section">
                <tr>
                    <td></td>
                    <td>
                        <div>{{ $date }}</div>
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
