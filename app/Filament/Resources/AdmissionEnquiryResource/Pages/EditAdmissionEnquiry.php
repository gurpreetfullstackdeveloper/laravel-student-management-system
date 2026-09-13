<?php

namespace App\Filament\Resources\AdmissionEnquiryResource\Pages;

use App\Filament\Resources\AdmissionEnquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdmissionEnquiry extends EditRecord
{
    protected static string $resource = AdmissionEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
