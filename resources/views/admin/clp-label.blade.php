<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    @page {
        /* 76mm × 50mm landscape */
        size: 76mm 50mm;
        margin: 0;
    }

    html, body {
        width: 76mm;
        height: 50mm;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 5.5pt;
        color: #000;
        background: #fff;
    }

    .label {
        width: 76mm;
        height: 50mm;
        padding: 2mm;
        display: block;
        position: relative;
        border: 0.3mm solid #000;
    }

    /* ── Top bar: product name + quantity ── */
    .label-header {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 1mm;
        margin-bottom: 1mm;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }

    .product-name {
        font-size: 7pt;
        font-weight: bold;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        flex: 1;
    }

    .quantity {
        font-size: 6pt;
        font-weight: bold;
        margin-left: 2mm;
        white-space: nowrap;
    }

    /* ── Main body: two columns ── */
    .label-body {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    .col-left {
        display: table-cell;
        width: 20mm;
        vertical-align: top;
        padding-right: 1.5mm;
    }

    .col-right {
        display: table-cell;
        vertical-align: top;
    }

    /* ── Signal word ── */
    .signal-word {
        font-size: 7.5pt;
        font-weight: bold;
        margin-bottom: 1mm;
        letter-spacing: 0.03em;
    }

    .signal-danger  { color: #c00; }
    .signal-warning { color: #b85c00; }

    /* ── Pictograms ── */
    .pictograms {
        display: block;
    }

    .pictogram-row {
        display: block;
        margin-bottom: 0.5mm;
    }

    /*
     * Each pictogram: 10mm × 10mm diamond
     * DomPDF doesn't support CSS transforms well,
     * so we use a table-based diamond approach
     */
    .pictogram {
        display: inline-block;
        width: 10mm;
        height: 10mm;
        margin-right: 0.5mm;
        margin-bottom: 0.5mm;
        position: relative;
    }

    .pictogram-outer {
        width: 10mm;
        height: 10mm;
        border: 0.5mm solid #e00;
        background: #fff;
        transform: rotate(45deg);
        position: absolute;
        top: 0;
        left: 0;
    }

    .pictogram-inner {
        position: absolute;
        top: 1.5mm;
        left: 1.5mm;
        width: 7mm;
        height: 7mm;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pictogram img {
        width: 7mm;
        height: 7mm;
        display: block;
        /* Images are embedded as base64 — no rotation needed */
    }

    /* ── Statements ── */
    .section-label {
        font-size: 4.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #555;
        margin-bottom: 0.5mm;
        margin-top: 1mm;
        border-bottom: 0.2mm solid #ccc;
        padding-bottom: 0.3mm;
    }

    .section-label:first-child {
        margin-top: 0;
    }

    .statement-line {
        font-size: 5pt;
        line-height: 1.35;
        margin-bottom: 0.2mm;
    }

    .statement-code {
        font-weight: bold;
    }

    /* ── Footer: supplier details ── */
    .label-footer {
        position: absolute;
        bottom: 2mm;
        left: 2mm;
        right: 2mm;
        border-top: 0.3mm solid #000;
        padding-top: 0.8mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .supplier-info {
        font-size: 4.5pt;
        line-height: 1.3;
        color: #222;
    }

    .supplier-name {
        font-weight: bold;
    }

    .supplementary {
        font-size: 4pt;
        color: #444;
        font-style: italic;
        max-width: 35mm;
        text-align: right;
        line-height: 1.3;
    }
</style>
</head>
<body>
<div class="label">

    {{-- ── Header ── --}}
    <div class="label-header">
        <div class="product-name">{{ $label->product_name }}</div>
        @if($label->nominal_quantity)
            <div class="quantity">{{ $label->nominal_quantity }}</div>
        @endif
    </div>

    {{-- ── Body (two columns) ── --}}
    <div class="label-body">

        {{-- Left column: signal word + pictograms --}}
        <div class="col-left">
            @if($label->signal_word)
                <div class="signal-word {{ strtolower($label->signal_word) === 'danger' ? 'signal-danger' : 'signal-warning' }}">
                    {{ $label->signal_word }}
                </div>
            @endif

            {{-- Pictograms --}}
            <div class="pictograms">
                @foreach($pictogramImages as $picKey => $picBase64)
                    <div class="pictogram-row">
                        {{-- Diamond wrapper using nested divs (DomPDF compatible) --}}
                        <table style="width:10mm; height:10mm; border-collapse:collapse; display:inline-table;">
                            <tr>
                                <td style="width:3.5mm; height:3.5mm; border-top:0.5mm solid #e00; border-left:0.5mm solid #e00; background:#fff;"></td>
                                <td style="width:3mm; height:3.5mm; border-top:0.5mm solid #e00; background:#fff;"></td>
                                <td style="width:3.5mm; height:3.5mm; border-top:0.5mm solid #e00; border-right:0.5mm solid #e00; background:#fff;"></td>
                            </tr>
                            <tr>
                                <td style="border-left:0.5mm solid #e00; background:#fff;"></td>
                                <td style="text-align:center; vertical-align:middle; background:#fff;">
                                    <img src="{{ $picBase64 }}" style="width:5.5mm; height:5.5mm;" />
                                </td>
                                <td style="border-right:0.5mm solid #e00; background:#fff;"></td>
                            </tr>
                            <tr>
                                <td style="width:3.5mm; height:3.5mm; border-bottom:0.5mm solid #e00; border-left:0.5mm solid #e00; background:#fff;"></td>
                                <td style="width:3mm; height:3.5mm; border-bottom:0.5mm solid #e00; background:#fff;"></td>
                                <td style="width:3.5mm; height:3.5mm; border-bottom:0.5mm solid #e00; border-right:0.5mm solid #e00; background:#fff;"></td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right column: H and P statements --}}
        <div class="col-right">

            @if(!empty($hStatements))
                <div class="section-label">Hazard statements</div>
                @foreach($hStatements as $code => $text)
                    <div class="statement-line">
                        <span class="statement-code">{{ $code }}</span> {{ $text }}
                    </div>
                @endforeach
            @endif

            @if(!empty($pStatements))
                <div class="section-label">Precautionary statements</div>
                @foreach($pStatements as $code => $text)
                    <div class="statement-line">
                        <span class="statement-code">{{ $code }}</span> {{ $text }}
                    </div>
                @endforeach
            @endif

        </div>
    </div>

    {{-- ── Footer: supplier + supplementary ── --}}
    <div class="label-footer">
        <div class="supplier-info">
            <div class="supplier-name">{{ $label->supplier_name }}</div>
            @if($label->supplier_address)
                <div>{{ $label->supplier_address }}</div>
            @endif
            @if($label->supplier_phone)
                <div>T: {{ $label->supplier_phone }}</div>
            @endif
        </div>

        @if($label->supplementary_info)
            <div class="supplementary">{{ $label->supplementary_info }}</div>
        @endif
    </div>

</div>
</body>
</html>
