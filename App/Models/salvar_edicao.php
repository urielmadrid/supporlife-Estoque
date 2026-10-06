<?php

session_start();

require_once "connect.php";

$origemIndex = $_POST['origem_index'] ?? $_SESSION['origem_index'] ?? '';
$id = (int)($_POST['id_itens'] ?? 0);
$password = md5($_POST['password'] ?? '');

if ($id <= 0) {
    die('Item inválido.');
}

/* ==============================
   VERIFICA SENHA
   ============================== */
$idSessao = (int)($_SESSION['id_user'] ?? 0);

$stmtSenha = $connect->SQL->prepare(
    "SELECT id_user FROM usuario WHERE id_user = ? AND password = ?"
);

if (!$stmtSenha) {
    die('Erro ao preparar verificação de senha.');
}

$stmtSenha->bind_param('is', $idSessao, $password);
$stmtSenha->execute();
$resSenha = $stmtSenha->get_result();

if (!$resSenha || $resSenha->num_rows === 0) {
    echo '<script>alert("Senha incorreta!"); history.back();</script>';
    exit;
}

/* ==============================
   RECEBE NOVOS DADOS
   ============================== */
$codigo = trim($_POST['codigo_item'] ?? '');
$nome = trim($_POST['nome_item'] ?? '');
$lote = trim($_POST['lote'] ?? '');
$marca = trim($_POST['marca_item'] ?? '');
$local = trim($_POST['local'] ?? '');
$representante = trim($_POST['representante'] ?? '');
$data_compra = $_POST['data_compra'] ?? '';
$data_vencimento = $_POST['data_vencimento'] ?? '';

if ($nome === '') {
    echo '<script>alert("O nome do item é obrigatório!"); history.back();</script>';
    exit;
}

/* ==============================
   BUSCA DADOS ANTIGOS
   ============================== */
$stmtAntigo = $connect->SQL->prepare(
    "SELECT * FROM itens WHERE id_itens = ?"
);

if (!$stmtAntigo) {
    die('Erro ao preparar consulta do item.');
}

$stmtAntigo->bind_param('i', $id);
$stmtAntigo->execute();
$itemAntigo = $stmtAntigo->get_result()->fetch_assoc();

if (!$itemAntigo) {
    echo '<script>alert("Item não encontrado!"); history.back();</script>';
    exit;
}

/* ==============================
   VERIFICA O QUE REALMENTE MUDOU
   ============================== */
$alteracoes = [];

$campos = [
    'codigo_item' => ['Código', $codigo],
    'nome_item' => ['Nome', $nome],
    'lote' => ['Lote', $lote],
    'marca_item' => ['Marca', $marca],
    'local' => ['Local', $local],
    'representante' => ['Representante', $representante],
    'data_compra' => ['Data da compra', $data_compra],
    'data_vencimento' => ['Data de vencimento', $data_vencimento]
];

foreach ($campos as $campo => [$rotulo, $novoValor]) {
    $valorAntigo = (string)($itemAntigo[$campo] ?? '');
    $novoValor = (string)$novoValor;

    if ($valorAntigo !== $novoValor) {
        $alteracoes[] = $rotulo . ":\n" .
            ($valorAntigo !== '' ? $valorAntigo : '-') .
            " -> " .
            ($novoValor !== '' ? $novoValor : '-');
    }
}

/* Nada mudou: não cria movimentação falsa. */
if (count($alteracoes) === 0) {
    unset($_SESSION['item_antigo']);

    $urlRetorno = '../../views/index_farmacia.php';

    header('Location: ' . $urlRetorno);
    exit;
}

/* ==============================
   ATUALIZA ITEM
   ============================== */
$stmtUpdate = $connect->SQL->prepare(
    "UPDATE itens SET
        codigo_item = ?,
        nome_item = ?,
        lote = ?,
        marca_item = ?,
        local = ?,
        representante = ?,
        data_compra = NULLIF(?, ''),
        data_vencimento = NULLIF(?, '')
     WHERE id_itens = ?"
);

if (!$stmtUpdate) {
    die('Erro ao preparar atualização do item.');
}

$stmtUpdate->bind_param(
    'ssssssssi',
    $codigo,
    $nome,
    $lote,
    $marca,
    $local,
    $representante,
    $data_compra,
    $data_vencimento,
    $id
);

if (!$stmtUpdate->execute()) {
    die('Erro ao atualizar o item: ' . $stmtUpdate->error);
}

/* ==============================
   REGISTRA COMO EDITOU
   ============================== */
$descricao = "Alteração realizada:\n\n" . implode("\n\n", $alteracoes);
$acao = 'EDITOU';
$idUsuario = $idSessao;

$stmtHistorico = $connect->SQL->prepare(
    "INSERT INTO historico_movimentacoes
        (id_item, id_usuario, acao, descricao, data_movimentacao)
     VALUES (?, ?, ?, ?, NOW())"
);

if (!$stmtHistorico) {
    die('Erro ao preparar histórico: ' . $connect->SQL->error);
}

$stmtHistorico->bind_param(
    'iiss',
    $id,
    $idUsuario,
    $acao,
    $descricao
);

if (!$stmtHistorico->execute()) {
    die('Erro ao registrar histórico: ' . $stmtHistorico->error);
}

unset($_SESSION['item_antigo']);

/* ==============================
   DEFINE RETORNO
   ============================== */
$urlRetorno = '../../views/index_farmacia.php';
header('Location: ' . $urlRetorno);
exit;
