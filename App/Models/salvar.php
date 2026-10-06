
<?php

session_start();

require_once "../Models/connect.php";

// RECEBE DADOS DO FORMULÁRIO
$setor           = 'FARMACIA';
$codigo_item     = $_POST['codigo_item'];
$nome_item       = $_POST['nome_item'];
$marca_item      = $_POST['marca_item'];
$lote            = $_POST['lote'];
$quant_itens     = (int)$_POST['quant_itens'];
$local           = $_POST['local'];
$representante   = $_POST['representante'];
$data_compra     = $_POST['data_compra'];
$data_vencimento = $_POST['data_vencimento'];
$status_item     = $_POST['status_item'] ?? 'ESTOQUE';
$usuario_item    = $_POST['usuario_item'] ?? '';

if ($status_item == 'ESTOQUE') {
    $usuario_item = '';
}

$sucesso = true;
$primeiro_id = null;


/*
================================================
CADASTRA CADA UNIDADE DO ITEM
================================================
*/

for ($i = 1; $i <= $quant_itens; $i++) {

    $sql = "
        INSERT INTO itens
        (
            setor,
            codigo_item,
            nome_item,
            lote,
            marca_item,
            quant_itens,
            local,
            representante,
            data_compra,
            data_vencimento,
            ativo,
            status_item,
            usuario_item
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            1,
            ?,
            ?,
            ?,
            ?,
            1,
            ?,
            ?
        )
    ";

    // PREPARA A CONSULTA
    $stmt = $connect->SQL->prepare($sql);

    if (!$stmt) {
        $sucesso = false;
        break;
    }

    // VINCULA OS VALORES
    $stmt->bind_param(
        "sssssssssss",
        $setor,
        $codigo_item,
        $nome_item,
        $lote,
        $marca_item,
        $local,
        $representante,
        $data_compra,
        $data_vencimento,
        $status_item,
        $usuario_item
    );

    // EXECUTA
    if (!$stmt->execute()) {
        $sucesso = false;
        break;
    }

    // PEGA O ID DO PRIMEIRO ITEM CADASTRADO
    if ($primeiro_id === null) {
        $primeiro_id = $connect->SQL->insert_id;
    }

    $stmt->close();
}


/*
================================================
REGISTRA HISTÓRICO
================================================
*/

if ($sucesso) {

    $id_usuario = $_SESSION['id_user'] ?? null;

    $acao = "CADASTRO";

    $descricao =
        "Cadastro realizado: " .
        $quant_itens .
        " unidade(s) do item " .
        $nome_item;

    $sqlHistorico = "
        INSERT INTO historico_movimentacoes
        (
            id_item,
            id_usuario,
            acao,
            descricao
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ";

    $stmtHistorico = $connect->SQL->prepare($sqlHistorico);

    if (!$stmtHistorico) {
        $_SESSION['erro'] = "Erro ao preparar o histórico!";
        header("Location: ../cadastro.php");
        exit();
    }

    $stmtHistorico->bind_param(
        "iiss",
        $primeiro_id,
        $id_usuario,
        $acao,
        $descricao
    );

    if (!$stmtHistorico->execute()) {
    $_SESSION['erro'] = "Erro ao registrar o histórico!";
    $stmtHistorico->close();
    
    $_SESSION['sucesso'] = "Itens cadastrados com sucesso!";

header("Location: ../../views/index_farmacia.php");
exit();
    
    exit();
}

$stmtHistorico->close();

$_SESSION['sucesso'] = "Itens cadastrados com sucesso!";

$_SESSION['sucesso'] = "Itens cadastrados com sucesso!";

header("Location: ../../views/index_farmacia.php");
exit();

exit();

}
    


?>

