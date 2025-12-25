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
        Schema::table('inventory_item_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable();
            $table->bigInteger('quantity_allocated');
            $table->string('status', 25)->charset('ascii')->collation('ascii_bin')->change();
            $table->index(['tenant_id', 'location_id'], 'idx_item_instances_tenant_location');
            $table->index(['tenant_id', 'status'], 'idx_item_instances_tenant_status');
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
