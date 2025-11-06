<?php

namespace App\Filament\Widgets;

use App\Models\Blog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentBlogsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Blog Posts';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Blog::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('display_image')
                    ->circular()
                    ->size(40),
                
                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Draft',
                        'success' => 'Published',
                        'danger' => 'Archived',
                    ]),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Blog $record): string => env('APP_BLOG_URL').'/'. $record->slug)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
            ]);
    }
}