<link rel="icon" href="../favicon.ico" type="image/x-icon">

<?php
require_once '../App/auth.php';
require_once '../layout/script.php';
require_once '../App/Models/connect.php';

exigirAcesso("farmacia");
$_SESSION['origem_cadastro'] = 'farmacia';

$connect = new Connect();
$db = $connect->SQL;

/* ============================================================
   HELPERS
   ============================================================ */
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dataBR($data) {
    if (empty($data) || $data === '0000-00-00') return '-';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '-';
}

/* ============================================================
   REQUISIÇÕES
   A tabela é criada somente se ainda não existir.
   ============================================================ */
$requisicoesDisponiveis = true;
$erroRequisicao = '';

$criarTabela = mysqli_query($db, "
    CREATE TABLE IF NOT EXISTS requisicoes (
        id_requisicao INT AUTO_INCREMENT PRIMARY KEY,
        solicitante VARCHAR(150) NOT NULL,
        item VARCHAR(255) NOT NULL,
        quantidade INT NOT NULL DEFAULT 1,
        observacao TEXT NULL,
        status ENUM('PENDENTE','APROVADA','SEPARADA','CONCLUIDA','CANCELADA') NOT NULL DEFAULT 'PENDENTE',
        data_requisicao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        setor VARCHAR(50) NOT NULL DEFAULT 'FARMACIA'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if (!$criarTabela) {
    $requisicoesDisponiveis = false;
    $erroRequisicao = mysqli_error($db);
}

/* ============================================================
   AÇÕES DAS REQUISIÇÕES
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requisicoesDisponiveis) {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'nova_requisicao') {
        $solicitante = trim($_POST['solicitante'] ?? '');
        $item = trim($_POST['item'] ?? '');
        $quantidade = (int)($_POST['quantidade'] ?? 0);
        $observacao = trim($_POST['observacao'] ?? '');

        if ($solicitante === '' || $item === '' || $quantidade < 1) {
            $_SESSION['erro_requisicao'] = 'Preencha solicitante, item e uma quantidade válida.';
        } else {
            $stmtReq = mysqli_prepare($db, "
                INSERT INTO requisicoes (solicitante, item, quantidade, observacao, setor)
                VALUES (?, ?, ?, ?, 'FARMACIA')
            ");

            if ($stmtReq) {
                mysqli_stmt_bind_param($stmtReq, 'ssis', $solicitante, $item, $quantidade, $observacao);
                if (mysqli_stmt_execute($stmtReq)) {
                    $_SESSION['sucesso_requisicao'] = 'Requisição criada com sucesso.';
                } else {
                    $_SESSION['erro_requisicao'] = 'Não foi possível criar a requisição.';
                }
                mysqli_stmt_close($stmtReq);
            } else {
                $_SESSION['erro_requisicao'] = 'Erro ao preparar a requisição.';
            }
        }

        header('Location: index_farmacia.php?aba=requisicoes');
        exit;
    }

    if ($acao === 'alterar_status_requisicao') {
        $id = (int)($_POST['id_requisicao'] ?? 0);
        $status = $_POST['status'] ?? '';
        $statusPermitidos = ['PENDENTE', 'APROVADA', 'SEPARADA', 'CONCLUIDA', 'CANCELADA'];

        if ($id > 0 && in_array($status, $statusPermitidos, true)) {
            $stmtStatus = mysqli_prepare($db, "
                UPDATE requisicoes
                SET status = ?
                WHERE id_requisicao = ?
                  AND setor = 'FARMACIA'
            ");

            if ($stmtStatus) {
                mysqli_stmt_bind_param($stmtStatus, 'si', $status, $id);
                mysqli_stmt_execute($stmtStatus);
                mysqli_stmt_close($stmtStatus);
            }
        }

        header('Location: index_farmacia.php?aba=requisicoes');
        exit;
    }

    if ($acao === 'excluir_requisicao') {
        $id = (int)($_POST['id_requisicao'] ?? 0);

        if ($id > 0) {
            $stmtExcluir = mysqli_prepare($db, "
                DELETE FROM requisicoes
                WHERE id_requisicao = ?
                  AND setor = 'FARMACIA'
            " );

            if ($stmtExcluir) {
                mysqli_stmt_bind_param($stmtExcluir, 'i', $id);

                if (mysqli_stmt_execute($stmtExcluir)) {
                    if (mysqli_stmt_affected_rows($stmtExcluir) > 0) {
                        $_SESSION['sucesso_requisicao'] = 'Requisição excluída com sucesso.';
                    } else {
                        $_SESSION['erro_requisicao'] = 'Requisição não encontrada.';
                    }
                } else {
                    $_SESSION['erro_requisicao'] = 'Não foi possível excluir a requisição.';
                }
                mysqli_stmt_close($stmtExcluir);
            } else {
                $_SESSION['erro_requisicao'] = 'Erro ao preparar a exclusão da requisição.';
            }
        } else {
            $_SESSION['erro_requisicao'] = 'Requisição inválida.';
        }

        header('Location: index_farmacia.php?aba=requisicoes');
        exit;
    }
}

/* Agora que todas as ações POST foram processadas, os cabeçalhos ainda podem ser enviados. */
echo $css;
echo $head;
echo $header;
echo $aside;

if (isset($_SESSION['sucesso_requisicao'])) {
    $mensagemSucessoReq = $_SESSION['sucesso_requisicao'];
    unset($_SESSION['sucesso_requisicao']);
} else {
    $mensagemSucessoReq = '';
}

if (isset($_SESSION['erro_requisicao'])) {
    $mensagemErroReq = $_SESSION['erro_requisicao'];
    unset($_SESSION['erro_requisicao']);
} else {
    $mensagemErroReq = '';
}

$abaParam = $_GET['aba'] ?? 'dashboard';
$aba = in_array($abaParam, ['dashboard', 'produtos', 'requisicoes'], true) ? $abaParam : 'dashboard';
$pesquisa = trim($_GET['pesquisa'] ?? '');
$termo = '%' . $pesquisa . '%';

/* ============================================================
   RESUMO DO ESTOQUE / VENCIMENTOS
   90 dias = próximos 3 meses.
   30 dias = próximos 30 dias.
   ============================================================ */
$stats = [
    'registros' => 0,
    'quantidade' => 0,
    'lotes_90' => 0,
    'qtd_90' => 0,
    'lotes_30' => 0,
    'qtd_30' => 0,
    'lotes_vencidos' => 0,
    'qtd_vencidos' => 0,
    'em_uso' => 0,
];

$sqlStats = "
    SELECT
        COUNT(*) AS registros,
        COALESCE(SUM(quant_itens),0) AS quantidade,
        COUNT(DISTINCT CASE
            WHEN data_vencimento >= CURDATE()
             AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            THEN lote END) AS lotes_90,
        COALESCE(SUM(CASE
            WHEN data_vencimento >= CURDATE()
             AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
            THEN quant_itens ELSE 0 END),0) AS qtd_90,
        COUNT(DISTINCT CASE
            WHEN data_vencimento >= CURDATE()
             AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            THEN lote END) AS lotes_30,
        COALESCE(SUM(CASE
            WHEN data_vencimento >= CURDATE()
             AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            THEN quant_itens ELSE 0 END),0) AS qtd_30,
        COUNT(DISTINCT CASE
            WHEN data_vencimento < CURDATE()
             AND data_vencimento IS NOT NULL
            THEN lote END) AS lotes_vencidos,
        COALESCE(SUM(CASE
            WHEN data_vencimento < CURDATE()
             AND data_vencimento IS NOT NULL
            THEN quant_itens ELSE 0 END),0) AS qtd_vencidos,
        COALESCE(SUM(CASE
            WHEN status_item = 'USO'
            THEN quant_itens ELSE 0 END),0) AS em_uso
    FROM itens
    WHERE ativo = 1
      AND setor = 'FARMACIA'
";

$resStats = mysqli_query($db, $sqlStats);
if ($resStats && ($rowStats = mysqli_fetch_assoc($resStats))) {
    foreach ($stats as $key => $unused) {
        $stats[$key] = (int)($rowStats[$key] ?? 0);
    }
}

/* ============================================================
   LISTAS DE VENCIMENTO POR LOTE
   ============================================================ */
function buscarLotesVencimento($db, $condicao, $ordem = 'data_vencimento ASC') {
    $sql = "
        SELECT
            lote,
            MIN(data_vencimento) AS data_vencimento,
            SUM(quant_itens) AS quantidade,
            COUNT(*) AS itens,
            GROUP_CONCAT(DISTINCT codigo_item ORDER BY codigo_item SEPARATOR ', ') AS codigos,
            GROUP_CONCAT(DISTINCT nome_item ORDER BY nome_item SEPARATOR ' | ') AS nomes
        FROM itens
        WHERE ativo = 1
          AND setor = 'FARMACIA'
          AND lote IS NOT NULL
          AND lote <> ''
          AND ($condicao)
        GROUP BY lote
        ORDER BY $ordem
    ";
    $res = mysqli_query($db, $sql);
    return $res ?: false;
}

$res90 = buscarLotesVencimento(
    $db,
    "data_vencimento >= CURDATE() AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)"
);

$res30 = buscarLotesVencimento(
    $db,
    "data_vencimento >= CURDATE() AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
);

$resVencidos = buscarLotesVencimento(
    $db,
    "data_vencimento < CURDATE() AND data_vencimento IS NOT NULL",
    'data_vencimento DESC'
);

/* ============================================================
   LISTA DE PRODUTOS / LOTES
   ============================================================ */
$sqlProdutos = "
    SELECT
        lote AS origem,
        CASE
            WHEN COUNT(DISTINCT codigo_item) = 1 THEN MAX(codigo_item)
            ELSE CONCAT(MIN(codigo_item), ' ao ', MAX(codigo_item))
        END AS codigo_item,
        SUM(CASE WHEN status_item = 'ESTOQUE' THEN quant_itens ELSE 0 END) AS estoque,
        SUM(CASE WHEN status_item = 'USO' THEN quant_itens ELSE 0 END) AS em_uso,
        MIN(data_vencimento) AS proximo_vencimento,
        MAX(data_compra) AS data_compra,
        MAX(representante) AS representante,
        MAX(local) AS local
    FROM itens
    WHERE ativo = 1
      AND setor = 'FARMACIA'
      AND (
          codigo_item LIKE ?
          OR nome_item LIKE ?
          OR marca_item LIKE ?
          OR lote LIKE ?
          OR local LIKE ?
          OR representante LIKE ?
      )
    GROUP BY lote
    ORDER BY lote DESC
";

$stmtProd = mysqli_prepare($db, $sqlProdutos);
if ($stmtProd) {
    mysqli_stmt_bind_param($stmtProd, 'ssssss', $termo, $termo, $termo, $termo, $termo, $termo);
    mysqli_stmt_execute($stmtProd);
    $resultadoProdutos = mysqli_stmt_get_result($stmtProd);
} else {
    $resultadoProdutos = false;
}

/* ============================================================
   REQUISIÇÕES
   ============================================================ */
$requisicoes = false;
if ($requisicoesDisponiveis) {
    $resReq = mysqli_query($db, "
        SELECT id_requisicao, solicitante, item, quantidade, observacao, status, data_requisicao
        FROM requisicoes
        WHERE setor = 'FARMACIA'
        ORDER BY id_requisicao DESC
        LIMIT 100
    ");
    $requisicoes = $resReq ?: false;
}

$contReqPendentes = 0;
if ($requisicoesDisponiveis) {
    $resPend = mysqli_query($db, "SELECT COUNT(*) total FROM requisicoes WHERE setor='FARMACIA' AND status='PENDENTE'");
    if ($resPend && ($rp = mysqli_fetch_assoc($resPend))) $contReqPendentes = (int)$rp['total'];
}


/* ============================================================
   ALERTAS AO ENTRAR NO INDEX DA FARMÁCIA
   ============================================================ */
$itensVencidosPopup = [];

$resVencPopup = mysqli_query($db, "
    SELECT
        id_itens,
        codigo_item,
        nome_item,
        lote,
        quant_itens,
        data_vencimento,
        local
    FROM itens
    WHERE ativo = 1
      AND setor = 'FARMACIA'
      AND data_vencimento IS NOT NULL
      AND data_vencimento <> '0000-00-00'
      AND data_vencimento < CURDATE()
    ORDER BY data_vencimento ASC, nome_item ASC
");

if ($resVencPopup) {
    while ($itemVencPopup = mysqli_fetch_assoc($resVencPopup)) {
        $itensVencidosPopup[] = $itemVencPopup;
    }
}

/*
 * Requisições pendentes já são contabilizadas em $contReqPendentes.
 * Buscamos também os dados para exibir no popup.
 */
$requisicoesPopup = [];

if ($requisicoesDisponiveis && $contReqPendentes > 0) {
    $resReqPopup = mysqli_query($db, "
        SELECT
            id_requisicao,
            solicitante,
            item,
            quantidade,
            observacao,
            data_requisicao
        FROM requisicoes
        WHERE setor = 'FARMACIA'
          AND status = 'PENDENTE'
        ORDER BY id_requisicao DESC
        LIMIT 100
    ");

    if ($resReqPopup) {
        while ($reqPopup = mysqli_fetch_assoc($resReqPopup)) {
            $requisicoesPopup[] = $reqPopup;
        }
    }
}

/*
 * Os popups devem aparecer somente uma vez por login.
 * Atualizar/recarregar a página não exibe novamente.
 * O identificador do login é criado em App/session.php quando o
 * usuário autentica novamente.
 */
$mostrarAlertasEntrada = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $loginAtual = $_SESSION['login_alerta_id'] ?? '';
    $loginJaAvisado = $_SESSION['alertas_farmacia_exibidos_login'] ?? '';

    if ($loginAtual !== '' && $loginAtual !== $loginJaAvisado) {
        $mostrarAlertasEntrada = true;
        $_SESSION['alertas_farmacia_exibidos_login'] = $loginAtual;
    }
}
?>

<style>
    .farm-dashboard .small-box { border-radius: 6px; }
    .farm-dashboard .small-box h3 { font-size: 30px; }
    .farm-dashboard .dash-card { border-top: 3px solid #3c8dbc; }
    .farm-dashboard .dash-card.danger { border-top-color: #dd4b39; }
    .farm-dashboard .dash-card.warning { border-top-color: #f39c12; }
    .farm-dashboard .dash-card.info { border-top-color: #00c0ef; }
    .farm-dashboard .dash-card.success { border-top-color: #00a65a; }
    .vencido-row { background: #fcebea !important; }
    .proximo-row { background: #fff8df !important; }
    .badge-status { font-size: 11px; padding: 5px 8px; }
    .requisicao-form .form-control { margin-bottom: 10px; }
    .requisicao-item { color: #333 !important; background-color: #fff !important; font-weight: 600; }
    .requisicao-item strong { color: #222 !important; }
    .nav-tabs-custom > .nav-tabs > li.active { border-top-color: #3c8dbc; }
    .dashboard-title { margin-top: 0; }
    .table td, .table th { vertical-align: middle !important; }
    .lote-link { font-weight: 600; }


    /* ========================================================
       POPUPS DE ALERTA DA FARMÁCIA
       ======================================================== */
    .farm-alert-modal .modal-dialog {
        width: 650px;
        max-width: calc(100% - 30px);
        margin: 30px auto;
    }

    .farm-alert-modal .modal-content {
        border: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,.30);
    }

    .farm-alert-modal .modal-header {
        color: #fff;
        border-bottom: 0;
        padding: 18px 22px;
    }

    .farm-alert-modal .modal-header.danger {
        background: #dd4b39;
    }

    .farm-alert-modal .modal-header.warning {
        background: #f39c12;
    }

    .farm-alert-modal .modal-title {
        font-size: 20px;
        font-weight: 600;
    }

    .farm-alert-modal .modal-body {
        padding: 20px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .farm-alert-item,
    .farm-alert-request {
        background: #f8f9fa;
        border: 1px solid #e4e4e4;
        border-left: 4px solid #dd4b39;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
    }

    .farm-alert-request {
        border-left-color: #f39c12;
    }

    .farm-alert-item:last-child,
    .farm-alert-request:last-child {
        margin-bottom: 0;
    }

    .farm-alert-item .titulo,
    .farm-alert-request .titulo {
        font-weight: 700;
        margin-bottom: 5px;
        color: #333 !important;
        background-color: #fff !important;
        opacity: 1 !important;
    }

    .farm-alert-item,
    .farm-alert-request {
        color: #333 !important;
        background-color: #fff !important;
        opacity: 1 !important;
    }

    .farm-alert-item small,
    .farm-alert-request small {
        color: #666;
    }

    .farm-alert-modal .modal-footer {
        border-top: 1px solid #eee;
        padding: 14px 20px;
    }

    .farm-alert-modal .btn-alerta {
        min-width: 170px;
    }

    @media (max-width: 600px) {
        .farm-alert-modal .modal-dialog {
            margin: 15px auto;
        }

        .farm-alert-modal .modal-body {
            max-height: 65vh;
        }
    }
</style>

<div class="content-wrapper farm-dashboard">
    <section class="content">

        <?php if ($mensagemSucessoReq): ?>
            <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?= h($mensagemSucessoReq) ?></div>
        <?php endif; ?>

        <?php if ($mensagemErroReq): ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?= h($mensagemErroReq) ?></div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title dashboard-title"><i class="fa fa-dashboard"></i> Dashboard da Farmácia</h3>
            </div>
            <div class="box-body">
                <ul class="nav nav-tabs" style="margin-bottom:20px;">
                    <li class="<?= $aba === 'dashboard' ? 'active' : '' ?>">
                        <a href="index_farmacia.php?aba=dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="index_farmacia.php?aba=produtos"><i class="fa fa-cubes"></i> Produtos</a>
                    </li>
                    <li class="<?= $aba === 'requisicoes' ? 'active' : '' ?>">
                        <a href="index_farmacia.php?aba=requisicoes">
                            <i class="fa fa-file-text-o"></i> Requisições
                            <?php if ($contReqPendentes > 0): ?>
                                <span class="badge bg-yellow"><?= $contReqPendentes ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="itens_em_uso.php">
                            <i class="fa fa-user"></i> Itens em Uso
                        </a>
                    </li>
                </ul>

                <?php if ($aba === 'dashboard'): ?>

                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-aqua dash-card info">
                                <div class="inner">
                                    <h3><?= $stats['registros'] ?></h3>
                                    <p>Itens cadastrados</p>
                                    <small><?= number_format($stats['quantidade'], 0, ',', '.') ?> unidades</small>
                                </div>
                                <div class="icon"><i class="fa fa-cubes"></i></div>
                                <a href="index_farmacia.php?aba=produtos" class="small-box-footer">Ver produtos <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-yellow dash-card warning">
                                <div class="inner">
                                    <h3><?= $stats['lotes_90'] ?></h3>
                                    <p>Próximos 3 meses</p>
                                    <small><?= number_format($stats['qtd_90'], 0, ',', '.') ?> unidades em <?= $stats['lotes_90'] ?> lote(s)</small>
                                </div>
                                <div class="icon"><i class="fa fa-calendar"></i></div>
                                <a href="#vencimentos90" class="small-box-footer">Ver lotes <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-orange dash-card warning">
                                <div class="inner">
                                    <h3><?= $stats['lotes_30'] ?></h3>
                                    <p>Próximos 30 dias</p>
                                    <small><?= number_format($stats['qtd_30'], 0, ',', '.') ?> unidades em <?= $stats['lotes_30'] ?> lote(s)</small>
                                </div>
                                <div class="icon"><i class="fa fa-clock-o"></i></div>
                                <a href="#vencimentos30" class="small-box-footer">Ver lotes <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-red dash-card danger">
                                <div class="inner">
                                    <h3><?= number_format($stats['em_uso'], 0, ',', '.') ?></h3>
                                    <p>Itens em uso</p>
                                    <small>Unidades atualmente em uso</small>
                                </div>
                                <div class="icon"><i class="fa fa-user"></i></div>
                                <a href="itens_em_uso.php" class="small-box-footer">Ver itens em uso <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="small-box bg-red dash-card danger">
                                <div class="inner">
                                    <h3><?= $stats['lotes_vencidos'] ?></h3>
                                    <p>Vencidos</p>
                                    <small><?= number_format($stats['qtd_vencidos'], 0, ',', '.') ?> unidades em <?= $stats['lotes_vencidos'] ?> lote(s)</small>
                                </div>
                                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                                <a href="#vencidos" class="small-box-footer">Ver vencidos <i class="fa fa-arrow-circle-down"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="box box-warning" id="vencimentos90">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-calendar"></i> Lotes próximos do vencimento — próximos 3 meses</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr><th>Lote</th><th>Vencimento</th><th>Quantidade</th><th>Código(s)</th><th>Item</th><th>Ação</th></tr>
                                </thead>
                                <tbody>
                                <?php if (!$res90 || mysqli_num_rows($res90) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Nenhum lote vence nos próximos 3 meses.</td></tr>
                                <?php else: while ($lote = mysqli_fetch_assoc($res90)): ?>
                                    <tr class="<?= (strtotime($lote['data_vencimento']) - time() <= 30*86400) ? 'proximo-row' : '' ?>">
                                        <td><span class="lote-link"><?= h($lote['lote']) ?></span></td>
                                        <td><?= dataBR($lote['data_vencimento']) ?></td>
                                        <td><span class="label label-warning"><?= (int)$lote['quantidade'] ?></span></td>
                                        <td><?= h($lote['codigos']) ?></td>
                                        <td><?= h($lote['nomes']) ?></td>
                                        <td><a class="btn btn-primary btn-xs" href="itens_lote.php?lote=<?= urlencode($lote['lote']) ?>&setor=FARMACIA"><i class="fa fa-eye"></i> Ver lote</a></td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box box-warning" id="vencimentos30">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-clock-o"></i> Lotes com vencimento em até 30 dias</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr><th>Lote</th><th>Vencimento</th><th>Quantidade</th><th>Código(s)</th><th>Item</th><th>Ação</th></tr>
                                </thead>
                                <tbody>
                                <?php if (!$res30 || mysqli_num_rows($res30) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Nenhum lote vence nos próximos 30 dias.</td></tr>
                                <?php else: while ($lote = mysqli_fetch_assoc($res30)): ?>
                                    <tr class="proximo-row">
                                        <td><span class="lote-link"><?= h($lote['lote']) ?></span></td>
                                        <td><strong><?= dataBR($lote['data_vencimento']) ?></strong></td>
                                        <td><span class="label label-warning"><?= (int)$lote['quantidade'] ?></span></td>
                                        <td><?= h($lote['codigos']) ?></td>
                                        <td><?= h($lote['nomes']) ?></td>
                                        <td><a class="btn btn-primary btn-xs" href="itens_lote.php?lote=<?= urlencode($lote['lote']) ?>&setor=FARMACIA"><i class="fa fa-eye"></i> Ver lote</a></td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="box box-danger" id="vencidos">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Lotes vencidos</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr><th>Lote</th><th>Vencimento</th><th>Quantidade</th><th>Código(s)</th><th>Item</th><th>Ação</th></tr>
                                </thead>
                                <tbody>
                                <?php if (!$resVencidos || mysqli_num_rows($resVencidos) === 0): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Nenhum lote vencido.</td></tr>
                                <?php else: while ($lote = mysqli_fetch_assoc($resVencidos)): ?>
                                    <tr class="vencido-row">
                                        <td><span class="lote-link"><?= h($lote['lote']) ?></span></td>
                                        <td><strong class="text-danger"><?= dataBR($lote['data_vencimento']) ?></strong></td>
                                        <td><span class="label label-danger"><?= (int)$lote['quantidade'] ?></span></td>
                                        <td><?= h($lote['codigos']) ?></td>
                                        <td><?= h($lote['nomes']) ?></td>
                                        <td><a class="btn btn-danger btn-xs" href="itens_lote.php?lote=<?= urlencode($lote['lote']) ?>&setor=FARMACIA"><i class="fa fa-eye"></i> Ver lote</a></td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- LISTA DE ITENS/LOTES RESTAURADA NO DASHBOARD -->
                    <div class="box box-primary" id="lista-itens">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-cubes"></i> Produtos cadastrados</h3>
                            <div class="box-tools">
                                <form method="get" class="form-inline">
                                    <input type="hidden" name="aba" value="dashboard">
                                    <div class="input-group input-group-sm" style="width:280px">
                                        <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar lote, código, item..." value="<?= h($pesquisa) ?>">
                                        <span class="input-group-btn"><button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover table-produtos">
                                <thead>
                                    <tr>
                                        <th>Lote</th>
                                        <th>Código</th>
                                        <th>Em Estoque</th>
                                        <th>Em Uso</th>
                                        <th>Próximo Vencimento</th>
                                        <th>Local</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!$resultadoProdutos || mysqli_num_rows($resultadoProdutos) === 0): ?>
                                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px"><i class="fa fa-info-circle"></i> Nenhum produto encontrado.</td></tr>
                                <?php else: while ($produto = mysqli_fetch_assoc($resultadoProdutos)): ?>
                                    <?php
                                        $classeVenc = '';
                                        if (!empty($produto['proximo_vencimento']) && $produto['proximo_vencimento'] !== '0000-00-00') {
                                            $dias = (strtotime($produto['proximo_vencimento']) - strtotime(date('Y-m-d'))) / 86400;
                                            if ($dias < 0) $classeVenc = 'vencido-row';
                                            elseif ($dias <= 30) $classeVenc = 'proximo-row';
                                        }
                                    ?>
                                    <tr class="<?= $classeVenc ?>">
                                        <td><?= h($produto['origem']) ?></td>
                                        <td><?= h($produto['codigo_item']) ?></td>
                                        <td><span class="label label-success"><?= (int)$produto['estoque'] ?></span></td>
                                        <td><span class="label label-warning"><?= (int)$produto['em_uso'] ?></span></td>
                                        <td>
                                            <?php if ($classeVenc === 'vencido-row'): ?>
                                                <strong class="text-danger"><?= dataBR($produto['proximo_vencimento']) ?></strong>
                                            <?php elseif ($classeVenc === 'proximo-row'): ?>
                                                <strong class="text-warning"><?= dataBR($produto['proximo_vencimento']) ?></strong>
                                            <?php else: ?>
                                                <?= dataBR($produto['proximo_vencimento']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h($produto['local']) ?></td>
                                        <td>
                                            <a href="itens_lote.php?lote=<?= urlencode($produto['origem']) ?>&setor=FARMACIA" class="btn btn-primary btn-sm">
                                                <i class="fa fa-eye"></i> Ver Itens
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($aba === 'requisicoes'): ?>

                    <?php if (!$requisicoesDisponiveis): ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-warning"></i>
                            Não foi possível habilitar as requisições. O banco recusou a criação da tabela.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="box box-primary requisicao-form">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><i class="fa fa-plus"></i> Nova requisição</h3>
                                    </div>
                                    <form method="post">
                                        <div class="box-body">
                                            <input type="hidden" name="acao" value="nova_requisicao">
                                            <label>Solicitante</label>
                                            <input type="text" name="solicitante" class="form-control" maxlength="150" required>
                                            <label>Item / medicamento</label>
                                            <input type="text" name="item" class="form-control" maxlength="255" required>
                                            <label>Quantidade</label>
                                            <input type="number" name="quantidade" class="form-control" min="1" value="1" required>
                                            <label>Observação</label>
                                            <textarea name="observacao" class="form-control" rows="4" placeholder="Opcional"></textarea>
                                        </div>
                                        <div class="box-footer">
                                            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-send"></i> Criar requisição</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title"><i class="fa fa-list"></i> Requisições da Farmácia</h3>
                                    </div>
                                    <div class="box-body table-responsive no-padding">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr><th>#</th><th>Solicitante</th><th>Item</th><th>Qtd.</th><th>Data</th><th>Status</th><th>Ação</th></tr>
                                            </thead>
                                            <tbody>
                                            <?php if (!$requisicoes || mysqli_num_rows($requisicoes) === 0): ?>
                                                <tr><td colspan="7" class="text-center text-muted" style="padding:30px">Nenhuma requisição cadastrada.</td></tr>
                                            <?php else: while ($req = mysqli_fetch_assoc($requisicoes)): ?>
                                                <?php
                                                    $classeStatus = 'label-default';
                                                    if ($req['status'] === 'PENDENTE') $classeStatus = 'label-warning';
                                                    if ($req['status'] === 'APROVADA') $classeStatus = 'label-info';
                                                    if ($req['status'] === 'SEPARADA') $classeStatus = 'label-primary';
                                                    if ($req['status'] === 'CONCLUIDA') $classeStatus = 'label-success';
                                                    if ($req['status'] === 'CANCELADA') $classeStatus = 'label-danger';
                                                ?>
                                                <tr>
                                                    <td><?= (int)$req['id_requisicao'] ?></td>
                                                    <td><?= h($req['solicitante']) ?></td>
                                                    <td>
                                                        <strong class="requisicao-item"><?= h($req['item']) ?></strong>
                                                        <?php if (!empty($req['observacao'])): ?><br><small class="text-muted"><?= h($req['observacao']) ?></small><?php endif; ?>
                                                    </td>
                                                    <td><?= (int)$req['quantidade'] ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($req['data_requisicao'])) ?></td>
                                                    <td><span class="label <?= $classeStatus ?> badge-status"><?= h($req['status']) ?></span></td>
                                                    <td>
                                                        <div style="display:flex; align-items:center; gap:5px; flex-wrap:wrap;">
                                                            <form method="post" style="margin:0;">
                                                                <input type="hidden" name="acao" value="alterar_status_requisicao">
                                                                <input type="hidden" name="id_requisicao" value="<?= (int)$req['id_requisicao'] ?>">
                                                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                                                    <?php foreach (['PENDENTE','APROVADA','SEPARADA','CONCLUIDA','CANCELADA'] as $status): ?>
                                                                        <option value="<?= $status ?>" <?= $req['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </form>
                                                            <form method="post" style="margin:0;" onsubmit="return confirm('Tem certeza que deseja excluir esta requisição?');">
                                                                <input type="hidden" name="acao" value="excluir_requisicao">
                                                                <input type="hidden" name="id_requisicao" value="<?= (int)$req['id_requisicao'] ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" title="Excluir requisição">
                                                                    <i class="fa fa-trash"></i> Excluir
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <?php /* Produtos */ ?>
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-cubes"></i> Produtos cadastrados por lote</h3>
                            <div class="box-tools">
                                <form method="get" class="form-inline">
                                    <input type="hidden" name="aba" value="produtos">
                                    <div class="input-group input-group-sm" style="width:280px">
                                        <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar..." value="<?= h($pesquisa) ?>">
                                        <span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover table-produtos">
                                <thead><tr><th>Lote</th><th>Código</th><th>Em estoque</th><th>Em uso</th><th>Próximo vencimento</th><th>Local</th><th>Ações</th></tr></thead>
                                <tbody>
                                <?php if (!$resultadoProdutos || mysqli_num_rows($resultadoProdutos) === 0): ?>
                                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px"><i class="fa fa-info-circle"></i> Nenhum produto encontrado.</td></tr>
                                <?php else: while ($produto = mysqli_fetch_assoc($resultadoProdutos)): ?>
                                    <?php
                                        $classeVenc = '';
                                        if (!empty($produto['proximo_vencimento']) && $produto['proximo_vencimento'] !== '0000-00-00') {
                                            $dias = (strtotime($produto['proximo_vencimento']) - strtotime(date('Y-m-d'))) / 86400;
                                            if ($dias < 0) $classeVenc = 'vencido-row';
                                            elseif ($dias <= 30) $classeVenc = 'proximo-row';
                                        }
                                    ?>
                                    <tr class="<?= $classeVenc ?>">
                                        <td><?= h($produto['origem']) ?></td>
                                        <td><?= h($produto['codigo_item']) ?></td>
                                        <td><span class="label label-success"><?= (int)$produto['estoque'] ?></span></td>
                                        <td><span class="label label-warning"><?= (int)$produto['em_uso'] ?></span></td>
                                        <td><?= dataBR($produto['proximo_vencimento']) ?></td>
                                        <td><?= h($produto['local']) ?></td>
                                        <td><a href="itens_lote.php?lote=<?= urlencode($produto['origem']) ?>&setor=FARMACIA" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Ver itens</a></td>
                                    </tr>
                                <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>


<?php if ($mostrarAlertasEntrada && (count($itensVencidosPopup) > 0 || count($requisicoesPopup) > 0)): ?>

<!-- ==========================================================
     POPUP: ITENS VENCIDOS
     ========================================================== -->
<?php if (count($itensVencidosPopup) > 0): ?>
<div class="modal fade farm-alert-modal" id="popupItensVencidos" tabindex="-1" role="dialog" aria-labelledby="popupItensVencidosTitulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header danger">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="popupItensVencidosTitulo">
                    <i class="fa fa-exclamation-triangle"></i>
                    Itens vencidos
                </h4>
            </div>

            <div class="modal-body">
                <p>
                    Existem <strong><?= count($itensVencidosPopup) ?></strong>
                    item(ns) vencido(s) no estoque da farmácia:
                </p>

                <?php foreach ($itensVencidosPopup as $itemVencPopup): ?>
                    <div class="farm-alert-item">
                        <div class="titulo">
                            <?= h($itemVencPopup['nome_item']) ?>
                        </div>

                        <small>
                            <strong>Código:</strong> <?= h($itemVencPopup['codigo_item']) ?>
                            &nbsp; | &nbsp;
                            <strong>Lote:</strong> <?= h($itemVencPopup['lote']) ?>
                            <br>

                            <strong>Vencimento:</strong>
                            <span class="text-danger">
                                <?= dataBR($itemVencPopup['data_vencimento']) ?>
                            </span>

                            &nbsp; | &nbsp;

                            <strong>Quantidade:</strong>
                            <?= (int)$itemVencPopup['quant_itens'] ?>

                            <?php if (!empty($itemVencPopup['local'])): ?>
                                <br>
                                <strong>Local:</strong> <?= h($itemVencPopup['local']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-alerta" data-dismiss="modal">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ==========================================================
     POPUP: NOVAS REQUISIÇÕES
     ========================================================== -->
<?php if (count($requisicoesPopup) > 0): ?>
<div class="modal fade farm-alert-modal" id="popupNovasRequisicoes" tabindex="-1" role="dialog" aria-labelledby="popupNovasRequisicoesTitulo">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header warning">
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="popupNovasRequisicoesTitulo">
                    <i class="fa fa-file-text-o"></i>
                    Novas requisições
                </h4>
            </div>

            <div class="modal-body">
                <p>
                    Há <strong><?= count($requisicoesPopup) ?></strong>
                    requisição(ões) pendente(s) aguardando atendimento:
                </p>

                <?php foreach ($requisicoesPopup as $reqPopup): ?>
                    <div class="farm-alert-request">
                        <div class="titulo requisicao-item">
                            <?= h($reqPopup['item']) ?>
                        </div>

                        <small>
                            <strong>Solicitante:</strong>
                            <?= h($reqPopup['solicitante']) ?>

                            &nbsp; | &nbsp;

                            <strong>Quantidade:</strong>
                            <?= (int)$reqPopup['quantidade'] ?>

                            <br>

                            <strong>Data:</strong>
                            <?= date('d/m/Y H:i', strtotime($reqPopup['data_requisicao'])) ?>

                            <?php if (!empty($reqPopup['observacao'])): ?>
                                <br>
                                <strong>Observação:</strong>
                                <?= h($reqPopup['observacao']) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-footer">
                <a href="index_farmacia.php?aba=requisicoes" class="btn btn-warning btn-alerta">
                    <i class="fa fa-file-text-o"></i> Ver requisições
                </a>
                <button type="button" class="btn btn-default btn-alerta" data-dismiss="modal">
                    Continuar
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<?php endif; ?>

<?php
if (isset($stmtProd) && $stmtProd) mysqli_stmt_close($stmtProd);
echo $footer;
echo $javascript;
?>

<script>
// O jQuery e o Bootstrap são carregados em $javascript acima.
// Por isso este código precisa ficar depois deles.
jQuery(function ($) {
    var temVencidos = <?= count($itensVencidosPopup) > 0 ? 'true' : 'false' ?>;
    var temRequisicoes = <?= count($requisicoesPopup) > 0 ? 'true' : 'false' ?>;

    if (temVencidos && $('#popupItensVencidos').length) {
        $('#popupItensVencidos').modal('show');

        $('#popupItensVencidos').one('hidden.bs.modal', function () {
            if (temRequisicoes && $('#popupNovasRequisicoes').length) {
                $('#popupNovasRequisicoes').modal('show');
            }
        });
    } else if (temRequisicoes && $('#popupNovasRequisicoes').length) {
        $('#popupNovasRequisicoes').modal('show');
    }
});
</script>
</body>
</html>
