<?php

/*
==========================================================
CONFIGURAÇÃO
==========================================================
*/

// Caminho relativo da página atual até a pasta /views.
// O layout é reutilizado por páginas em /views e em subpastas
// como /views/itens, /views/prod etc.
$scriptDir = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
$depthFromRoot = $scriptDir === '' ? 0 : substr_count($scriptDir, '/') + 1;
$url = str_repeat('../', max(0, $depthFromRoot - 1));

/*
==========================================================
VARIÁVEIS
==========================================================
*/

$username = $_SESSION['username'] ?? 'Usuário';

$pesquisa = $_GET['pesquisa'] ?? '';

$origem = $_SESSION['origem_cadastro'] ?? '';

/*
==========================================================
CSS
==========================================================
*/

$css = <<<'CSS'

<style>

/* ======================================================
   RESET
====================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* ======================================================
   BODY
====================================================== */

html,
body {
    background: #111827 !important;
    color: #e5e7eb;
    font-family: 'Segoe UI', Arial, sans-serif;
}


/* ======================================================
   HEADER
====================================================== */

.main-header .logo {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    ) !important;

    color: #fff !important;

    font-size: 22px;
    font-weight: bold;

    border: none;
}

.main-header .navbar {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    ) !important;

    border: none;
}

.main-header .navbar .nav > li > a {
    color: #fff !important;
    transition: .3s;
}

.main-header .navbar .nav > li > a:hover {
    background: rgba(255,255,255,.15) !important;
}


/* ======================================================
   SIDEBAR
====================================================== */

.skin-blue .main-sidebar,
.skin-blue .left-side {
    background: #111827 !important;
}

.skin-blue .sidebar-menu > li.header {
    background: #0f172a !important;
    color: #94a3b8 !important;

    font-weight: bold;
}

.skin-blue .sidebar-menu > li > a {
    color: #e5e7eb !important;

    border-left: 4px solid transparent;

    transition: .3s;
}

.skin-blue .sidebar-menu > li > a:hover {
    background: #1e293b !important;
    color: #fff !important;

    border-left: 4px solid #dc3545;
}

.skin-blue .sidebar-menu > li.active > a {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    ) !important;

    color: #fff !important;

    border-left: 4px solid #fff;
}

.skin-blue .treeview-menu {
    background: #1a2435 !important;
}

.skin-blue .treeview-menu > li > a {
    color: #cbd5e1 !important;
}

.skin-blue .treeview-menu > li > a:hover {
    background: #243244 !important;
    color: #fff !important;
}


/* ======================================================
   USER PANEL
====================================================== */

.user-panel {
    background: #0f172a;
    padding: 15px;
}

.user-panel > .info {
    color: #fff;
}

.user-panel > .info > a {
    color: #fff;
}


/* ======================================================
   CONTENT
====================================================== */

.wrapper {
    background: #111827 !important;
}

.content-wrapper,
.right-side,
.content,
.content-wrapper .content {
    background: #111827 !important;
}

.content-header {
    background: #111827 !important;
    color: #fff;
}

.content-header h1 {
    color: #fff;
    font-weight: 700;
}

.content-header > .breadcrumb {
    background: transparent !important;
}


/* ======================================================
   BOX
====================================================== */

.box {
    background: #fff;

    color: #333;

    border: none;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 10px 25px rgba(0,0,0,.15);

    transition: .3s;
}

.box:hover {
    transform: translateY(-3px);

    box-shadow:
        0 20px 40px rgba(0,0,0,.20);
}

.box-header {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    );

    color: #fff;

    border: none;
}

.box-title {
    color: #fff;
    font-weight: bold;
}

.box-body {
    background: #fff;
    color: #333;
}

.box-footer {
    background: #f8f9fa;
    color: #333;

    border-top: 1px solid #dee2e6;
}


/* ======================================================
   BOTÕES
====================================================== */

.btn-primary {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    );

    border: none;

    border-radius: 10px;

    transition: .3s;
}

.btn-primary:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(220,53,69,.40);
}

.btn-success,
.btn-danger,
.btn-warning {
    border-radius: 10px;
}


