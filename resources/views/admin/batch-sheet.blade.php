<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page { size: A4; margin: 18mm 15mm; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9pt;
        color: #000;
        background: #fff;
    }

    /* ── Header ── */
    .page-header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .company-name {
        font-size: 18pt;
        font-weight: bold;
        letter-spacing: 3px;
        text-transform: uppercase;
    }
    .doc-title {
        font-size: 11pt;
        font-weight: bold;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .doc-subtitle {
        font-size: 8pt;
        color: #555;
        margin-top: 3px;
    }

    /* ── Section wrapper ── */
    .section {
        margin-bottom: 10px;
    }
    .section-title {
        font-size: 9.5pt;
        font-weight: bold;
        background: #000;
        color: #fff;
        padding: 3px 6px;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ── Info grid ── */
    .info-grid {
        width: 100%;
        border-collapse: collapse;
    }
    .info-grid td {
        padding: 3px 5px;
        border: 0.5px solid #ccc;
        font-size: 9pt;
        vertical-align: middle;
    }
    .info-grid .label {
        font-weight: bold;
        background: #f5f5f5;
        width: 38%;
        white-space: nowrap;
    }
    .info-grid .value {
        width: 12%;
    }

    /* ── Ingredients table ── */
    .ing-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
    }
    .ing-table th {
        background: #222;
        color: #fff;
        padding: 4px 5px;
        font-weight: bold;
        text-align: left;
        border: 0.5px solid #000;
        font-size: 8pt;
    }
    .ing-table td {
        padding: 4px 5px;
        border: 0.5px solid #aaa;
        vertical-align: middle;
    }
    .ing-table tr:nth-child(even) td {
        background: #f9f9f9;
    }

    /* ── Compliance checks ── */
    .compliance-grid {
        width: 100%;
        border-collapse: collapse;
    }
    .compliance-grid td {
        padding: 4px 6px;
        border: 0.5px solid #ccc;
        font-size: 9pt;
        vertical-align: middle;
    }
    .compliance-grid .label {
        font-weight: bold;
        background: #f5f5f5;
        width: 45%;
    }
    .check-yes { color: #166534; font-weight: bold; }
    .check-no  { color: #991b1b; font-weight: bold; }

    /* ── Notes ── */
    .notes-box {
        border: 0.5px solid #aaa;
        padding: 8px;
        min-height: 70px;
        font-size: 9pt;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    /* ── Footer ── */
    .doc-footer {
        margin-top: 12px;
        border-top: 1px solid #ccc;
        padding-top: 6px;
        display: table;
        width: 100%;
    }
    .footer-cell {
        display: table-cell;
        font-size: 7.5pt;
        color: #666;
        vertical-align: bottom;
    }
    .footer-cell.right { text-align: right; }

    /* ── Signature row ── */
    .sig-row {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }
    .sig-row td {
        padding: 4px 8px;
        border: 0.5px solid #ccc;
        font-size: 8.5pt;
        width: 50%;
        vertical-align: bottom;
    }
    .sig-line {
        border-bottom: 1px solid #000;
        height: 22px;
        margin-bottom: 2px;
    }
    .sig-label { font-size: 7.5pt; color: #666; }
</style>
</head>
<body>

{{-- ── Page header ── --}}
<div class="page-header">
    <div class="company-name">Chapter of You</div>
    <div class="doc-title">Production Batch Sheet</div>
    <div class="doc-subtitle">
        Complete a new sheet for every blend produced. Keep accurate records of ingredients, suppliers, weights and IFRA/CLP references.
    </div>
</div>

{{-- ── Batch information ── --}}
<div class="section">
    <div class="section-title">Batch Information</div>
    <table class="info-grid">
        <tr>
            <td class="label">Batch Number</td>
            <td colspan="3">{{ $sheet->batch_number }}</td>
        </tr>
        <tr>
            <td class="label">Blend Name</td>
            <td colspan="3">{{ $sheet->blend_name }}</td>
        </tr>
        <tr>
            <td class="label">Date of Manufacture</td>
            <td>{{ $sheet->date_of_manufacture?->format('d/m/Y') }}</td>
            <td class="label">Produced By</td>
            <td>{{ $sheet->produced_by }}</td>
        </tr>
        <tr>
            <td class="label">Bottle Size (ml)</td>
            <td>{{ $sheet->bottle_size_ml }} ml</td>
            <td class="label">Total Units Produced</td>
            <td>{{ $sheet->total_units_produced }}</td>
        </tr>
        @if($sheet->order)
        <tr>
            <td class="label">Linked Order</td>
            <td colspan="3">#{{ $sheet->order->id }} — {{ $sheet->order->first_name }} {{ $sheet->order->last_name }}</td>
        </tr>
        @endif
        @if($sheet->product)
        <tr>
            <td class="label">Product</td>
            <td colspan="3">{{ $sheet->product->name }} ({{ $sheet->product->mpn }})</td>
        </tr>
        @endif
    </table>
</div>

{{-- ── Ingredients ── --}}
<div class="section">
    <div class="section-title">Ingredients Used</div>
    <table class="ing-table">
        <thead>
            <tr>
                <th style="width:22%">Ingredient</th>
                <th style="width:18%">Supplier</th>
                <th style="width:14%">Lot/Batch No.</th>
                <th style="width:10%">% Used</th>
                <th style="width:12%">Weight (g)</th>
                <th style="width:24%">SDS/IFRA Ref.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sheet->ingredients as $row)
            <tr>
                <td>{{ $row['ingredient'] ?? '' }}</td>
                <td>{{ $row['supplier'] ?? '' }}</td>
                <td>{{ $row['lot_batch_no'] ?? '' }}</td>
                <td style="text-align:center">{{ $row['percent_used'] ?? '' }}</td>
                <td style="text-align:center">{{ $row['weight_g'] ?? '' }}</td>
                <td>{{ $row['sds_ifra_ref'] ?? '' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#999;">No ingredients recorded</td></tr>
            @endforelse
            {{-- Blank rows to pad to minimum 6 rows --}}
            @for($i = count($sheet->ingredients); $i < 6; $i++)
            <tr>
                <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            @endfor
        </tbody>
    </table>
</div>

{{-- ── Compliance checks ── --}}
<div class="section">
    <div class="section-title">Compliance Checks</div>
    <table class="compliance-grid">
        <tr>
            <td class="label">IFRA Certificate Checked?</td>
            <td>
                @if($sheet->ifra_certificate_checked)
                    <span class="check-yes">✓ Yes</span>
                @else
                    <span class="check-no">✗ No</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Max % Allowed</td>
            <td>{{ $sheet->max_percent_allowed ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">SDS Hazards Noted</td>
            <td colspan="3">{{ $sheet->sds_hazards_noted ?: '—' }}</td>
        </tr>
        <tr>
            <td class="label">CLP Label Prepared?</td>
            <td colspan="3">
                @if($sheet->clp_label_prepared)
                    <span class="check-yes">✓ Yes</span>
                @else
                    <span class="check-no">✗ No</span>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ── Notes ── --}}
<div class="section">
    <div class="section-title">Notes &amp; Observations</div>
    <div class="notes-box">{{ $sheet->notes ?: '' }}</div>
</div>

{{-- ── Signatures ── --}}
<div class="section">
    <div class="section-title">Sign Off</div>
    <table class="sig-row">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div class="sig-label">Produced by (signature)</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div class="sig-label">Checked by (signature)</div>
            </td>
        </tr>
        <tr>
            <td style="padding-top:10px;">
                <div class="sig-line"></div>
                <div class="sig-label">Date</div>
            </td>
            <td style="padding-top:10px;">
                <div class="sig-line"></div>
                <div class="sig-label">Date</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Footer ── --}}
<div class="doc-footer">
    <div class="footer-cell">
        Batch: {{ $sheet->batch_number }} &nbsp;|&nbsp;
        Generated: {{ now()->format('d/m/Y H:i') }}
        @if($sheet->createdBy) &nbsp;|&nbsp; Created by: {{ $sheet->createdBy->name }} @endif
    </div>
    <div class="footer-cell right">
        Chapter of You &nbsp;|&nbsp; CONFIDENTIAL — Internal Use Only
    </div>
</div>

</body>
</html>
