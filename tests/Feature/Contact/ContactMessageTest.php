<?php

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Mail\MessageReplyMail;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('creates a new customer and links the message when the email is unknown', function () {
    $response = $this->post('/kapcsolat', [
        'name' => 'Vendég Érdeklődő',
        'email' => 'erdeklodo@example.com',
        'phone' => '+36301234567',
        'message' => 'Van kapható kocka a boltban?',
    ]);

    $response->assertRedirect(route('contact.create'));

    $customer = Customer::where('email', 'erdeklodo@example.com')->first();

    expect($customer)->not->toBeNull();

    $message = Message::first();

    expect($message)->not->toBeNull()
        ->and($message->customer_id)->toBe($customer->id)
        ->and($message->message)->toBe('Van kapható kocka a boltban?');
});

it('links the message to the existing customer instead of creating a duplicate', function () {
    $customer = Customer::factory()->create(['email' => 'torzsvasarlo@example.com']);

    $this->post('/kapcsolat', [
        'name' => 'Törzsvásárló',
        'email' => 'torzsvasarlo@example.com',
        'message' => 'Mikor várható utánrendelés?',
    ]);

    expect(Customer::where('email', 'torzsvasarlo@example.com')->count())->toBe(1);

    $message = Message::first();

    expect($message->customer_id)->toBe($customer->id);
});

it('links the message to the logged in customer', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);
    $customer = Customer::factory()->create(['user_id' => $user->id, 'email' => $user->email]);

    $this->actingAs($user)->post('/kapcsolat', [
        'name' => $customer->name,
        'email' => $customer->email,
        'message' => 'Szeretnék érdeklődni.',
    ]);

    $message = Message::first();

    expect($message->customer_id)->toBe($customer->id);
});

it('allows an admin to reply to a message, which queues an email and stamps replied_at', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    $message = Message::factory()->create();

    $this->actingAs($admin);

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->fillForm(['reply' => 'Köszönjük, hamarosan utánrendelünk!'])
        ->call('save')
        ->assertHasNoFormErrors();

    $message->refresh();

    expect($message->reply)->toBe('Köszönjük, hamarosan utánrendelünk!')
        ->and($message->replied_at)->not->toBeNull();

    Mail::assertQueued(MessageReplyMail::class, fn ($mail) => $mail->message->is($message));
});
