<?php

namespace App\Data\Locations;

use App\Domain\Inventory\InventoryLocation;
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

    public static function fromInventoryLocation(InventoryLocation $location): self
    {
        $id = $location->idAndTenant->id;

        if ($id === null) {
            throw new \RuntimeException('Location id missing.');
        }

        return new self(
            id: $id,
            referenceTypeId: $location->referenceTypeId->value,
            referenceId: $location->referenceId,
            heldInventoryIsAvailable: $location->heldInventoryIsAvailable,
        );
    }
}
