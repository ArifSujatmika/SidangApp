<?php

test('guests are redirected from home to login', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
});
