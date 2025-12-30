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

test('authenticated users can visit the create product page', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    // Act
    $response = $this->get('/products/create');

    // Assert
    $response->assertOk();
});

test('authenticated users can create a product', function () {
    // Arrange
    $this->actingAs(User::factory()->create());

    $repository = resolve(InventoryItemDefinitionRepository::class);

    // Act
    $response = $this->post('/products', [
        'sku_id' => 'SKU-NEW-1',
        'name' => 'Product New',
        'is_lot_tracked' => true,
        'is_serial_tracked' => false,
    ]);

    // Assert
    $response->assertRedirect('/products');

    $createdProduct = $repository
        ->listByTenantId(1)
        ->first(fn (InventoryItemDefinition $product) => $product->skuId === 'SKU-NEW-1');

    expect($createdProduct)->not->toBeNull();
    expect($createdProduct?->name)->toBe('Product New');
    expect($createdProduct?->isLotTracked)->toBeTrue();
    expect($createdProduct?->isSerialTracked)->toBeFalse();
});
