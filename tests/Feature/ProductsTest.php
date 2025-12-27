<?php

use App\Domain\Inventory\InventoryItemDefinition;
use App\Models\User;
use App\Repositories\InventoryItemDefinitionRepository;
use CodeTooling\Testing\FactoryForTests;

test('authenticated users can visit the inventory item definitions page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $response = $this
        ->withoutExceptionHandling()
        ->get(route('products.index'));

    // Assert
    $response->assertOk();
});

test('authenticated users can visit an individual product page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryItemDefinitionRepository::class);

    $itemId = $repository->add(
        FactoryForTests::create(InventoryItemDefinition::class)->withArgs(name: 'Product A'),
    );

    // Act
    $response = $this
        ->withoutExceptionHandling()
        ->get("/products/$itemId");

    // Assert
    $response
        ->assertOk()
        ->assertSee('Product A');
});
