<?php

namespace App\Filament\Resources\JobApplications\Pages;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Imports\BulkJobApplicationExcelImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListJobApplications extends ListRecords
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \EightyNine\ExcelImport\ExcelImportAction::make()
                ->slideOver()
                ->color("warning")
                ->use(BulkJobApplicationExcelImport::class)
                ->sampleExcel(
                    sampleData: [
                        [
                            // USER
                            'name' => 'John Doe',
                            'email' => 'john@example.com',
                            'mobile' => '9876543210',
                            'gender' => 'male',
                            'dob' => '1998-05-21',

                            // JOB APPLICATION
                            'recruitment_id' => 101,
                            'applicant_type' => 'external',
                            'applicant_id' => 'APP-0001',
                            'guardian_name' => 'Robert Doe',
                            'educations' => 'B.Tech, M.Tech',
                            'skills' => 'Laravel,Vue,MySQL',
                            'experiences' => '3 years backend development',
                            'reference_name' => 'Jane Smith',
                            'reference_contact' => '9998887776',
                            'is_paid' => 1,
                            'amount' => 500,
                            'transaction_id' => 'TXN123456',

                            // ADDRESS
                            'address_title' => 'Home',
                            'address_person_name' => 'John Doe',
                            'address_person_email' => 'john@example.com',
                            'address_person_mobile' => '9876543210',
                            'alternate_contact' => '9123456789',
                            'address_type' => 'permanent',
                            'address_1' => 'Street 12, ABC Nagar',
                            'address_2' => 'Near City Mall',
                            'landmark' => 'Metro Station',
                            'city' => 'Delhi',
                            'postal_code' => '110001',
                            'block_id' => 25,
                            'state_code' => 'DL',
                            'country_code' => 'IN',
                            'latitude' => '28.6139',
                            'longitude' => '77.2090',
                            'is_default' => 1,
                            'priority' => 1,
                            'pickup_location' => 0,
                        ],
                    ],
                    fileName: 'job_application_import_sample.xlsx',
                    sampleButtonLabel: 'Download Sample Excel',
                    customiseActionUsing: fn (Action $action) => $action
                        ->color('secondary')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->requiresConfirmation(),
                )
                ->beforeImport(function ($data, $livewire, $excelImportAction) {
                    // Perform actions before import
                    dd('before import',$data,$excelImportAction,$livewire);
                })
                ->afterImport(function ($data, $livewire, $excelImportAction) {
                    // Perform actions after import
                    dd('after import',$data,$excelImportAction,$livewire);
                }),
            CreateAction::make(),
        ];
    }
}
