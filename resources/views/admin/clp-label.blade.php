<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 5pt;
        color: #000;
        line-height: 1.3;
    }

    .label {
        width: 74mm;
        border: 0.4mm solid #000;
        padding: 1.5mm;
    }

    /* Header */
    .hdr {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.6mm;
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
    .col-left { width: 16mm; vertical-align: top; padding-right: 1mm; }
    .col-right { vertical-align: top; }

    /* Signal word */
    .signal { font-size: 7pt; font-weight: bold; margin-bottom: 0.8mm; display: block; }
    .danger  { color: #cc0000; }
    .warning { color: #b85c00; }

    /* Pictogram */
    .pic-wrap { margin-bottom: 1mm; }
    .pic-table { width: 13mm; border-collapse: collapse; }
    .pc { background: #fff; }
    .pm { text-align: center; vertical-align: middle; background: #fff; padding: 0.5mm; }
    .pm img { width: 7mm; height: 7mm; }

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
    .stmt { font-size: 4.2pt; line-height: 1.3; margin-bottom: 0.15mm; display: block; }
    .code { font-weight: bold; }

    /* Footer */
    .footer {
        border-top: 0.3mm solid #000;
        margin-top: 0.6mm;
        padding-top: 0.4mm;
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
</style>
</head>
<body>
<div class="label">

    <div class="hdr cf">
        <span class="hdr-name">{{ $label->product_name }}</span>
        @if($label->nominal_quantity)
            <span class="hdr-qty">{{ $label->nominal_quantity }}</span>
        @endif
    </div>

    <table class="body-table">
        <tr>
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
                                <td class="pc" style="width:4.5mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="height:2.5mm;border-top:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="width:4.5mm;height:2.5mm;border-top:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                            <tr>
                                <td class="pc" style="border-left:0.4mm solid #dd0000;"></td>
                                <td class="pm"><img src="{{ $src }}" /></td>
                                <td class="pc" style="border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                            <tr>
                                <td class="pc" style="width:4.5mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-left:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="height:2.5mm;border-bottom:0.4mm solid #dd0000;"></td>
                                <td class="pc" style="width:4.5mm;height:2.5mm;border-bottom:0.4mm solid #dd0000;border-right:0.4mm solid #dd0000;"></td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            </td>

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
</body>
</html>
