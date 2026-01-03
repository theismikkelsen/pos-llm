import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type {
    ProductData,
    ProductInventoryLevelLocationData,
    ProductInventoryLevelsData,
} from '@/types/generated';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head } from '@inertiajs/react';
import { Fragment } from 'react';

const orderLocationsByName = (items: ProductInventoryLevelLocationData[]) =>
    [...items].sort((left, right) =>
        left.locationName.localeCompare(right.locationName),
    );

const StockTable = ({
    locations,
}: {
    locations: ProductInventoryLevelLocationData[];
}) => (
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Location</TableHead>
                <TableHead className="text-right">Quantity</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            {orderLocationsByName(locations).map((location) => (
                <TableRow
                    key={`${location.locationName}-${location.quantity}`}
                    className="hover:bg-transparent"
                >
                    <TableCell>{location.locationName}</TableCell>
                    <TableCell className="text-right">
                        {location.quantity}
                    </TableCell>
                </TableRow>
            ))}
        </TableBody>
    </Table>
);

type GroupedStockTableProps = {
    groups: Array<{
        label: string;
        locations: ProductInventoryLevelLocationData[];
    }>;
};

const GroupedStockTable = ({ groups }: GroupedStockTableProps) => (
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Location</TableHead>
                <TableHead className="text-right">Quantity</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            {groups.map((group) => (
                <Fragment key={group.label}>
                    <TableRow
                        className="hover:bg-transparent"
                    >
                        <TableCell
                            colSpan={2}
                            className="bg-muted/40 text-xs font-semibold uppercase tracking-wide"
                        >
                            {group.label}
                        </TableCell>
                    </TableRow>
                    {orderLocationsByName(group.locations).map((location) => (
                        <TableRow
                            key={`${group.label}-${location.locationName}-${location.quantity}`}
                            className="hover:bg-transparent"
                        >
                            <TableCell>{location.locationName}</TableCell>
                            <TableCell className="text-right">
                                {location.quantity}
                            </TableCell>
                        </TableRow>
                    ))}
                </Fragment>
            ))}
        </TableBody>
    </Table>
);

export default function ProductsShow({
    item,
    inventoryLevels,
}: {
    item: ProductData;
    inventoryLevels: ProductInventoryLevelsData;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Products',
            href: '/products',
        },
        {
            title: item.name,
            href: `/products/${item.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={item.name} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>{item.name}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-3 text-sm">
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">SKU</dt>
                                <dd>{item.skuId}</dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">
                                    Lot tracked
                                </dt>
                                <dd>{item.isLotTracked ? 'Yes' : 'No'}</dd>
                            </div>
                            <div className="flex items-center justify-between">
                                <dt className="text-muted-foreground">
                                    Serial tracked
                                </dt>
                                <dd>{item.isSerialTracked ? 'Yes' : 'No'}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Stock levels</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {inventoryLevels.ungroupedLocations && (
                            <StockTable
                                locations={inventoryLevels.ungroupedLocations}
                            />
                        )}
                        {inventoryLevels.groups && inventoryLevels.groupLabel && (
                            <GroupedStockTable
                                groups={inventoryLevels.groups.map((group) => ({
                                    label: `${inventoryLevels.groupLabel} ${group.groupValue}`,
                                    locations: group.locations,
                                }))}
                            />
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
