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
        title: 'Products',
        href: '/products',
    },
    {
        title: 'New product',
        href: '/products/create',
    },
];

export default function ProductsCreate() {
    const { data, setData, post, processing, errors } = useForm({
        sku_id: '',
        name: '',
        is_lot_tracked: false,
        is_serial_tracked: false,
    });

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();
        post('/products');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New product" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-lg font-semibold">New product</h1>
                        <p className="text-sm text-muted-foreground">
                            Add a product that you can stock in the warehouse.
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href="/products">Back to products</Link>
                    </Button>
                </div>
                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle>Product details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-6" onSubmit={handleSubmit}>
                            <div className="grid gap-2">
                                <Label htmlFor="sku_id">SKU</Label>
                                <Input
                                    id="sku_id"
                                    name="sku_id"
                                    type="text"
                                    value={data.sku_id}
                                    onChange={(event) =>
                                        setData('sku_id', event.target.value)
                                    }
                                    placeholder="e.g. SKU-001"
                                    required
                                />
                                <InputError message={errors.sku_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(event) =>
                                        setData('name', event.target.value)
                                    }
                                    placeholder="e.g. Standard pallet"
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-3">
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="is_lot_tracked"
                                        checked={data.is_lot_tracked}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'is_lot_tracked',
                                                checked === true,
                                            )
                                        }
                                    />
                                    <Label htmlFor="is_lot_tracked">
                                        Lot tracked
                                    </Label>
                                </div>
                                <InputError message={errors.is_lot_tracked} />
                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="is_serial_tracked"
                                        checked={data.is_serial_tracked}
                                        onCheckedChange={(checked) =>
                                            setData(
                                                'is_serial_tracked',
                                                checked === true,
                                            )
                                        }
                                    />
                                    <Label htmlFor="is_serial_tracked">
                                        Serial tracked
                                    </Label>
                                </div>
                                <InputError
                                    message={errors.is_serial_tracked}
                                />
                            </div>

                            <div className="flex items-center gap-3">
                                <Button type="submit" disabled={processing}>
                                    Create product
                                </Button>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    asChild
                                >
                                    <Link href="/products">Cancel</Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
