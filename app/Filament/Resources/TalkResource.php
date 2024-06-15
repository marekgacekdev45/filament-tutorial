<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Talk;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TalkResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TalkResource\RelationManagers;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('abstract')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('speaker_id')
                    ->relationship('speaker', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->persistFiltersInSession()
        ->filtersTriggerAction(function ($action){
            return $action->button()->label('Filters');
        })
            ->columns([
                Tables\Columns\TextInputColumn::make('title')
                    ->rules(['required'])
                    ->searchable(),
                // ->description(function (Talk $record) {
                //     return Str::of($record->abstract)->limit(40);
                // }),
                Tables\Columns\ImageColumn::make('speaker.avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=D8ABC&color=fff&name=' . urlencode($record->speaker->name);
                    }),
                Tables\Columns\TextColumn::make('speaker.name')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('new-talk')->boolean(),
                Tables\Columns\ToggleColumn::make('new-talk'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(function ($state) {
                        return $state->getColor();
                    }),
                Tables\Columns\IconColumn::make('length')
                    ->icon(function ($state) {
                        return match($state){
TalkLength::NORMAL=>'heroicon-o-bolt',
TalkLength::LONG=>'heroicon-o-megaphone',
TalkLength::SHORT=>'heroicon-o-star',
                        
                        };
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
            'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}
