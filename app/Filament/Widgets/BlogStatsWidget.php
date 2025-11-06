<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BlogStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Blog Posts Overview';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = $this->getBlogTrendData();

        return [
            'datasets' => [
                [
                    'label' => 'Blog Posts Created',
                    'data' => $data['counts'],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['dates'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    private function getBlogTrendData(): array
    {
        // Using raw SQL query to avoid GROUP BY issues
        $data = DB::table('blogs')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [
                now()->subMonth()->startOfDay(),
                now()->endOfDay()
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];

        // Fill in missing dates with zero counts
        $startDate = now()->subMonth()->startOfDay();
        $endDate = now()->endOfDay();
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $dates[] = $dateString;
            
            // Find count for this date or use 0
            $count = $data->firstWhere('date', $dateString);
            $counts[] = $count ? $count->count : 0;
            
            $currentDate->addDay();
        }

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }

    public static function getStats(): array
    {
        return [
            'total' => Blog::count(),
            'published' => Blog::where('status', 'Publish')->count(),
            'draft' => Blog::where('status', 'Draft')->count(),
            'archived' => Blog::where('status', 'Archived')->count(),
        ];
    }
}