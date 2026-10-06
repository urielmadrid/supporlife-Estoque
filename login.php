<?php
session_start();
$loginErro = $_SESSION['login_erro'] ?? '';
unset($_SESSION['login_erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>SupportLife | Sistema de Estoque</title>


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

    width:100%;

    display:flex;

    justify-content:center;

    align-items:center;

    font-family:"Segoe UI", sans-serif;


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


.login-card{

    width:450px;

    max-width:calc(100% - 40px);

    border:none;

    border-radius:25px;

    overflow:hidden;


    background:rgba(255,255,255,0.95);


    box-shadow:

    0 25px 50px rgba(0,0,0,.30);


}



/* CABEÇALHO */


.login-header{


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

    justify-content:center;

    align-items:center;


    margin:0 auto 15px;


    box-shadow:

    0 5px 15px rgba(0,0,0,.2);


}



.login-header h2{

    font-weight:bold;

}


.login-header p{

    margin:0;

}



/* CORPO */


.card-body{

    padding:40px;

}



/* CAMPOS */


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



.form-control:focus{


    border-color:#0d6efd;


    box-shadow:

    0 0 10px rgba(13,110,253,.2);


}



/* BOTÃO LOGIN */


.btn-login{


    width:100%;


    height:50px;


    border-radius:10px;


    border:none;


    color:white;


    font-weight:bold;



    background:linear-gradient(

        90deg,

        #0056d6,

        #dc3545

    );


    transition:.3s;


}



.btn-login:hover{


    transform:translateY(-2px);


    box-shadow:

    0 8px 20px rgba(220,53,69,.3);


}



/* DIVISOR */


.divider{


    display:flex;


    align-items:center;


    margin:30px 0;


    color:#888;


}



.divider::before,
.divider::after{


    content:"";


    flex:1;


    border-bottom:1px solid #ddd;


}



.divider::before{


    margin-right:15px;


}



.divider::after{


    margin-left:15px;


}



/* LINKS */


.footer-links{


    text-align:center;


    margin-top:20px;


}



.footer-links a{


    color:#0056d6;


    text-decoration:none;


    font-weight:600;


}



.footer-links a:hover{


    color:#dc3545;


}



/* SELEÇÃO ESTOQUE */


.estoque-box{


    display:flex;


    gap:10px;


    margin-bottom:20px;


}



.estoque-option{


    flex:1;


}



.estoque-option input{


    display:none;


}



.estoque-option label{


    display:block;


    text-align:center;


    padding:15px 8px;


    border-radius:12px;


    border:2px solid #ddd;


    cursor:pointer;


    font-weight:600;


    transition:.3s;


    color:#555;


}



.estoque-option label i{


    display:block;


    font-size:22px;


    margin-bottom:5px;


}

.estoque-option input:checked + label{


    background:linear-gradient(

        90deg,

        #0056d6,

        #dc3545

    );


    color:white;


    border-color:#0056d6;


    box-shadow:

    0 5px 15px rgba(0,0,0,.15);


}



/* SENHA EXTRA */


#senhaExtra{


    animation:aparecer .3s ease;


}



@keyframes aparecer{


    from{


        opacity:0;

        transform:translateY(-10px);


    }


    to{


        opacity:1;

        transform:translateY(0);


    }


}







/* MOBILE */


@media(max-width:576px){


.login-card{


    margin:20px;


}



.card-body{


    padding:25px;


}



.estoque-box{


    flex-direction:column;


}



}



</style>


</head>


<body>




<div class="card login-card">



<div class="login-header">



<div class="logo">


<i class="bi bi-box-seam"></i>


</div>



<h2>

SupportLife

</h2>



<p>

Sistema de Controle de Estoque

</p>



</div>





<div class="card-body">

<?php if ($loginErro !== ''): ?>
<div class="alert alert-danger text-center" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <?= htmlspecialchars($loginErro, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>



<h4 class="text-center mb-4">

Bem-vindo!

</h4>





<form action="App/session.php" method="POST">



<!-- ACESSO EXCLUSIVO À FARMÁCIA -->
<div class="mb-4">
<label class="form-label">Setor</label>
<div class="estoque-box">
<div class="estoque-option">
<input type="radio" id="farmacia" name="tipo" value="farmacia" checked>
<label for="farmacia"><i class="bi bi-capsule"></i> Farmácia</label>
</div>
</div>
</div>

<!-- SENHA DA FARMÁCIA -->


<div id="senhaExtra">



<label class="form-label">

Senha da Farmácia

</label>




<div class="input-group">



<span class="input-group-text">


<i class="bi bi-shield-lock-fill"></i>


</span>





<input


type="password"


class="form-control"


name="senha_estoque"


id="senhaEstoque"


placeholder="Digite a senha da Farmácia" required>



</div>



</div>















<!-- USUÁRIO -->


<div class="mb-3">


<label class="form-label">

Usuário

</label>




<div class="input-group">



<span class="input-group-text">


<i class="bi bi-person-fill"></i>


</span>





<input


type="text"


class="form-control"


name="username"


placeholder="Digite seu usuário"


required>



</div>


</div>






<!-- SENHA -->


<div class="mb-4">


<label class="form-label">

Senha

</label>





<div class="input-group">



<span class="input-group-text">


<i class="bi bi-lock-fill"></i>


</span>




<input


type="password"


class="form-control"


name="password"


placeholder="Digite sua senha"


required>



</div>


</div>







<button


type="submit"


class="btn btn-login">


Entrar


</button>





</form>




<div class="divider">

ou

</div>




<div class="footer-links">


Não possui uma conta?


<a href="App/register.php">


Cadastre-se


</a>


</div>



</div>


</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>



</script>



</body>

</html>