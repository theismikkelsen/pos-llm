<?php

namespace App\Data\Locations;

use App\Domain\Inventory\ReceptacleForInventoryItems;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class LocationData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $referenceTypeId,
        public readonly string $referenceId,
        public readonly bool $heldInventoryIsAvailable,
    ) {
    }

    public static function fromReceptacleForInventoryItems(ReceptacleForInventoryItems $location): self
    {
        return new self(
            id: $location->idAndTenant->id,
            referenceTypeId: $location->referenceTypeId->value,
            referenceId: $location->referenceId,
            heldInventoryIsAvailable: $location->heldInventoryIsAvailable,
        );
    }
}
