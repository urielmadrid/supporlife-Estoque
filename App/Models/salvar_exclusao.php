
<?php

require_once "../auth.php";
require_once "connect.php";

$urlVoltar = '../../views/index_farmacia.php';

?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<style>
    body {
        background: #f8f9fa;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card {
        max-width: 500px;
        width: 100%;
        box-shadow: 0 0 20px rgba(0,0,0,.15);
        border: none;
        border-radius: 15px;
    }
</style>

<?php

if (
    !isset(
        $_POST['id_itens'],
        $_POST['password'],
        $_POST['tipo_exclusao']
    )
) {

    header("Location: " . $urlVoltar);
    exit;
}


$id = intval($_POST['id_itens']);

$password = md5($_POST['password']);

$tipo_exclusao = $_POST['tipo_exclusao'];

$id_usuario = $_SESSION['id_user'];


// =========================
// Verifica a senha
// =========================

$sqlSenha = "
SELECT password
FROM usuario
WHERE id_user = ?
";

$stmtSenha = $connect->SQL->prepare($sqlSenha);

$stmtSenha->bind_param(
    "i",
    $id_usuario
);

$stmtSenha->execute();

$resultSenha = $stmtSenha->get_result();

$usuario = $resultSenha->fetch_assoc();


if (!$usuario || $password != $usuario['password']) {

    echo "
    <script>
        alert('Senha incorreta!');
        history.back();
    </script>
    ";

    exit;
}


// =========================
// Busca o item
// =========================

$sqlItem = "
SELECT *
FROM itens
WHERE id_itens = ?
";

$stmtItem = $connect->SQL->prepare($sqlItem);

$stmtItem->bind_param(
    "i",
    $id
);

$stmtItem->execute();

$resultItem = $stmtItem->get_result();

$item = $resultItem->fetch_assoc();


if (!$item) {

    echo '
    <div class="container mt-5">
        <div class="alert alert-danger text-center shadow">

            <h4 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Item não encontrado!
            </h4>

            <p class="mb-3">
                O item informado não existe ou já foi removido.
            </p>

            <a href="' . htmlspecialchars($urlVoltar, ENT_QUOTES, 'UTF-8') . '" class="btn btn-danger">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>

        </div>
    </div>
    ';

    exit;
}


$nome_item = $item['nome_item'];

$codigo_item = $item['codigo_item'];

$lote = $item['lote'];


// =========================
// Monta histórico
// =========================

if ($tipo_exclusao == "unidade") {

    $descricao = "
Unidade removida do estoque.

Item: $nome_item
Código: $codigo_item
Lote: $lote
";

} else {

    $descricao = "
Todas as unidades do lote foram removidas.

Item: $nome_item
Código: $codigo_item
Lote: $lote
";

}


// =========================
// Salva histórico
// =========================

$acao = "EXCLUIU";

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

$stmtHistorico = $connect->SQL->prepare(
    $sqlHistorico
);

$stmtHistorico->bind_param(
    "iiss",
    $id,
    $id_usuario,
    $acao,
    $descricao
);

$stmtHistorico->execute();


// =========================
// Exclui definitivamente
// =========================

if ($tipo_exclusao == "unidade") {

    $sqlDelete = "
        DELETE FROM itens
        WHERE id_itens = ?
    ";

    $stmtDelete = $connect->SQL->prepare(
        $sqlDelete
    );

    $stmtDelete->bind_param(
        "i",
        $id
    );

} else {

    $sqlDelete = "
        DELETE FROM itens
        WHERE codigo_item = ?
        AND lote = ?
    ";

    $stmtDelete = $connect->SQL->prepare(
        $sqlDelete
    );

    $stmtDelete->bind_param(
        "ss",
        $codigo_item,
        $lote
    );

}


if (!$stmtDelete->execute()) {

    die(
        "Erro ao excluir: " .
        $stmtDelete->error
    );

}


// =========================
// Mensagem de sucesso
// =========================

if ($tipo_exclusao == "unidade") {

    $titulo = "Item excluído com sucesso!";

    $mensagem = "A unidade foi removida definitivamente do estoque.";

} else {

    $titulo = "Lote excluído com sucesso!";

    $mensagem = "Todas as unidades deste lote foram removidas definitivamente.";

}


?>

<div class="container mt-5">

    <div class="alert alert-success text-center shadow">

        <h4 class="alert-heading">

            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?>

        </h4>

        <p class="mb-3">

            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>

        </p>

        <a
            href="<?= htmlspecialchars($urlVoltar, ENT_QUOTES, 'UTF-8'); ?>"
            class="btn btn-success"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>

</div>

<?php

exit;

?>
```