/* ======================================================
   INPUTS
====================================================== */

.form-control {
    background: #273449;

    border: 1px solid #3b4a63;

    color: #fff;

    border-radius: 10px;

    box-shadow: none;
}

.form-control:focus {
    background: #273449;

    color: #fff;

    border-color: #0d6efd;

    box-shadow:
        0 0 10px rgba(13,110,253,.30);
}

.form-control::placeholder {
    color: #b8c2d0;
}


/* ======================================================
   TABELAS
====================================================== */

.table {
    background: #fff;
    color: #333;
}

.table thead {
    background: linear-gradient(
        90deg,
        #0d6efd,
        #dc3545
    );

    color: #fff;
}

.table thead th {
    text-align: center;
    vertical-align: middle;
}

.table tbody td {
    color: #333;

    border-color: #ddd;

    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background: #f5f5f5;
}


/* ======================================================
   TABELA DE PRODUTOS
====================================================== */

.table-produtos {
    width: 100%;

    border-collapse: collapse;

    border: 2px solid #6f6f6f;
}

.table-produtos th,
.table-produtos td {
    border: 1px solid #8a8a8a !important;

    padding: 10px;

    vertical-align: middle;
}

.table-produtos tbody tr {
    border-bottom: 2px solid #6f6f6f;
}

.table-produtos tbody tr:last-child {
    border-bottom: none;
}


/* ======================================================
   SITUAÇÃO
====================================================== */

.col-situacao {
    width: auto;
    min-width: 90px;
}

.situacao-uso,
.situacao-estoque {
    margin-bottom: 15px;
}

.usuario-item {
    margin-top: 10px;

    padding: 8px;

    background: #f8f9fa;

    border-radius: 8px;

    font-size: 13px;

    color: #555;
}

.acao-status {
    margin-top: 10px;
}

.acao-status .btn {
    padding: 6px 12px;

    border-radius: 8px;
}


/* ======================================================
   LABEL
====================================================== */

.label {
    display: inline-block;

    padding: 7px 12px;

    border-radius: 15px;

    font-size: 12px;
}

.label-primary {
    background: #0d6efd !important;
}

.label-danger {
    background: #dc3545 !important;
}

.label-success {
    background: #16a34a !important;
}

.label-warning {
    background: #f59e0b !important;
}


/* ======================================================
   SMALL BOX / INFO BOX
====================================================== */

.small-box,
.info-box {
    background: #fff;

    color: #333;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 10px 25px rgba(0,0,0,.15);

    transition: .3s;
}

.small-box:hover,
.info-box:hover {
    transform: translateY(-5px);

    box-shadow:
        0 20px 35px rgba(0,0,0,.20);
}

.small-box h3,
.small-box p,
.info-box-text,
.info-box-number {
    color: #333 !important;
}

.small-box > .inner h3 {
    font-weight: bold;
}


/* ======================================================
   FOOTER
====================================================== */

.main-footer {
    background: #111827;

    border-top: 3px solid #dc3545;

    color: #d1d5db;

    padding: 15px 20px;
}

.main-footer a {
    color: #60a5fa;

    text-decoration: none;
}

.main-footer a:hover {
    color: #dc3545;
}


/* ======================================================
   MODAL
====================================================== */

.modal-content {
    background: #1e293b;

    color: #fff;
}

.modal-header,
.modal-footer {
    border-color: #334155;
}


/* ======================================================
   DROPDOWN
====================================================== */

.dropdown-menu {
    background: #1e293b;

    border: 1px solid #334155;
}

.dropdown-menu > li > a {
    color: #fff;
}

.dropdown-menu > li > a:hover {
    background: #334155;
}


/* ======================================================
   CONTROL SIDEBAR
====================================================== */

.control-sidebar,
.control-sidebar-bg {
    background: #111827 !important;
}


/* ======================================================
   SCROLL
====================================================== */

::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #111827;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(
        #0d6efd,
        #dc3545
    );

    border-radius: 20px;
}


/* ======================================================
   RESPONSIVIDADE
====================================================== */

