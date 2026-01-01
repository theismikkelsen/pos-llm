<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\ReceptacleForInventoryItems;
use App\Domain\Inventory\ReceptacleForInventoryItemsReferenceType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ReceptacleForInventoryItemsRepository
{
    public function add(ReceptacleForInventoryItems $location): int
    {
        return DB::table('receptacles_for_inventory_items')->insertGetId(
            [
                ...self::mapToPersistence($location),
                'time_created' => CarbonImmutable::now(),
                'time_updated' => CarbonImmutable::now(),
            ]
        );
    }

    public function getById(int $tenantId, int $id): ReceptacleForInventoryItems
    {
        $dbRow = DB::table('receptacles_for_inventory_items')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    /**
     * @return Collection<int, ReceptacleForInventoryItems>
     */
    public function listByTenantId(int $tenantId): Collection
    {
        return DB::table('receptacles_for_inventory_items')
            ->where('tenant_id', $tenantId)
            ->orderBy('reference_type_id')
            ->orderBy('reference_id')
            ->get()
            ->map(static function (object $dbRow): ReceptacleForInventoryItems {
                return self::mapToDomain($dbRow);
            });
    }

    public function existsByReference(
        int $tenantId,
        ReceptacleForInventoryItemsReferenceType $referenceType,
        string $referenceId
    ): bool {
        return DB::table('receptacles_for_inventory_items')
            ->where([
                'tenant_id' => $tenantId,
                'reference_type_id' => $referenceType->value,
                'reference_id' => $referenceId,
            ])
            ->exists();
    }

    private static function mapToDomain(object $dbRow): ReceptacleForInventoryItems
    {
        return new ReceptacleForInventoryItems(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            heldInventoryIsAvailable: (bool) $dbRow->held_inventory_is_available, // @phpstan-ignore property.notFound
            referenceTypeId: ReceptacleForInventoryItemsReferenceType::from($dbRow->reference_type_id), // @phpstan-ignore property.notFound
            referenceId: $dbRow->reference_id, // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, bool|int|string|null|CarbonImmutable>
     */
    private static function mapToPersistence(ReceptacleForInventoryItems $location): array
    {
        return [
            'id' => $location->idAndTenant->id,
            'tenant_id' => $location->idAndTenant->tenantId,
            'held_inventory_is_available' => $location->heldInventoryIsAvailable,
            'reference_type_id' => $location->referenceTypeId->value,
            'reference_id' => $location->referenceId,
        ];
    }
}
