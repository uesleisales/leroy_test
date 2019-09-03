<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Leroy_Merlin.svg/1200px-Leroy_Merlin.svg.png" width="300">

## Desafio 
Criar uma API RESTful que:

- [Receberá uma planilha de produtos ("planilha_test.xlsx") que deve ser
processada em background (queue)].
- [Ter um endpoint que informe se a planilha for processada com sucesso ou
não.].
- [Seja possível visualizar, atualizar e apagar os produtos (só é possível criar
novos produtos via planilha)].


## Tecnologias utilizadas

- Laravel 5.8 (Poderia ser utilizado o Lumen).
- Biblioteca "cyber-duck/laravel-excel" para importação e tratamento de Arquivos Excel.
- PHPUnit (Instalado nativamente no Laravel).

## Instruções para utilização da API
- Durante a submissão de um novo arquivo Excel para a API é necessário que dois serviços do laravel estejam ativos, são ele:
- [Server] (php artisan serve)
- [Jobs/Queue] (php artisan queue:work)

## Endpoints:

### Enviar um nova planilha
- localhost:8001/api/product/create
- [Requisitos] :
- Submeter um campo do tipo "File" com o nome de "planilha".

### Lista de produtos (GET)
- localhost:8001/api/products
### Quantidade de produtos (GET)
- localhost:8001/api/products/quantity
### Categoria de produtos (GET)
- localhost:8001/api/category/:id/products
### Retornar um produto (GET)
- localhost:8001/api/product/:id 


