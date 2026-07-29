<?php

use App\Models\User;

test('admin can access manage users screen', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'username' => 'adminuser',
        'email' => 'adminuser@example.com',
    ]);

    $this->actingAs($admin);

    $response = $this->get(route('admin.users.index'));

    $response->assertOk();
});
