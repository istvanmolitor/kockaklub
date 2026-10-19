<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Messages\MessageResource;
use App\Mail\MessageReplyMail;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class ViewMessage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MessageResource::class;

    protected string $view = 'filament.resources.messages.pages.view-message';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return 'Üzenet – '.$this->record->name;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('reply')
                    ->label('Válasz')
                    ->rows(4)
                    ->required(),
            ])
            ->statePath('data');
    }

    /**
     * @return Collection<int, Message>
     */
    public function getHistory(): Collection
    {
        return $this->record->customer
            ->messages()
            ->orderBy('created_at')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewCustomer')
                ->label('Ügyfél megtekintése')
                ->url(fn () => CustomerResource::getUrl('view', ['record' => $this->record->customer])),
        ];
    }

    public function sendReply(): void
    {
        $state = $this->form->getState();

        $this->record->update([
            'reply' => $state['reply'],
            'replied_at' => now(),
        ]);

        Mail::to($this->record->email)->queue(new MessageReplyMail($this->record));

        Notification::make()
            ->title('Válasz elküldve')
            ->success()
            ->send();

        $this->record->refresh();
        $this->form->fill();
    }
}
