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
        Schema::table('inventory_item_instances', function (Blueprint $table): void {
            $table->index(
                ['tenant_id', 'inventory_item_definition_id', 'lot_number'],
                'idx_item_instances_tenant_definition_lot',
            );
            $table->unique(
                ['tenant_id', 'inventory_item_definition_id', 'serial_number'],
                'uniq_item_instances_tenant_definition_serial',
            );
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
