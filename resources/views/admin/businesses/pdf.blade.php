<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Intelligence Report</title>
    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
            size: a4 landscape;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
            background-color: #ffffff;
            padding: 5px;
        }

        /* Header block */
        .report-header {
            margin-bottom: 12px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .meta-box {
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }

        .meta-badge {
            display: inline-block;
            background-color: #eef2ff;
            color: #4338ca;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
        }

        /* Stats summary cards */
        .stats-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
        }

        .stat-val {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .stat-lbl {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .data-table thead th {
            background-color: #0f172a;
            color: #f8fafc;
            text-align: left;
            padding: 6px 7px;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 1px solid #0f172a;
            border-bottom: 1px solid #0f172a;
        }

        .data-table tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .biz-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 9.5px;
        }

        .biz-category {
            display: inline-block;
            background-color: #f1f5f9;
            color: #334155;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            margin-top: 2px;
            border: 1px solid #e2e8f0;
        }

        .biz-phone {
            font-weight: 600;
            color: #0284c7;
        }

        .biz-email {
            color: #64748b;
            font-size: 8px;
        }

        .biz-address {
            color: #475569;
            font-size: 8px;
        }

        .biz-city {
            font-weight: 600;
            color: #1e293b;
        }

        .rating-box {
            font-weight: bold;
            color: #b45309;
        }

        .review-count {
            font-size: 7.5px;
            color: #64748b;
        }

        .url-link {
            color: #4f46e5;
            text-decoration: none;
            word-break: break-all;
            font-size: 8px;
        }

        /* Footer */
        .footer-note {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="report-header">
        <table class="header-table">
            <tr>
                <td style="width: 60%;">
                    <div class="brand-title">Google Maps Collector</div>
                    <div class="brand-subtitle">{{ $subtitle }} &bull; Confidential Business Intelligence Report</div>
                </td>
                <td style="width: 40%;" class="meta-box">
                    <span class="meta-badge">OFFICIAL EXPORT</span>
                    <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                    <div><strong>Total Records:</strong> {{ $stats['total'] }} records</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Stats -->
    <table class="stats-table">
        <tr>
            <td class="stat-card" style="width: 20%;">
                <div class="stat-val">{{ $stats['total'] }}</div>
                <div class="stat-lbl">Total Records</div>
            </td>
            <td class="stat-card" style="width: 20%;">
                <div class="stat-val">{{ $stats['avg_rating'] }} ★</div>
                <div class="stat-lbl">Average Rating</div>
            </td>
            <td class="stat-card" style="width: 20%;">
                <div class="stat-val">{{ $stats['with_phone'] }}</div>
                <div class="stat-lbl">With Phone</div>
            </td>
            <td class="stat-card" style="width: 20%;">
                <div class="stat-val">{{ $stats['with_website'] }}</div>
                <div class="stat-lbl">With Website</div>
            </td>
            <td class="stat-card" style="width: 20%;">
                <div class="stat-val">{{ $stats['with_email'] }}</div>
                <div class="stat-lbl">With Email</div>
            </td>
        </tr>
    </table>

    <!-- Main Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">#</th>
                <th style="width: 24%;">Business Details</th>
                <th style="width: 14%;">Category</th>
                <th style="width: 15%;">Contact</th>
                <th style="width: 20%;">Location</th>
                <th style="width: 10%; text-align: center;">Rating</th>
                <th style="width: 14%;">Online Presence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($businesses as $index => $b)
                <tr>
                    <td style="text-align: center; color: #64748b; font-weight: bold;">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        <div class="biz-name">{{ $b->name }}</div>
                        @if($b->place_id)
                            <div style="font-size: 7.5px; color: #94a3b8; font-family: monospace;">{{ Str::limit($b->place_id, 24) }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="biz-category">{{ $b->category ?: 'Uncategorized' }}</span>
                    </td>
                    <td>
                        @if($b->phone)
                            <div class="biz-phone">{{ $b->phone }}</div>
                        @else
                            <div style="color: #94a3b8; font-style: italic;">No Phone</div>
                        @endif

                        @if($b->email)
                            <div class="biz-email">{{ $b->email }}</div>
                        @endif
                    </td>
                    <td>
                        @if($b->city || $b->state)
                            <div class="biz-city">
                                {{ implode(', ', array_filter([$b->city, $b->state, $b->country])) }}
                            </div>
                        @endif
                        @if($b->address)
                            <div class="biz-address">{{ Str::limit($b->address, 65) }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($b->rating)
                            <div class="rating-box">★ {{ number_format($b->rating, 1) }}</div>
                            <div class="review-count">({{ $b->review_count ?: 0 }} reviews)</div>
                        @else
                            <span style="color: #94a3b8; font-size: 8px;">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($b->website)
                            <div><a href="{{ $b->website }}" class="url-link">{{ Str::limit(preg_replace('#^https?://#', '', $b->website), 28) }}</a></div>
                        @endif
                        @if($b->maps_url)
                            <div style="margin-top: 2px;"><a href="{{ $b->maps_url }}" class="url-link" style="color: #0284c7;">Google Maps &rarr;</a></div>
                        @endif
                        @if(!$b->website && !$b->maps_url)
                            <span style="color: #94a3b8; font-style: italic;">None</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">
                        No business records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Page generated by Google Maps Collector &bull; Exported on {{ $generatedAt }}
    </div>

</body>
</html>
