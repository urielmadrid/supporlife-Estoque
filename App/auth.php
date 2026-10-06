<?php
session_start();

if (!isset($_SESSION['usuario'], $_SESSION['id_user'], $_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$idUsuario = (int)$_SESSION['id_user'];
$usuario = $_SESSION['username'];
$permissao = $_SESSION['permissao'] ?? '';
$tipoAcesso = 'farmacia';
$tipoUsuario = $_SESSION['tipo'] ?? '';

$_SESSION['tipo_acesso'] = 'farmacia';
$_SESSION['setor'] = 'farmacia';

function usuarioLogado() {
    return isset($_SESSION['id_user']);
}

function exigirLogin() {
    if (!isset($_SESSION['id_user'])) {
        header('Location: ../login.php');
        exit;
    }
}

function redirecionarParaSetor() {
    exigirLogin();
    header('Location: ../views/index_farmacia.php');
    exit;
}

function exigirAcesso($tipoPermitido) {
    exigirLogin();
    if ($tipoPermitido !== 'farmacia') {
        header('Location: ../views/index_farmacia.php');
        exit;
    }
    $_SESSION['tipo_acesso'] = 'farmacia';
    $_SESSION['setor'] = 'farmacia';
    }
?>