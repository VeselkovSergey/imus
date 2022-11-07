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
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('startPointId')
                ->index();
            $table->unsignedBigInteger('endPointId')
                ->index();
            $table->timestamps();

            $table->unique(['startPointId', 'endPointId']);

            $table->foreign('startPointId')
                ->references('id')
                ->on('points')
                ->cascadeOnDelete();

            $table->foreign('endPointId')
                ->references('id')
                ->on('points')
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
        Schema::dropIfExists('lines');
    }
};
