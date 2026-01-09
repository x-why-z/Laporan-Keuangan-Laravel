<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Laporan - Percetakan Mutiara Rizki</title>
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
            padding: 15px 25px;
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
        
        /* Summary Cards */
        .summary-cards {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-cards td {
            width: 33.33%;
            padding: 8px;
            text-align: center;
            vertical-align: top;
        }
        
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
        }
        
        .card-green { background-color: #f0fff4; border-color: #38a169; }
        .card-red { background-color: #fff5f5; border-color: #e53e3e; }
        .card-blue { background-color: #ebf8ff; border-color: #3182ce; }
        .card-orange { background-color: #fffaf0; border-color: #dd6b20; }
        .card-purple { background-color: #faf5ff; border-color: #805ad5; }
        
        .card-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        
        .card-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }
        
        .card-green .card-value { color: #22543d; }
        .card-red .card-value { color: #742a2a; }
        .card-blue .card-value { color: #2c5282; }
        .card-orange .card-value { color: #7b341e; }
        .card-purple .card-value { color: #553c9a; }
        
        /* Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        .report-table th {
            background-color: #2d3748;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        
        .report-table th.amount {
            text-align: right;
            width: 150px;
        }
        
        .report-table td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        .report-table td.amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .report-table tr.total-row {
            font-weight: bold;
        }
        
        .report-table tr.total-row td {
            border-top: 2px solid #2d3748;
            border-bottom: 2px solid #2d3748;
            padding: 8px 6px;
        }
        
        .section-header {
            background-color: #4a5568;
            color: white;
            padding: 6px;
            font-weight: bold;
            font-size: 11px;
        }
        
        /* Section Headers colored */
        .revenue-header { background-color: #38a169 !important; }
        .hpp-header { background-color: #e53e3e !important; }
        .expense-header { background-color: #dd6b20 !important; }
        
        .revenue-total { background-color: #c6f6d5 !important; }
        .revenue-total td { color: #22543d; }
        
        .hpp-total { background-color: #fed7d7 !important; }
        .hpp-total td { color: #742a2a; }
        
        .expense-total { background-color: #feebc8 !important; }
        .expense-total td { color: #7b341e; }
        
        /* Profit Box */
        .profit-box {
            margin: 15px 0;
            padding: 12px;
            border: 2px solid;
            text-align: center;
            border-radius: 6px;
        }
        
        .profit-box.gross { border-color: #3182ce; background-color: #ebf8ff; }
        .profit-box.net-profit { border-color: #805ad5; background-color: #faf5ff; }
        .profit-box.net-loss { border-color: #e53e3e; background-color: #fff5f5; }
        
        .profit-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .profit-amount {
            font-size: 18px;
            font-weight: bold;
        }
        
        .profit-box.gross .profit-amount { color: #2c5282; }
        .profit-box.net-profit .profit-amount { color: #553c9a; }
        .profit-box.net-loss .profit-amount { color: #742a2a; }
        
        /* Order Stats */
        .stats-table {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .stats-table td {
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
        }
        
        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        .orders-table th {
            background-color: #4a5568;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
        }
        
        .orders-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .orders-table td.amount {
            text-align: right;
        }
        
        .orders-table td.center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .badge-green { background-color: #c6f6d5; color: #22543d; }
        .badge-yellow { background-color: #fefcbf; color: #744210; }
        .badge-red { background-color: #fed7d7; color: #742a2a; }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .signature-section {
            width: 100%;
            margin-top: 25px;
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
            margin: 50px auto 4px auto;
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
        
        .page-break {
            page-break-before: always;
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
        <div class="report-title">REKAP LAPORAN KEUANGAN</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($data['startDate'])->format('d F Y') }} - {{ \Carbon\Carbon::parse($data['endDate'])->format('d F Y') }}
        </div>
        
        <!-- Summary Cards -->
        <table class="summary-cards">
            <tr>
                <td>
                    <div class="card card-green">
                        <div class="card-label">Total Omset</div>
                        <div class="card-value">Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="card card-red">
                        <div class="card-label">Total HPP</div>
                        <div class="card-value">Rp {{ number_format($data['totalHPP'], 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="card card-blue">
                        <div class="card-label">Laba Kotor</div>
                        <div class="card-value">Rp {{ number_format($data['labaKotor'], 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
            @if($data['showOperationalExpenses'])
            <tr>
                <td colspan="3" style="padding-top: 0;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 50%; padding: 4px;">
                                <div class="card card-orange">
                                    <div class="card-label">Beban Operasional</div>
                                    <div class="card-value">Rp {{ number_format($data['totalOperationalExpenses'], 0, ',', '.') }}</div>
                                </div>
                            </td>
                            <td style="width: 50%; padding: 4px;">
                                <div class="card card-purple">
                                    <div class="card-label">Laba Bersih</div>
                                    <div class="card-value">Rp {{ number_format($data['labaBersih'], 0, ',', '.') }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endif
        </table>
        
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
                    <td colspan="2" style="text-align: center; color: #666;">Tidak ada pendapatan</td>
                </tr>
                @endforelse
                <tr class="total-row revenue-total">
                    <td><strong>TOTAL PENDAPATAN</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- HPP Section -->
        <table class="report-table">
            <thead>
                <tr>
                    <th colspan="2" class="hpp-header">HARGA POKOK PENJUALAN (HPP)</th>
                </tr>
                <tr style="background-color: #4a5568;">
                    <th>Kode - Nama Akun</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['hppList'] as $hpp)
                <tr>
                    <td><strong>{{ $hpp['code'] }}</strong> - {{ $hpp['name'] }}</td>
                    <td class="amount">Rp {{ number_format($hpp['amount'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #666;">Tidak ada HPP</td>
                </tr>
                @endforelse
                <tr class="total-row hpp-total">
                    <td><strong>TOTAL HPP</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($data['totalHPP'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Laba Kotor -->
        <div class="profit-box gross">
            <div class="profit-label">LABA KOTOR</div>
            <div class="profit-amount">Rp {{ number_format($data['labaKotor'], 0, ',', '.') }}</div>
        </div>
        
        @if($data['showOperationalExpenses'])
        <!-- Operational Expenses -->
        <table class="report-table">
            <thead>
                <tr>
                    <th colspan="2" class="expense-header">BEBAN OPERASIONAL</th>
                </tr>
                <tr style="background-color: #4a5568;">
                    <th>Kode - Nama Akun</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['operationalExpenses'] as $expense)
                <tr>
                    <td><strong>{{ $expense['code'] }}</strong> - {{ $expense['name'] }}</td>
                    <td class="amount">Rp {{ number_format($expense['amount'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; color: #666;">Tidak ada beban operasional</td>
                </tr>
                @endforelse
                <tr class="total-row expense-total">
                    <td><strong>TOTAL BEBAN OPERASIONAL</strong></td>
                    <td class="amount"><strong>Rp {{ number_format($data['totalOperationalExpenses'], 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Laba Bersih -->
        <div class="profit-box {{ $data['labaBersih'] >= 0 ? 'net-profit' : 'net-loss' }}">
            <div class="profit-label">{{ $data['labaBersih'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</div>
            <div class="profit-amount">Rp {{ number_format(abs($data['labaBersih']), 0, ',', '.') }}</div>
            @if($data['totalRevenue'] > 0)
            <div style="font-size: 9px; margin-top: 4px; color: #666;">
                Margin: {{ number_format(($data['labaBersih'] / $data['totalRevenue']) * 100, 1) }}%
            </div>
            @endif
        </div>
        @endif
        
        <!-- Order Statistics -->
        <div style="margin-top: 20px;">
            <div class="section-header">STATISTIK PESANAN</div>
            <table class="stats-table">
                <tr>
                    <td>
                        <div class="stat-value">{{ $data['orderStats']['total'] }}</div>
                        <div class="stat-label">Total Pesanan</div>
                    </td>
                    <td style="background-color: #f0fff4;">
                        <div class="stat-value" style="color: #22543d;">{{ $data['orderStats']['paid'] }}</div>
                        <div class="stat-label">Lunas</div>
                    </td>
                    <td style="background-color: #fffff0;">
                        <div class="stat-value" style="color: #744210;">{{ $data['orderStats']['partial'] }}</div>
                        <div class="stat-label">Sebagian</div>
                    </td>
                    <td style="background-color: #fff5f5;">
                        <div class="stat-value" style="color: #742a2a;">{{ $data['orderStats']['unpaid'] }}</div>
                        <div class="stat-label">Belum Dibayar</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Orders Table -->
        @if(count($orders) > 0)
        <div style="margin-top: 15px;">
            <div class="section-header">DETAIL PESANAN</div>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: right;">Dibayar</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order['order_number'] }}</td>
                        <td>{{ $order['order_date'] }}</td>
                        <td>{{ $order['customer_name'] }}</td>
                        <td class="amount">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                        <td class="amount">Rp {{ number_format($order['paid_amount'], 0, ',', '.') }}</td>
                        <td class="center">
                            @switch($order['payment_status'])
                                @case('paid')
                                    <span class="badge badge-green">Lunas</span>
                                    @break
                                @case('partial')
                                    <span class="badge badge-yellow">Sebagian</span>
                                    @break
                                @default
                                    <span class="badge badge-red">Unpaid</span>
                            @endswitch
                        </td>
                    </tr>
                    @endforeach
                </tbody>
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
