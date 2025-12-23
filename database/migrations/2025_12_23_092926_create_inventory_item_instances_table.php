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
        Schema::create('inventory_item_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('inventory_item_definition_id');
            $table->string('lot_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->bigInteger('quantity');
            $table->unsignedBigInteger('container_id')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->string('status');
            $table->index(['tenant_id', 'inventory_item_definition_id'], 'idx_item_instances_tenant_definition');
            $table->timestamps();
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
