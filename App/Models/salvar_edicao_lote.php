<?php
require_once "../auth.php";
require_once "connect.php";

$tipoAcesso = 'farmacia';
$paginaVoltar = '../../views/index_farmacia.php';

$ids = $_POST['ids'] ?? [];
if (!is_array($ids)) $ids = [];
$ids = array_values(array_unique(array_filter(
    array_map('intval', $ids),
    function($id){ return $id > 0; }
)));

if (!$ids) {
    $_SESSION['erro'] = 'Nenhum item foi selecionado.';
    header('Location: ' . $paginaVoltar); exit;
}

$password = md5($_POST['password'] ?? '');
$idUsuario = (int)($_SESSION['id_user'] ?? 0);

$stmtSenha = $connect->SQL->prepare("SELECT password FROM usuario WHERE id_user = ?");
$stmtSenha->bind_param("i", $idUsuario);
$stmtSenha->execute();
$usuario = $stmtSenha->get_result()->fetch_assoc();

if (!$usuario || !hash_equals((string)$usuario['password'], $password)) {
    echo '<script>alert("Senha incorreta!"); history.back();</script>'; exit;
}

$campos = [
    'codigo_item' => trim($_POST['codigo_item'] ?? ''),
    'nome_item' => trim($_POST['nome_item'] ?? ''),
    'lote' => trim($_POST['lote'] ?? ''),
    'marca_item' => trim($_POST['marca_item'] ?? ''),
    'local' => trim($_POST['local'] ?? ''),
    'representante' => trim($_POST['representante'] ?? ''),
    'data_compra' => trim($_POST['data_compra'] ?? ''),
    'data_vencimento' => trim($_POST['data_vencimento'] ?? '')
];

$updates = [];
$values = [];
$types = '';

foreach ($campos as $campo => $valor) {
    if ($valor !== '') {
        $updates[] = "$campo = ?";
        $values[] = $valor;
        $types .= 's';
    }
}

if (!$updates) {
    $_SESSION['erro'] = 'Nenhum campo foi preenchido para alteração.';
    header('Location: ' . $paginaVoltar); exit;
}

$ph = implode(',', array_fill(0, count($ids), '?'));
$typesIds = str_repeat('i', count($ids));
$setorBanco = 'FARMACIA';

try {
    $connect->SQL->begin_transaction();

    $sqlBusca = "SELECT id_itens, nome_item, codigo_item, lote, marca_item, local
                 FROM itens WHERE id_itens IN ($ph) AND setor = ?";
    $stmtBusca = $connect->SQL->prepare($sqlBusca);
    $idsBusca = $ids;
    $params = [$typesIds . 's'];
    foreach ($idsBusca as $k => $v) $params[] = &$idsBusca[$k];
    $params[] = &$setorBanco;
    call_user_func_array([$stmtBusca, 'bind_param'], $params);
    $stmtBusca->execute();
    $result = $stmtBusca->get_result();

    $itens = [];
    while ($row = $result->fetch_assoc()) $itens[] = $row;
    if (!$itens) throw new Exception('Nenhum item válido foi encontrado.');

    $sqlUpdate = "UPDATE itens SET " . implode(', ', $updates) .
                 " WHERE id_itens IN ($ph) AND setor = ?";
    $stmtUpdate = $connect->SQL->prepare($sqlUpdate);

    $allValues = $values;
    foreach ($ids as $id) $allValues[] = $id;
    $allValues[] = $setorBanco;

    $paramsUpdate = [$types . $typesIds . 's'];
    foreach ($allValues as $k => $v) $paramsUpdate[] = &$allValues[$k];
    call_user_func_array([$stmtUpdate, 'bind_param'], $paramsUpdate);

    if (!$stmtUpdate->execute()) throw new Exception('Erro ao atualizar os itens.');

    $stmtHist = $connect->SQL->prepare(
        "INSERT INTO historico_movimentacoes
         (id_item, id_usuario, acao, descricao, data_movimentacao)
         VALUES (?, ?, ?, ?, NOW())"
    );

    $camposAlterados = implode(', ', array_keys(array_filter(
        $campos, function($v){ return $v !== ''; }
    )));
    $acao = 'EDITOU';

    foreach ($itens as $item) {
        $idItem = (int)$item['id_itens'];
        $descricao = "Alteração em lote. Campos alterados: " . $camposAlterados;
        $stmtHist->bind_param("iiss", $idItem, $idUsuario, $acao, $descricao);
        if (!$stmtHist->execute()) throw new Exception('Erro ao registrar histórico.');
    }

    $connect->SQL->commit();
    $_SESSION['sucesso'] = count($itens) . ' item(ns) editado(s) com sucesso.';
    header('Location: ' . $paginaVoltar); exit;

} catch (Exception $e) {
    $connect->SQL->rollback();
    die('Erro ao editar os itens: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
