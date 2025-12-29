<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemInstance;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class InventoryItemInstanceRepository
{
    public function add(InventoryItemInstance $instance): int
    {
        return DB::table('inventory_item_instances')->insertGetId(
            [
                ...self::mapToPersistence($instance),
                'time_created' => CarbonImmutable::now(),
                'time_updated' => CarbonImmutable::now(),
            ]
        );
    }

    public function getById(int $tenantId, int $id): InventoryItemInstance
    {
        $dbRow = DB::table('inventory_item_instances')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    private static function mapToDomain(object $dbRow): InventoryItemInstance
    {
        return new InventoryItemInstance(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
            inventoryItemDefinitionId: $dbRow->inventory_item_definition_id, // @phpstan-ignore property.notFound
            lotNumber: $dbRow->lot_number, // @phpstan-ignore property.notFound
            serialNumber: $dbRow->serial_number, // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, int|string|CarbonImmutable|null>
     */
    private static function mapToPersistence(InventoryItemInstance $instance): array
    {
        return [
            'id' => $instance->idAndTenant->id,
            'tenant_id' => $instance->idAndTenant->tenantId,
            'inventory_item_definition_id' => $instance->inventoryItemDefinitionId,
            'lot_number' => $instance->lotNumber,
            'serial_number' => $instance->serialNumber,
        ];
    }
}
