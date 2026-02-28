<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ინვოისი #{{ $invoiceNo }}</title>
    <style>
        @font-face {
            font-family: 'GeoFont';
            src: url('{{ asset('fonts/font.ttf') }}') format('truetype');
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #525659;
            font-family: 'GeoFont', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 40px 0;
            color: #333;
        }
        
        .invoice-container {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        
        .logo-area img {
            height: 60px;
            object-fit: contain;
        }
        
        .company-info {
            text-align: right;
            color: #555;
            font-size: 0.9rem;
        }
        
        .company-info h4 {
            color: #333;
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .bill-to {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        
        .bill-to h5 {
            color: #888;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .client-box {
            border-left: 4px solid #3b82f6;
            padding-left: 15px;
            flex: 1;
        }
        
        .client-box .fw-bold {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 5px;
        }
        
        .client-box .text-muted {
            color: #666;
            font-size: 0.9rem;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .inv-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .inv-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge.bg-secondary {
            background-color: #6c757d;
            color: #fff;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background: #f8f9fa;
            color: #444;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 12px;
            border-bottom: 2px solid #ddd;
            text-align: left;
            font-weight: 600;
        }
        
        .invoice-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #333;
            vertical-align: top;
        }
        
        .invoice-table .amount {
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .total-row td {
            border-top: 2px solid #333;
            font-size: 1.1rem;
            font-weight: 700;
            color: #000;
            padding-top: 15px;
        }
        
        .purpose-box {
            background: #f0f9ff;
            border: 1px solid #b9e0f7;
            padding: 15px;
            border-radius: 8px;
            color: #0c4a6e;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        
        .bank-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px dashed #ccc;
        }
        
        .bank-info h5 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #444;
        }
        
        .bank-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .bank-row:last-child {
            border: none;
            margin: 0;
        }
        
        .bank-label {
            color: #666;
        }
        
        .bank-val {
            font-weight: 600;
            color: #333;
        }
        
        .no-print {
            display: block;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn i,         .btn svg {
            margin-right: 8px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 1rem;
        }
        
        .mt-5 {
            margin-top: 3rem;
        }
        
        .small {
            font-size: 0.875rem;
        }
        
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                padding: 20px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .purpose-box {
                background-color: #f0f9ff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .bank-info {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="logo-area">
                <img src="{{ $companyLogo }}" alt="Company Logo" onerror="this.style.display='none'">
            </div>
            <div class="company-info">
                <h4>{{ $companyName }}</h4>
                <div>{{ $companyAddress }}</div>
                <div>Tel: {{ $companyPhone }}</div>
                <div>Email: {{ $companyEmail }}</div>
            </div>
        </div>

        <div class="bill-to">
            <div class="client-box">
                <h5>გადამხდელი / Bill To</h5>
                @if($billTo)
                    <div class="fw-bold">{{ $billTo['name'] }}</div>
                    @if(isset($billTo['id']))
                        <div class="text-muted small">
                            @if($billTo['type'] === 'dealer')
                                Dealer ID: {{ $billTo['id'] }}
                            @else
                                ID: {{ $billTo['id'] }}
                            @endif
                        </div>
                    @endif
                    @if(isset($billTo['phone']))
                        <div class="text-muted small">{{ $billTo['phone'] }}</div>
                    @endif
                @else
                    <div class="text-muted">მყიდველი არ არის მითითებული</div>
                @endif
            </div>
            <div class="invoice-details">
                <div class="inv-number">{{ $invoiceNo }}</div>
                <div class="inv-date">Date: {{ now()->format('d/m/Y') }}</div>
                <div class="mt-2">
                    <span class="badge bg-secondary">გადასახდელი</span>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>აღწერა / Description</th>
                    <th class="text-end">თანხა / Amount ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceItems as $item)
                    <tr>
                        <td>{!! nl2br(e($item['desc'])) !!}</td>
                        <td class="amount">${{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach

                <tr class="total-row">
                    <td class="text-end">სულ / Total:</td>
                    <td class="amount">${{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="purpose-box">
            <strong>დანიშნულება:</strong> {{ $paymentPurpose }}
        </div>

        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <div style="max-width: 400px; width: 100%;">
                <div class="bank-info">
                    <h5>
                        <svg style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        საბანკო რეკვიზიტები
                    </h5>
                    <div class="bank-row">
                        <span class="bank-label">ბანკი:</span>
                        <span class="bank-val">{{ $bankName }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">მიმღები:</span>
                        <span class="bank-val">{{ $bankRecipient }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">IBAN:</span>
                        <span class="bank-val">{{ $bankIban }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">SWIFT:</span>
                        <span class="bank-val">{{ $bankSwift }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                ბეჭდვა
            </button>
            <button onclick="closeInvoice()" class="btn btn-secondary" style="margin-left: 10px;">
                დახურვა
            </button>
        </div>

        <div class="text-center mt-4 text-muted small" style="color: #666; font-size: 0.85rem;">
            გმადლობთ რომ სარგებლობთ ჩვენი მომსახურებით!
        </div>
    </div>
    
    <script>
        function closeInvoice() {
            // Check if window was opened by script
            if (window.opener) {
                window.close();
                return;
            }
            
            // Try to close the window/tab
            try {
                // For tabs opened with target="_blank", this might not work
                window.close();
                
                // If close() didn't work, try alternative methods
                setTimeout(function() {
                    // If still open, redirect to blank page
                    if (!document.hidden) {
                        window.location.href = 'about:blank';
                    }
                }, 100);
            } catch (e) {
                // Fallback: redirect to blank page
                window.location.href = 'about:blank';
            }
        }
        
        // Also allow closing with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeInvoice();
            }
        });
    </script>
</body>
</html>
