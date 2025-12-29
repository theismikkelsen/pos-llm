<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryLocation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class InventoryLocationRepository
{
    public function add(InventoryLocation $location): int
    {
        return DB::table('inventory_locations')->insertGetId(
            [
                ...self::mapToPersistence($location),
                'time_created' => CarbonImmutable::now(),
                'time_updated' => CarbonImmutable::now(),
            ]
        );
    }

    public function getById(int $tenantId, int $id): InventoryLocation
    {
        $dbRow = DB::table('inventory_locations')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    private static function mapToDomain(object $dbRow): InventoryLocation
    {
        return new InventoryLocation(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            heldInventoryIsAvailable: (bool) $dbRow->held_inventory_is_available, // @phpstan-ignore property.notFound
            referenceTypeId: $dbRow->reference_type_id, // @phpstan-ignore property.notFound
            referenceId: $dbRow->reference_id, // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, bool|int|CarbonImmutable>
     */
    private static function mapToPersistence(InventoryLocation $location): array
    {
        return [
            'id' => $location->idAndTenant->id,
            'tenant_id' => $location->idAndTenant->tenantId,
            'held_inventory_is_available' => $location->heldInventoryIsAvailable,
            'reference_type_id' => $location->referenceTypeId,
            'reference_id' => $location->referenceId,
        ];
    }
}
