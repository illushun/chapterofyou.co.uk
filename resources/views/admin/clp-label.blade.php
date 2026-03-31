<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    /*
     * mPDF blade — 76mm × 50mm label
     * mPDF respects the paper size set in the constructor,
     * so no @page trickery needed. Margins are all 0 in the
     * constructor so this content fills the full page.
     */

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 5pt;
        color: #000;
        line-height: 1.3;
    }

    /* Outer border — 0.5mm inset from the paper edge */
    .label {
        width: 75mm;
        border: 0.4mm solid #000;
        padding: 1.5mm;
    }

    /* ── Header: product name + quantity ── */
    .hdr {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.8mm;
        margin-bottom: 0.8mm;
        overflow: hidden;
    }
    .hdr-name {
        float: left;
        font-size: 6.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        max-width: 56mm;
    }
    .hdr-qty {
        float: right;
        font-size: 6pt;
        font-weight: bold;
    }
    .clearfix::after { content: ""; display: table; clear: both; }

    /* ── Body: two-column table ── */
    .body-table {
        width: 100%;
        border-collapse: collapse;
    }
    .col-left {
        width: 17mm;
        vertical-align: top;
        padding-right: 1mm;
    }
    .col-right { vertical-align: top; }

    /* Signal word */
    .signal {
        font-size: 8pt;
        font-weight: bold;
        margin-bottom: 1mm;
        display: block;
    }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    /* Pictogram — table-based diamond */
    .pic-wrap { margin-bottom: 1.2mm; }
    .pic-table { width: 12mm; border-collapse: collapse; }
    .pc { background: #fff; }
    .pm { text-align: center; vertical-align: middle; background: #fff; }
    .pm img { width: 6mm; height: 6mm; }

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
        margin-top: 0.8mm;
        display: block;
    }
    .sec-first { margin-top: 0; }
    .stmt {
        font-size: 4.5pt;
        line-height: 1.35;
        margin-bottom: 0.2mm;
        display: block;
    }
    .code { font-weight: bold; }

    /* ── Footer ── */
    .footer {
        border-top: 0.3mm solid #000;
        margin-top: 0.8mm;
        padding-top: 0.5mm;
        overflow: hidden;
    }
    .sup-left {
        float: left;
        font-size: 4pt;
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
        max-width: 26mm;
        line-height: 1.3;
    }
</style>
</head>
<body>
<div class="label">

    {{-- Header --}}
    <div class="hdr clearfix">
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
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer clearfix">
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
</body>
</html>
