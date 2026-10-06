<?php
require_once "auth.php";
require_once "Models/connect.php";

$tipoAcesso = 'farmacia';
$paginaVoltar = '../views/index_farmacia.php';

$ids = $_POST['ids'] ?? [];
if (!is_array($ids)) $ids = [];

$ids = array_values(array_unique(array_filter(
    array_map('intval', $ids),
    function ($id) { return $id > 0; }
)));

if (!$ids) {
    $_SESSION['erro'] = 'Nenhum item foi selecionado.';
    header('Location: ' . $paginaVoltar);
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$values = $ids;
$values[] = 'FARMACIA';
$types = str_repeat('i', count($ids)) . 's';

$sql = "SELECT id_itens, codigo_item, nome_item, lote
        FROM itens
        WHERE id_itens IN ($placeholders) AND setor = ?
        ORDER BY nome_item ASC";

$stmt = $connect->SQL->prepare($sql);
$params = [$types];
foreach ($values as $key => $value) $params[] = &$values[$key];
call_user_func_array([$stmt, 'bind_param'], $params);
$stmt->execute();
$resultado = $stmt->get_result();

$itens = [];
while ($row = $resultado->fetch_assoc()) $itens[] = $row;

if (!$itens) {
    $_SESSION['erro'] = 'Nenhum item válido foi encontrado.';
    header('Location: ' . $paginaVoltar);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar itens selecionados | SupportLife</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(135deg,#0d6efd,#dc3545);font-family:"Segoe UI",sans-serif;padding:25px}
.card-edicao{width:750px;max-width:100%;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 45px rgba(0,0,0,.25)}
.header{background:linear-gradient(90deg,#0056d6,#dc3545);padding:30px;text-align:center;color:#fff}
.body-card{padding:30px}.lista-itens{max-height:240px;overflow:auto;margin-bottom:25px}
.item-lote{background:#f8f9fa;border:1px solid #ddd;border-radius:10px;padding:10px;margin-bottom:7px}
.form-control{height:48px;border-radius:9px}
.aviso{font-size:14px;color:#555;background:#fff8d6;border:1px solid #ffe69c;border-radius:10px;padding:12px;margin-bottom:20px}
.btn-salvar{height:50px;width:100%;border:0;border-radius:10px;color:#fff;font-weight:bold;background:linear-gradient(90deg,#0056d6,#dc3545)}
</style>
</head>
<body>
<div class="card-edicao">
<div class="header">
<i class="bi bi-pencil-square" style="font-size:45px"></i>
<h2>Editar itens selecionados</h2>
<p class="mb-0">Alteração em <?= count($itens); ?> item(ns)</p>
</div>
<div class="body-card">
<div class="lista-itens">
<?php foreach ($itens as $item): ?>
<div class="item-lote">
<strong><?= htmlspecialchars($item['codigo_item'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
— <?= htmlspecialchars($item['nome_item'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
<small class="text-muted"> | Lote: <?= htmlspecialchars($item['lote'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
</div>
<?php endforeach; ?>
</div>

<div class="aviso">
<strong>Como funciona:</strong> preencha somente os campos que deseja alterar.
Os campos deixados em branco permanecem como estão em cada item.
</div>

<form method="POST" action="Models/salvar_edicao_lote.php">
<?php foreach ($itens as $item): ?>
<input type="hidden" name="ids[]" value="<?= (int)$item['id_itens']; ?>">
<?php endforeach; ?>
<input type="hidden" name="origem_index" value="<?= htmlspecialchars($tipoAcesso, ENT_QUOTES, 'UTF-8'); ?>">

<label class="form-label fw-bold">Código do Item</label>
<input type="text" name="codigo_item" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Nome do Item</label>
<input type="text" name="nome_item" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Lote / Compartimento</label>
<input type="text" name="lote" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Marca</label>
<input type="text" name="marca_item" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Local</label>
<input type="text" name="local" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Representante</label>
<input type="text" name="representante" class="form-control mb-3" placeholder="Deixe em branco para não alterar">

<label class="form-label fw-bold">Data da Compra</label>
<input type="date" name="data_compra" class="form-control mb-3">

<label class="form-label fw-bold">Data de Vencimento</label>
<input type="date" name="data_vencimento" class="form-control mb-3">

<label class="form-label fw-bold">Senha</label>
<input type="password" name="password" class="form-control mb-4" required placeholder="Digite sua senha para confirmar">

<button type="submit" class="btn-salvar"><i class="bi bi-save"></i> Salvar alterações nos itens</button>
<a href="<?= htmlspecialchars($paginaVoltar, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary w-100 mt-2"><i class="bi bi-arrow-left"></i> Cancelar</a>
</form>
</div>
</div>
</body>
</html>
