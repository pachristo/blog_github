<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Filament\Forms\Components\Wizard;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Content')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (\Filament\Forms\Set $set, ?string $state) {
                                        if (filled($state)) {
                                            $set('slug', \Str::slug($state));
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true), // Fix for the unique slug issue on edit

                                TinyEditor::make('content')
                                    ->label('Blog Content')
                                    ->columnSpanFull(),
                            ])->columns(columns: 1),


                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Image & Status')
                            ->schema([
                                Forms\Components\FileUpload::make('display_image')
                                    ->image()
                                    ->directory('blog-images')
                                    ->maxSize(10240) // 10MB
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('675'),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'Draft' => 'Draft',
                                        'Publish' => 'Published',
                                        'Archived' => 'Archived',
                                    ])
                                    ->required()
                                    ->default('Draft'),
                            ]),

                        Forms\Components\Section::make('Associations')
                            ->schema([
                                Forms\Components\Select::make('creator')
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->default(auth()->id()),

                                Forms\Components\Select::make('category')
                                    ->options([
                                        'Sport News' => 'Sport News',
                                        'esports news' => 'esports news',
                                        'Preview' => 'Preview',
                                        'Updates' => 'Updates',
                                    ])
                                    ->required()
                                    ->default('Sport News'),

                                Forms\Components\Select::make('lang')
                                    ->label('Language')
                                    ->options([
                                        'en' => 'English', 'fr' => 'French', 'es' => 'Spanish', 'de' => 'German',
                                        'it' => 'Italian', 'pt' => 'Portuguese', 'ru' => 'Russian', 'ar' => 'Arabic',
                                        'zh' => 'Chinese', 'ja' => 'Japanese', 'ko' => 'Korean', 'hi' => 'Hindi',
                                        'bn' => 'Bengali', 'ur' => 'Urdu', 'tr' => 'Turkish', 'fa' => 'Persian',
                                        'nl' => 'Dutch', 'sv' => 'Swedish', 'pl' => 'Polish', 'uk' => 'Ukrainian',
                                        'ro' => 'Romanian', 'cs' => 'Czech', 'el' => 'Greek', 'he' => 'Hebrew',
                                        'th' => 'Thai', 'vi' => 'Vietnamese', 'id' => 'Indonesian', 'ms' => 'Malay',
                                        'ta' => 'Tamil', 'te' => 'Telugu', 'mr' => 'Marathi', 'gu' => 'Gujarati',
                                        'kn' => 'Kannada', 'ml' => 'Malayalam', 'pa' => 'Punjabi', 'sr' => 'Serbian',
                                        'no' => 'Norwegian', 'da' => 'Danish', 'fi' => 'Finnish', 'hu' => 'Hungarian',
                                        'sk' => 'Slovak', 'sl' => 'Slovenian', 'bg' => 'Bulgarian', 'hr' => 'Croatian',
                                        'lt' => 'Lithuanian', 'lv' => 'Latvian', 'et' => 'Estonian', 'is' => 'Icelandic',
                                        'ga' => 'Irish', 'mt' => 'Maltese', 'sw' => 'Swahili', 'zu' => 'Zulu',
                                        'xh' => 'Xhosa', 'af' => 'Afrikaans',
                                    ])
                                    ->required()
                                    ->default('en'),
                            ]),
                                 Forms\Components\Section::make('SEO')
                            ->schema([
                                Forms\Components\Textarea::make('meta_keywords')
                                    ->maxLength(500)
                                    ->rows(3),

                                Forms\Components\Textarea::make('meta_description')
                                    ->maxLength(300)
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('display_image')
                    ->circular()
                    ->defaultImageUrl(url('default-blog.jpg')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Draft',
                        'success' => 'Publish',
                        'danger' => 'Archived',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('likes')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'publish' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->action(fn ($records) => $records->each->update(['status' => 'Publish']))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
