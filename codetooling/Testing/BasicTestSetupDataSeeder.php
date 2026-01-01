<?php declare(strict_types=1);

namespace CodeTooling\Testing;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use App\Repositories\InventoryItemAtSkuLevelRepository;
use App\Repositories\InventoryItemAtLowestDistinctLevelRepository;
use App\Repositories\ReceptacleForInventoryItemsRepository;
use CodeTooling\Testing\FactoryForTests;

class BasicTestSetupDataSeeder
{
    private ReceptacleForInventoryItemsRepository $locationRepository;
    private InventoryItemAtSkuLevelRepository $definitionRepository;
    private InventoryItemAtLowestDistinctLevelRepository $instanceRepository;

    private function __construct(
        private readonly int $tenantId
    ) {
        // Resolve repositories once to keep the seeding loop clean
        $this->locationRepository = resolve(ReceptacleForInventoryItemsRepository::class);
        $this->definitionRepository = resolve(InventoryItemAtSkuLevelRepository::class);
        $this->instanceRepository = resolve(InventoryItemAtLowestDistinctLevelRepository::class);
    }

    /**
     * Entry point for the fluent seeder. Sets the tenant context for all subsequent data.
     */
    public static function forTenant(int $id): self
    {
        return new self($id);
    }

    /**
     * Seeds Inventory Locations with specific, hardcoded IDs.
     *
     * @param int[] $ids List of specific IDs to assign to the locations.
     */
    public function seedLocations(array $ids): self
    {
        foreach ($ids as $id) {
            $this->locationRepository->add(
                FactoryForTests::create(ReceptacleForInventoryItems::class)->withArgs(
                    idAndTenant: new IdAndTenant(id: $id, tenantId: $this->tenantId),
                    referenceTypeId: ReceptacleForInventoryItemsReferenceType::WAREHOUSE_LOCATION,
                    referenceId: (string) $id,
                )
            );
        }

        return $this;
    }

    /**
     * Seeds Inventory Item Definitions with specific, hardcoded IDs.
     *
     * @param int[] $ids List of specific IDs to assign to the definitions.
     */
    public function seedInventoryItemAtSkuLevels(array $ids): self
    {
        foreach ($ids as $id) {
            $this->definitionRepository->add(
                FactoryForTests::create(InventoryItemAtSkuLevel::class)->withArgs(
                    idAndTenant: new IdAndTenant(id: $id, tenantId: $this->tenantId),
                    skuId: "SKU-ITEM-{$id}",
                )
            );
        }

        return $this;
    }

    /**
     * Seeds Inventory Item Instances where the Instance ID matches the Definition ID.
     *
     * Example: Passing [1] will create an Instance with ID 1, linked to Definition ID 1.
     * Note: This assumes Definition ID 1 already exists.
     *
     * @param int[] $ids List of IDs to use for both the Instance and the Parent Definition.
     */
    public function seedInventoryItemAtLowestDistinctLevelWithIdsThatMirrorTheIdOfTheirParentInventoryItemAtSkuLevel(array $ids): self
    {
        foreach ($ids as $id) {
            // VERIFICATION STEP:
            // Ensure the parent definition actually exists before trying to link an instance to it.
            // This prevents "ghost" data in tests where an instance points to a non-existent definition.
            $parentDefinition = $this->definitionRepository->getById(tenantId: $this->tenantId, id: $id);

            $this->instanceRepository->add(
                FactoryForTests::create(InventoryItemAtLowestDistinctLevel::class)->withArgs(
                    idAndTenant: new IdAndTenant(id: $id, tenantId: $this->tenantId),
                    inventoryItemAtSkuLevelId: $id, // Mirroring the ID
                )
            );
        }

        return $this;
    }
}
