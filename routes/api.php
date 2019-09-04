<?php



#-------------------------------------------------
# Rota responsável por exibir a lista de produtos
Route::get('products', 'api\ProductController@index');
#-------------------------------------------------

#-----------------------------------------------------------------
# Rota responsável por exibir a quantidade de produtos
Route::get('products/quantity', 'api\ProductController@quantity'); 
#-----------------------------------------------------------------

#------------------------------------------------------------------------------
# Rota responsável por exibir a quantidade de produtos de determinada categoria
Route::get('category/{id}/products', 'api\ProductController@catProducts');
#------------------------------------------------------------------------------

#---------------------------------------------------------
# Rota responsável por exibir um produto específico
Route::get('product/{id}', 'api\ProductController@show');
#---------------------------------------------------------

#-----------------------------------------------------------------
# Rota responsável por criar um novo produto (Através da planilha) 
Route::post('product/create', 'api\ProductController@store');
#-----------------------------------------------------------------

#------------------------------------------------------------------
# Rota responśavel por atualizar um produto
Route::post('product/update/{id}', 'api\ProductController@update');
#------------------------------------------------------------------

#-------------------------------------------------------------------
# Rota responśavel por remover um produto
Route::get('product/delete/{id}', 'api\ProductController@destroy');
#-------------------------------------------------------------------

#------------------------------------------------------------------------
# Rota responsável por verificar se a planilha foi processada com sucesso
Route::get('spreadsheet/verify/{cdPlan}', 'api\ProductController@spreadsheetVerify');
#------------------------------------------------------------------------

