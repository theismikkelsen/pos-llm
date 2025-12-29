import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import type { LocationData } from '@/types/generated';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Locations',
        href: '/locations',
    },
];

export default function LocationsIndex({
    locations,
}: {
    locations: LocationData[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Locations" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-lg font-semibold">Locations</h1>
                    <Button asChild>
                        <Link href="/locations/create">New location</Link>
                    </Button>
                </div>
                <div className="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    {locations.length === 0 ? (
                        <div className="px-4 py-6 text-sm text-muted-foreground">
                            No locations yet.
                        </div>
                    ) : (
                        <Table>
                            <TableHeader className="bg-muted/50 text-muted-foreground">
                                <TableRow className="border-sidebar-border/70 dark:border-sidebar-border">
                                    <TableHead className="px-4 py-2">
                                        Reference id
                                    </TableHead>
                                    <TableHead className="px-4 py-2">
                                        Availability
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {locations.map((location) => (
                                    <TableRow
                                        key={location.id}
                                        className="border-sidebar-border/70 dark:border-sidebar-border"
                                    >
                                        <TableCell className="px-4 py-2">
                                            {location.referenceId}
                                        </TableCell>
                                        <TableCell className="px-4 py-2">
                                            {location.heldInventoryIsAvailable
                                                ? 'Available'
                                                : 'Unavailable'}
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
