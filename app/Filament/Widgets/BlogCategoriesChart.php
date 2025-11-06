<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class BlogCategoriesChart extends ChartWidget
{
    protected static ?string $heading = 'Blog Posts by Category';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categories = Blog::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Posts by Category',
                    'data' => $categories->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                ],
            ],
            'labels' => $categories->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    protected function getMaxHeight(): ?string
{
    return '280px'; // or '400px', '500px', etc.
}
}