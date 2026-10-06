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

$idUsuario = (int)($_SESSION['id_user'] ?? 0);
$password = md5($_POST['password'] ?? '');

$stmtSenha = $connect->SQL->prepare("SELECT password FROM usuario WHERE id_user = ?");
$stmtSenha->bind_param("i", $idUsuario);
$stmtSenha->execute();
$usuario = $stmtSenha->get_result()->fetch_assoc();

if (!$usuario || !hash_equals((string)$usuario['password'], $password)) {
    echo '<script>alert("Senha incorreta!"); history.back();</script>'; exit;
}

$setorBanco = 'FARMACIA';
$ph = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids)) . 's';

try {
    $connect->SQL->begin_transaction();

    $sqlBusca = "SELECT id_itens, nome_item, codigo_item, lote
                 FROM itens WHERE id_itens IN ($ph) AND setor = ?";
    $stmtBusca = $connect->SQL->prepare($sqlBusca);

    $values = $ids;
    $values[] = $setorBanco;
    $params = [$types];
    foreach ($values as $k => $v) $params[] = &$values[$k];
    call_user_func_array([$stmtBusca, 'bind_param'], $params);
    $stmtBusca->execute();

    $result = $stmtBusca->get_result();
    $itens = [];
    while ($row = $result->fetch_assoc()) $itens[] = $row;

    if (!$itens) throw new Exception('Nenhum item válido foi encontrado.');

    $stmtHist = $connect->SQL->prepare(
        "INSERT INTO historico_movimentacoes
         (id_item, id_usuario, acao, descricao, data_movimentacao)
         VALUES (?, ?, ?, ?, NOW())"
    );

    $acao = 'EXCLUIU';

    foreach ($itens as $item) {
        $idItem = (int)$item['id_itens'];
        $descricao = "Item excluído em seleção múltipla.\n" .
                     "Nome: " . $item['nome_item'] . "\n" .
                     "Código: " . $item['codigo_item'] . "\n" .
                     "Lote: " . $item['lote'];

        $stmtHist->bind_param("iiss", $idItem, $idUsuario, $acao, $descricao);
        if (!$stmtHist->execute()) throw new Exception('Erro ao registrar histórico.');
    }

    $sqlDelete = "DELETE FROM itens WHERE id_itens IN ($ph) AND setor = ?";
    $stmtDelete = $connect->SQL->prepare($sqlDelete);

    $valuesDelete = $ids;
    $valuesDelete[] = $setorBanco;
    $paramsDelete = [$types];
    foreach ($valuesDelete as $k => $v) $paramsDelete[] = &$valuesDelete[$k];
    call_user_func_array([$stmtDelete, 'bind_param'], $paramsDelete);

    if (!$stmtDelete->execute()) throw new Exception('Erro ao excluir os itens.');

    $connect->SQL->commit();

    $_SESSION['sucesso'] = count($itens) . ' item(ns) excluído(s) com sucesso.';
    header('Location: ' . $paginaVoltar); exit;

} catch (Exception $e) {
    $connect->SQL->rollback();
    die('Erro ao excluir os itens: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
