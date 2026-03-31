<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page {
        size: 76mm 50mm;
        margin: 0mm;
    }

    html, body {
        width: 76mm;
        height: 50mm;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 5pt;
        color: #000;
        background: #fff;
        line-height: 1.3;
        overflow: hidden;
    }

    .label {
        width: 76mm;
        height: 50mm;
        border: 0.4mm solid #000;
        padding: 1.5mm;
        overflow: hidden;
    }

    /* Header */
    .label-header {
        border-bottom: 0.3mm solid #000;
        padding-bottom: 0.8mm;
        margin-bottom: 0.8mm;
    }
    .header-row { display: table; width: 100%; table-layout: fixed; }
    .product-name {
        display: table-cell;
        font-size: 6.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .quantity {
        display: table-cell;
        font-size: 6pt;
        font-weight: bold;
        text-align: right;
        width: 15mm;
        white-space: nowrap;
        vertical-align: bottom;
    }

    /* Body columns */
    .label-body { display: table; width: 100%; table-layout: fixed; }
    .col-left {
        display: table-cell;
        width: 18mm;
        vertical-align: top;
        padding-right: 1mm;
    }
    .col-right { display: table-cell; vertical-align: top; overflow: hidden; }

    /* Signal word */
    .signal-word { font-size: 8pt; font-weight: bold; margin-bottom: 1mm; }
    .signal-danger  { color: #cc0000; }
    .signal-warning { color: #b85c00; }

    /* Pictogram */
    .pictogram-wrap { margin-bottom: 1mm; }
    .pictogram-table { width: 11mm; height: 11mm; border-collapse: collapse; }
    .pic-corner { background: #fff; }
    .pic-center { text-align: center; vertical-align: middle; background: #fff; }
    .pic-center img { width: 6mm; height: 6mm; }

    /* Statements */
    .section-label {
        font-size: 4pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #444;
        border-bottom: 0.15mm solid #aaa;
        padding-bottom: 0.2mm;
        margin-bottom: 0.4mm;
        margin-top: 0.8mm;
    }
    .section-label.first { margin-top: 0; }
    .statement-line { font-size: 4.5pt; line-height: 1.3; margin-bottom: 0.15mm; }
    .stmt-code { font-weight: bold; }

    /* Footer */
    .label-footer {
        border-top: 0.3mm solid #000;
        padding-top: 0.6mm;
        margin-top: 0.5mm;
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .footer-supplier {
        display: table-cell;
        font-size: 4pt;
        line-height: 1.35;
        color: #111;
        vertical-align: bottom;
    }
    .footer-supplier .supplier-name { font-weight: bold; }
    .footer-supplementary {
        display: table-cell;
        font-size: 3.5pt;
        color: #444;
        font-style: italic;
        text-align: right;
        vertical-align: bottom;
        width: 30mm;
        line-height: 1.3;
    }
</style>
</head>
<body>
<div class="label">

    <div class="label-header">
        <div class="header-row">
            <div class="product-name">{{ $label->product_name }}</div>
            @if($label->nominal_quantity)
                <div class="quantity">{{ $label->nominal_quantity }}</div>
            @endif
        </div>
    </div>

    <div class="label-body">
        <div class="col-left">
            @if($label->signal_word)
                <div class="signal-word {{ strtolower($label->signal_word) === 'danger' ? 'signal-danger' : 'signal-warning' }}">
                    {{ $label->signal_word }}
                </div>
            @endif

            @foreach($pictogramImages as $picKey => $picBase64)
                <div class="pictogram-wrap">
                    <table class="pictogram-table">
                        <tr>
                            <td class="pic-corner" style="width:3.5mm;height:3mm;border-top:0.5mm solid #dd0000;border-left:0.5mm solid #dd0000;"></td>
                            <td class="pic-corner" style="width:4mm;height:3mm;border-top:0.5mm solid #dd0000;"></td>
                            <td class="pic-corner" style="width:3.5mm;height:3mm;border-top:0.5mm solid #dd0000;border-right:0.5mm solid #dd0000;"></td>
                        </tr>
                        <tr>
                            <td class="pic-corner" style="width:3.5mm;border-left:0.5mm solid #dd0000;"></td>
                            <td class="pic-center"><img src="{{ $picBase64 }}" /></td>
                            <td class="pic-corner" style="width:3.5mm;border-right:0.5mm solid #dd0000;"></td>
                        </tr>
                        <tr>
                            <td class="pic-corner" style="width:3.5mm;height:3mm;border-bottom:0.5mm solid #dd0000;border-left:0.5mm solid #dd0000;"></td>
                            <td class="pic-corner" style="height:3mm;border-bottom:0.5mm solid #dd0000;"></td>
                            <td class="pic-corner" style="width:3.5mm;height:3mm;border-bottom:0.5mm solid #dd0000;border-right:0.5mm solid #dd0000;"></td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>

        <div class="col-right">
            @if(!empty($hStatements))
                <div class="section-label first">Hazard statements</div>
                @foreach($hStatements as $code => $text)
                    <div class="statement-line"><span class="stmt-code">{{ $code }}</span> {{ $text }}</div>
                @endforeach
            @endif

            @if(!empty($pStatements))
                <div class="section-label">Precautionary statements</div>
                @foreach($pStatements as $code => $text)
                    <div class="statement-line"><span class="stmt-code">{{ $code }}</span> {{ $text }}</div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="label-footer">
        <div class="footer-supplier">
            <span class="supplier-name">{{ $label->supplier_name }}</span>
            @if($label->supplier_address)
                &nbsp;{{ $label->supplier_address }}
            @endif
            @if($label->supplier_phone)
                &nbsp;T: {{ $label->supplier_phone }}
            @endif
        </div>
        @if($label->supplementary_info)
            <div class="footer-supplementary">{{ $label->supplementary_info }}</div>
        @endif
    </div>

</div>
</body>
</html>
