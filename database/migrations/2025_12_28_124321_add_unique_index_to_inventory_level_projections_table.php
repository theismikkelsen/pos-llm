<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_level_projections', function (Blueprint $table): void {
            $table->unique(
                ['tenant_id', 'inventory_item_instance_id', 'inventory_location_id'],
                'inventory_level_projections_tenant_item_location_unique',
            );
        });
    }

    public function down(): void
    {
        throw new \Exception('Down-method disabled');
    }
};
