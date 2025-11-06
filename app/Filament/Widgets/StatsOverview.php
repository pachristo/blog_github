<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $blogStats = $this->getBlogStats();

        return [
            // Stat::make('Total Users', User::count())
            //     ->description('Registered users')
            //     ->descriptionIcon('heroicon-m-users')
            //     ->color('primary')
            //     ->chart($this->getUserGrowthData()),

            Stat::make('Total Blog Posts', $blogStats['total'])
                ->description('All blog posts')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Published Posts', $blogStats['published'])
                ->description('Currently published')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Draft Posts', $blogStats['draft'])
                ->description('In draft status')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('warning'),

            Stat::make('Archived Posts', $blogStats['archived'])
                ->description('Archived posts')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('danger'),
        ];
    }

    private function getBlogStats(): array
    {
        return [
            'total' => Blog::count(),
            'published' => Blog::where('status', 'publish')->count(),
            'draft' => Blog::where('status', 'draft')->count(),
            'archived' => Blog::where('status', 'archived')->count(),
        ];
    }

}
