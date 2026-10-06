<?php


session_start();


require_once "Models/connect.php";




if(isset($_POST['cadastrar'])){



    $username = mysqli_real_escape_string(

        $connect->SQL,

        $_POST['username']

    );



    $password = md5($_POST['password']);



    $tipo = 'farmacia';






    // Verifica se selecionou um tipo


    if(empty($tipo)){



        echo "

        <script>

        alert('Selecione o tipo de acesso!');

        </script>";



        exit;



    }








    // Verifica se usuário já existe


    $sql = "

    SELECT *

    FROM usuario

    WHERE username='$username'

    ";




    $resultado = mysqli_query(

        $connect->SQL,

        $sql

    );






    if(mysqli_num_rows($resultado) > 0){



        echo "

        <script>

        alert('Este login já existe!');

        window.location.href='register.php';

        </script>";



        exit;



    }









    // Cadastro


    $sql = "

           INSERT INTO usuario
        (
        username,
        password,
        tipo_acesso
        )
        VALUES
        (
        '$username',
        '$password',
        '$tipo'
        )

    ";









    if(mysqli_query($connect->SQL,$sql)){





        echo "

        <script>

        alert('Usuário cadastrado com sucesso!');

        window.location.href='register.php';

        </script>";







    }else{





        echo "

        <script>

        alert('Erro ao cadastrar usuário!');

        </script>";




    }





}



?>





<!DOCTYPE html>

<html lang="pt-BR">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>

Cadastro de Usuário

</title>

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

        #0d6efd 0%,

        #1f6feb 25%,

        #ffffff 50%,

        #dc3545 75%,

        #b71c1c 100%

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







/* CARD */


.cadastro-card{


    width:100%;


    max-width:450px;


    border-radius:25px;


    overflow:hidden;


    background:rgba(255,255,255,.95);


    box-shadow:

    0 25px 50px rgba(0,0,0,.30);



}







/* CABEÇALHO */


.cadastro-header{


    background:linear-gradient(

        90deg,

        #0056d6,

        #dc3545

    );



    color:white;


    text-align:center;


    padding:35px;



}







.logo{


    width:85px;


    height:85px;


    border-radius:50%;



    background:white;


    color:#dc3545;



    font-size:40px;



    display:flex;



    align-items:center;


    justify-content:center;



    margin:auto;



    margin-bottom:15px;



    box-shadow:

    0 5px 15px rgba(0,0,0,.2);



}







.cadastro-header h2{


    font-weight:bold;


}



.cadastro-header p{


    margin-top:8px;


    opacity:.9;


}








/* CORPO */


.card-body{


    padding:40px;


}







/* CAMPOS */


label{


    font-weight:600;


    color:#333;



}



input,


select{


    width:100%;


    height:50px;


    padding:10px 15px;



    border-radius:10px;



    border:1px solid #ced4da;



    margin-top:8px;



    font-size:16px;



    background:white;



}



input:focus,

select:focus{


    outline:none;



    border-color:#0d6efd;



    box-shadow:

    0 0 10px rgba(13,110,253,.2);



}







/* BOTÃO */


.btn-cadastrar{


    width:100%;


    height:50px;


    margin-top:25px;



    border:none;


    border-radius:10px;




    background:linear-gradient(

        90deg,

        #0056d6,

        #dc3545

    );



    color:white;



    font-weight:bold;



    font-size:16px;



    cursor:pointer;



    transition:.3s;



}




.btn-cadastrar:hover{


    transform:translateY(-2px);



    box-shadow:

    0 8px 20px rgba(220,53,69,.3);



}






/* TIPO DE ACESSO */


.tipo-box{


    display:flex;


    gap:10px;


    margin-top:10px;


}



.tipo-option{


    flex:1;


}



.tipo-option input{


    display:none;


}




.tipo-option label{


    display:block;


    text-align:center;



    padding:12px 5px;



    border-radius:10px;



    border:2px solid #ddd;



    cursor:pointer;



    transition:.3s;



    font-size:14px;



}




.tipo-option label i{


    display:block;


    font-size:22px;


    margin-bottom:5px;



}





.tipo-option input:checked + label{


    background:linear-gradient(

        90deg,

        #0056d6,

        #dc3545

    );



    color:white;



    border-color:#0056d6;



}







/* LINK FINAL */


.footer-links{


    text-align:center;


    margin-top:25px;



}



.footer-links a{


    text-decoration:none;


    color:#0056d6;



    font-weight:600;



}




.footer-links a:hover{


    color:#dc3545;


}





/* MOBILE */


@media(max-width:576px){



.card-body{


    padding:25px;


}



.cadastro-card{


    margin:20px;


}



.tipo-box{


    flex-direction:column;


}



}



</style>


</head>

<body>



<div class="cadastro-card">





<div class="cadastro-header">





<div class="logo">


    <i class="bi bi-person-plus-fill"></i>


</div>





<h2>

    Cadastrar Usuário

</h2>





<p>

    Crie um novo acesso ao sistema

</p>





</div>









<div class="card-body">






<form method="POST">







<label>

Login:

</label>






<input

type="text"

name="username"

placeholder="Digite o login"

required>









<br>








<label>

Senha:

</label>







<input

type="password"

name="password"

placeholder="Digite a senha"

required>









<br>








<input type="hidden" name="tipo_acesso" value="farmacia">
<div class="alert alert-info"><i class="bi bi-capsule"></i> Usuários terão acesso exclusivo à <strong>Farmácia</strong>.</div>

<button

class="btn-cadastrar"

name="cadastrar"

type="submit">



Cadastrar Usuário



</button>









<div class="footer-links">



<a href="../login.php">


<i class="bi bi-arrow-left"></i>


Voltar para Login



</a>




</div>







</form>







</div>






</div>








</body>


</html>