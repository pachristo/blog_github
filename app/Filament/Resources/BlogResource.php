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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

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
                                    ->columnSpanFull()
                                    ->configure([
                                        'plugins' => 'autolink link image lists advlist fullscreen media table paste',
                                        'toolbar' => 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | fullscreen',
                                        'paste_data_images' => true,
                                        'images_upload_handler' => '((blobInfo, progress) => new Promise((resolve, reject) => {
                                            const file = blobInfo.blob();

                                            const uploadFile = (fileToUpload, fileName) => {
                                                const xhr = new XMLHttpRequest();
                                                xhr.open("POST", "/tiny-editor/upload");
                                                xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector(`meta[name="csrf-token"]`).content);

                                                xhr.upload.onprogress = (e) => {
                                                    progress(e.loaded / e.total * 100);
                                                };

                                                xhr.onload = () => {
                                                    if (xhr.status < 200 || xhr.status >= 300) {
                                                        return reject("HTTP Error: " + xhr.status);
                                                    }
                                                    const json = JSON.parse(xhr.responseText);
                                                    if (!json || typeof json.location != "string") {
                                                        return reject("Invalid JSON: " + xhr.responseText);
                                                    }
                                                    resolve(json.location);
                                                };

                                                xhr.onerror = () => {
                                                    reject("Image upload failed due to a network error.");
                                                };

                                                const formData = new FormData();
                                                formData.append("file", fileToUpload, fileName);
                                                xhr.send(formData);
                                            };

                                            if (file.type.startsWith("image/") && file.size > 700 * 1024) {
                                                const reader = new FileReader();
                                                reader.onload = function(e) {
                                                    const img = new Image();
                                                    img.onload = function() {
                                                        const canvas = document.createElement("canvas");
                                                        const ctx = canvas.getContext("2d");
                                                        const maxWidth = 1200;
                                                        const quality = 0.60;

                                                        let width = img.width;
                                                        let height = img.height;

                                                        if (width > maxWidth) {
                                                            height *= maxWidth / width;
                                                            width = maxWidth;
                                                        }

                                                        canvas.width = width;
                                                        canvas.height = height;
                                                        ctx.drawImage(img, 0, 0, width, height);

                                                        canvas.toBlob(function(resizedBlob) {
                                                            uploadFile(resizedBlob, blobInfo.filename());
                                                        }, "image/jpeg", quality);
                                                    };
                                                    img.src = e.target.result;
                                                };
                                                reader.readAsDataURL(file);
                                            } else {
                                                uploadFile(file, blobInfo.filename());
                                            }
                                        }))',
                                    ]),
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
                                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $get) {
                                        // Define the target path
                                        $fileName = $file->hashName();
                                        $directory = 'blog-images';
                                        $path = $directory . '/' . $fileName;

                                        // Check file size (700KB = 700 * 1024 bytes)
                                        if ($file->getSize() > 700 * 1024) {
                                            // If > 700KB, resize and compress
                                            $image = Image::make($file->getRealPath())
                                                ->resize(1200, 675, function ($constraint) {
                                                    $constraint->aspectRatio();
                                                    $constraint->upsize();
                                                })
                                                ->quality(60);

                                            // Save the processed image to the final destination
                                            Storage::disk('public')->put($path, (string) $image->encode());
                                        } else {
                                            // If <= 700KB, just store the original file
                                            $file->storeAs($directory, $fileName, 'public');
                                        }

                                        // Return the path to be saved in the database
                                        return $path;
                                    }),

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
