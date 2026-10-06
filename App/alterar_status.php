<?php

require_once "auth.php";
require_once "Models/connect.php";

$tipoAcesso = 'farmacia';
$paginaVoltar = '../views/index_farmacia.php';

/*
==========================================================
IDENTIFICA SE É ALTERAÇÃO MÚLTIPLA
==========================================================
*/

$modoMultiplo = false;

$idsSelecionados = [];


/*
==========================================================
RECEBE IDS VIA POST
==========================================================
*/

if (
    isset($_POST['ids']) &&
    is_array($_POST['ids'])
) {

    foreach ($_POST['ids'] as $id) {

        $id = intval($id);

        if ($id > 0) {

            $idsSelecionados[] = $id;

        }

    }


    /*
    Remove IDs duplicados
    */

    $idsSelecionados =
        array_values(
            array_unique($idsSelecionados)
        );


    if (count($idsSelecionados) > 0) {

        $modoMultiplo = true;

    }

}


/*
==========================================================
MODO INDIVIDUAL
==========================================================
*/

if (!$modoMultiplo) {

    if (!isset($_GET['id'])) {

        $_SESSION['erro'] =
            'Item não informado.';

        header(
            'Location: ' . $paginaVoltar
        );

        exit;

    }


    $id = intval($_GET['id']);


    if ($id <= 0) {

        $_SESSION['erro'] =
            'Item inválido.';

        header(
            'Location: ' . $paginaVoltar
        );

        exit;

    }


    /*
    =====================================
    BUSCA ITEM
    =====================================
    */

    $sql = "
        SELECT *
        FROM itens
        WHERE id_itens = ?
    ";


    $stmt = $connect->SQL->prepare($sql);


    if (!$stmt) {

        die(
            'Erro ao preparar consulta: ' .
            $connect->SQL->error
        );

    }


    $stmt->bind_param(
        "i",
        $id
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


    $item =
        $resultado->fetch_assoc();


    if (!$item) {

        $_SESSION['erro'] =
            'Item não encontrado.';

        header(
            'Location: ' . $paginaVoltar
        );

        exit;

    }


    /*
    =====================================
    DADOS DO ITEM
    =====================================
    */

    $status_anterior =
        $item['status_item'] ?? 'ESTOQUE';

    $usuario_anterior =
        $item['usuario_item'] ?? '';

}


/*
==========================================================
MODO MÚLTIPLO
==========================================================
*/

if ($modoMultiplo) {


    /*
    =====================================
    MONTA PLACEHOLDERS
    =====================================
    */

    $placeholders =
        implode(
            ',',
            array_fill(
                0,
                count($idsSelecionados),
                '?'
            )
        );


    /*
    =====================================
    TIPOS DOS IDS
    =====================================
    */

    $tipos =
        str_repeat(
            'i',
            count($idsSelecionados)
        );


    /*
    =====================================
    BUSCA TODOS OS ITENS
    =====================================
    */

    $sql = "
        SELECT *
        FROM itens
        WHERE id_itens IN ($placeholders)
        ORDER BY nome_item ASC
    ";


    $stmt =
        $connect->SQL->prepare($sql);


    if (!$stmt) {

        die(
            'Erro ao preparar consulta: ' .
            $connect->SQL->error
        );

    }


    $parametros = [];

    $parametros[] = $tipos;


    foreach ($idsSelecionados as $key => $id) {

        $parametros[] =
            &$idsSelecionados[$key];

    }


    call_user_func_array(
        [$stmt, 'bind_param'],
        $parametros
    );


    $stmt->execute();


    $resultado =
        $stmt->get_result();


    $itensSelecionados = [];


    while (
        $itemSelecionado =
        $resultado->fetch_assoc()
    ) {

        $itensSelecionados[] =
            $itemSelecionado;

    }


    if (
        count($itensSelecionados) === 0
    ) {

        $_SESSION['erro'] =
            'Nenhum dos itens selecionados foi encontrado.';

        header(
            'Location: ' . $paginaVoltar
        );

        exit;

    }

}


?>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}


