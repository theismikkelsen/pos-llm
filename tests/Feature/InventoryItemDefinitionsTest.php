<?php

use App\Models\User;

test('authenticated users can visit the inventory item definitions page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('inventory-item-definitions.index'))->assertOk();
});
