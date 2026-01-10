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
        Schema::table('transfers_of_inventory_item', function (Blueprint $table) {
            $table->unsignedBigInteger('receptacle_for_inventory_item_id_from')->nullable()->change();
            $table->unsignedBigInteger('receptacle_for_inventory_item_id_to')->nullable()->change();
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
