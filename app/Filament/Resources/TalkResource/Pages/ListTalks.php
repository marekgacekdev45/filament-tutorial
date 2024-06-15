<?php

namespace App\Filament\Resources\TalkResource\Pages;

use App\Enums\TalkStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\TalkResource;
use Filament\Resources\Pages\ListRecords;

class ListTalks extends ListRecords
{
    protected static string $resource = TalkResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Talks'),
            'accepted' => Tab::make("Accepted")
                ->modifyQueryUsing(function ($query) {
                    return $query->where('status', TalkStatus::APPROVED);
                })
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
