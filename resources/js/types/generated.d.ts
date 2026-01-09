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
export type ProductInventoryLevelGroupData = {
groupValue: string;
locations: Array<App.Data.Products.ProductInventoryLevelLocationData>;
};
export type ProductInventoryLevelLocationData = {
locationName: string;
quantity: number;
};
export type ProductInventoryLevelsData = {
groupLabel: string | null;
groups: Array<App.Data.Products.ProductInventoryLevelGroupData> | null;
ungroupedLocations: Array<App.Data.Products.ProductInventoryLevelLocationData> | null;
};
export type ProductOverviewData = {
id: number;
skuId: string;
name: string;
isLotTracked: boolean;
isSerialTracked: boolean;
stockQuantity: number;
};
}
declare namespace App.Domain.Inventory {
export type ReceptacleForInventoryItemsReferenceType = 1 | 2;
}
