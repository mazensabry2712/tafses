<?php

test('the application root redirects to the dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});
