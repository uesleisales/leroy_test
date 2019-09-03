<?php

namespace App\Http\Controllers\api;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Importer;
use App\Jobs\SendExcelFile;


class ProductController extends Controller
{
    

    #---------------------------------------------
    # Retorna a lista de todos os produtos cadastrados
    public function index()
    {   

        return response()->json(Product::all(),200);
    }


    #-------------------------------------
    # Retorna a quantidade de produtos
    public function quantity()
    {   
        $c_prd = Product::all()->count();
        return response()->json($c_prd,200);
    }

    #-----------------------------------------------------
    # Retorna a lista de produtos de determinada categoria
    public function catProducts($id)
    {   
        $products = Product::where('category','=',$id)->get();

        #-----------------------------------
        # Verifica se existem produtos
        if(!$products->isEmpty()){
            return response()->json($products,200);
        }else{
            return response()->json("Nenhum produto cadastrado nessa categoria",404);
        }
    }
    

    #----------------------------------------
    # Carrega os dados do Arq Excel para o BD
    public function store(Request $request)
    {   

         #---------------------------------------------
         # Faz a validação dos dados
         $v = Validator::make($request->all(), [
            'planilha' => 'required',
         ]);

         if ($v->fails()) {
                return response()->json($v->errors(),202);
         }
         #---------------------------------------------

        
        #-----------------------------------------------
        # Carregamento do arquivo Excel
        $path = $request->file('planilha')->getRealPath();
        $excel = Importer::make('Excel');
        $excel->load($path);
        $collection = $excel->getCollection();
        #-----------------------------------------------

        #-----------------------------------------------
        # Verifica a quantidade de registros (Excluindo título e campo categoria)
        if($collection->count() - 2 > 0){

            $headers = $collection[1];
            $category = $collection[0][1];

            #-----------------------------------
            # Remove a categoria e os títulos para inserção no banco de dados
            unset($collection[0],$collection[1]);

            $cdPlan = $this->gera_cdPlan(); //Gera um código único para cada planilha 
            $c = 0;
            foreach ($collection as $c) {


                $product = Product::create(['lm' => $c[0],
                          'name' => $c[1], 
                          'free_shipping' => $c[2],
                          'description' => $c[3],
                          'price' => $c[4],
                          'category' => $category,
                          'cdPlan' => $cdPlan]
                         );

                SendExcelFile::dispatch($product);
                $c++;

            }
            
            

            if($product){
                return response()->json(
                    [
                        'cdPlan' => $cdPlan,
                        'Mensagem' => count($c)." produtos cadastrado(s) com sucesso",
                    ]
                );

            }else{
                return response()->json("Falha ao adicionar os produtos");
            }


        }else{
            return response()->json('Nenhum registro para adicionar',200);
        }

    }

    
    #----------------------------------------
    # Faz a busca de um produto através do id

    public function show($id)
    {   
        $product = Product::find($id);

        #---------------------------------------------
        # Retorna se o produto está cadastrado

        if(empty($product)){
            return response()->json("Nenhum produto encontrado",404);
        }else{
            return response()->json($product,200);
        }

        #----------------------------------------------

    }
    #------------------------------------------


   
    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }


    #-----------------------------------------------------
    # Função para gerar código de planilha
    private function gera_cdPlan() {
        # +++++++++++++++
        # Criar código de 11 dígitos
        $cdPlan = rand(1,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        # +++++++++++++++
        if(strlen($cdPlan) == 11){
            $cdPlan = md5(uniqid(rand(), true));
            # +++++++++++++++
            # Consulta código gerado
            $listCod = Product::select('cdPlan')->where('cdPlan', '=', $cdPlan)->count();

            if($listCod > 0){
                $this->gera_cdPlan();
            }else {
                return $cdPlan;
            }
        }else{
            $this->gera_cdPlan();
        }
        exit;
    }
}