body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    font-family:"Segoe UI",sans-serif;

    background:

    linear-gradient(
        135deg,
        #0d6efd,
        #1f6feb,
        #ffffff,
        #dc3545,
        #b71c1c
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


.card-status{

    width:600px;

    max-width:95%;

    max-height:95vh;

    overflow-y:auto;

    background:rgba(255,255,255,.95);

    border-radius:25px;

    overflow-x:hidden;

    box-shadow:
        0 25px 50px rgba(0,0,0,.3);

}


.card-status-header{

    background:

    linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    padding:35px;

    text-align:center;

    color:white;

}


.card-status-header h2{

    font-weight:bold;

    margin-top:15px;

}


.logo-status{

    width:80px;

    height:80px;

    background:white;

    color:#dc3545;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    font-size:35px;

    margin:auto;

    box-shadow:
        0 5px 15px rgba(0,0,0,.2);

}


.card-status-body{

    padding:40px;

}


.info-item{

    background:#f8f9fa;

    padding:20px;

    border-radius:15px;

    margin-bottom:25px;

    border-left:
        5px solid #0056d6;

}


.info-item h3{

    text-align:center;

    color:#0056d6;

    margin-bottom:20px;

    font-weight:bold;

}


.info-linha{

    display:flex;

    justify-content:space-between;

    background:white;

    padding:12px;

    margin-bottom:10px;

    border-radius:8px;

}


.info-linha span{

    font-weight:bold;

}


.lista-itens{

    max-height:280px;

    overflow-y:auto;

}


.item-multiplo{

    background:white;

    border:1px solid #ddd;

    border-radius:10px;

    padding:12px;

    margin-bottom:8px;

}


.item-multiplo strong{

    color:#0056d6;

}


.card-status-body label{

    font-weight:600;

    margin-bottom:8px;

    display:block;

}


.card-status-body select,
.card-status-body input{

    width:100%;

    height:50px;

    border-radius:10px;

    border:1px solid #ccc;

    padding:0 15px;

    font-size:16px;

    margin-bottom:20px;

    outline:none;

}


.card-status-body select:focus,
.card-status-body input:focus{

    border-color:#0056d6;

    box-shadow:
        0 0 8px rgba(0,86,214,.3);

}


.btn-status{

    height:50px;

    width:100%;

    border:none;

    border-radius:10px;

    color:white;

    font-weight:bold;

    font-size:16px;

    background:

    linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    cursor:pointer;

    transition:.3s;

}


.btn-status:hover{

    transform:translateY(-2px);

    box-shadow:
        0 8px 20px rgba(220,53,69,.3);

}


.voltar-status{

    text-align:center;

    margin-top:20px;

}


.voltar-status a{

    color:#0056d6;

    font-weight:bold;

    text-decoration:none;

}


@media(max-width:600px){

    .card-status{

        width:95%;

    }

    .card-status-body{

        padding:25px;

    }

    .card-status-header{

        padding:25px;

    }

    .info-linha{

        flex-direction:column;

        gap:5px;

    }

}

</style>


<div class="card-status">


    <div class="card-status-header">


        <div class="logo-status">

            <i class="fa fa-refresh"></i>

        </div>


        <?php if ($modoMultiplo): ?>

            <h2>

                Alterar Situação dos Itens

            </h2>

        <?php else: ?>

            <h2>

                Alterar Situação do Item

            </h2>

        <?php endif; ?>


    </div>



    <div class="card-status-body">


        <?php if ($modoMultiplo): ?>


            <div class="info-item">


                <h3>

                    Itens selecionados

                </h3>


                <div class="lista-itens">


                    <?php foreach (
                        $itensSelecionados
                        as $itemSelecionado
                    ): ?>


                        <div class="item-multiplo">


                            <strong>

                                Código:

                            </strong>

                            <?= htmlspecialchars(
                                $itemSelecionado['codigo_item'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>


                            <br>


                            <strong>

                                Produto:

                            </strong>

                            <?= htmlspecialchars(
                                $itemSelecionado['nome_item'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>


                            <br>


                            <strong>

                                Marca:

                            </strong>

                            <?= htmlspecialchars(
                                $itemSelecionado['marca_item'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>


                            <br>


                            <strong>

                                Situação atual:

                            </strong>


                            <?php if (
                                ($itemSelecionado['status_item'] ?? '')
                                === 'USO'
                            ): ?>

                                <span style="color:#dc3545;">

                                    Em uso

                                </span>

                            <?php else: ?>

                                <span style="color:#198754;">

                                    No estoque

                                </span>

                            <?php endif; ?>


                        </div>


                    <?php endforeach; ?>


                </div>


                <div
                    style="
                        margin-top:15px;
                        text-align:center;
                        font-weight:bold;
                    "
                >

                    <?= count($itensSelecionados); ?>

                    item(ns) selecionado(s)

                </div>


            </div>


        <?php else: ?>


            <div class="info-item">


                <h3>

                    Informações do Item

                </h3>


                <div class="info-linha">

                    <span>Código:</span>

                    <?= htmlspecialchars(
                        $item['codigo_item'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </div>


                <div class="info-linha">

                    <span>Produto:</span>

                    <?= htmlspecialchars(
                        $item['nome_item'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </div>


                <div class="info-linha">

                    <span>Marca:</span>

                    <?= htmlspecialchars(
                        $item['marca_item'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </div>


                <div class="info-linha">

                    <span>Quantidade:</span>

                    <?= htmlspecialchars(
                        $item['quant_itens'] ?? '0',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>

                </div>


                <div
                    style="
                        text-align:center;
                        margin-top:15px;
                    "
                >


                    <?php if (
                        ($item['status_item'] ?? '')
                        === 'USO'
                    ): ?>


                        <span
                            style="
                                background:#dc3545;
                                color:white;
                                padding:10px 20px;
                                border-radius:25px;
                                font-weight:bold;
                            "
                        >

                            <i class="fa fa-user"></i>

                            Em uso

                        </span>


                        <br><br>


                        <strong>

                            Com:

                        </strong>


                        <?= htmlspecialchars(
                            $item['usuario_item'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>


                    <?php else: ?>


                        <span
                            style="
                                background:#198754;
                                color:white;
                                padding:10px 20px;
                                border-radius:25px;
                                font-weight:bold;
                            "
                        >

                            <i class="fa fa-check"></i>

                            No estoque

                        </span>


                    <?php endif; ?>


                </div>


            </div>


        <?php endif; ?>



        <form method="POST">


            <?php if ($modoMultiplo): ?>


                <?php foreach (
                    $idsSelecionados
                    as $idSelecionado
                ): ?>

                    <input
                        type="hidden"
                        name="ids[]"
                        value="<?= (int)$idSelecionado; ?>"
                    >

                <?php endforeach; ?>


            <?php else: ?>


                <input
                    type="hidden"
                    name="id_itens"
                    value="<?= (int)$id; ?>"
                >


            <?php endif; ?>


            <input
                type="hidden"
                name="salvar"
                value="1"
            >


            <label>

                Alterar situação

            </label>


            <select
                name="status_item"
                id="status_item"
            >


                <option
                    value="ESTOQUE"
                >

                    No estoque

                </option>


                <option
                    value="USO"
                >

                    Em uso

                </option>


            </select>



            <label>

                Com quem está?

            </label>


            <input
                type="text"
                name="usuario_item"
                id="usuario_item"
                value="<?= !$modoMultiplo
                    ? htmlspecialchars(
                        $item['usuario_item'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    )
                    : ''
                ?>"
                placeholder="Nome da pessoa ou setor"
            >



            <button
                type="submit"
                name="salvar"
                class="btn-status"
            >

                <i class="fa fa-save"></i>

                <?php if ($modoMultiplo): ?>

                    Salvar Alteração nos Itens

                <?php else: ?>

                    Salvar Alteração

                <?php endif; ?>

            </button>


        </form>



        <div class="voltar-status">


            <a
                href="<?= htmlspecialchars(
                    $paginaVoltar,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
            >

                <i class="fa fa-arrow-left"></i>

                Voltar

            </a>


        </div>


    </div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const status =
            document.getElementById(
                'status_item'
            );


        const usuario =
            document.getElementById(
                'usuario_item'
            );


        function atualizarUsuario() {


            if (
                status.value === 'ESTOQUE'
            ) {

                usuario.value = '';

                usuario.disabled = true;

                usuario.style.backgroundColor =
                    '#e9ecef';

            } else {

                usuario.disabled = false;

                usuario.style.backgroundColor =
                    '#ffffff';

            }

        }


        status.addEventListener(
            'change',
            atualizarUsuario
        );


        atualizarUsuario();


    }
);

</script>


<?php


/*
==========================================================
PROCESSA SALVAMENTO
==========================================================
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['salvar'])
) {


    /*
    ======================================================
    RECEBE STATUS
    ======================================================
    */

    $status =
        $_POST['status_item'] ?? '';


    $usuario =
        trim(
            $_POST['usuario_item'] ?? ''
        );


    /*
    ======================================================
    VALIDA STATUS
    ======================================================
    */

    if (
        $status !== 'ESTOQUE' &&
        $status !== 'USO'
    ) {

        echo '

        <script>

        alert("Situação inválida!");

        history.back();

        </script>

        ';

        exit;

    }


    /*
    ======================================================
    SE ESTIVER NO ESTOQUE
    ======================================================
    */

    if ($status === 'ESTOQUE') {

        $usuario = '';

    }


    /*
    ======================================================
    ALTERAÇÃO MÚLTIPLA
    ======================================================
    */

    if ($modoMultiplo) {


        $connect->SQL->begin_transaction();


        try {


            foreach (
                $itensSelecionados
                as $itemSelecionado
            ) {


                $idAtual =
                    (int)$itemSelecionado['id_itens'];


                $statusAnterior =
                    $itemSelecionado['status_item'] ?? '';


                $usuarioAnterior =
                    $itemSelecionado['usuario_item'] ?? '';


                /*
                ==========================================
                ATUALIZA ITEM
                ==========================================
                */

                $sqlUpdate = "
                    UPDATE itens
                    SET
                        status_item = ?,
                        usuario_item = ?
                    WHERE id_itens = ?
                ";


                $stmtUpdate =
                    $connect->SQL->prepare(
                        $sqlUpdate
                    );


                if (!$stmtUpdate) {

                    throw new Exception(
                        'Erro ao preparar atualização.'
                    );

                }


                $stmtUpdate->bind_param(
                    "ssi",
                    $status,
                    $usuario,
                    $idAtual
                );


                if (
                    !$stmtUpdate->execute()
                ) {

                    throw new Exception(
                        'Erro ao atualizar item.'
                    );

                }


                /*
                ==========================================
                VERIFICA SE HOUVE ALTERAÇÃO
                ==========================================
                */

                if (
                    $statusAnterior !== $status ||
                    $usuarioAnterior !== $usuario
                ) {


                    if ($statusAnterior === 'ESTOQUE' && $status === 'USO') {

                        $descricao =
                            "Item entregue para: " .
                            $usuario;

                    } elseif ($statusAnterior === 'USO' && $status === 'ESTOQUE') {

                        $descricao =
                            "Item devolvido para estoque";

                    } else {

                        $descricao =
                            "Alteração do usuário responsável pelo item.";

                    }


                    $idUsuario =
                        $_SESSION['id_user']
                        ?? 0;


                    $descricaoHistorico =
                        $descricao .
                        "\n\nItem: " .
                        ($itemSelecionado['nome_item'] ?? '') .
                        "\nCódigo: " .
                        ($itemSelecionado['codigo_item'] ?? '') .
                        "\nLote: " .
                        ($itemSelecionado['lote'] ?? '');


                    /*
                    ======================================
                    HISTÓRICO
                    ======================================
                    */

                    $sqlHistorico = "
                        INSERT INTO historico_movimentacoes
                        (
                            id_item,
                            id_usuario,
                            acao,
                            descricao,
                            data_movimentacao
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            NOW()
                        )
                    ";


                    $stmtHistorico =
                        $connect->SQL->prepare(
                            $sqlHistorico
                        );


                    if (!$stmtHistorico) {

                        throw new Exception(
                            'Erro ao preparar histórico.'
                        );

                    }


                    if ($statusAnterior === 'ESTOQUE' && $status === 'USO') {

                        $acao = 'EM_USO';

                    } elseif ($statusAnterior === 'USO' && $status === 'ESTOQUE') {

                        $acao = 'DEVOLUCAO';

                    } else {

                        $acao = 'EDITOU';

                    }


                    $stmtHistorico->bind_param(
                        "iiss",
                        $idAtual,
                        $idUsuario,
                        $acao,
                        $descricaoHistorico
                    );


                    if (
                        !$stmtHistorico->execute()
                    ) {

                        throw new Exception(
                            'Erro ao registrar histórico.'
                        );

                    }

                }

            }


            /*
            ==========================================
            CONFIRMA TRANSAÇÃO
            ==========================================
            */

            $connect->SQL->commit();


            $_SESSION['sucesso'] =
                count($itensSelecionados) .
                ' item(ns) alterado(s) com sucesso.';


            header(
                'Location: ' . $paginaVoltar
            );

            exit;


        } catch (Exception $e) {


            /*
            ==========================================
            DESFAZ TRANSAÇÃO
            ==========================================
            */

            $connect->SQL->rollback();


            die(
                'Erro ao alterar os itens: ' .
                htmlspecialchars(
                    $e->getMessage(),
                    ENT_QUOTES,
                    'UTF-8'
                )
            );

        }


    }


    /*
    ======================================================
    ALTERAÇÃO INDIVIDUAL
    ======================================================
    */

    else {


        $idAtual =
            (int)$id;


        /*
        ==========================================
        ATUALIZA ITEM
        ==========================================
        */

        $sqlUpdate = "
            UPDATE itens
            SET
                status_item = ?,
                usuario_item = ?
            WHERE id_itens = ?
        ";


        $stmtUpdate =
            $connect->SQL->prepare(
                $sqlUpdate
            );


        if (!$stmtUpdate) {

            die(
                'Erro ao preparar atualização: ' .
                $connect->SQL->error
            );

        }


        $stmtUpdate->bind_param(
            "ssi",
            $status,
            $usuario,
            $idAtual
        );


        if (
            !$stmtUpdate->execute()
        ) {

            die(
                'Erro ao atualizar item: ' .
                $stmtUpdate->error
            );

        }


        /*
        ==========================================
        VERIFICA ALTERAÇÃO
        ==========================================
        */

        if (
            $status_anterior !== $status ||
            $usuario_anterior !== $usuario
        ) {


            if ($status === 'USO') {

                $descricao =
                    "Item entregue para: " .
                    $usuario;

            } else {

                $descricao =
                    "Item devolvido para estoque";

            }


            $idUsuario =
                $_SESSION['id_user']
                ?? 0;


            /*
            ==========================================
            HISTÓRICO
            ==========================================
            */

            $sqlHistorico = "
                INSERT INTO historico_movimentacoes
                (
                    id_item,
                    id_usuario,
                    acao,
                    descricao,
                    data_movimentacao
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW()
                )
            ";


            $stmtHistorico =
                $connect->SQL->prepare(
                    $sqlHistorico
                );


            if (!$stmtHistorico) {

                die(
                    'Erro ao preparar histórico: ' .
                    $connect->SQL->error
                );

            }


            if ($statusAnterior === 'ESTOQUE' && $status === 'USO') {

                        $acao = 'EM_USO';

                    } elseif ($statusAnterior === 'USO' && $status === 'ESTOQUE') {

                        $acao = 'DEVOLUCAO';

                    } else {

                        $acao = 'EDITOU';

                    }


            $stmtHistorico->bind_param(
                "iiss",
                $idAtual,
                $idUsuario,
                $acao,
                $descricao
            );


            if (
                !$stmtHistorico->execute()
            ) {

                die(
                    'Erro ao registrar histórico: ' .
                    $stmtHistorico->error
                );

            }

        }


        /*
        ==========================================
        RETORNA
        ==========================================
        */

        $_SESSION['sucesso'] =
            'Situação do item alterada com sucesso.';


        header(
            'Location: ' . $paginaVoltar
        );

        exit;

    }

}

?>
