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
        Schema::rename('inventory_item_definitions', 'inventory_items_at_sku_level');
        Schema::rename('inventory_item_instances', 'inventory_items_at_lowest_distinct_level');

        Schema::table('inventory_items_at_lowest_distinct_level', function (Blueprint $table): void {
            $table->renameColumn('inventory_item_definition_id', 'inventory_item_at_sku_level_id');
        });

        Schema::table('inventory_level_projections', function (Blueprint $table): void {
            $table->renameColumn('inventory_item_instance_id', 'inventory_item_at_lowest_distinct_level_id');
        });

        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->renameColumn('inventory_item_instance_id', 'inventory_item_at_lowest_distinct_level_id');
        });

        Schema::table('inventory_reservations', function (Blueprint $table): void {
            $table->renameColumn('inventory_item_instance_id', 'inventory_item_at_lowest_distinct_level_id');
        });

        Schema::table('inventory_reservation_projections', function (Blueprint $table): void {
            $table->renameColumn('inventory_item_instance_id', 'inventory_item_at_lowest_distinct_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \Exception('Down-method disabled');
    }
};
