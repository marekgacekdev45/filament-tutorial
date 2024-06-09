<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Region;
use App\Models\Venue;
use Filament\Forms\Form;
use App\Models\Conference;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConferenceResource\Pages;
use App\Filament\Resources\ConferenceResource\RelationManagers;
use App\Models\Speaker;
use Filament\Forms\Components\CheckboxList;

class ConferenceResource extends Resource
{
    protected static ?string $model = Conference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Conference Name')
                    ->required()
                    ->helperText('The name of the conference')
                    ->hint('Here is the hint')
                    ->hintIcon('heroicon-o-rectangle-stack')
                    ->markAsRequired(false)
                    ->default('My Conference')
                    ->maxLength(60),

                //webiste
                // ->url()
                // ->prefix('https://')
                // ->prefixIcon('heroicon-o-globe-alt'),
                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->disableToolbarButtons([''])
                    ->helperText('Hello'),
                Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'draft'=>"Draft",
                        'published'=>"Published",
                        'archived'=>"Archived",

                    ]),
                Forms\Components\Select::make('region')
                ->live()
                    ->enum(Region::class)
                    ->options(Region::class),
                Forms\Components\Select::make('venue_id')
                ->searchable()
                ->preload()
                ->editOptionForm(Venue::getForm())
                ->createOptionForm(Venue::getForm())
                    ->relationship('venue', 'name', modifyQueryUsing:function(Builder $query, Forms\Get $get){
                        return $query->where('region', $get('region'));
                    }),
                    Forms\Components\CheckboxList::make('speakers')
                    ->relationship('speakers', 'name')
                    ->options(
                        Speaker::all()->pluck('name','id')
                    ),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->searchable(),
                Tables\Columns\TextColumn::make('venue.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListConferences::route('/'),
            'create' => Pages\CreateConference::route('/create'),
            'edit' => Pages\EditConference::route('/{record}/edit'),
        ];
    }
}
