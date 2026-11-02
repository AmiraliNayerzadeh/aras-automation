<?php

it('sends the home page to the dashboard', function () {
    $this->get('/')->assertRedirect(route('dashboard'));
});
