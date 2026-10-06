<?php
require_once '../App/auth.php';
require_once '../App/Models/connect.php';
require_once '../layout/script.php';
require_once '../App/itens_uso_helper.php';

exigirAcesso("farmacia");

$connect = new Connect();
$db = $connect->SQL;

function hUso($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dataUsoBR($data) {
    if (empty($data)) return '-';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '-';
}

function horaUsoBR($data) {
    if (empty($data)) return '-';
    $ts = strtotime($data);
    return $ts ? date('H:i:s', $ts) : '-';
}

$periodo = $_GET['periodo'] ?? '30_dias';
$permitidos = ['hoje', '7_dias', '30_dias', 'este_mes', '3_meses', 'personalizado'];

if (!in_array($periodo, $permitidos, true)) {
    $periodo = '30_dias';
}

$dataInicial = trim($_GET['data_inicial'] ?? '');
$dataFinal = trim($_GET['data_final'] ?? '');
$erro = '';

try {
    $datas = obterPeriodoItensUso($periodo, $dataInicial, $dataFinal);
    $itensEmUso = consultarItensEmUso($db, $datas['inicio'], $datas['fim']);
} catch (Throwable $e) {
    $datas = ['inicio' => '', 'fim' => ''];
    $itensEmUso = [];
    $erro = $e->getMessage();
}

/*
 * Agrupa primeiro pelo texto digitado em "Com quem está?" (usuario_item).
 * Dentro de cada grupo, consolida os itens pelo nome, mantendo a soma
 * da quantidade. O HTML do botão/collapse permanece igual ao da primeira correção.
 */
$itensEmUsoPorResponsavel = [];

foreach ($itensEmUso as $item) {
    $responsavelGrupo = trim((string)($item['usuario_item'] ?? ''));
    if ($responsavelGrupo === '') {
        $responsavelGrupo = 'NÃO INFORMADO';
    }

    if (!isset($itensEmUsoPorResponsavel[$responsavelGrupo])) {
        $itensEmUsoPorResponsavel[$responsavelGrupo] = [
            'responsavel' => $responsavelGrupo,
            'quantidade' => 0,
            'itens' => []
        ];
    }

    $quantidade = max(0, (int)($item['quant_itens'] ?? 0));
    $itensEmUsoPorResponsavel[$responsavelGrupo]['quantidade'] += $quantidade;

    $nomeItem = trim((string)($item['nome_item'] ?? ''));
    $chaveItem = function_exists('mb_strtolower')
        ? mb_strtolower($nomeItem, 'UTF-8')
        : strtolower($nomeItem);
    if ($chaveItem === '') {
        $chaveItem = 'item_nao_informado';
        $nomeItem = 'ITEM NÃO INFORMADO';
    }

    if (!isset($itensEmUsoPorResponsavel[$responsavelGrupo]['itens'][$chaveItem])) {
        $itensEmUsoPorResponsavel[$responsavelGrupo]['itens'][$chaveItem] = [
            'nome_item' => $nomeItem,
            'quantidade' => 0
        ];
    }

    $itensEmUsoPorResponsavel[$responsavelGrupo]['itens'][$chaveItem]['quantidade'] += $quantidade;
}


echo $css;
echo $head;
echo $header;
echo $aside;
?>

<style>
.itens-uso-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    flex-wrap:wrap;
}

.filtro-uso {
    background:#f8f9fa;
    border:1px solid #e5e7eb;
    border-radius:10px;
    padding:15px;
    margin-bottom:20px;
}

.filtro-uso label {
    font-weight:600;
}