.table-responsive {
    width: 100%;

    overflow-x: auto;

    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;
}

.table-produtos {
    min-width: 850px;
}

img {
    max-width: 100%;
    height: auto;
}

.form-control {
    width: 100%;
}

.btn {
    max-width: 100%;
}


/* ======================================================
   TABLET
====================================================== */

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


/* ======================================================
   CELULAR
====================================================== */

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
        font-size: 12px;

        padding: 8px;
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


/* ======================================================
   CELULAR PEQUENO
====================================================== */

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

CSS;


/*
==========================================================
HEAD
==========================================================
*/

$head = '
<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        http-equiv="content-language"
        content="pt-br"
    >

    <title>
        Support Life | Controle de Estoque
    </title>


    <!-- Bootstrap CSS -->

    <link
        rel="stylesheet"
        href="' . $url . 'bootstrap/css/bootstrap.min.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
    >


    <!-- Ionicons -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css"
    >


    <!-- AdminLTE -->

    <link
        rel="stylesheet"
        href="' . $url . 'dist/css/AdminLTE.min.css"
    >


    <!-- Tema -->

    <link
        rel="stylesheet"
        href="' . $url . 'dist/css/skins/skin-blue.min.css"
    >


    <!-- CSS personalizado -->

    <link
        rel="stylesheet"
        href="' . $url . 'dist/css/custom-theme.css"
    >


    <!-- HTML5 Shim -->

    <!--[if lt IE 9]>

        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>

        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <![endif]-->

</head>


<body class="hold-transition skin-blue sidebar-mini fixed">


<div class="wrapper">
';


/*
==========================================================
HEADER
==========================================================
*/

$header = '

<header class="main-header">


    <!-- LOGO -->

    <a
        href="' . $url . 'index_farmacia.php"
        class="logo"
    >

        <span class="logo-mini">

            <i class="fa fa-cubes"></i>

        </span>


        <span class="logo-lg">

            <b>Support</b>Estoque

        </span>

    </a>


    <!-- NAVBAR -->

    <nav class="navbar navbar-static-top">


        <!-- BOTÃO SIDEBAR -->

        <a
            href="#"
            class="sidebar-toggle"
            data-toggle="offcanvas"
            role="button"
        >

            <span class="sr-only">
                Alternar Menu
            </span>

        </a>


        <!-- MENU USUÁRIO -->

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">


                <li class="dropdown user user-menu">


                    <a
                        href="#"
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                    >

                        <i
                            class="fa fa-user-circle"
                            style="font-size:18px;"
                        ></i>


                        <span class="hidden-xs">

                            ' . htmlspecialchars(
                                $username,
                                ENT_QUOTES,
                                'UTF-8'
                            ) . '

                        </span>

                    </a>


                    <ul class="dropdown-menu">


                        <li class="user-header">

                            <i
                                class="fa fa-user-circle fa-5x"
                            ></i>


                            <p style="margin-top:15px;">

                                ' . htmlspecialchars(
                                    $username,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) . '

                                <small>
                                    Usuário
                                </small>

                            </p>

                        </li>


                        <li class="user-footer">


                            <div class="pull-right">

                                <a
                                    href="' . $url . 'destroy.php"
                                    class="btn btn-danger btn-flat"
                                >

                                    <i class="fa fa-sign-out"></i>

                                    Sair

                                </a>

                            </div>


                        </li>


                    </ul>


                </li>


            </ul>

        </div>


    </nav>


</header>
';


/*
==========================================================
ASIDE / MENU
==========================================================
*/

$aside = '

