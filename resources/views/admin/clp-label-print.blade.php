<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>CLP Label — {{ $label->product_name }}</title>
<style>
    /* ── Screen preview styles ── */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #e5e7eb;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        min-height: 100vh;
        font-family: Arial, Helvetica, sans-serif;
        padding: 20px;
    }

    .screen-toolbar {
        background: #1f2937;
        color: #fff;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        width: 100%;
        max-width: 500px;
    }

    .screen-toolbar h1 {
        font-size: 14px;
        font-weight: 600;
        flex: 1;
    }

    .print-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .print-btn:hover { background: #1d4ed8; }

    .instructions {
        background: #fef9c3;
        border: 1px solid #ca8a04;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 12px;
        color: #713f12;
        margin-bottom: 20px;
        width: 100%;
        max-width: 500px;
        line-height: 1.5;
    }

    /* The label preview on screen */
    .label-preview {
        background: #fff;
        box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        /* Show at 3x scale on screen for easy preview */
        width: 228mm; /* 76mm * 3 */
        height: 150mm; /* 50mm * 3 */
        transform-origin: top center;
    }

    /* ── The actual label — all sizes in mm for print accuracy ── */
    .label {
        width: 76mm;
        height: 50mm;
        border: 0.5mm solid #000;
        padding: 1.5mm;
        overflow: hidden;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 5pt;
        color: #000;
        line-height: 1.3;
        /* Scale up 3x for screen preview */
        transform: scale(3);
        transform-origin: top left;
    }

    /* Header */
    .hdr {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.7mm;
        margin-bottom: 0.8mm;
        overflow: hidden;
    }
    .hdr-name {
        float: left;
        font-size: 6pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        max-width: 55mm;
    }
    .hdr-qty {
        float: right;
        font-size: 5.5pt;
        font-weight: bold;
    }
    .cf::after { content: ""; display: table; clear: both; }

    /* Body */
    .body-table { width: 100%; border-collapse: collapse; }
    .col-left   { width: 17mm; vertical-align: top; padding-right: 1mm; }
    .col-right  { vertical-align: top; }

    /* Signal word */
    .signal { font-size: 7.5pt; font-weight: bold; margin-bottom: 0.8mm; display: block; }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    /* Pictogram */
    .pic-wrap  { margin-bottom: 1mm; }
    .pic-table { width: 12mm; border-collapse: collapse; }
    .pc { background: #fff; }
    .pm { text-align: center; vertical-align: middle; background: #fff; }
    .pm img { width: 6mm; height: 6mm; display: block; margin: auto; }

    /* Statements */
    .sec {
        font-size: 3.8pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #444;
        border-bottom: 0.15mm solid #999;
        padding-bottom: 0.2mm;
        margin-bottom: 0.3mm;
        margin-top: 0.7mm;
        display: block;
    }
    .sec-first { margin-top: 0; }
    .stmt      { font-size: 4pt; line-height: 1.3; margin-bottom: 0.15mm; display: block; }
    .code      { font-weight: bold; }

    /* Footer */
    .footer {
        border-top: 0.3mm solid #000;
        margin-top: 0.7mm;
        padding-top: 0.5mm;
        overflow: hidden;
    }
    .sup-left {
        float: left;
        font-size: 3.8pt;
        line-height: 1.4;
        color: #111;
        max-width: 46mm;
    }
    .sup-name { font-weight: bold; }
    .sup-right {
        float: right;
        font-size: 3.5pt;
        font-style: italic;
        color: #444;
        text-align: right;
        max-width: 24mm;
        line-height: 1.3;
    }

    /* ── Print styles ──
     *
     * When the user prints, the browser uses these rules.
     * @page sets the exact paper size — this works reliably in
     * Chrome, Firefox and Safari's print engines, unlike server-side PDF libs.
     *
     * Instruct user to:
     *   - Set paper size to "Custom" → 76mm x 50mm  OR use the @page size
     *   - Set margins to None/0
     *   - Disable "Print headers and footers"
     *   - Print at 100% scale (not "Fit to page")
    */
    @media print {
        @page {
            size: 76mm 50mm;
            margin: 0;
        }

        body {
            background: #fff;
            display: block;
            padding: 0;
            margin: 0;
        }

        .screen-toolbar,
        .instructions {
            display: none !important;
        }

        .label-preview {
            box-shadow: none;
            width: 76mm;
            height: 50mm;
        }

        .label {
            transform: none;
            width: 76mm;
            height: 50mm;
        }
    }
</style>
</head>
<body>

<div class="screen-toolbar">
    <h1>CLP Label: {{ $label->product_name }}</h1>
    <button class="print-btn" onclick="window.print()">🖨 Print / Save PDF</button>
</div>

<div class="instructions">
    <strong>Printing instructions:</strong><br>
    1. Click "Print / Save PDF" above<br>
    2. Set <strong>Paper size</strong> to <strong>76mm × 50mm</strong> (or use a label sheet)<br>
    3. Set <strong>Margins</strong> to <strong>None</strong><br>
    4. Set scale to <strong>100%</strong> — do NOT use "Fit to page"<br>
    5. Disable "Print headers and footers" in More settings
</div>

<div class="label-preview">
    <div class="label">

        {{-- Header --}}
        <div class="hdr cf">
            <span class="hdr-name">{{ $label->product_name }}</span>
            @if($label->nominal_quantity)
                <span class="hdr-qty">{{ $label->nominal_quantity }}</span>
            @endif
        </div>

        {{-- Body --}}
        <table class="body-table">
            <tr>
                {{-- Left: signal word + pictograms --}}
                <td class="col-left">
                    @if($label->signal_word)
                        <span class="signal {{ strtolower($label->signal_word) === 'danger' ? 'danger' : 'warning' }}">
                            {{ $label->signal_word }}
                        </span>
                    @endif

                    @foreach($pictogramImages as $picKey => $src)
                        <div class="pic-wrap">
                            <table class="pic-table">
                                <tr>
                                    <td class="pc" style="width:4mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                    <td class="pc" style="height:2.5mm;border-top:0.4mm solid #dd0000;"></td>
                                    <td class="pc" style="width:4mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                                </tr>
                                <tr>
                                    <td class="pc" style="border-left:0.4mm solid #dd0000;"></td>
                                    <td class="pm"><img src="{{ $src }}" /></td>
                                    <td class="pc" style="border-right:0.4mm solid #dd0000;"></td>
                                </tr>
                                <tr>
                                    <td class="pc" style="width:4mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                    <td class="pc" style="height:2.5mm;border-bottom:0.4mm solid #dd0000;"></td>
                                    <td class="pc" style="width:4mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </td>

                {{-- Right: H and P statements --}}
                <td class="col-right">
                    @if(!empty($hStatements))
                        <span class="sec sec-first">Hazard statements</span>
                        @foreach($hStatements as $code => $text)
                            <span class="stmt"><span class="code">{{ $code }}</span> {{ $text }}</span>
                        @endforeach
                    @endif

                    @if(!empty($pStatements))
                        <span class="sec">Precautionary statements</span>
                        @foreach($pStatements as $code => $text)
                            <span class="stmt"><span class="code">{{ $code }}</span> {{ $text }}</span>
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>

        {{-- Footer --}}
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
            @if($label->supplementary_info)
                <div class="sup-right">{{ $label->supplementary_info }}</div>
            @endif
        </div>

    </div>
</div>

<script>
    // Auto-open print dialog when page loads
    // Comment this out if you want manual trigger only
    window.addEventListener('load', function() {
        // Small delay to ensure images are rendered
        setTimeout(function() {
            window.print();
        }, 800);
    });
</script>

</body>
</html>
