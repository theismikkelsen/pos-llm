<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_item_definitions', function (Blueprint $table) {
            $table->unique(['tenant_id', 'sku_id'], 'uniq_item_definitions_tenant_sku');
        });

        Schema::table('inventory_item_instances', function (Blueprint $table) {
            $table->index(['tenant_id', 'container_id'], 'idx_item_instances_tenant_container');
            $table->index(['tenant_id', 'serial_number'], 'idx_item_instances_tenant_serial');
            $table->index(['tenant_id', 'lot_number'], 'idx_item_instances_tenant_lot');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'created_at'], 'idx_inv_tx_tenant_created_at');
            $table->index(['tenant_id', 'type'], 'idx_inv_tx_tenant_type');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->unique(['tenant_id', 'code'], 'uniq_locations_tenant_code');
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->unique(['tenant_id', 'lpn_code'], 'uniq_containers_tenant_lpn');
            $table->index(['tenant_id', 'location_id'], 'idx_containers_tenant_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \Exception('Down-method disabled.');
    }
};