<aside class="main-sidebar">

    <section class="sidebar">

        <!-- USUÁRIO -->

        <div class="user-panel">

            <div class="pull-left image">

                <i
                    class="fa fa-user-circle fa-3x"
                    style="color:#FFF;"
                ></i>

            </div>

            <div class="pull-left info">

                <p>
                    ' . htmlspecialchars(
                        $username,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '
                </p>

                <a href="#">

                    <i class="fa fa-circle text-success"></i>

                    Online

                </a>

            </div>

        </div>


        <!-- PESQUISA -->

        <form
            action=""
            method="GET"
            class="sidebar-form"
        >

            <div class="input-group">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar item..."
                    value="' . htmlspecialchars(
                        $pesquisa,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '"
                >

                <input
                    type="hidden"
                    name="origem"
                    value="' . htmlspecialchars(
                        $origem,
                        ENT_QUOTES,
                        'UTF-8'
                    ) . '"
                >

                <span class="input-group-btn">

                    <button
                        type="submit"
                        class="btn btn-flat"
                    >

                        <i class="fa fa-search"></i>

                    </button>

                </span>

            </div>

        </form>


        <!-- MENU -->

        <ul
            class="sidebar-menu"
            data-widget="tree"
        >

            <li class="header">
                MENU PRINCIPAL
            </li>


            <!-- CADASTROS -->

            <li>

                <a
                    href="' . $url . '../App/cadastro.php?origem=' . urlencode($origem) . '"
                >

                    <i class="fa fa-cubes"></i>

                    <span>
                        Cadastros de Itens
                    </span>

                </a>

            </li>


            <!-- MOVIMENTAÇÕES -->

            <li>

                <a
                    href="' . $url . '../App/validar_senha.php"
                >

                    <i class="fa fa-exchange"></i>

                    <span>
                        Movimentações
                    </span>

                </a>

            </li>


            <!-- CONFIGURAÇÕES -->

            <li>

                <a
                    href="' . $url . 'configs.php"
                >

                    <i class="fa fa-cogs"></i>

                    <span>
                        Configurações
                    </span>

                </a>

            </li>


            <!-- SAIR -->

            <li>

                <a
                    href="' . $url . 'destroy.php"
                >

                    <i class="fa fa-sign-out text-red"></i>

                    <span>
                        Sair
                    </span>

                </a>

            </li>


        </ul>

    </section>

</aside>

';



/*
==========================================================
FOOTER
==========================================================
*/

$anoAtual = date('Y');

$footer = '

<footer class="main-footer">


    <div class="pull-right hidden-xs">

        <strong>
            Versão
        </strong>

        1.0.0

    </div>


    <strong>

        &copy;

        ' . $anoAtual . '


        <a
            
           
        >

            Nexo MP & Support Life

        </a>.

    </strong>


    Todos os direitos reservados.


</footer>

</div>
';


/*
==========================================================
JAVASCRIPT
==========================================================
*/

$javascript = '

<!-- ==================================================
     JQUERY
================================================== -->

<script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
></script>


<!-- ==================================================
     JQUERY SLIMSCROLL
     Necessário para o layout fixed do AdminLTE
================================================== -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"
></script>


<!-- ==================================================
     BOOTSTRAP
================================================== -->

<script
    src="' . $url . 'bootstrap/js/bootstrap.min.js"
></script>


<!-- ==================================================
     ADMINLTE
================================================== -->

<script
    src="' . $url . 'dist/js/app.min.js"
></script>


<!-- ==================================================
     SWEETALERT
================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
></script>


<!-- ==================================================
     SCRIPTS DO SISTEMA
================================================== -->

<script>

$(document).ready(function () {

    /*
    ================================================
    MENU ADMINLTE
    ================================================
    */

    if ($.fn.tree) {
        $(".sidebar-menu").tree();
    }


    /*
    ================================================
    TOOLTIP
    ================================================
    */

    if ($.fn.tooltip) {
        $("[data-toggle=\\"tooltip\\"]").tooltip();
    }


    /*
    ================================================
    ALERTAS
    ================================================
    */

    setTimeout(function () {

        $(".alert").fadeOut("slow");

    }, 5000);


    /*
    ================================================
    CONFIRMAÇÃO
    ================================================
    */

    $(".confirmar").on("click", function (e) {

        if (!confirm(
            "Deseja realmente realizar esta operação?"
        )) {

            e.preventDefault();

        }

    });


    /*
    ================================================
    TABELAS
    ================================================
    */

    $("table").addClass(
        "table table-bordered table-hover"
    );

});

</script>

';