.tabela-uso th {
    white-space:nowrap;
    background:linear-gradient(135deg,#0d6efd,#dc3545);
    color:#fff;
    vertical-align:middle!important;
}

.tabela-uso td {
    vertical-align:middle!important;
}

.tabela-uso tbody tr:hover {
    background:#f7faff;
}

.periodo-personalizado {
    display:none;
}

.periodo-personalizado.ativo {
    display:block;
}

.badge-uso {
    display:inline-block;
    padding:6px 12px;
    border-radius:20px;
    background:#dc3545;
    color:#fff;
    font-weight:600;
    white-space:nowrap;
}

.local-uso {
    white-space:normal;
    min-width:160px;
}

.grupo-local-uso {
    margin-bottom:15px;
    border:1px solid #e5e7eb;
    border-radius:10px;
    overflow:hidden;
    background:#fff;
}

.grupo-local-uso:last-child {
    margin-bottom:0;
}

.grupo-local-uso-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    padding:14px 16px;
    background:#f8f9fa;
}

.grupo-local-uso-titulo {
    margin:0;
    color:#333;
    font-size:16px;
    font-weight:700;
}

.grupo-local-uso-itens {
    padding:10px 15px 15px;
    background:#fff;
}

.grupo-local-uso-itens .table {
    margin-bottom:0;
}

