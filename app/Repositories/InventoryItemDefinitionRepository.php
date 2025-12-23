<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\InventoryItemDefinition;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InventoryItemDefinitionRepository
{
    public function add(InventoryItemDefinition $definition): int
    {
        return DB::table('inventory_item_definitions')->insertGetId(
            self::mapToPersistence($definition),
        );
    }

    public function getById(int $tenantId, int $id): InventoryItemDefinition
    {
        $dbRow = DB::table('inventory_item_definitions')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    /**
     * @return Collection<int, InventoryItemDefinition>
     */
    public function listByTenantId(int $tenantId): Collection
    {
        return DB::table('inventory_item_definitions')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(static function (object $dbRow): InventoryItemDefinition {
                return self::mapToDomain($dbRow);
            });
    }

    private static function mapToDomain(object $dbRow): InventoryItemDefinition
    {
        return new InventoryItemDefinition(
            id: $dbRow->id, // @phpstan-ignore property.notFound
            tenantId: $dbRow->tenant_id, // @phpstan-ignore property.notFound
            skuId: $dbRow->sku_id, // @phpstan-ignore property.notFound
            name: $dbRow->name, // @phpstan-ignore property.notFound
            isLotTracked: (bool) $dbRow->is_lot_tracked, // @phpstan-ignore property.notFound
            isSerialTracked: (bool) $dbRow->is_serial_tracked, // @phpstan-ignore property.notFound
            createdAt: $dbRow->created_at ? CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dbRow->created_at) : NULL, // @phpstan-ignore property.notFound
        );
    }

    /**
     * @return array<string, bool|int|string|CarbonImmutable>
     */
    private static function mapToPersistence(InventoryItemDefinition $definition): array
    {
        return [
            'tenant_id' => $definition->tenantId,
            'sku_id' => $definition->skuId,
            'name' => $definition->name,
            'is_lot_tracked' => $definition->isLotTracked,
            'is_serial_tracked' => $definition->isSerialTracked,
            'created_at' => $definition->createdAt,
        ];
    }
}
