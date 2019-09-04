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

	     \App\Models\Product::create([
	    	'lm'            => 1,
	        'name'          => 'Produto de teste1',
	        'free_shipping' => 0,
	        'description'   => 'Descrição de teste',
	        'category'      => 1110,
	        'price'         => 123.45,
	        'cdPlan'        => 12345,
	    ]);

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

        echo  PHP_EOL;

        foreach ($urls as $url) {
            $response = $this->get($url);
            if((int)$response->status() !== 200){
                echo  $appURL . $url . ' (FAILED) did not return a 200.';
                $this->assertTrue(false);
            } else {
                echo $appURL . $url . ' (success ?)';
                $this->assertTrue(true);
            }
            echo  PHP_EOL;
        }

    }
    #------------------------------------------------


    #-------------------------------------------------------------
    # Verifica se a rota 'api/products/quantity' retorna um número
    public function testVerifyRouteQuantity(){
    	$response = $this->get('api/products/quantity');
    	$quantity = $response->original['data'];
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
        $user = \App\Models\Product::create([
            'lm'            => 2,
            'name'          => 'Produto de teste2',
            'free_shipping' => 0,
            'description'   => 'Descrição de teste2',
            'category'      => 1110,
            'price'         => 123.45,
            'cdPlan'        => 12345,
        ]);
        #---------------------------------------------

        //Capturamos o id do usuário criado
        $user_id = $user->id;        

        $response = $this->get('api/product/'.$user_id);
        
        if((int)$response->status() == 200){
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }
    #-----------------------------------------------------------------------




   
}
