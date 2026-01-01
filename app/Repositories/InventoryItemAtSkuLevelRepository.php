<?php declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Inventory\IdAndTenant;
use App\Domain\Inventory\InventoryItemAtSkuLevel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class InventoryItemAtSkuLevelRepository
{
    public function add(InventoryItemAtSkuLevel $definition): int
    {
        return DB::table('inventory_items_at_sku_level')->insertGetId(
            self::mapToPersistence($definition),
        );
    }

    public function getById(int $tenantId, int $id): InventoryItemAtSkuLevel
    {
        $dbRow = DB::table('inventory_items_at_sku_level')
            ->where([
                'tenant_id' => $tenantId,
                'id' => $id,
            ])
            ->sole();

        return self::mapToDomain($dbRow);
    }

    /**
     * @return Collection<int, InventoryItemAtSkuLevel>
     */
    public function listByTenantId(int $tenantId): Collection
    {
        return DB::table('inventory_items_at_sku_level')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(static function (object $dbRow): InventoryItemAtSkuLevel {
                return self::mapToDomain($dbRow);
            });
    }

    public function existsBySkuId(int $tenantId, string $skuId): bool
    {
        return DB::table('inventory_items_at_sku_level')
            ->where([
                'tenant_id' => $tenantId,
                'sku_id' => $skuId,
            ])
            ->exists();
    }

    private static function mapToDomain(object $dbRow): InventoryItemAtSkuLevel
    {
        return new InventoryItemAtSkuLevel(
            idAndTenant: new IdAndTenant(id: $dbRow->id, tenantId: $dbRow->tenant_id), // @phpstan-ignore property.notFound, property.notFound
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
    private static function mapToPersistence(InventoryItemAtSkuLevel $definition): array
    {
        return [
            'id' => $definition->idAndTenant->id,
            'tenant_id' => $definition->idAndTenant->tenantId,
            'sku_id' => $definition->skuId,
            'name' => $definition->name,
            'is_lot_tracked' => $definition->isLotTracked,
            'is_serial_tracked' => $definition->isSerialTracked,
            'created_at' => $definition->createdAt,
        ];
    }
}
