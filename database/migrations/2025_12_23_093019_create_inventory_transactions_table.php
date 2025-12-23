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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('inventory_item_instance_id');
            $table->string('type');
            $table->bigInteger('quantity');
            $table->string('from_lpn')->nullable();
            $table->string('to_lpn')->nullable();
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->index(['tenant_id', 'inventory_item_instance_id'], 'idx_inv_tx_tenant_instance');
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
