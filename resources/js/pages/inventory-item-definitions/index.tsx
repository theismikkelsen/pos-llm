import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head } from '@inertiajs/react';

type InventoryItemDefinition = {
    id: number;
    skuId: string;
    name: string;
    isLotTracked: boolean;
    isSerialTracked: boolean;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Item definitions',
        href: '/inventory-item-definitions',
    },
];

export default function InventoryItemDefinitions({
    items,
}: {
    items: InventoryItemDefinition[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Item definitions" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-lg font-semibold">Item definitions</h1>
                </div>
                <div className="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    {items.length === 0 ? (
                        <div className="px-4 py-6 text-sm text-muted-foreground">
                            No item definitions yet.
                        </div>
                    ) : (
                        <Table>
                            <TableHeader className="bg-muted/50 text-muted-foreground">
                                <TableRow className="border-sidebar-border/70 dark:border-sidebar-border">
                                    <TableHead className="px-4 py-2">
                                        SKU
                                    </TableHead>
                                    <TableHead className="px-4 py-2">
                                        Name
                                    </TableHead>
                                    <TableHead className="px-4 py-2">
                                        Lot tracked
                                    </TableHead>
                                    <TableHead className="px-4 py-2">
                                        Serial tracked
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow
                                        key={item.id}
                                        className="border-sidebar-border/70 dark:border-sidebar-border"
                                    >
                                        <TableCell className="px-4 py-2">
                                            {item.skuId}
                                        </TableCell>
                                        <TableCell className="px-4 py-2">
                                            {item.name}
                                        </TableCell>
                                        <TableCell className="px-4 py-2">
                                            {item.isLotTracked ? 'Yes' : 'No'}
                                        </TableCell>
                                        <TableCell className="px-4 py-2">
                                            {item.isSerialTracked
                                                ? 'Yes'
                                                : 'No'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
