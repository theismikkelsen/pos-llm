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
        Schema::rename('inventory_reservations', 'reservations_of_inventory_item');
        Schema::rename('inventory_transactions', 'transfers_of_inventory_item');
        Schema::rename('inventory_locations', 'receptacles_for_inventory_items');

        Schema::table('transfers_of_inventory_item', function (Blueprint $table): void {
            $table->renameColumn('inventory_location_id_from', 'receptacle_for_inventory_item_id_from');
            $table->renameColumn('inventory_location_id_to', 'receptacle_for_inventory_item_id_to');
        });

        Schema::table('inventory_level_projections', function (Blueprint $table): void {
            $table->renameColumn('inventory_location_id', 'receptacle_for_inventory_item_id');
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
