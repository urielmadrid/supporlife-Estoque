<?php
require_once "auth.php";
require_once "Models/connect.php";

if (!isset($_GET['id'])) {
    header("Location: ../views/index_farmacia.php");
    exit;
}

$id = (int)$_GET['id'];

$sql = "SELECT * FROM itens WHERE id_itens = ? AND setor = 'FARMACIA'";
$stmt = $connect->SQL->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$item = $resultado->fetch_assoc();

if (!$item) {
    echo "Item não encontrado";
    exit;
}

$_SESSION['item_antigo'] = $item;
$_SESSION['origem_index'] = 'farmacia';

// CONTA QUANTIDADE DO GRUPO

$sqlQuantidade = "

SELECT COUNT(*) AS total

FROM itens

WHERE codigo_item = ?

AND lote = ?

";


$stmtQtd = $connect->SQL->prepare($sqlQuantidade);


$stmtQtd->bind_param(
"ss",
$item['codigo_item'],
$item['lote']
);


$stmtQtd->execute();


$resultQtd = $stmtQtd->get_result();


$qtd = $resultQtd->fetch_assoc();


$quantidadeTotal = $qtd['total'];



?>



<!DOCTYPE html>

<html lang="pt-BR">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Edição de Item | SupportLife</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}


body{

min-height:100vh;

display:flex;

justify-content:center;

align-items:center;

font-family:"Segoe UI",sans-serif;


background:linear-gradient(
135deg,
#0d6efd,
#1f6feb,
#ffffff,
#dc3545,
#b71c1c
);


background-size:400% 400%;

animation:gradient 15s ease infinite;

}



@keyframes gradient{

0%{
background-position:0% 50%;
}

50%{
background-position:100% 50%;
}

100%{
background-position:0% 50%;
}

}



.card-edicao{

width:500px;

max-width:95%;

background:white;

border-radius:25px;

overflow:hidden;

box-shadow:
0 25px 50px rgba(0,0,0,.3);

}



.header{

background:linear-gradient(
90deg,
#0056d6,
#dc3545
);

padding:35px;

text-align:center;

color:white;

}



.logo{

width:80px;

height:80px;

background:white;

color:#dc3545;

border-radius:50%;

display:flex;

justify-content:center;

align-items:center;

font-size:40px;

margin:auto;

}



.body-card{

padding:40px;

}



.form-label{

font-weight:600;

}



.form-control{

height:50px;

border-radius:10px;

}



.input-group-text{

background:white;

}



.btn-editar{

height:50px;

width:100%;

border:none;

border-radius:10px;

color:white;

font-weight:bold;

background:linear-gradient(
90deg,
#0056d6,
#dc3545
);

}



.voltar{

text-align:center;

margin-top:20px;

}


</style>



</head>



<body>



<div class="card-edicao">


<div class="header">


<div class="logo">

<i class="bi bi-pencil-square"></i>

</div>


<h2>
Editar Item
</h2>


<p>
Sistema de Controle de Estoque
</p>


</div>





<div class="body-card">



<form method="POST" action="Models/salvar_edicao.php">

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


<input type="hidden" name="id_itens"
value="<?= $item['id_itens']; ?>">





<label class="form-label">

Código do Item

</label>


<input 
type="text"
name="codigo_item"
class="form-control mb-3"
value="<?= htmlspecialchars($item['codigo_item']); ?>"
required>







<label class="form-label">

Nome do Item

</label>


<input 
type="text"
name="nome_item"
class="form-control mb-3"
value="<?= htmlspecialchars($item['nome_item']); ?>"
required>








<label class="form-label">

Lote / Compartimento

</label>


<input

type="text"

name="lote"

class="form-control mb-3"

value="<?= htmlspecialchars($item['lote']); ?>"

required>








<label class="form-label">

Marca

</label>


<input

type="text"

name="marca_item"

class="form-control mb-3"

value="<?= htmlspecialchars($item['marca_item']); ?>"

>
















<label class="form-label">

Local

</label>


<input

type="text"

name="local"

class="form-control mb-3"

value="<?= htmlspecialchars($item['local']); ?>"

>







<label class="form-label">

Representante

</label>


<input

type="text"

name="representante"

class="form-control mb-3"

value="<?= htmlspecialchars($item['representante']); ?>"

>







<label class="form-label">

Data da Compra

</label>


<input

type="date"

name="data_compra"

class="form-control mb-3"

value="<?= $item['data_compra']; ?>"

>







<label class="form-label">

Data de Vencimento

</label>


<input

type="date"

name="data_vencimento"

class="form-control mb-3"

value="<?= $item['data_vencimento']; ?>"

>







<label class="form-label">

Confirmar senha

</label>


<input

type="password"

name="password"

class="form-control mb-3"

placeholder="Digite sua senha"

required>







<button class="btn-editar">

<i class="bi bi-save"></i>

Salvar Alterações

</button>






<div class="voltar">

<?php $urlVoltar = '../views/index_farmacia.php'; ?>

<a href="<?= $urlVoltar; ?>">

    <i class="bi bi-arrow-left"></i>

    Voltar

</a>

</div>





</form>



</div>



</div>



</body>


</html>