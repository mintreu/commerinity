<?php

namespace App\Filament\Resources\Lifecycle\StageResource\Pages;


use App\Filament\Resources\Lifecycle\StageResource;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelMoney\LaravelMoney;

class ViewStage extends ViewRecord
{

    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)->schema($this->getStageInfolistAppSchema());
    }



    public function getStageInfolistAppSchema():array
    {

        return [

            Section::make('Stage')
                ->heading(null)
                ->aside()
                ->description(fn() => $this->getStageInfolistDescription())
                ->schema([
                    KeyValueEntry::make('benefits')
                        ->keyLabel('Property name')
                        ->valueLabel('Property value'),

                    KeyValueEntry::make('accessibility')
                        ->keyLabel('Property name')
                        ->valueLabel('Property value'),
                ]),


            RepeatableEntry::make('confirmLevels')
                ->label('Available Levels')
                ->grid(4)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('name')
                        ->hiddenLabel()
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(FontWeight::SemiBold)
                        ->color('primary'),

                    Split::make([
                        TextEntry::make('team_member_limit')
                            ->size(TextEntry\TextEntrySize::Medium)
                            ->icon('heroicon-m-users')
                            ->label('Team Limit'),

                        TextEntry::make('joining_bonus')
                            ->label('Bonus')
                            ->money(LaravelMoney::defaultCurrency())
                            ->size(TextEntry\TextEntrySize::Medium),
                    ])
                ]),




        ];


    }





    private function getStageInfolistDescription(): HtmlString
    {
        return new HtmlString('
        <div class="p-2">
             <h1 class="txt-size-4xl font-semibold">' . $this->record->name . '</h1>
            <span class="text-lg ">' . LaravelMoney::format($this->record->price) . '</span>
            <p class="text-justify w-full my-2 ">' . $this->record->desc . '</p>
            <h2 class="font-semibold mt-3 txt-size-xl">Details</h2>
            <table class="mx-2 w-full p-2">
                <tbody>
                    <tr class="border-b">
                        <td class="font-semibold p-2">Max Team Members</td>
                        <td class="p-2">' . $this->record->max_team_members . '</td>
                    </tr>
                    <tr class="border-b">
                        <td class="font-semibold p-2">Total Joining Points</td>
                        <td class="p-2">' . $this->record->estimated_total_joining_points . '</td>
                    </tr>
                    <tr class="border-b">
                        <td class="font-semibold p-2">Total Purchasing Points</td>
                        <td class="p-2">' . $this->record->estimated_total_purchasing_points . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
    ');
    }



}
