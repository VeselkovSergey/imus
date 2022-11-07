<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('layout_id')->index();

            $table->decimal('latitude', 15, 12);
            $table->decimal('longitude', 15, 12);

            $table->timestamps();

            $table->foreign('layout_id')
                ->references('id')
                ->on('layouts')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points');
    }
};
