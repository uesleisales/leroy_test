<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('lm');
            $table->string('cdPlan',32); /*Código único da planilha*/
            $table->string('name',255); 
            $table->integer('free_shipping');
            $table->text('description');
            $table->integer('category');
            $table->float('price');
            $table->timestamps();
        });


        /**
        * Não adicionei os relacionamentos pois não foi solicitado a criação de outras tabelas
        * 
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
