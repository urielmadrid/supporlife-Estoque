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



/* CARD MANUTENÇÃO */


.maintenance-card{

    width:500px;

    max-width:calc(100% - 40px);

    border:none;

    border-radius:25px;

    overflow:hidden;


    background:rgba(255,255,255,.95);


    box-shadow:
    0 25px 50px rgba(0,0,0,.30);


}



/* CABEÇALHO */


.maintenance-header{

    background:linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );


    color:white;

    text-align:center;

    padding:40px;

}



.logo{

    width:90px;

    height:90px;


    border-radius:50%;


    background:white;


    color:#dc3545;


    font-size:42px;


    display:flex;

    justify-content:center;

    align-items:center;


    margin:0 auto 20px;


    box-shadow:
    0 8px 20px rgba(0,0,0,.25);


}



.maintenance-header h2{

    font-weight:bold;

    margin-bottom:10px;

}



.maintenance-header p{

    margin:0;

    opacity:.9;

}



/* CORPO */


.card-body{

    padding:45px;

    text-align:center;

}



/* ÍCONE CENTRAL */


.maintenance-icon{

    width:120px;

    height:120px;


    margin:0 auto 25px;


    border-radius:50%;


    display:flex;

    justify-content:center;

    align-items:center;


    background:#f8f9fa;


    color:#0d6efd;


    font-size:55px;


    box-shadow:
    0 10px 25px rgba(0,0,0,.15);


    animation:pulse 2s infinite;

}



@keyframes pulse{


    0%{

        transform:scale(1);

    }


    50%{

        transform:scale(1.08);

    }


    100%{

        transform:scale(1);

    }


}



/* TEXTO */


.card-body h3,
.card-body h4{

    color:#333;

    font-weight:bold;

}



.card-body p{

    color:#777;

    line-height:1.6;

}



/* BOTÃO */


.btn-maintenance{


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



.btn-maintenance:hover{


    transform:translateY(-3px);


    box-shadow:
    0 10px 25px rgba(220,53,69,.35);


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



/* RODAPÉ */


.footer-links{

    text-align:center;

}



.footer-links a{


    color:#0056d6;

    text-decoration:none;

    font-weight:600;


}



.footer-links a:hover{

    color:#dc3545;

}



/* ANIMAÇÃO DE ENGRENAGEM */

.gear{

    animation:rotate 5s linear infinite;

}



@keyframes rotate{


    from{

        transform:rotate(0deg);

    }


    to{

        transform:rotate(360deg);

    }


}



/* MOBILE */


@media(max-width:576px){


    .maintenance-card{

        margin:20px;

    }


    .maintenance-header{

        padding:30px 20px;

    }


    .card-body{

        padding:25px;

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

} </style>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema em Manutenção</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

<div class="maintenance-card">

    <div class="maintenance-header">

        <div class="logo">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>

        <h2>Sistema em Manutenção</h2>

        <p>Estamos realizando melhorias para oferecer uma experiência ainda melhor.</p>

    </div>

    <div class="card-body text-center">

        <div class="maintenance-icon">
            <i class="fa-solid fa-gears"></i>
        </div>

        <h4 class="mb-3">
            Voltaremos em breve!
        </h4>

        <p class="text-muted mb-4">
            Nosso sistema está temporariamente indisponível enquanto realizamos atualizações.
            Agradecemos sua compreensão.
        </p>

        <button class="btn-maintenance" onclick="location.reload()">
            <i class="fa-solid fa-rotate-right"></i>
            Atualizar Página
        </button>

        <div class="divider">
            Obrigado pela paciência
        </div>

        <div class="footer-info">
            <small>
                © 2026 • SupportLife
            </small>
        </div>

    </div>

</div>

</body>
</html>