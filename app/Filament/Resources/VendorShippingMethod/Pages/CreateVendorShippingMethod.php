<?php

namespace App\Filament\Resources\VendorShippingMethod\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\VendorShippingMethod\VendorShippingMethodResource;

class CreateVendorShippingMethod extends CreateRecord
{
    protected static string $resource = VendorShippingMethodResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If user is a vendor, automatically set the vendor_id
        if (auth()->user()->hasRole('vendor')) {
            $data['vendor_id'] = auth()->user()->id;
        }
        
        return $data;
    }
}