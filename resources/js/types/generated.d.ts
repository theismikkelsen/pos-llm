declare namespace App.Data.Locations {
export type LocationData = {
id: number;
referenceTypeId: number;
referenceId: string;
heldInventoryIsAvailable: boolean;
};
}
declare namespace App.Data.Products {
export type ProductData = {
id: number;
skuId: string;
name: string;
isLotTracked: boolean;
isSerialTracked: boolean;
};
}
declare namespace App.Domain.Inventory {
export type InventoryLocationReferenceType = 1 | 2;
export type InventoryItemDefinition = {
idAndTenant: any;
skuId: string;
name: string;
isLotTracked: boolean;
isSerialTracked: boolean;
createdAt: string;
};
}
