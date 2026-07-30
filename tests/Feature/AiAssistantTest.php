<?php

use App\Livewire\ChatAssistant;
use App\Models\User;
use App\Services\AiAssistantService;
use Livewire\Livewire;

test('admin can access ai assistant page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.ai-assistant'))
        ->assertOk();
});

test('non-admin cannot access ai assistant page', function () {
    $dosen = User::factory()->dosen()->create();

    $this->actingAs($dosen)
        ->get(route('admin.ai-assistant'))
        ->assertForbidden();

    $mahasiswa = User::factory()->create();

    $this->actingAs($mahasiswa)
        ->get(route('admin.ai-assistant'))
        ->assertForbidden();
});

test('chat assistant component sends message and receives reply', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(AiAssistantService::class);
    $mock->shouldReceive('chat')
        ->once()
        ->with('Halo, apa kabar?', $admin->id)
        ->andReturn('Halo! Saya baik. Ada yang bisa saya bantu?');

    $this->instance(AiAssistantService::class, $mock);

    Livewire::actingAs($admin)
        ->test(ChatAssistant::class)
        ->set('newMessage', 'Halo, apa kabar?')
        ->call('sendMessage')
        ->assertSet('newMessage', '')
        ->assertHasNoErrors()
        ->assertSee('Halo, apa kabar?')
        ->assertSee('Halo! Saya baik. Ada yang bisa saya bantu?');
});

test('chat assistant validates empty message', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ChatAssistant::class)
        ->set('newMessage', '')
        ->call('sendMessage')
        ->assertHasErrors('newMessage');
});

test('chat assistant can clear history', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(AiAssistantService::class);
    $mock->shouldReceive('chat')
        ->once()
        ->andReturn('Test reply');
    $mock->shouldReceive('clearHistory')
        ->once()
        ->with($admin->id);

    $this->instance(AiAssistantService::class, $mock);

    Livewire::actingAs($admin)
        ->test(ChatAssistant::class)
        ->set('newMessage', 'Test')
        ->call('sendMessage');

    Livewire::actingAs($admin)
        ->test(ChatAssistant::class)
        ->call('clearHistory');
});

test('chat assistant persists messages in component state', function () {
    $admin = User::factory()->admin()->create();

    $mock = Mockery::mock(AiAssistantService::class);
    $mock->shouldReceive('chat')
        ->once()
        ->with('Test message', $admin->id)
        ->andReturn('Test reply');

    $this->instance(AiAssistantService::class, $mock);

    Livewire::actingAs($admin)
        ->test(ChatAssistant::class)
        ->set('newMessage', 'Test message')
        ->call('sendMessage')
        ->assertCount('messages', 2);
});
