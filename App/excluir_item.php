<?php

require_once "auth.php";
require_once "Models/connect.php";

if(!isset($_GET['id'])){

    header("Location: ../views/index_farmacia.php");
    exit;

}


$id = intval($_GET['id']);

/*
=====================================
DEFINE O INDEX DE ORIGEM
=====================================
*/

if (
    ($_SESSION['tipo_acesso'] ?? '') === 'farmacia'
) {

    $_SESSION['origem_index'] = 'farmacia';

} elseif (
    ($_SESSION['tipo_acesso'] ?? '') === 'farmacia'
) {

    $_SESSION['origem_index'] = 'farmacia';

}


$sql = "SELECT * FROM itens WHERE id_itens = ?";


$stmt = $connect->SQL->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$resultado = $stmt->get_result();


$item = $resultado->fetch_assoc();



if(!$item){

    echo "Item não encontrado";
    exit;

}


?>


<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Excluir Item | SupportLife</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<style>


body{

min-height:100vh;

display:flex;

justify-content:center;

align-items:center;

background:linear-gradient(135deg,#0d6efd,#dc3545,#8b0000);

font-family:"Segoe UI",sans-serif;

}


.card{

width:500px;

border-radius:20px;

overflow:hidden;

box-shadow:0 20px 40px rgba(0,0,0,.3);

}


.card-header{

background:#dc3545;

color:white;

text-align:center;

padding:30px;

}


.logo{

width:80px;

height:80px;

background:white;

color:#dc3545;

border-radius:50%;

display:flex;

align-items:center;

justify-content:center;

margin:auto;

font-size:40px;

}


.card-body{

padding:35px;

}


.info{

background:#f8f9fa;

padding:20px;

border-radius:10px;

}


.btn-danger{

width:100%;

height:50px;

font-weight:bold;

}



</style>


</head>



<body>


<div class="card">


<div class="card-header">


<div class="logo">

<i class="bi bi-trash3-fill"></i>

</div>


<h2>Excluir Item</h2>


<p>Esta ação removerá o registro do estoque.</p>


</div>




<div class="card-body">


<form method="POST" action="Models/salvar_exclusao.php">

    <input
        type="hidden"
        name="id_itens"
        value="<?= $item['id_itens']; ?>"
    >

    <input
        type="hidden"
        name="origem_index"
        value="<?= htmlspecialchars(
            $_SESSION['origem_index'] ?? '',
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >




<input 
type="hidden"
name="id_itens"
value="<?= $item['id_itens']; ?>">





<div class="mb-3">


<label class="form-label">

Escolha o tipo de exclusão:

</label>



<div class="form-check text-start">


<input

class="form-check-input"

type="radio"

name="tipo_exclusao"

value="unidade"

id="unidade"

checked>


<label class="form-check-label" for="unidade">

Excluir somente este item selecionado

</label>


</div>





<div class="form-check text-start mt-2">


<input

class="form-check-input"

type="radio"

name="tipo_exclusao"

value="todos"

id="todos">


<label class="form-check-label" for="todos">

Excluir todas as unidades deste lote

</label>


</div>



</div>






<div class="info">


<p>

<strong>Código:</strong>

<?= htmlspecialchars($item['codigo_item']); ?>

</p>



<p>

<strong>Nome:</strong>

<?= htmlspecialchars($item['nome_item']); ?>

</p>



<p>

<strong>Lote:</strong>

<?= htmlspecialchars($item['lote']); ?>

</p>



<p>

<strong>Marca:</strong>

<?= htmlspecialchars($item['marca_item']); ?>

</p>



<p>

<strong>Quantidade:</strong>

<?= $item['quant_itens']; ?>

</p>



<p>

<strong>Data Compra:</strong>

<?= $item['data_compra']; ?>

</p>



<p>

<strong>Data Vencimento:</strong>

<?= $item['data_vencimento']; ?>

</p>



</div>





<div class="alert alert-danger mt-3">

<i class="bi bi-exclamation-triangle-fill"></i>

Confirme sua senha para remover o item.

</div>






<label class="form-label">

Senha:

</label>



<input

type="password"

name="password"

class="form-control mb-3"

required>






<button class="btn btn-danger">


<i class="bi bi-trash3-fill"></i>

Excluir


</button>






<?php

switch ($_SESSION['origem_index'] ?? '') {

    case 'TI':

        $urlVoltar = '../views/index_farmacia.php';

        break;

    case 'farmacia':

        $urlVoltar = '../views/index_farmacia.php';

        break;

    default:

        $urlVoltar = '../login.php';

        break;

}

?>

<a
    href="<?= $urlVoltar; ?>"
    class="btn btn-secondary"
>

    <i class="bi bi-arrow-left"></i>

    Voltar

</a>



</form>


</div>


</div>


</body>

</html>