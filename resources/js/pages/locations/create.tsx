import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Locations',
        href: '/locations',
    },
    {
        title: 'New location',
        href: '/locations/create',
    },
];

export default function LocationsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        reference_id: '',
        held_inventory_is_available: false,
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        post('/locations');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New location" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-lg font-semibold">New location</h1>
                        <p className="text-sm text-muted-foreground">
                            Add a new location for storing inventory.
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href="/locations">Back to locations</Link>
                    </Button>
                </div>
                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle>Location details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div className="grid gap-2">
                                <Label htmlFor="reference_id">
                                    Reference id
                                </Label>
                                <Input
                                    id="reference_id"
                                    name="reference_id"
                                    type="text"
                                    value={data.reference_id}
                                    onChange={(event) =>
                                        setData(
                                            'reference_id',
                                            event.target.value,
                                        )
                                    }
                                    placeholder="e.g. A-1001"
                                    required
                                />
                                <InputError message={errors.reference_id} />
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="held_inventory_is_available"
                                    checked={data.held_inventory_is_available}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'held_inventory_is_available',
                                            checked === true,
                                        )
                                    }
                                />
                                <Label htmlFor="held_inventory_is_available">
                                    Held inventory is available
                                </Label>
                                <InputError
                                    message={
                                        errors.held_inventory_is_available
                                    }
                                />
                            </div>

                            <div className="flex items-center gap-3">
                                <Button type="submit" disabled={processing}>
                                    Create location
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    asChild
                                >
                                    <Link href="/locations">Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
