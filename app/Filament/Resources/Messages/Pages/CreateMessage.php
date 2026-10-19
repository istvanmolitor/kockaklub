<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Resources\Messages\Schemas\CreateMessageForm;
use App\Mail\MessageReplyMail;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    public function form(Schema $schema): Schema
    {
        return CreateMessageForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $customer = Customer::findOrFail($data['customer_id']);

        return [
            ...$data,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'message' => null,
            'initiated_by_admin' => true,
            'replied_at' => now(),
        ];
    }

    protected function afterCreate(): void
    {
        Mail::to($this->record->email)->queue(new MessageReplyMail($this->record));
    }

    protected function getRedirectUrl(): string
    {
        return MessageResource::getUrl('view', ['record' => $this->record]);
    }
}
