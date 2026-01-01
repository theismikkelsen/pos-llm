<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtLowestDistinctLevel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class InventoryItemAtLowestDistinctLevelRepository
{
    public function add(InventoryItemAtLowestDistinctLevel $instance): int
    {
        return DB::table('inventory_items_at_lowest_distinct_level')->insertGetId(
            [
                ...self::mapToPersistence($instance),
                'time_created' => CarbonImmutable::now(),
                'time_updated' => CarbonImmutable::now(),
            ]
        );
    }

    public function getById(int $tenantId, int $id): InventoryItemAtLowestDistinctLevel
    {
        $dbRow = DB::table('inventory_items_at_lowest_distinct_level')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    private static function mapToDomain(object $dbRow): InventoryItemAtLowestDistinctLevel
    {
        return new InventoryItemAtLowestDistinctLevel(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            inventoryItemAtSkuLevelId: $dbRow->inventory_item_at_sku_level_id, // @phpstan-ignore property.notFound
            lotNumber: $dbRow->lot_number, // @phpstan-ignore property.notFound
            serialNumber: $dbRow->serial_number, // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, int|string|CarbonImmutable|null>
     */
    private static function mapToPersistence(InventoryItemAtLowestDistinctLevel $instance): array
    {
        return [
            'id' => $instance->idAndTenant->id,
            'tenant_id' => $instance->idAndTenant->tenantId,
            'inventory_item_at_sku_level_id' => $instance->inventoryItemAtSkuLevelId,
            'lot_number' => $instance->lotNumber,
            'serial_number' => $instance->serialNumber,
        ];
    }
}
