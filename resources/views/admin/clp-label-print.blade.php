<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>CLP Label — {{ $label->product_name }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #e5e7eb;
        font-family: Arial, Helvetica, sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px;
        min-height: 100vh;
    }

    /* ── Screen toolbar ── */
    .toolbar {
        background: #1f2937;
        color: #fff;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        max-width: 520px;
    }
    .toolbar h1 { font-size: 13px; font-weight: 600; flex: 1; }
    .print-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .print-btn:hover { background: #1d4ed8; }

    .instructions {
        background: #fef9c3;
        border: 1px solid #ca8a04;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 11px;
        color: #713f12;
        margin-bottom: 20px;
        width: 100%;
        max-width: 520px;
        line-height: 1.6;
    }

    /* ── Label preview wrapper — scaled 3x on screen ── */
    .preview {
        /* 76mm * 3.7795px/mm * 3 scale ≈ 862px × 568px */
        width: 862px;
        height: 568px;
        background: #fff;
        box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }

    /* ── THE LABEL — all dimensions in mm for print accuracy ── */
    .label {
        width: 76mm;
        height: 50mm;
        border: 0.5mm solid #000;
        padding: 1.8mm;
        overflow: hidden;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        line-height: 1.25;
        /* Scale 3x for screen preview */
        transform: scale(3);
        transform-origin: top left;
        position: absolute;
        top: 0;
        left: 0;
    }

    /* ── 1. Product Identifier (top strip) ── */
    .product-id {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.6mm;
        margin-bottom: 0.7mm;
    }
    .product-company {
        font-size: 4.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #555;
    }
    .product-name {
        font-size: 6.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        line-height: 1.2;
    }
    .product-meta {
        font-size: 4pt;
        color: #333;
        margin-top: 0.2mm;
    }

    /* ── 2. Allergen strip ── */
    .allergens {
        font-size: 4pt;
        color: #222;
        border-bottom: 0.2mm solid #ccc;
        padding-bottom: 0.5mm;
        margin-bottom: 0.6mm;
        line-height: 1.3;
    }
    .allergens-label {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 3.5pt;
        letter-spacing: 0.05em;
    }

    /* ── 3. Middle row: pictograms + signal word | statements ── */
    .middle {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.5mm;
    }
    .col-pics {
        width: 16mm;
        vertical-align: top;
        padding-right: 1.5mm;
    }
    .col-stmts {
        vertical-align: top;
    }

    /* Signal word */
    .signal {
        font-size: 7pt;
        font-weight: bold;
        margin-bottom: 0.8mm;
        display: block;
    }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    /* Pictogram diamond */
    .pic-wrap  { margin-bottom: 0.8mm; }
    .pic-table { width: 12mm; border-collapse: collapse; }
    .pc { background: #fff; }
    .pm { text-align: center; vertical-align: middle; background: #fff; }
    .pm img { width: 5.5mm; height: 5.5mm; display: block; margin: auto; }

    /* ── 4. H & P statements — single flowing paragraph ── */
    .statements {
        font-size: 4pt;
        line-height: 1.35;
        color: #000;
    }

    /* ── 5. Supplier footer ── */
    .footer {
        position: absolute;
        bottom: 1.5mm;
        left: 1.8mm;
        right: 1.8mm;
        border-top: 0.3mm solid #000;
        padding-top: 0.5mm;
        overflow: hidden;
    }
    .sup-left {
        float: left;
        font-size: 3.8pt;
        line-height: 1.4;
        color: #111;
        max-width: 48mm;
    }
    .sup-name { font-weight: bold; }
    .sup-right {
        float: right;
        font-size: 3.5pt;
        font-style: italic;
        color: #444;
        text-align: right;
        max-width: 22mm;
        line-height: 1.3;
    }
    .cf::after { content: ""; display: table; clear: both; }

    /* ── Print ── */
    @media print {
        @page {
            size: 76mm 50mm;
            margin: 0;
        }
        body {
            background: #fff;
            display: block;
            padding: 0;
        }
        .toolbar, .instructions { display: none !important; }
        .preview {
            width: 76mm;
            height: 50mm;
            box-shadow: none;
        }
        .label {
            transform: none;
            position: static;
        }
    }
</style>
</head>
<body>

<div class="toolbar">
    <h1>CLP Label: {{ $label->product_name }}</h1>
    <button class="print-btn" onclick="window.print()">🖨 Print / Save PDF</button>
</div>

<div class="instructions">
    <strong>Print instructions:</strong> Set paper size to <strong>76 × 50mm</strong>, margins to <strong>None</strong>, scale <strong>100%</strong>. Disable headers &amp; footers.
</div>

<div class="preview">
<div class="label">

    {{-- ── 1. Product Identifier ── --}}
    <div class="product-id">
        <div class="product-company">{{ $supplierName ?? config('clp.supplier_name', 'Chapter of You') }}</div>
        <div class="product-name">{{ $label->product_name }}</div>
        @if(!empty($productMeta))
            <div class="product-meta">{{ $productMeta }}</div>
        @endif
    </div>

    {{-- ── 2. Allergen Information ── --}}
    @if(!empty($allergens))
        <div class="allergens">
            <span class="allergens-label">Contains: </span>{{ implode(', ', $allergens) }}
        </div>
    @endif

    {{-- ── 3 & 4. Pictograms + Signal Word | Statements ── --}}
    <table class="middle">
        <tr>
            {{-- Left: signal word + pictograms --}}
            <td class="col-pics">
                @if($label->signal_word)
                    <span class="signal {{ strtolower($label->signal_word) === 'danger' ? 'danger' : 'warning' }}">
                        {{ $label->signal_word }}
                    </span>
                @endif

                @foreach($pictogramImages as $picKey => $src)
                    <div class="pic-wrap">
                        <table class="pic-table">
                            <tr>
                                <td class="pc" style="width:3.5mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="height:2.5mm;border-top:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="width:3.5mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                            <tr>
                                <td class="pc" style="border-left:0.4mm solid #dd0000;"></td>
                                <td class="pm"><img src="{{ $src }}" /></td>
                                <td class="pc" style="border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                            <tr>
                                <td class="pc" style="width:3.5mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="height:2.5mm;border-bottom:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="width:3.5mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            </td>

            {{-- Right: single paragraph of H + P statement text only ── --}}
            <td class="col-stmts">
                <div class="statements">
                    {{--
                        All H and P statement texts merged into one flowing paragraph.
                        No codes shown, no section headers — just the plain text.
                    --}}
                    @php
                        $allStatements = array_merge(
                            array_values($hStatements),
                            array_values($pStatements)
                        );
                    @endphp
                    {{ implode(' ', $allStatements) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── 5. Supplier Contact Details ── --}}
    <div class="footer cf">
        <div class="sup-left">
            <span class="sup-name">{{ $label->supplier_name }}</span>
            @if($label->supplier_address)
                &nbsp;{{ $label->supplier_address }}
            @endif
            @if($label->supplier_phone)
                &nbsp;T:{{ $label->supplier_phone }}
            @endif
        </div>
        @if(!empty($label->supplementary_info))
            <div class="sup-right">{{ $label->supplementary_info }}</div>
        @endif
    </div>

</div>
</div>

<script>
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 800);
    });
</script>
</body>
</html>
