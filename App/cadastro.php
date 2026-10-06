<!DOCTYPE html>
<html lang="pt-BR">

<head>
 
<link rel="icon" href="favicon.ico" type="image/x-icon">

 
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Cadastro de Itens | SupportLife</title>


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




.card-cadastro{


width:500px;


max-width:95%;


background:rgba(255,255,255,.95);


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


box-shadow:0 5px 15px rgba(0,0,0,.2);


}




.header h2{


font-weight:bold;

margin-top:15px;


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


border-radius:10px 0 0 10px;


}




.btn-cadastrar{


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


transition:.3s;


}



.btn-cadastrar:hover{


transform:translateY(-2px);


box-shadow:
0 8px 20px rgba(220,53,69,.3);


}



.voltar{


text-align:center;


margin-top:20px;


}



.voltar a{


color:#0056d6;


text-decoration:none;


font-weight:bold;


}

/* ===========================
   RESPONSIVIDADE
=========================== */

/* Faz tabelas rolarem horizontalmente no celular */
.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.table-produtos {
    width: 100%;
}

.table-produtos th,
.table-produtos td {
    white-space: nowrap;
}

/* Imagens */
img {
    max-width: 100%;
    height: auto;
}

/* Inputs */
.form-control {
    width: 100%;
}

/* Botões */
.btn {
    max-width: 100%;
    white-space: normal;
}

/* Coluna Situação */
.col-situacao {
    min-width: auto;
}

/* Tablets */
@media (max-width: 992px) {

    .content-wrapper,
    .content {
        padding: 10px;
    }

    .box {
        margin-bottom: 15px;
    }

    .box-header h3 {
        font-size: 18px;
    }

    .table-produtos th,
    .table-produtos td {
        padding: 8px;
        font-size: 13px;
    }

    .btn {
        font-size: 13px;
        padding: 8px 10px;
    }

    .small-box h3 {
        font-size: 24px;
    }

}

/* Celulares */
@media (max-width: 768px) {

    .main-header .logo {
        font-size: 18px;
    }

    .content-header h1 {
        font-size: 22px;
    }

    .box {
        border-radius: 12px;
    }

    .box-header {
        padding: 10px;
    }

    .box-body {
        padding: 10px;
    }

    .table-produtos th,
    .table-produtos td {
        font-size: 12px;
        padding: 6px;
    }

    .btn {
        width: 100%;
        margin-bottom: 6px;
    }

    .usuario-item {
        font-size: 12px;
    }

    .label {
        display: block;
        width: 100%;
        text-align: center;
        margin-bottom: 5px;
    }

    .acao-status {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

}

/* Celulares pequenos */
@media (max-width: 480px) {

    body {
        font-size: 14px;
    }

    .content-header h1 {
        font-size: 18px;
    }

    .box-header h3 {
        font-size: 16px;
    }

    .small-box h3 {
        font-size: 20px;
    }

    .small-box p {
        font-size: 12px;
    }

    .table-produtos th,
    .table-produtos td {
        font-size: 11px;
        padding: 5px;
    }

    .btn {
        font-size: 12px;
        padding: 8px;
    }

}

</style>



</head>

<script>

function alterarUsuario(){

    let status = document.getElementById("status_item");
    let usuario = document.getElementById("usuario_item");

    if(status.value == "USO"){

        usuario.disabled = false;
        usuario.required = true;

    }else{

        usuario.value = "";
        usuario.disabled = true;
        usuario.required = false;

    }

}

window.onload = alterarUsuario;

</script>

<body>
<?php
if (isset($_GET['origem'])) {
    $_SESSION['origem_cadastro'] = $_GET['origem'];
}
?>

<div class="card-cadastro">



<div class="header">


<div class="logo">

<i class="bi bi-box-seam"></i>

</div>



<h2>
Cadastro de Itens
</h2>


<p>
Sistema de Controle de Estoque
</p>



</div>





<div class="body-card">



<form method="POST" action="Models/salvar.php">

<input type="hidden" name="setor" value="FARMACIA">
<div class="alert alert-info mb-3"><i class="bi bi-capsule"></i> Cadastro destinado à <strong>Farmácia</strong>.</div>
<label class="form-label">
Código do Item
</label>


<div class="input-group mb-3">

<span class="input-group-text">

<i class="bi bi-upc-scan"></i>

</span>


<input 
type="text"
name="codigo_item"
class="form-control"
placeholder="Digite o código do item"
required>


</div>







<label class="form-label">
Nome do Item
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-box"></i>

</span>


<input 
type="text"
name="nome_item"
class="form-control"
placeholder="Digite o nome do item"
required>


</div>




<!-- Compartimento -->

<label class="form-label">
Lote / Compartimento
</label>

<div class="input-group mb-3">

    <span class="input-group-text">
        <i class="bi bi-boxes"></i>
    </span>

    <input
        type="text"
        name="lote"
        class="form-control"
        placeholder="Ex: Caixa A01"
        required>

</div>


<label class="form-label">
Marca do produto
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-tag"></i>

</span>


<input 
type="text"
name="marca_item"
class="form-control"
placeholder="Digite a marca do item"
required>


</div>


<label class="form-label">
Quantidade de Itens
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-123"></i>

</span>


<input 
type="number"
name="quant_itens"
class="form-control"
placeholder="Digite a quantidade"
required>


</div>


<label class="form-label">
Local do produto
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-geo-alt-fill"></i>

</span>


<input 
type="text"
name="local"
class="form-control"
placeholder="Digite o local que o produto se encontra"
required>


</div>


<label class="form-label">
Representante
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-person"></i>

</span>


<input 
type="text"
name="representante"
class="form-control"
placeholder="Digite quem será o representante deste item"
required>


</div>




<label class="form-label">
Data da Compra
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-calendar"></i>

</span>


<input 
type="date"
name="data_compra"
class="form-control"
required>


</div>






<label class="form-label">
Data de Vencimento
</label>


<div class="input-group mb-3">


<span class="input-group-text">

<i class="bi bi-calendar-x"></i>

</span>


<input 
type="date"
name="data_vencimento"
class="form-control"
required>


</div>



<label class="form-label">
Situação do Item
</label>

<div class="input-group mb-3">

    <span class="input-group-text">
        <i class="bi bi-check-circle"></i>
    </span>

    <select
        name="status_item"
        id="status_item"
        class="form-control"
        onchange="alterarUsuario()"
        required>

        <option value="ESTOQUE" selected>No estoque</option>
        <option value="USO">Em uso</option>

    </select>

</div>


<label class="form-label">
Com quem está?
</label>

<div class="input-group mb-3">

    <span class="input-group-text">
        <i class="bi bi-person"></i>
    </span>

    <input
        type="text"
        name="usuario_item"
        id="usuario_item"
        class="form-control"
        placeholder="Nome da pessoa ou setor"
        disabled>

</div>



<button class="btn-cadastrar">

<i class="bi bi-save"></i>

Cadastrar Item

</button>





<div class="voltar">
    <a href="#" onclick="voltarParaOrigem(); return false;">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<script>
function voltarParaOrigem() {
    window.location.href = '../views/index_farmacia.php';
}
</script>




</form>



</div>


</div>



</body>


</html>