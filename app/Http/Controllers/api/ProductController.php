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
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    
    private $product;

    public function __construct(Product $product){
        $this->product = $product;
    }


    #---------------------------------------------
    # Retorna a lista de todos os produtos cadastrados
    public function index()
    {   
        return response()->json([
            'data' => Product::all()], 200
            );
    }

    #-------------------------------------
    # Retorna a quantidade de produtos
    public function quantity()
    {   
        $c_prd = Product::all()->count();
        return response()->json(['data' => $c_prd, 'message' => 'success'],200);
    }

    #-----------------------------------------------------
    # Retorna a lista de produtos de determinada categoria
    public function catProducts($id)
    {   
        $products = Product::where('category','=',$id)->get();

        #-----------------------------------
        # Verifica se existem produtos
        if(!$products->isEmpty()){
            return response()->json(['data' => $products, 'message' => 'success'],200);
        }else{
            return response()->json(['message' => "Nenhum produto cadastrado nessa categoria", 'data' => ''],404);
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
                return response()->json(['message' => $v->errors(), 'data' => ''],422);
         }
         #---------------------------------------------
        
        
        try{
            #-----------------------------------------------
            # Carregamento do arquivo Excel
            $path = $request->file('planilha')->getRealPath();
            $excel = Importer::make('Excel');
            $excel->load($path);
            $collection = $excel->getCollection();
             #-----------------------------------------------
        }catch(\Exception $e){
            return response()->json(['message' => "Falha ao abrir o arquivo", 'data' => ''], 500);
        }
        

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

            #----------------------
            # Inicia um transaction 
            DB::beginTransaction();
            #----------------------
            foreach ($collection as $c) {

                #----------------------------------------
                # Adiciona os dados da planilha no banco
                try{

                    $product = Product::create(['lm' => $c[0],
                          'name' => $c[1], 
                          'free_shipping' => $c[2],
                          'description' => $c[3],
                          'price' => $c[4],
                          'category' => $category,
                          'cdPlan' => $cdPlan]
                         );
                    #-----------------------------------------

                    #---------------------------------------
                    # Dispara o Job de envio da Planilha
                    SendExcelFile::dispatch($product);
                    #---------------------------------------

                }catch(\Exception $e){
                    #---------------------------------------------
                    # Faz um roolback das informações da transação
                    DB::rollback();
                    #---------------------------------------------
                    return response()->json(['message' => "Falha ao adicionar os produtos", 'data' => ''], 500);
                }
                $c++;
            }

            #--------------------------------------
            # Faz a persistência dos dados no banco
            DB::commit();
            #--------------------------------------
            
            #-----------------------------------------------------------------------
            # Retorno de sucesso
            return response()->json(
                    [
                        'cdPlan' => $cdPlan,
                        'message' => count($c)." produtos cadastrado(s) com sucesso",
                    ] , 200
            );
            #-----------------------------------------------------------------------

        }else{
            #------------------------------------------------------------
            # Retorno caso os dados venham em branco
            return response()->json(['message' => 'Nenhum registro para adicionar', 'data' => ''],202);
            #------------------------------------------------------------
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
            return response()->json(['message' => "Nenhum produto encontrado"], 404);
        }else{
            return response()->json(['message' => 'success' , 'data' => $product],200);
        }

        #----------------------------------------------

    }
    #------------------------------------------

   
    public function update(Request $request, $id)
    {
         #---------------------------------------------
         # Faz a validação dos dados
         $v = Validator::make($request->all(), [
            'lm'            => 'numeric|required',
            'name'          => 'required|max:255',
            'free_shipping' => 'required|integer',
            'description'   => 'required',
            'category'      => 'required|integer',
            'price'         => 'required',
         ]);

         if ($v->fails()) {
                return response()->json(['message' => $v->errors()], 422);
         }
         #---------------------------------------------

        $data = [
            'lm'            => $request->get('lm'), 
            'name'          => $request->get('name'), 
            'free_shipping' => $request->get('free_shipping'),
            'description'   => $request->get('description'),
            'category'      => $request->get('category'),
            'price'         => $request->get('price'),
        ];


        try{

            #----------------------
            # Inicia um transaction 
            DB::beginTransaction();
            #----------------------

            Product::where('id', '=',$id)->update($data);

            #--------------------------------------
            # Faz a persistência dos dados no banco
            DB::commit();
            #--------------------------------------

            #-------------------------------------------------
            # Exibe a mensagem de sucesso e retorna os dados
            return response()->json([
                'message' => "Produto atualizado com sucesso",
                'data'    => $data,
                ] , 200);
            #------------------------------------------------

        }catch(\Exception $e){
            #---------------------------------------------
            # Faz um roolback das informações da transação
            DB::rollback();
            #---------------------------------------------
            return response()->json(['message' => "Falha ao atualizar o produto", 'data' => ''], 500);
        }
    }

    #------------------------------------------
    # Método responśavel por remover um produto
    public function destroy($id)
    {
                
        if(!$product = $this->product->find($id)){
            return response()->json(['error' => 'product_not_found'] , 404);
        }

        if ( !$delete = $product->delete() ) {
            return response()->json(['error' => 'product_not_delete', 500]);
        }
        return response()->json(['response' => $delete]);
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