.lista-itens-compacta { padding: 8px 16px 10px; }
.item-uso-compacto { display:flex; justify-content:space-between; gap:15px; padding:9px 0; border-bottom:1px solid #eee; }
.item-uso-compacto:last-child { border-bottom:0; }
.item-uso-compacto span { color:#555; font-weight:600; white-space:nowrap; }

@media (max-width:768px) {
    .itens-uso-header {
        align-items:stretch;
    }

    .itens-uso-header .btn {
        width:100%;
    }

    .filtro-uso .btn {
        width:100%;
        margin-top:8px;
    }
}
</style>

<div class="content-wrapper">
    <section class="content">

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i>
                <?= hUso($erro) ?>
            </div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <div class="itens-uso-header">
                    <h3 class="box-title">
                        <i class="fa fa-user"></i> Itens em Uso
                    </h3>

                    <button
                        type="button"
                        class="btn btn-success"
                        data-toggle="modal"
                        data-target="#modalExportarUso"
                    >
                        <i class="fa fa-file-excel-o"></i> Exportar relatório
                    </button>
                </div>
            </div>

            <div class="box-body">

                <form method="get" class="filtro-uso">
                    <div class="row">

                        <div class="col-md-4">
                            <label for="periodo">Período da alteração para EM USO</label>
                            <select name="periodo" id="periodo" class="form-control">
                                <option value="hoje" <?= $periodo === 'hoje' ? 'selected' : '' ?>>Hoje</option>
                                <option value="7_dias" <?= $periodo === '7_dias' ? 'selected' : '' ?>>Últimos 7 dias</option>
                                <option value="30_dias" <?= $periodo === '30_dias' ? 'selected' : '' ?>>Últimos 30 dias</option>
                                <option value="este_mes" <?= $periodo === 'este_mes' ? 'selected' : '' ?>>Este mês</option>
                                <option value="3_meses" <?= $periodo === '3_meses' ? 'selected' : '' ?>>Últimos 3 meses</option>
                                <option value="personalizado" <?= $periodo === 'personalizado' ? 'selected' : '' ?>>Período personalizado</option>
                            </select>
                        </div>

                        <div class="col-md-6 periodo-personalizado <?= $periodo === 'personalizado' ? 'ativo' : '' ?>" id="camposPeriodo">
                            <div class="row">
                                <div class="col-sm-6">
                                    <label for="data_inicial">Data inicial</label>
                                    <input type="date" name="data_inicial" id="data_inicial" class="form-control" value="<?= hUso($dataInicial) ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label for="data_final">Data final</label>
                                    <input type="date" name="data_final" id="data_final" class="form-control" value="<?= hUso($dataFinal) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-filter"></i> Filtrar
                            </button>
                        </div>

                    </div>
                </form>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Exibindo itens que <strong>continuam EM USO</strong> e cuja última alteração para esse estado ocorreu no período selecionado.
                </div>

                <?php if (!$itensEmUsoPorResponsavel): ?>
                    <div class="alert alert-info text-center" style="margin-bottom:0;">
                        <i class="fa fa-info-circle"></i>
                        Nenhum item em uso encontrado para o período selecionado.
                    </div>
                <?php else: ?>
                    <?php foreach ($itensEmUsoPorResponsavel as $grupo): ?>
                        <div class="grupo-local-uso">
                            <div class="grupo-local-uso-header">
                                <h4 class="grupo-local-uso-titulo">
                                    <i class="fa fa-user"></i>
                                    <?= hUso($grupo['responsavel']) ?>
                                </h4>

                                <button
    type="button"
    class="btn btn-primary btn-sm btn-ver-itens"
    data-target="#itensLocal<?= md5($grupo['responsavel']) ?>"
>
    Ver itens
</button>
                            </div>

                            <div
                                id="itensLocal<?= md5($grupo['responsavel']) ?>"
                                class="collapse grupo-local-uso-itens"
                            >
                                <div class="lista-itens-compacta">
                                    <?php foreach ($grupo['itens'] as $itemAgrupado): ?>
                                        <div class="item-uso-compacto">
                                            <strong><?= hUso($itemAgrupado['nome_item']) ?></strong>
                                            <span><?= (int)$itemAgrupado['quantidade'] ?> un.</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>

    </section>
</div>

<div class="modal fade" id="modalExportarUso" tabindex="-1" role="dialog" aria-labelledby="modalExportarUsoLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <form method="get" action="../App/exportar_itens_uso.php">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalExportarUsoLabel">
                        <i class="fa fa-file-excel-o"></i> Exportar itens em uso
                    </h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="periodoExportacao">Período</label>
                        <select name="periodo" id="periodoExportacao" class="form-control">
                            <option value="hoje">Hoje</option>
                            <option value="7_dias">Últimos 7 dias</option>
                            <option value="30_dias" selected>Últimos 30 dias</option>
                            <option value="este_mes">Este mês</option>
                            <option value="3_meses">Últimos 3 meses</option>
                            <option value="personalizado">Período personalizado</option>
                        </select>
                    </div>

                    <div id="camposExportacao" style="display:none;">
                        <div class="row">
                            <div class="col-sm-6">
                                <label for="dataInicialExportacao">Data inicial</label>
                                <input type="date" name="data_inicial" id="dataInicialExportacao" class="form-control">
                            </div>
                            <div class="col-sm-6">
                                <label for="dataFinalExportacao">Data final</label>
                                <input type="date" name="data_final" id="dataFinalExportacao" class="form-control">
                            </div>
                        </div>
                    </div>

                    <p class="text-muted" style="margin-top:15px;margin-bottom:0;">
                        <i class="fa fa-clock-o"></i>
                        O relatório usará a data e hora registradas no histórico da alteração para <strong>EM USO</strong>.
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Gerar Excel
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
(function () {
    var periodo = document.getElementById('periodo');
    var campos = document.getElementById('camposPeriodo');

    function atualizarPeriodo() {
        if (!periodo || !campos) return;
        campos.classList.toggle('ativo', periodo.value === 'personalizado');
    }

    if (periodo) {
        periodo.addEventListener('change', atualizarPeriodo);
    }

    var periodoExportacao = document.getElementById('periodoExportacao');
    var camposExportacao = document.getElementById('camposExportacao');

    function atualizarExportacao() {
        if (!periodoExportacao || !camposExportacao) return;
        camposExportacao.style.display =
            periodoExportacao.value === 'personalizado' ? 'block' : 'none';
    }

    if (periodoExportacao) {
        periodoExportacao.addEventListener('change', atualizarExportacao);
        atualizarExportacao();
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-ver-itens').forEach(function (botao) {
        botao.addEventListener('click', function () {
            const alvo = document.querySelector(this.getAttribute('data-target'));

            if (!alvo) {
                console.error('Lista de itens não encontrada:', this.getAttribute('data-target'));
                return;
            }

            if (alvo.style.display === 'none' || !alvo.classList.contains('aberto')) {
                alvo.style.display = 'block';
                alvo.classList.add('aberto');
                this.textContent = 'Ocultar itens';
            } else {
                alvo.style.display = 'none';
                alvo.classList.remove('aberto');
                this.textContent = 'Ver itens';
            }
        });
    });
});
</script>
