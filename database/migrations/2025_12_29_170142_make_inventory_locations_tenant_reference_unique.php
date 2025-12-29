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
        Schema::table('inventory_locations', function (Blueprint $table): void {
            $table->dropIndex('idx_inventory_locations_tenant_reference');
            $table->unique(
                ['tenant_id', 'reference_type_id', 'reference_id'],
                'idx_inventory_locations_tenant_reference',
            );
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
