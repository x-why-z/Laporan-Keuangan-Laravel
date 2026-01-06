<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\AccountingService;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * After creating, calculate the order total and record DP if provided.
     */
    protected function afterCreate(): void
    {
        // Calculate and save the order total
        $this->record->calculateTotal();
        
        // If down payment was provided, record it via AccountingService
        $downPayment = (float) ($this->record->down_payment ?? 0);
        if ($downPayment > 0) {
            $accountingService = app(AccountingService::class);
            $accountingService->recordDownPayment($this->record, $downPayment);
        }
    }
}

