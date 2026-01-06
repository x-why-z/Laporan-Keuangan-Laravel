<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Laba Rugi - Percetakan Mutiara Rizki</title>
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
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .report-table th {
            background-color: #2d3748;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .report-table th.amount {
            text-align: right;
            width: 180px;
        }
        
        .report-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .report-table td.amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .report-table tr.total-row {
            background-color: #edf2f7;
            font-weight: bold;
        }
        
        .report-table tr.total-row td {
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
        
        /* Revenue Section */
        .revenue-header {
            background-color: #38a169 !important;
        }
        
        .revenue-total {
            background-color: #c6f6d5 !important;
        }
        
        .revenue-total td {
            color: #22543d;
        }
        
        /* Expense Section */
        .expense-header {
            background-color: #e53e3e !important;
        }
        
        .expense-total {
            background-color: #fed7d7 !important;
        }
        
        .expense-total td {
            color: #742a2a;
        }
        
        /* Net Profit/Loss */
        .net-profit-box {
            margin-top: 20px;
            padding: 15px;
            border: 3px solid;
            text-align: center;
        }
        
        .net-profit-box.profit {
            border-color: #38a169;
            background-color: #f0fff4;
        }
        
        .net-profit-box.loss {
            border-color: #e53e3e;
            background-color: #fff5f5;
        }
        
        .net-profit-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .net-profit-amount {
            font-size: 24px;
            font-weight: bold;
        }
        
        .net-profit-box.profit .net-profit-amount {
            color: #22543d;
        }
        
        .net-profit-box.loss .net-profit-amount {
            color: #742a2a;
        }
        
        /* Summary Box */
        .summary-box {
            background-color: #ebf8ff;
            border: 1px solid #4299e1;
            padding: 15px;
            margin: 20px 0;
        }
        
        .summary-table {
            width: 100%;
        }
        
        .summary-table td {
            padding: 5px 10px;
        }
        
        .summary-table td.label {
            font-weight: bold;
            width: 200px;
        }
        
        .summary-table td.amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
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
        
        /* Gross Margin Row */
        .gross-margin-row {
            background-color: #faf5ff;
        }
        
        .gross-margin-row td {
            color: #553c9a;
            font-weight: bold;
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
        <div class="report-title">LAPORAN LABA RUGI (INCOME STATEMENT)</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($data['startDate'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($data['endDate'])->format('d F Y') }}
        </div>
        
        <!-- Revenue Section -->
        <table class="report-table">
            <thead>
                <tr>
                    <th colspan="2" class="revenue-header">PENDAPATAN</th>
                </tr>
                <tr style="background-color: #4a5568;">
                    <th>Kode - Nama Akun</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['revenues'] as $revenue)
                <tr>
                    <td><strong>{{ $revenue['code'] }}</strong> - {{ $revenue['name'] }}</td>
                    <td class="amount">Rp {{ number_format($revenue['amount'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #666;">Tidak ada pendapatan pada periode ini</td>
                </tr>
                @endforelse
                <tr class="total-row revenue-total">
                    <td><strong>TOTAL PENDAPATAN</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Expense Section -->
        <table class="report-table">
            <thead>
                <tr>
                    <th colspan="2" class="expense-header">BEBAN</th>
                </tr>
                <tr style="background-color: #4a5568;">
                    <th>Kode - Nama Akun</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['expenses'] as $expense)
                <tr>
                    <td><strong>{{ $expense['code'] }}</strong> - {{ $expense['name'] }}</td>
                    <td class="amount">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #666;">Tidak ada beban pada periode ini</td>
                </tr>
                @endforelse
                <tr class="total-row expense-total">
                    <td><strong>TOTAL BEBAN</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($data['totalExpense'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Summary -->
        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td class="label">Total Pendapatan</td>
                    <td class="amount">Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Beban</td>
                    <td class="amount">(Rp {{ number_format($data['totalExpense'], 0, ',', '.') }})</td>
                </tr>
                <tr style="border-top: 2px solid #4299e1;">
                    <td class="label" style="font-size: 13px;">LABA/RUGI BERSIH</td>
                    <td class="amount" style="font-size: 13px; font-weight: bold; color: {{ $data['netProfit'] >= 0 ? '#22543d' : '#742a2a' }};">
                        Rp {{ number_format($data['netProfit'], 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Net Profit/Loss Box -->
        <div class="net-profit-box {{ $data['netProfit'] >= 0 ? 'profit' : 'loss' }}">
            <div class="net-profit-label">
                {{ $data['netProfit'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}
            </div>
            <div class="net-profit-amount">
                Rp {{ number_format(abs($data['netProfit']), 0, ',', '.') }}
            </div>
            @if($data['totalRevenue'] > 0)
            <div style="font-size: 11px; margin-top: 8px; color: #666;">
                Margin: {{ number_format(($data['netProfit'] / $data['totalRevenue']) * 100, 1) }}%
            </div>
            @endif
        </div>
        
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
