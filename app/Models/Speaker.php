<?php

namespace App\Models;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;

class Speaker extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'qualifications'=>'array'
    ];

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }

    public static function getForm() :array{
      return  [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
                FileUpload::make('avatar')
                ->imageEditor()
                ->avatar()
                ->image()
                ->maxSize(1024 * 1024 *10),
            TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
            Textarea::make('bio')
                
                ->columnSpanFull(),
            TextInput::make('twitter_handle')
               
                ->maxLength(255),
                CheckboxList::make('qualifications')
                ->columnSpanFull()
                ->bulkToggleable()
                ->searchable()
                ->options([
                    'business-leader' => 'Business Leader',
                    'charisma' => 'Charismatic Speaker',
                    'first-time' => 'First Time Speaker',
                    'hometown-hero' => 'Hometown Hero',
                    'humanitarian' => 'Works in Humanitarian Field',
                    'laracasts-contributor' => 'Laracasts Contributor',
                    'twitter-influencer' => 'Large Twitter Following',
                    'youtube-influencer' => 'Large YouTube Following',
                    'open-source' => 'Open Source Creator / Maintainer',
                    'unique-perspective' => 'Unique Perspective'
                ])
                ->descriptions([
                    'business-leader'=>'Nice long description',
                    'charisma'=>'Descriptiob',
                ])
                ->columns(3)
        ];
    }
}
