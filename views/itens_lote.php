<?php

/*
==========================================================
AUTENTICAÇÃO
==========================================================
*/

require_once '../App/auth.php';
require_once '../App/Models/connect.php';
require_once '../layout/script.php';


/* A tela é exclusiva da Farmácia. */
$setor = 'farmacia';
$nomeSetor = 'Farmácia';
$paginaVoltar = 'index_farmacia.php';
$setorBanco = 'FARMACIA';

/*
==========================================================
VERIFICA LOTE
==========================================================
*/

if (
    !isset($_GET['lote']) ||
    trim($_GET['lote']) === ''
) {

    $_SESSION['erro'] =
        'Lote não informado.';

    header(
        'Location: ' . $paginaVoltar
    );

    exit;

}


$lote = trim($_GET['lote']);


/*
==========================================================
CONEXÃO
==========================================================
*/

$connect = new Connect();


/*
==========================================================
CONSULTA
==========================================================
*/

$sql = "

    SELECT *

    FROM itens

    WHERE lote = ?

    AND setor = ?

    AND ativo = 1

    ORDER BY nome_item ASC

";


$stmt = mysqli_prepare(
    $connect->SQL,
    $sql
);


if (!$stmt) {

    die(
        'Erro ao preparar consulta: ' .
        mysqli_error($connect->SQL)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    'ss',
    $lote,
    $setorBanco
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        'Erro ao executar consulta: ' .
        mysqli_stmt_error($stmt)
    );

}


$resultado = mysqli_stmt_get_result($stmt);


if (!$resultado) {

    die(
        'Erro ao obter resultado: ' .
        mysqli_error($connect->SQL)
    );

}





/*
==========================================================
TIPO DE ACESSO
==========================================================
*/

$tipoAcesso = $tipoAcesso ?? '';

?>



<style>

