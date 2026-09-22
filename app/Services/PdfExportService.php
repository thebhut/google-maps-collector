<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\Response;

class PdfExportService
{
    /**
     * Generate and download PDF export for a given Eloquent query.
     */
    public function export(Builder $query, string $filename = 'businesses_export.pdf', string $subtitle = 'Business Directory Report'): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $businesses = $query->get();

        $validRatings = $businesses->filter(fn($b) => is_numeric($b->rating) && $b->rating > 0);
        $avgRating = $validRatings->count() > 0 ? number_format($validRatings->avg('rating'), 1) : 'N/A';

        $stats = [
            'total' => $businesses->count(),
            'avg_rating' => $avgRating,
            'with_phone' => $businesses->filter(fn($b) => !empty(trim((string)$b->phone)))->count(),
            'with_website' => $businesses->filter(fn($b) => !empty(trim((string)$b->website)))->count(),
            'with_email' => $businesses->filter(fn($b) => !empty(trim((string)$b->email)))->count(),
        ];

        $pdf = Pdf::loadView('admin.businesses.pdf', [
            'businesses' => $businesses,
            'subtitle' => $subtitle,
            'stats' => $stats,
            'generatedAt' => now()->format('M d, Y - h:i A'),
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Helvetica',
            'chroot' => base_path(),
        ]);

        return $pdf->download($filename);
    }
}
