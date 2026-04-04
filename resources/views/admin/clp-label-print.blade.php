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

    .save-btn {
        background: #16a34a;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    .save-btn:hover { background: #15803d; }
    .save-btn:disabled { background: #6b7280; cursor: wait; }

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

    /* Preview wrapper — 3x scale */
    .preview {
        width: 862px;
        height: 568px;
        background: #fff;
        box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }

    .label {
        width: 76mm;
        height: 50mm;
        padding: 1.5mm;
        overflow: hidden;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        line-height: 1.25;
        transform: scale(3);
        transform-origin: top left;
        position: absolute;
        top: 0;
        left: 0;
    }

    /* ── 1. Product Identifier ── */
    .product-id {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.5mm;
        margin-bottom: 0.6mm;
        text-align: center;
    }
    .product-company {
        font-size: 4pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #555;
    }
    .product-name {
        font-size: 6pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .product-meta {
        font-size: 3.8pt;
        color: #333;
        margin-top: 0.2mm;
    }

    /* ── 2. Allergens ── */
    .allergens {
        font-size: 6.5pt;
        color: #111;
        padding-bottom: 0.4mm;
        margin-bottom: 0.5mm;
        text-align: center;
        line-height: 1.2;
    }
    .allergens-label {
        font-weight: bold;
        font-size: 6pt;
        letter-spacing: 0.04em;
    }

    /* ── 3. Pictograms + Signal Word ── */
    .hazard-row {
        text-align: center;
        margin-bottom: 0.5mm;
        padding-bottom: 0.5mm;
    }
    .hazard-inner {
        display: inline-block;
        vertical-align: middle;
    }
    .signal {
        font-size: 7pt;
        font-weight: bold;
        display: block;
        margin-bottom: 0.3mm;
    }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    .pics-row { text-align: center; }
    .pic-wrap {
        display: inline-block;
        margin: 0 0.8mm;
        vertical-align: middle;
    }
    .pic-img {
        width: 10mm;
        height: 10mm;
        display: block;
    }

    /* ── 4. Statements paragraph ── */
    .statements {
        font-size: 6.5pt;
        line-height: 1.2;
        color: #000;
        text-align: justify;
        margin-bottom: 5mm;
    }

    /* ── 5. Supplier footer — pinned to bottom ── */
    .footer {
        position: absolute;
        bottom: 1.5mm;
        left: 1.5mm;
        right: 1.5mm;
        padding-top: 0.4mm;
        overflow: hidden;
    }
    .sup-left {
        float: left;
        font-size: 6pt;
        line-height: 1.4;
        color: #111;
        max-width: 50mm;
    }
    .sup-name { font-weight: bold; }
    .sup-right {
        float: right;
        font-size: 3pt;
        font-style: italic;
        color: #555;
        text-align: right;
        max-width: 22mm;
        line-height: 1.3;
    }
    .cf::after { content: ""; display: table; clear: both; }

    /* ── Print ── */
    @media print {
        @page { size: 76mm 50mm; margin: 0; }
        body { background: #fff; display: block; padding: 0; }
        .toolbar, .instructions { display: none !important; }
        .preview { width: 76mm; height: 50mm; box-shadow: none; }
        .label { transform: none; position: static; }
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

<div class="toolbar">
    <h1>CLP Label — {{ $label->product_name }}</h1>
    <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
    <button class="save-btn" id="saveBtn" onclick="saveAsImage()">Save as Image</button>
</div>

<div class="instructions">
    <strong>Print instructions:</strong> Paper size <strong>76 × 50mm</strong> · Margins <strong>None</strong> · Scale <strong>100%</strong> · Disable headers &amp; footers.
</div>

<div class="preview">
<div class="label">

    {{-- 1. Product Identifier --}}
    <div class="product-id">
        <div class="product-company">{{ $supplierName ?? config('clp.supplier_name', 'Chapter of You') }}</div>
        <div class="product-name">{{ $label->product_name }} @if(!empty($productMeta))<span style="text-transform: lowercase;">( {{ $productMeta}} )</span>@endif</div>
    </div>

    {{-- 2. Allergen Information --}}
    @if(!empty($allergens))
        <div class="allergens">
            <span class="allergens-label">Contains: </span>{{ implode(', ', $allergens) }}
        </div>
    @endif

    {{-- 3. Pictograms + Signal Word — centred --}}
    <div class="hazard-row">
        @if($label->signal_word)
            <span class="signal {{ strtolower($label->signal_word) === 'danger' ? 'danger' : 'warning' }}">
                {{ $label->signal_word }}
            </span>
        @endif
        <div class="pics-row">
            @foreach($pictogramImages as $picKey => $src)
                <div class="pic-wrap">
                    <img src="{{ $src }}" class="pic-img" />
                </div>
            @endforeach
        </div>
    </div>

    {{-- 4. H & P Statements --}}
    <div class="statements">
        @php
            $allStatements = array_merge(
                array_values($hStatements),
                array_values($pStatements)
            );
        @endphp
        {{ implode('  ', $allStatements) }}
    </div>

    {{-- 5. Supplier Contact Details --}}
    <div class="footer cf">
        <div class="sup-left">
            <span class="sup-name">{{ $label->supplier_name }}</span>,
            @if($label->supplier_address)
                &nbsp;{{ $label->supplier_address }},
            @endif
            @if($label->supplier_phone)
                &nbsp;{{ $label->supplier_phone }}
            @endif
        </div>
        @if(!empty($label->supplementary_info))
            <div class="sup-right">{{ $label->supplementary_info }}</div>
        @endif
    </div>

</div>
</div>

<script>
    function saveAsImage() {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const label = document.querySelector('.label');

        // 76mm × 50mm at 96dpi — explicit pixel dimensions so the
        // position:absolute footer is included in the captured area
        const MM_TO_PX = 96 / 25.4;
        const labelW = Math.round(76 * MM_TO_PX);  // ≈ 287px
        const labelH = Math.round(50 * MM_TO_PX);  // ≈ 189px

        // Remove transform, set fixed pixel size so html2canvas
        // sees the full label including the pinned footer
        label.style.transform = 'none';
        label.style.position  = 'absolute';
        label.style.top       = '0';
        label.style.left      = '0';
        label.style.width     = labelW + 'px';
        label.style.height    = labelH + 'px';
        label.style.overflow  = 'hidden';

        html2canvas(label, {
            scale: 4,           // 4× → ~1148×756px high-res output
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            width: labelW,
            height: labelH,
            windowWidth: labelW,
            windowHeight: labelH,
            logging: false,
        }).then(function(canvas) {
            // Restore original styles
            label.style.transform = 'scale(3)';
            label.style.position  = 'absolute';
            label.style.width     = '';
            label.style.height    = '';

            const link = document.createElement('a');
            link.download = 'CLP-Label-{{ Str::slug($label->product_name) }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            btn.disabled = false;
            btn.textContent = 'Save as Image';
        }).catch(function(err) {
            console.error(err);
            label.style.transform = 'scale(3)';
            label.style.position  = 'absolute';
            label.style.width     = '';
            label.style.height    = '';
            btn.disabled = false;
            btn.textContent = 'Save as Image';
        });
    }

    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 800);
    });
</script>
</body>
</html>
