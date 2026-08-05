<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * Stream CSV download for a given Eloquent query.
     */
    public function export(Builder $query, string $filename = 'businesses_export.csv'): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Business Name',
            'Phone',
            'Email',
            'Address',
            'City',
            'State',
            'Country',
            'Postal Code',
            'Website',
            'Category',
            'Rating',
            'Review Count',
            'Google Maps URL',
            'Place ID',
            'Latitude',
            'Longitude',
            'First Collected',
            'Last Collected',
        ];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            $query->chunk(500, function ($businesses) use ($file) {
                foreach ($businesses as $b) {
                    fputcsv($file, [
                        $b->name,
                        $b->phone,
                        $b->email,
                        $b->address,
                        $b->city,
                        $b->state,
                        $b->country,
                        $b->postal_code,
                        $b->website,
                        $b->category,
                        $b->rating,
                        $b->review_count,
                        $b->maps_url,
                        $b->place_id,
                        $b->latitude,
                        $b->longitude,
                        $b->first_collected_at?->toDateTimeString(),
                        $b->last_collected_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
