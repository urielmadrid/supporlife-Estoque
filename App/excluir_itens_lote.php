<?php
require_once "auth.php";
require_once "Models/connect.php";

$tipoAcesso = 'farmacia';
$paginaVoltar = '../views/index_farmacia.php';

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

$ph = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids)) . 's';
$values = $ids;
$setorBanco = 'FARMACIA';
$values[] = $setorBanco;

$stmt = $connect->SQL->prepare(
    "SELECT id_itens, codigo_item, nome_item, lote, marca_item
     FROM itens WHERE id_itens IN ($ph) AND setor = ?
     ORDER BY nome_item ASC"
);
$params = [$types];
foreach ($values as $k => $v) $params[] = &$values[$k];
call_user_func_array([$stmt, 'bind_param'], $params);
$stmt->execute();
$result = $stmt->get_result();

$itens = [];
while ($row = $result->fetch_assoc()) $itens[] = $row;

if (!$itens) {
    $_SESSION['erro'] = 'Nenhum item válido foi encontrado.';
    header('Location: ' . $paginaVoltar); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Excluir itens selecionados | SupportLife</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(135deg,#dc3545,#8b0000);font-family:"Segoe UI",sans-serif;padding:25px}
.card-exclusao{width:650px;max-width:100%;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 45px rgba(0,0,0,.3)}
.header{background:#dc3545;padding:30px;text-align:center;color:#fff}.body-card{padding:30px}
.lista-itens{max-height:280px;overflow:auto}.item-lote{background:#f8f9fa;border:1px solid #ddd;border-radius:10px;padding:10px;margin-bottom:7px}
.alerta{background:#fff3cd;border:1px solid #ffecb5;border-radius:10px;padding:14px;margin:20px 0}
</style>
</head>
<body>
<div class="card-exclusao">
<div class="header">
<i class="bi bi-trash3-fill" style="font-size:45px"></i>
<h2>Excluir itens selecionados</h2>
<p class="mb-0"><?= count($itens); ?> item(ns) serão removidos</p>
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

<div class="alerta">
<i class="bi bi-exclamation-triangle-fill"></i>
<strong>Atenção:</strong> os itens selecionados serão excluídos definitivamente.
Esta ação não pode ser desfeita.
</div>

<form method="POST" action="Models/salvar_exclusao_lote.php">
<?php foreach ($itens as $item): ?>
<input type="hidden" name="ids[]" value="<?= (int)$item['id_itens']; ?>">
<?php endforeach; ?>
<input type="hidden" name="origem_index" value="<?= htmlspecialchars($tipoAcesso, ENT_QUOTES, 'UTF-8'); ?>">

<label class="form-label fw-bold">Senha</label>
<input type="password" name="password" class="form-control mb-3" required placeholder="Digite sua senha para confirmar">

<button type="submit" class="btn btn-danger w-100" style="height:50px;font-weight:bold"
onclick="return confirm('Confirma a exclusão definitiva de <?= count($itens); ?> item(ns)?');">
<i class="bi bi-trash3-fill"></i> Excluir itens selecionados
</button>

<a href="<?= htmlspecialchars($paginaVoltar, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary w-100 mt-2">
<i class="bi bi-arrow-left"></i> Cancelar
</a>
</form>
</div>
</div>
</body>
</html>
