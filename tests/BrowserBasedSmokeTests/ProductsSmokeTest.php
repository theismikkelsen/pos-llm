<?php

use App\Models\User;
use CodeTooling\Testing\BasicTestSetupDataSeeder;
test('products index page has no smoke', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [1]);

    // Act
    $page = visit('/products');

    // Assert
    $page->assertNoSmoke('');
    $page->assertSee('Products');
    $page->assertSee('Stock');
});

test('product create page has no smoke', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $page = visit('/products/create');

    // Assert
    $page->assertNoSmoke('');
    $page->assertSee('New product');
});

test('product show page has no smoke', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    BasicTestSetupDataSeeder::forTenant(id: 1)
        ->seedInventoryItemAtSkuLevels(ids: [1]);

    // Act
    $page = visit('/products/1');

    // Assert
    $page->assertNoSmoke('');
    $page->assertSee('Stock levels');
});