.table-responsive {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

.table-itens {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
    border: 2px solid #6f6f6f;
    background: #fff;
}

.table-itens th,
.table-itens td {
    border: 1px solid #8a8a8a;
    padding: 10px;
    vertical-align: middle;
    white-space: nowrap;
}

.table-itens thead {
    background: #f5f5f5;
}

.table-itens tbody tr:hover {
    background: #f8f9fa;
}

.table-itens tbody tr {
    border-bottom: 2px solid #6f6f6f;
}

.col-situacao {
    min-width: 180px;
    text-align: center;
}

.situacao-uso,
.situacao-estoque {
    margin-bottom: 10px;
}

.usuario-item {
    margin-top: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
    white-space: normal;
    color: #555;
}

.acao-status {
    margin-top: 10px;
}

.acao-status .btn {
    width: 100%;
}

.voltar {
    margin-top: 20px;
}

.voltar a {
    display: inline-block;
    padding: 8px 15px;
    background: #6c757d;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
}

.voltar a:hover {
    background: #5a6268;
    color: #fff;
    text-decoration: none;
}

@media (max-width: 768px) {

    .table-itens {
        min-width: 950px;
    }

    .table-itens th,
    .table-itens td {
        padding: 8px;
        font-size: 13px;
    }

}

@media (max-width: 480px) {

    .table-itens {
        min-width: 900px;
    }

    .table-itens th,
    .table-itens td {
        padding: 6px;
        font-size: 12px;
    }

}

/* ==========================================================
   SELEÇÃO DE ITENS
   ========================================================== */

.selecao-item {
    width: 45px;
    min-width: 45px;
    text-align: center;
}

.selecao-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.linha-selecionada {
    background-color: #fff8d6 !important;
}

.linha-vencida {
    background-color: #f8d7da !important;
}

.linha-quase-vencida {
    background-color: #fff3cd !important;
}

.filtro-vencimento {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.legenda-vencimento {
    font-size: 13px;
    color: #666;
}

.acoes-selecao {
    display: none;
    margin-bottom: 15px;
    padding: 12px 15px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.acoes-selecao.ativo {
    display: block;
}

.acoes-selecao .contador {
    font-weight: bold;
    margin-right: 15px;
}

.acoes-selecao .btn {
    margin-right: 5px;
}

.seletor-quantidade {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0 8px 0 3px;
    vertical-align: middle;
}

.seletor-quantidade label {
    margin: 0;
    font-weight: 600;
    white-space: nowrap;
}

.input-quantidade {
    width: 75px !important;
    height: 31px !important;
    display: inline-block !important;
    margin: 0 !important;
    padding: 4px 8px !important;
}

@media (max-width: 768px) {
    .seletor-quantidade {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
    }
}

</style>


<?php

/*
==========================================================
LAYOUT
==========================================================
*/

echo $css;
echo $head;
echo $header;
echo $aside;

?>


<div class="content-wrapper">

    <section class="content">


        <?php if (isset($_SESSION['erro'])): ?>

            <div class="alert alert-danger">

                <i class="fa fa-exclamation-circle"></i>

                <?= htmlspecialchars(
                    $_SESSION['erro'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>

            </div>

            <?php unset($_SESSION['erro']); ?>

        <?php endif; ?>


        <div class="box">

            <div class="box-header">

                <h3 class="box-title">

                    Itens do lote:

                    <strong>

                        <?= htmlspecialchars(
                            $lote,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </strong>

                    <small>

                        —
                        <?= htmlspecialchars(
                            $nomeSetor,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </small>

                </h3>

            </div>


            <div class="box-body">

                <div class="filtro-vencimento">
                    <button type="button" id="btnFiltroVencimento" class="btn btn-default btn-sm">
                        <i class="fa fa-calendar"></i> Mostrar próximos do vencimento
                    </button>
                    <span class="legenda-vencimento">
                        <span class="label label-danger">Vencido</span>
                        <span class="label label-warning">Próximo do vencimento</span>
                        (até 30 dias)
                    </span>
                </div>

    <?php if (true): ?>

        <div
            id="acoesSelecao"
            class="acoes-selecao"
        >

            <span class="contador">

                <span id="quantidadeSelecionada">0</span>

                item(ns) selecionado(s)

            </span>


            <button type="button" id="btnAlterarSelecionados" class="btn btn-primary btn-sm">
                <i class="fa fa-refresh"></i> Alterar situação
            </button>

            <button type="button" id="btnEditarSelecionados" class="btn btn-warning btn-sm">
                <i class="fa fa-pencil"></i> Editar selecionados
            </button>

            <button type="button" id="btnExcluirSelecionados" class="btn btn-danger btn-sm">
                <i class="fa fa-trash"></i> Excluir selecionados
            </button>

            <span class="seletor-quantidade">
                <label for="quantidadeParaSelecionar">Selecionar quantidade:</label>
                <input type="number" id="quantidadeParaSelecionar" min="1"
                       max="<?= max(1, mysqli_num_rows($resultado)); ?>"
                       value="1" class="form-control input-quantidade">
                <button type="button" id="btnSelecionarQuantidade" class="btn btn-info btn-sm">
                    Selecionar
                </button>
            </span>

            <button type="button" id="btnLimparSelecao" class="btn btn-default btn-sm">
                <i class="fa fa-times"></i> Limpar seleção
            </button>

        </div>

    <?php endif; ?>


    <div class="table-responsive">


                    <table class="table table-bordered table-hover table-itens">

                        <thead>

                           <tr>

    <?php if (true): ?>

        <th class="selecao-item">

            <input
                type="checkbox"
                id="selecionarTodos"
                title="Selecionar todos"
            >

        </th>

    <?php endif; ?>


    				<th>Código</th>
                                <th>Produto</th>
                                <th>Marca</th>
                                <th>Quantidade</th>
                                <th>Compra</th>
                                <th>Vencimento</th>
                                <th>Local</th>
                                <th>Editar</th>
                                <th>Excluir</th>
                                <th>Situação</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (mysqli_num_rows($resultado) === 0): ?>

                            <tr>

                                <td
    colspan="<?= 11; ?>"
    style="
        text-align:center;
        padding:30px;
        font-size:16px;
        color:#777;
    "
>

                                    ">

                                    <i class="fa fa-info-circle"></i>

                                    Nenhum item encontrado neste lote.

                                </td>

                            </tr>


                        <?php else: ?>


                            <?php while ($item = mysqli_fetch_assoc($resultado)): ?>

                                <?php
                                $classeVencimento = '';
                                $dataVencimentoItem = $item['data_vencimento'] ?? '';

                                if (!empty($dataVencimentoItem) && $dataVencimentoItem !== '0000-00-00') {
                                    try {
                                        $hoje = new DateTime('today');
                                        $vencimento = new DateTime($dataVencimentoItem);
                                        $diasParaVencer = (int)$hoje->diff($vencimento)->format('%r%a');

                                        if ($diasParaVencer < 0) {
                                            $classeVencimento = 'linha-vencida';
                                        } elseif ($diasParaVencer <= 30) {
                                            $classeVencimento = 'linha-quase-vencida';
                                        }
                                    } catch (Exception $e) {
                                        $classeVencimento = '';
                                    }
                                }
                                ?>

    <tr class="<?= $classeVencimento; ?>" data-vencimento-status="<?= $classeVencimento === 'linha-vencida' ? 'vencido' : ($classeVencimento === 'linha-quase-vencida' ? 'quase-vencido' : 'normal'); ?>">

        <?php if (true): ?>

            <td class="selecao-item">

                <input
                    type="checkbox"
                    class="checkbox-item"
                    value="<?= (int)$item['id_itens']; ?>"
                >

            </td>

        <?php endif; ?>


        <td>


                                        <?= htmlspecialchars(
                                            $item['codigo_item'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $item['nome_item'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $item['marca_item'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $item['quant_itens'] ?? '0',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?php

                                        $dataCompra =
                                            $item['data_compra'] ?? '';

                                        if (
                                            !empty($dataCompra) &&
                                            $dataCompra !== '0000-00-00'
                                        ) {

                                            $timestamp =
                                                strtotime($dataCompra);

                                            echo $timestamp
                                                ? date('d/m/Y', $timestamp)
                                                : '-';

                                        } else {

                                            echo '-';

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        $dataVencimento =
                                            $item['data_vencimento'] ?? '';

                                        if (
                                            !empty($dataVencimento) &&
                                            $dataVencimento !== '0000-00-00'
                                        ) {

                                            $timestamp =
                                                strtotime($dataVencimento);

                                            echo $timestamp
                                                ? date('d/m/Y', $timestamp)
                                                : '-';

                                        } else {

                                            echo '-';

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $item['local'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>

                                    </td>


                                    <?php if (
                                        true
                                    ): ?>


                                        <?php if (true): ?>

    <td>

        <a
            href="../App/editar_item.php?id=<?= (int)$item['id_itens']; ?>"
            class="btn btn-warning btn-sm"
        >

            <i class="fa fa-pencil"></i>
            Editar

        </a>

    </td>

<?php else: ?>

    <td>

        <span class="text-muted">

            <i class="fa fa-lock"></i>
            Somente leitura

        </span>

    </td>

<?php endif; ?>



                                        <?php if (true): ?>

    <td>

        <a
    href="../App/excluir_item.php?id=<?= (int)$item['id_itens']; ?>"
    class="btn btn-danger btn-sm"
    onclick="return confirm('Tem certeza que deseja excluir este item?');"
>

    <i class="fa fa-trash"></i>
    Excluir

</a>


    </td>

<?php else: ?>

    <td>

        <span class="text-muted">

            <i class="fa fa-lock"></i>
            Somente leitura

        </span>

    </td>

<?php endif; ?>



                                    <?php else: ?>


                                        <td>

                                            <span class="text-muted">

                                                <i class="fa fa-lock"></i>

                                                Não permitido

                                            </span>

                                        </td>


                                        <td>

                                            <span class="text-muted">

                                                <i class="fa fa-lock"></i>

                                                Não permitido

                                            </span>

                                        </td>


                                    <?php endif; ?>


                                    <td class="col-situacao">


                                        <?php if (
                                            ($item['status_item'] ?? '')
                                            === 'USO'
                                        ): ?>


                                            <div class="situacao-uso">

                                                <span class="label label-danger">

                                                    <i class="fa fa-user"></i>

                                                    Em uso

                                                </span>


                                                <div class="usuario-item">

                                                    <strong>
                                                        Com:
                                                    </strong>

                                                    <br>


                                                    <?php if (
                                                        !empty(
                                                            $item['usuario_item']
                                                        )
                                                    ): ?>

                                                        <?= htmlspecialchars(
                                                            $item['usuario_item'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>

                                                    <?php else: ?>

                                                        Usuário não informado

                                                    <?php endif; ?>

                                                </div>

                                            </div>


                                        <?php else: ?>


                                            <div class="situacao-estoque">

                                                <span class="label label-success">

                                                    <i class="fa fa-check"></i>

                                                    No estoque

                                                </span>

                                            </div>


                                        <?php endif; ?>


                                        <?php if (
                                            true
                                        ): ?>

                                            <?php if (true): ?>

    <div class="acao-status">

        <a
            href="../App/alterar_status.php?id=<?= (int)$item['id_itens']; ?>"
            class="btn btn-primary btn-xs"
        >

            <i class="fa fa-refresh"></i>

            Alterar

        </a>

    </div>

<?php else: ?>

    <div class="acao-status">

        <span class="text-muted">

            <i class="fa fa-lock"></i>

            Somente leitura

        </span>

    </div>

<?php endif; ?>


                                        <?php endif; ?>


                                    </td>

                                </tr>


                            <?php endwhile; ?>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>


                <div class="voltar">

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

    </section>

</div>


<?php

echo $footer;
echo $javascript;
?>
<script>
$(document).ready(function () {

    const selecionarTodos = $('#selecionarTodos');
    const acoesSelecao = $('#acoesSelecao');
    const quantidadeSelecionada = $('#quantidadeSelecionada');
    let ultimoIndiceSelecionado = null;

    function atualizarSelecao() {
        const selecionados = $('.checkbox-item:checked');
        quantidadeSelecionada.text(selecionados.length);

        acoesSelecao.toggleClass('ativo', selecionados.length > 0);

        $('.checkbox-item').each(function () {
            $(this).closest('tr').toggleClass(
                'linha-selecionada',
                $(this).is(':checked')
            );
        });

        const total = $('.checkbox-item').length;
        const totalSelecionados = selecionados.length;

        selecionarTodos.prop('checked', total > 0 && total === totalSelecionados);
        selecionarTodos.prop(
            'indeterminate',
            totalSelecionados > 0 && totalSelecionados < total
        );
    }

    function obterIdsSelecionados() {
        const ids = [];
        $('.checkbox-item:checked').each(function () {
            ids.push($(this).val());
        });
        return ids;
    }

    function criarFormulario(action, ids) {
        const formulario = $('<form>', { method: 'POST', action: action });

        ids.forEach(function(id) {
            $('<input>', {
                type: 'hidden',
                name: 'ids[]',
                value: id
            }).appendTo(formulario);
        });

        $('<input>', {
            type: 'hidden',
            name: 'origem_index',
            value: '<?= htmlspecialchars($setor, ENT_QUOTES, 'UTF-8'); ?>'
        }).appendTo(formulario);

        formulario.appendTo('body');
        formulario.submit();
    }

    selecionarTodos.on('change', function () {
        $('.checkbox-item').prop('checked', $(this).is(':checked'));
        ultimoIndiceSelecionado = null;
        atualizarSelecao();
    });

    /*
     * Clique normal: seleciona/desseleciona um item.
     * Shift + clique: seleciona o intervalo entre o último
     * item clicado e o item atual.
     */
    $(document).on('click', '.checkbox-item', function (event) {
        const checkboxes = $('.checkbox-item');
        const indiceAtual = checkboxes.index(this);

        if (event.shiftKey && ultimoIndiceSelecionado !== null) {
            const inicio = Math.min(ultimoIndiceSelecionado, indiceAtual);
            const fim = Math.max(ultimoIndiceSelecionado, indiceAtual);
            const marcar = $(this).is(':checked');

            for (let i = inicio; i <= fim; i++) {
                $(checkboxes[i]).prop('checked', marcar);
            }
        }

        ultimoIndiceSelecionado = indiceAtual;
        atualizarSelecao();
    });

    $('#btnLimparSelecao').on('click', function () {
        $('.checkbox-item').prop('checked', false);
        selecionarTodos.prop('checked', false);
        selecionarTodos.prop('indeterminate', false);
        ultimoIndiceSelecionado = null;
        atualizarSelecao();
    });

    /*
     * Ex.: digite 3 e clique em Selecionar.
     * Serão marcados os 3 primeiros itens da tabela.
     */
    $('#btnSelecionarQuantidade').on('click', function () {
        const total = $('.checkbox-item').length;
        const quantidade = parseInt($('#quantidadeParaSelecionar').val(), 10);

        if (!Number.isInteger(quantidade) || quantidade < 1) {
            alert('Informe uma quantidade válida maior que zero.');
            return;
        }

        if (quantidade > total) {
            alert(
                'A quantidade informada é maior que o número de itens disponíveis (' +
                total + ').'
            );
            return;
        }

        $('.checkbox-item').prop('checked', false);

        $('.checkbox-item').each(function(index) {
            if (index < quantidade) {
                $(this).prop('checked', true);
            }
        });

        ultimoIndiceSelecionado = quantidade - 1;
        atualizarSelecao();
    });

    $('#btnAlterarSelecionados').on('click', function () {
        const ids = obterIdsSelecionados();

        if (ids.length === 0) {
            alert('Selecione pelo menos um item.');
            return;
        }

        criarFormulario('../App/alterar_status.php', ids);
    });

    $('#btnEditarSelecionados').on('click', function () {
        const ids = obterIdsSelecionados();

        if (ids.length === 0) {
            alert('Selecione pelo menos um item.');
            return;
        }

        criarFormulario('../App/editar_itens_lote.php', ids);
    });

    $('#btnExcluirSelecionados').on('click', function () {
        const ids = obterIdsSelecionados();

        if (ids.length === 0) {
            alert('Selecione pelo menos um item.');
            return;
        }

        if (!confirm(
            'Você selecionou ' + ids.length +
            ' item(ns). Deseja continuar para a exclusão?'
        )) {
            return;
        }

        criarFormulario('../App/excluir_itens_lote.php', ids);
    });

    // Filtra somente os itens vencidos ou próximos do vencimento (até 30 dias).
    let filtroVencimentoAtivo = false;

    $('#btnFiltroVencimento').on('click', function () {
        filtroVencimentoAtivo = !filtroVencimentoAtivo;

        $('.table-itens tbody tr').each(function () {
            const status = $(this).attr('data-vencimento-status');

            if (!filtroVencimentoAtivo || status === 'vencido' || status === 'quase-vencido') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        if (filtroVencimentoAtivo) {
            $(this)
                .removeClass('btn-default')
                .addClass('btn-danger')
                .html('<i class="fa fa-calendar"></i> Mostrar todos os itens');
        } else {
            $(this)
                .removeClass('btn-danger')
                .addClass('btn-default')
                .html('<i class="fa fa-calendar"></i> Mostrar próximos do vencimento');
        }
    });

    atualizarSelecao();
});
</script>



