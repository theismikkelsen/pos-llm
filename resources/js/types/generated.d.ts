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
export type InventoryItemDefinition = {
idAndTenant: any;
skuId: string;
name: string;
isLotTracked: boolean;
isSerialTracked: boolean;
createdAt: string;
};
}
