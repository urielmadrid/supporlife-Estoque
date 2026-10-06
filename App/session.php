<?php
session_start();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$senhaEstoque = $_POST['senha_estoque'] ?? '';
$tipo = $_POST['tipo'] ?? 'farmacia';

if ($tipo !== 'farmacia') {
    $_SESSION['login_erro'] = 'Setor de acesso inválido.';
    header('Location: ../login.php');
    exit;
}

if ($username === '' || $password === '') {
    $_SESSION['login_erro'] = 'Digite seu usuário e senha.';
    header('Location: ../login.php');
    exit;
}

if ($senhaEstoque === '' || !hash_equals((string)(getenv('PHARMACY_ACCESS_PASSWORD') ?: ''), $senhaEstoque)) {
    $_SESSION['login_erro'] = 'Senha da Farmácia incorreta.';
    header('Location: ../login.php');
    exit;
}

require_once 'Models/connect.php';
$connect = new Connect();

if (!$connect->login($username, md5($password)) || !isset($_SESSION['usuario'])) {
    $_SESSION['login_erro'] = 'Usuário ou senha incorretos.';
    header('Location: ../login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['login_alerta_id'] = bin2hex(random_bytes(16));
unset($_SESSION['alertas_farmacia_exibidos_login']);
$_SESSION['tipo'] = 'farmacia';
$_SESSION['tipo_acesso'] = 'farmacia';
$_SESSION['setor'] = 'farmacia';

header('Location: ../views/index_farmacia.php');
exit;
?>