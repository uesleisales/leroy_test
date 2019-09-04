<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ProductTest extends TestCase
{
    
	use RefreshDatabase;

	#---------------------------------------------------------
	# Teste para verificar se um produto está sendo cadastrado
    public function testeCreateProduct(){
        $this->createProductAux();
	    $this->assertDatabaseHas('products',['cdPlan'=> 12345]);
    }
    #---------------------------------------------------------


    #-----------------------------------------------
    # Teste para verificar o funcionamento das rotas
    public function testRoutes()
    {
        $appURL = env('APP_URL');

        $urls = [
            '/api/products',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            if((int)$response->status() !== 200){
                $this->assertTrue(false);
            } else {
                $this->assertTrue(true);
            }
         
        }

    }
    #------------------------------------------------


    #-------------------------------------------------------------
    # Verifica se a rota 'api/products/quantity' retorna um número
    public function testVerifyRouteQuantity(){

    	$response = $this->get('api/products/quantity');
    	$quantity = $response->original['response'];
    	if(is_integer($quantity)){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }
    #-------------------------------------------------------------

    #----------------------------------------------------------------------
    # Verifica se a rota que retorna um product específico está funcionando 
    public function testVerifyReturnRouteProduct(){

        #----------------------------------------------
        # Primeiramente adicionamos um usuário no banco
        $product = $this->createProductAux();
        #---------------------------------------------

        //Capturamos o id do Produto criado
        $product_id = $product->id;        

        $response = $this->get('api/product/'.$product_id);

        if((int)$response->status() == 200){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }
    #-----------------------------------------------------------------------

    public function testeProductUpdate(){
        #----------------------------------------------
        # Primeiramente adicionamos um Produto no banco
        $product = $this->createProductAux();
        #---------------------------------------------

        $id_product = $product->id;

        #------------------------------------------------
        # Novos dados que serão utilizados para atualizar
        $data = [
            'lm'            => 3,
            'name'          => 'Produto de teste2 - Atulizado',
            'free_shipping' => 0,
            'description'   => 'Descrição de teste2 - Atualizado',
            'category'      => 2000,
            'price'         => 100.30,
            'cdPlan'        => 111111111,
        ];

        $response = $this->post('/api/product/update/'.$id_product, $data);

        if((int)$response->status() == 200){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }

    public function testProductDelete(){
        $product = $this->createProductAux();
        $response = $this->get('api/product/delete/'.$product->id);

        if((int)$response->status() == 200){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }

    public function testProductCategory(){
        $product = $this->createProductAux();
        $response = $this->get('api/product/delete/'.$product->id);

        if((int)$response->status() == 200){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }

    #----------------------------------
    # Função auxiliar para criação de Produtos (Poderia utilizar o Faker do laravel)
    public function createProductAux(){
        $product = \App\Models\Product::create([
            'lm'            => 2,
            'name'          => 'Produto de teste2',
            'free_shipping' => 0,
            'description'   => 'Descrição de teste2',
            'category'      => 1110,
            'price'         => 123.45,
            'cdPlan'        => 12345,
        ]);

        return $product;
    }


   
}
