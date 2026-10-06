<?php
require_once 'auth.php';
require_once 'Models/connect.php';
require_once 'itens_uso_helper.php';
require_once 'XlsxWriterSimples.php';

exigirAcesso("farmacia");

$periodo = $_GET['periodo'] ?? '30_dias';
$permitidos = ['hoje', '7_dias', '30_dias', 'este_mes', '3_meses', 'personalizado'];

if (!in_array($periodo, $permitidos, true)) {
    $periodo = '30_dias';
}

$dataInicial = trim($_GET['data_inicial'] ?? '');
$dataFinal = trim($_GET['data_final'] ?? '');

try {
    $datas = obterPeriodoItensUso($periodo, $dataInicial, $dataFinal);
    $itens = consultarItensEmUso($connect->SQL, $datas['inicio'], $datas['fim']);
} catch (Throwable $e) {
    http_response_code(400);
    exit('Não foi possível gerar o relatório: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function dataExportacao($data) {
    if (empty($data)) return '-';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '-';
}

function horaExportacao($data) {
    if (empty($data)) return '-';
    $ts = strtotime($data);
    return $ts ? date('H:i:s', $ts) : '-';
}

$writer = new XlsxWriterSimples();

$writer->addRow([
    'Item',
    'Código',
    'Lote',
    'Quantidade',
    'Local',
    'Responsável',
    'Data',
    'Hora',
    'Status'
]);

foreach ($itens as $item) {
    $writer->addRow([
        $item['nome_item'] ?? '',
        $item['codigo_item'] ?? '',
        $item['lote'] ?? '',
        (int)($item['quant_itens'] ?? 0),
        $item['local'] ?? '',
        $item['usuario_item'] ?? '',
        dataExportacao($item['data_uso'] ?? ''),
        horaExportacao($item['data_uso'] ?? ''),
        'EM USO'
    ]);
}

$periodoNome = $periodo === 'personalizado'
    ? ($dataInicial . '_a_' . $dataFinal)
    : $periodo;

$nomeArquivo = 'itens_em_uso_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $periodoNome) . '.xlsx';

$writer->output($nomeArquivo);
