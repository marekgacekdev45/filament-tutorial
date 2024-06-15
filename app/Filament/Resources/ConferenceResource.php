<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Talk;
use Filament\Tables;
use App\Actions\Star;
use App\Enums\Region;
use App\Models\Venue;
use App\Models\Speaker;
use Filament\Forms\Form;
use App\Models\Conference;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Actions\ResetStars;
use App\Enums\TalkStatus;
use Filament\Resources\Resource;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConferenceResource\Pages;
use App\Filament\Resources\ConferenceResource\RelationManagers;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\TernaryFilter;

class ConferenceResource extends Resource
{
    protected static ?string $model = Conference::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Section::make('Conference Details')
                    ->collapsible()
                    ->description('info')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->columnSpanFull()
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
                            // ->columnSpan(2)
                            ->columnSpan(['md' => 1, 'lg' => 2])
                            ->required()
                            ->disableToolbarButtons([''])
                            ->helperText('Hello'),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->native(false)
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->native(false)
                            ->required(),
                        Forms\Components\FieldSet::make('Status')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'draft' => "Draft",
                                        'published' => "Published",
                                        'archived' => "Archived",

                                    ]),


                            ]),
                    ]),
                Section::make('Location')
                    ->columns(2)
                    ->schema([

                        Forms\Components\Select::make('region')
                            ->live()
                            ->enum(Region::class)
                            ->options(Region::class),
                        Forms\Components\Select::make('venue_id')
                            ->searchable()
                            ->preload()
                            ->editOptionForm(Venue::getForm())
                            ->createOptionForm(Venue::getForm())
                            ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                return $query->where('region', $get('region'));
                            }),
                    ]),
                Forms\Components\CheckboxList::make('speakers')
                    ->relationship('speakers', 'name')
                    ->options(
                        Speaker::all()->pluck('name', 'id')
                    ),
                Actions::make([
                    Action::make('star')
                        ->visible(function (string $operation) {
                            if ($operation !== 'create') {
                                return false;
                            }
                            if (!app()->environment('local')) {
                                return false;
                            }
                            return true;
                        })
                        ->label('Fill with factory Data')
                        ->icon('heroicon-m-star')
                        ->action(function ($livewire) {
                            $data = Conference::factory()->make()->toArray();

                            $livewire->form->fill($data);
                        }),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
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
                TernaryFilter::make('new_talk'),
                //                 Tables\Filters\SelectFilter::make('speaker')->relationship('speaker', 'name')
                //                     ->multiple()
                //                     ->searchable()
                //                     ->preload()
                //                     ->query(function ($query)){
                //                         return $query->whereHas('speaker', function (Builder $query){
                // $query->whereNotNull('avatar');
                //                         });
                //                     }


            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->slideOver(),
                    Tables\Actions\Action::make('aprove')
                        ->visible(function ($record) {
                            return $record->status === (TalkStatus::APPROVED);
                        })
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Talk $record) {
                            $record->approve();
                        })->after(function () {
                            Notification::make()->success()->title('This talk was approved')

                                ->duration(3000)
                                ->body('The speaker has been notified')
                                ->send();
                        }),
                    Tables\Actions\EditAction::make()
                        ->slideOver(),
                    Tables\Actions\Action::make('aprove')
                        ->icon('heroicon-o-check-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Talk $record) {
                            $record->approve();
                        })->after(function () {
                            Notification::make()->success()->title('This talk was approved')

                                ->duration(3000)
                                ->body('The speaker has been notified')
                                ->send();
                        }),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->action(function (Collection $records) {
                            $records->each->approve();
                        })
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->tooltip('This will export all visible records')
                    ->action(function () {
                        echo $livewire->getFilteredTableQuery();
                    })
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
            // 'edit' => Pages\EditConference::route('/{record}/edit'),
        ];
    }
}
