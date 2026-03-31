<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    /*
     * Strategy: render on A4, but constrain ALL content inside
     * a fixed-size box. DomPDF will produce one A4 page with the
     * label sitting in the top-left corner. Print at actual size
     * and cut to 76x50mm.
     *
     * Do NOT set @page or body height — that is what causes the
     * blank page overflow issue in DomPDF.
     */

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        background: #fff;
        color: #000;
    }

    /* The label is a fixed-size box — nothing can escape it */
    .label {
        width: 76mm;
        height: 50mm;
        border: 0.5mm solid #000;
        padding: 1.5mm;
        overflow: hidden;
        display: block;
    }

    /* ── Header ── */
    .hdr {
        width: 100%;
        border-bottom: 0.3mm solid #000;
        margin-bottom: 1mm;
        padding-bottom: 0.5mm;
    }
    .hdr table { width: 100%; border-collapse: collapse; }
    .hdr-name {
        font-size: 6.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .hdr-qty {
        font-size: 6pt;
        font-weight: bold;
        text-align: right;
        white-space: nowrap;
        width: 14mm;
        vertical-align: bottom;
    }

    /* ── Body: two columns via table ── */
    .body-table {
        width: 100%;
        border-collapse: collapse;
    }
    .col-left {
        width: 18mm;
        vertical-align: top;
        padding-right: 1mm;
    }
    .col-right {
        vertical-align: top;
    }

    /* Signal word */
    .signal {
        font-size: 8pt;
        font-weight: bold;
        margin-bottom: 1mm;
        display: block;
    }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    /* Pictogram diamond via nested table */
    .pic-wrap { margin-bottom: 1mm; }
    .pic-table {
        width: 12mm;
        border-collapse: collapse;
    }
    .pc { background: #fff; }
    .pm { text-align: center; vertical-align: middle; background: #fff; }
    .pm img { width: 6.5mm; height: 6.5mm; display: block; margin: auto; }

    /* Statements */
    .sec {
        font-size: 4pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #444;
        border-bottom: 0.15mm solid #999;
        padding-bottom: 0.2mm;
        margin-bottom: 0.4mm;
        margin-top: 0.7mm;
        display: block;
    }
    .sec-first { margin-top: 0; }
    .stmt {
        font-size: 4.5pt;
        line-height: 1.3;
        margin-bottom: 0.2mm;
        display: block;
    }
    .code { font-weight: bold; }

    /* ── Footer ── */
    .footer {
        border-top: 0.3mm solid #000;
        margin-top: 0.8mm;
        padding-top: 0.5mm;
        width: 100%;
    }
    .footer table { width: 100%; border-collapse: collapse; }
    .sup-info {
        font-size: 4pt;
        line-height: 1.4;
        color: #111;
        vertical-align: bottom;
    }
    .sup-name { font-weight: bold; }
    .sup-extra {
        font-size: 3.5pt;
        font-style: italic;
        color: #444;
        text-align: right;
        vertical-align: bottom;
        width: 28mm;
        line-height: 1.3;
    }
</style>
</head>
<body>

<div class="label">

    {{-- Header --}}
    <div class="hdr">
        <table><tr>
            <td class="hdr-name">{{ $label->product_name }}</td>
            @if($label->nominal_quantity)
                <td class="hdr-qty">{{ $label->nominal_quantity }}</td>
            @endif
        </tr></table>
    </div>

    {{-- Body --}}
    <table class="body-table"><tr>

        {{-- Left: signal + pictograms --}}
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
                            <td class="pc" style="width:4mm;height:3mm;border-top:0.5mm solid #dd0000;border-left:0.5mm solid #dd0000;"></td>
                            <td class="pc" style="height:3mm;border-top:0.5mm solid #dd0000;"></td>
                            <td class="pc" style="width:4mm;height:3mm;border-top:0.5mm solid #dd0000;border-right:0.5mm solid #dd0000;"></td>
                        </tr>
                        <tr>
                            <td class="pc" style="border-left:0.5mm solid #dd0000;"></td>
                            <td class="pm"><img src="{{ $src }}" /></td>
                            <td class="pc" style="border-right:0.5mm solid #dd0000;"></td>
                        </tr>
                        <tr>
                            <td class="pc" style="width:4mm;height:3mm;border-bottom:0.5mm solid #dd0000;border-left:0.5mm solid #dd0000;"></td>
                            <td class="pc" style="height:3mm;border-bottom:0.5mm solid #dd0000;"></td>
                            <td class="pc" style="width:4mm;height:3mm;border-bottom:0.5mm solid #dd0000;border-right:0.5mm solid #dd0000;"></td>
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

    </tr></table>

    {{-- Footer --}}
    <div class="footer">
        <table><tr>
            <td class="sup-info">
                <span class="sup-name">{{ $label->supplier_name }}</span>
                @if($label->supplier_address)
                    &nbsp;{{ $label->supplier_address }}
                @endif
                @if($label->supplier_phone)
                    &nbsp;T:{{ $label->supplier_phone }}
                @endif
            </td>
            @if($label->supplementary_info)
                <td class="sup-extra">{{ $label->supplementary_info }}</td>
            @endif
        </tr></table>
    </div>

</div>

</body>
</html>
