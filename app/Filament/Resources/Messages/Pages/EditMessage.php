<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Messages\MessageResource;
use App\Mail\MessageReplyMail;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    private bool $shouldSendReply = false;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->shouldSendReply = filled($data['reply']) && $data['reply'] !== $this->record->reply;

        if ($this->shouldSendReply) {
            $data['replied_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->shouldSendReply) {
            Mail::to($this->record->email)->queue(new MessageReplyMail($this->record));
        }
    }
}
