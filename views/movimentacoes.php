<link rel="icon" href="favicon.ico" type="image/x-icon">

<?php

require_once '../App/auth.php';
require_once "../App/Models/connect.php";

if (!isset($_SESSION['movimentacoes_autorizado']) || $_SESSION['movimentacoes_autorizado'] !== true) {
    header("Location: validar_senha.php");
    exit;
}

$sql = $connect->SQL;

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$usuario = $_GET['usuario'] ?? '';
$item = $_GET['item'] ?? '';
$acao = $_GET['acao'] ?? '';
$local = $_GET['local'] ?? '';
$setor = 'FARMACIA';
$descricao = $_GET['descricao'] ?? '';

$where = [];

if ($data_inicio != '') {
    $data_inicio = mysqli_real_escape_string($sql, $data_inicio);
    $where[] = "DATE(h.data_movimentacao) >= '$data_inicio'";
}

if ($data_fim != '') {
    $data_fim = mysqli_real_escape_string($sql, $data_fim);
    $where[] = "DATE(h.data_movimentacao) <= '$data_fim'";
}

if ($usuario != '') {
    $usuario = (int)$usuario;
    $where[] = "h.id_usuario = $usuario";
}

if ($item != '') {
    $item = (int)$item;
    $where[] = "h.id_item = $item";
}

if ($acao != '') {
    $acao = mysqli_real_escape_string($sql, $acao);
    if ($acao == 'Entrada') {
        $where[] = "UPPER(TRIM(h.acao)) IN ('ENTRADA', 'CADASTRO')";
    } elseif ($acao == 'Saída') {
        $where[] = "UPPER(TRIM(h.acao)) IN ('SAIDA', 'SAÍDA')";
    } elseif ($acao == 'Em uso') {
        $where[] = "UPPER(TRIM(h.acao)) = 'EM_USO'";
    } elseif ($acao == 'Devolução') {
        $where[] = "UPPER(TRIM(h.acao)) = 'DEVOLUCAO'";
    } elseif ($acao == 'Alteração') {
        $where[] = "UPPER(TRIM(h.acao)) = 'EDITOU'";
    } elseif ($acao == 'Excluiu') {
        $where[] = "UPPER(TRIM(h.acao)) IN ('EXCLUIU', 'EXCLUI')";
    }
}

if ($local != '') {
    $local = mysqli_real_escape_string($sql, $local);
    $where[] = "i.local = '$local'";
}

if ($descricao != '') {
    $descricao = mysqli_real_escape_string($sql, $descricao);
    $where[] = "h.descricao LIKE '%$descricao%'";
}

$where[] = "i.setor = 'FARMACIA'";
$where_sql = '';

if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

$query = "
SELECT
    h.*,
    u.username,
    i.nome_item,
    i.codigo_item,
    i.local,
    i.setor

FROM historico_movimentacoes h

LEFT JOIN usuario u
    ON u.id_user = h.id_usuario

LEFT JOIN itens i
    ON i.id_itens = h.id_item

$where_sql

ORDER BY h.data_movimentacao DESC
";

$historico = mysqli_query($sql, $query);

if (!$historico) {
    die("Erro na consulta: " . mysqli_error($sql));
}

?>
<link rel="icon" href="favicon.ico" type="image/x-icon">

<style>

.voltar{
    margin-bottom:20px;
}

.voltar a{
    display:inline-flex;
    align-items:center;
    gap:8px;

    padding:10px 18px;

    background:linear-gradient(
        135deg,
        #0d6efd,
        #dc3545
    );

    color:#fff;

    text-decoration:none;

    font-size:14px;
    font-weight:600;

    border-radius:10px;

    box-shadow:0 5px 15px rgba(13,110,253,.20);

    transition:all .25s ease;
}

.voltar a i{
    font-size:18px;
}

.voltar a:hover{
    color:#fff;
    text-decoration:none;

    transform:translateY(-2px);

    box-shadow:0 8px 20px rgba(13,110,253,.30);

    background:linear-gradient(
        135deg,
        #0b5ed7,
        #bb2d3b
    );
}

.voltar a:active{
    transform:translateY(0);

    box-shadow:0 3px 8px rgba(0,0,0,.15);
}

@media(max-width:576px){

    .voltar{
        margin-bottom:15px;
    }

    .voltar a{
        padding:9px 15px;
        font-size:13px;
    }

    .voltar a i{
        font-size:16px;
    }

}
.table-produtos{
    border-collapse:separate;
    border-spacing:0;
    width:100%;
}

.table-produtos thead th{
    background:linear-gradient(
        135deg,
        #0d6efd,
        #dc3545
    );
    color:#fff;
    padding:15px 12px;
    text-align:center;
    font-weight:700;
    border-right:1px solid rgba(255,255,255,.25);
    border-bottom:3px solid #0b5ed7;
}

.table-produtos thead th:last-child{
    border-right:none;
}

.table-produtos tbody td{
    padding:14px 12px;
    background:#fff;
    border-right:1px solid #e5eaf1;
    border-bottom:1px solid #e5eaf1;
    vertical-align:middle;
}

.table-produtos tbody td:last-child{
    border-right:none;
}

.table-produtos tbody tr{
    transition:all .2s ease;
}

.table-produtos tbody tr:hover td{
    background:#f4f8ff;
}

.table-produtos tbody tr:last-child td{
    border-bottom:none;
}

.table-produtos tbody td:first-child{
    font-weight:600;
    color:#475569;
    white-space:nowrap;
}

.table-produtos tbody td:nth-child(2){
    color:#0d6efd;
    font-weight:600;
}

.table-produtos tbody td:nth-child(3){
    text-align:center;
}

.table-produtos tbody td:nth-child(4){
    font-weight:600;
    color:#334155;
}

.table-produtos tbody td:nth-child(5){
    color:#64748b;
}

.table-produtos tbody td:nth-child(6){
    color:#64748b;
}

.table-produtos tbody td:nth-child(7){
    color:#475569;
}

.table-produtos tbody tr:hover{
    transform:scale(1.002);
    box-shadow:0 4px 12px rgba(13,110,253,.08);
}

.table-produtos .label{
    min-width:80px;
    text-align:center;
    box-shadow:0 2px 5px rgba(0,0,0,.08);
}

:root{
    --primary:#0d6efd;
    --primary-dark:#0b5ed7;
    --danger:#dc3545;
    --danger-dark:#bb2d3b;
    --success:#198754;
    --warning:#ffc107;
    --secondary:#6c757d;

    --white:#ffffff;
    --light:#f4f6f9;
    --border:#e8ecf3;
    --text:#2d3748;
    --text-light:#6c757d;

    --radius:16px;

    --shadow-sm:0 4px 12px rgba(0,0,0,.05);
    --shadow:0 8px 25px rgba(0,0,0,.08);
    --shadow-lg:0 15px 40px rgba(0,0,0,.12);
}

body{
    background:#eef2f7;
    color:var(--text);
    font-family:"Segoe UI",sans-serif;
}

/****************************
HEADER
*****************************/

.content-header{
    margin-bottom:20px;
}

.content-header h1{
    font-weight:700;
    color:#1d3557;
}

.content-header small{
    color:#7b8794;
    font-size:15px;
}

/****************************
BOX
*****************************/

.box{
    background:white;
    border-radius:18px;
    border:none;
    overflow:hidden;
    margin-bottom:28px;
    box-shadow:var(--shadow);
    transition:.25s;
}

.box:hover{
    box-shadow:var(--shadow-lg);
}

.box-header{
    border:none;
    padding:18px 25px;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--danger)
    );
}

.box-title{
    color:white!important;
    font-weight:700;
    font-size:20px;
}

.box-title i{
    margin-right:8px;
}

.box-body{
    padding:25px;
}

/****************************
SMALL BOX
*****************************/

.small-box{
    border-radius:18px;
    overflow:hidden;
    color:white;
    box-shadow:var(--shadow);
    transition:.25s;
}

.small-box:hover{
    transform:translateY(-5px);
    box-shadow:var(--shadow-lg);
}

.small-box .inner{
    padding:22px;
}

.small-box h3{
    font-size:38px;
    font-weight:700;
}

.small-box p{
    font-size:15px;
}

.small-box .icon{
    top:15px;
    right:15px;
    opacity:.18;
    font-size:70px;
}

.bg-aqua{
    background:linear-gradient(135deg,#0d6efd,#3b82f6)!important;
}

.bg-green{
    background:linear-gradient(135deg,#16a34a,#22c55e)!important;
}

.bg-yellow{
    background:linear-gradient(135deg,#f59e0b,#facc15)!important;
}

.bg-red{
    background:linear-gradient(135deg,#dc3545,#ef4444)!important;
}

/****************************
TABLE
*****************************/

.table-responsive{
    border-radius:15px;
    overflow:hidden;
}

.table{
    margin-bottom:0;
    background:white;
}

.table thead th{

    background:linear-gradient(
    135deg,
    var(--primary),
    var(--danger));

    color:white;

    border:none;

    padding:15px;

    font-size:14px;

    text-transform:uppercase;

    letter-spacing:.4px;

    text-align:center;
}

.table tbody td{

    padding:15px;

    border-color:#edf1f7;

    vertical-align:middle;
}

.table-hover tbody tr{

    transition:.2s;
}

.table-hover tbody tr:hover{

    background:#f8fbff;
}

.table tbody tr:last-child td{

    border-bottom:none;
}

/****************************
LABEL
*****************************/

.label{

    border-radius:30px;

    padding:6px 15px;

    font-size:12px;

    font-weight:700;

    display:inline-block;
}

.label-primary{

    background:var(--primary)!important;

    color:white;
}

.label-success{

    background:var(--success)!important;

    color:white;
}

.label-danger{

    background:var(--danger)!important;

    color:white;
}

.label-warning{

    background:#f59e0b!important;

    color:white;
}

.label-default{

    background:#94a3b8!important;

    color:white;
}

/****************************
STATUS
*****************************/

.status{

    display:inline-block;

    padding:8px 18px;

    border-radius:30px;

    color:white;

    font-weight:600;
}

.status-estoque{

    background:var(--success);
}

.status-uso{

    background:var(--danger);
}

.status-excluido{

    background:#64748b;
}

/****************************
BUTTON
*****************************/

.btn{

    border-radius:10px;

    transition:.25s;

    font-weight:600;

    border:none;
}

.btn-primary{

    background:linear-gradient(
    135deg,
    var(--primary),
    var(--danger));
}

.btn-primary:hover{

    transform:translateY(-2px);

    box-shadow:
    0 10px 18px rgba(13,110,253,.25);
}

.btn-success{

    background:linear-gradient(
    135deg,
    #198754,
    #22c55e);
}

.btn-danger{

    background:linear-gradient(
    135deg,
    #dc3545,
    #ef4444);
}

/****************************
FORM
*****************************/

.form-control{

    height:44px;

    border-radius:10px;

    background:white;

    border:1px solid #dbe2ea;

    color:#444;
}

.form-control:focus{

    border-color:var(--primary);

    box-shadow:
    0 0 0 .2rem rgba(13,110,253,.15);

    background:white;
}

/****************************
USUARIO
*****************************/

.usuario-item{

    background:#f8fafc;

    border-left:5px solid var(--primary);

    border-radius:12px;

    padding:12px;

    margin-bottom:10px;
}

.usuario-item strong{

    color:var(--primary);
}

/****************************
HISTORICO
*****************************/

.historico-item{

    background:#fafafa;

    border-left:5px solid var(--primary);

    padding:15px;

    border-radius:12px;

    margin-bottom:10px;
}

.historico-item.cadastro{

    border-left-color:#22c55e;
}

.historico-item.edicao{

    border-left-color:#0d6efd;
}

.historico-item.exclusao{

    border-left-color:#dc3545;
}

/****************************
INFO BOX
*****************************/

.info-box{

    border-radius:18px;

    background:white;

    box-shadow:var(--shadow);

    transition:.25s;
}

.info-box:hover{

    transform:translateY(-4px);
}

.info-box-icon{

    background:linear-gradient(
    135deg,
    var(--primary),
    var(--danger))!important;

    color:white!important;
}

.info-box-text{

    color:#6b7280!important;

    font-weight:600;
}

.info-box-number{

    font-size:30px;

    font-weight:700;

    color:#111827!important;
}

/****************************
SCROLL
*****************************/

::-webkit-scrollbar{

    width:9px;
}

::-webkit-scrollbar-track{

    background:#e5e7eb;
}

::-webkit-scrollbar-thumb{

    background:linear-gradient(
    var(--primary),
    var(--danger));

    border-radius:20px;
}

/****************************
RESPONSIVO
*****************************/

@media(max-width:992px){

.box-body{

padding:18px;
}

.table th,
.table td{

padding:10px;

font-size:13px;
}

.small-box h3{

font-size:30px;
}

}

@media(max-width:768px){

.table th,
.table td{

font-size:12px;

padding:8px;
}

.box-title{

font-size:17px;
}

.small-box{

margin-bottom:20px;
}

.small-box h3{

font-size:26px;
}

.small-box .icon{

display:none;
}

.btn{

width:100%;
margin-bottom:6px;
}

}

@media(max-width:576px){

.content-header h1{

font-size:24px;
}

.box-body{

padding:15px;
}

.table{

font-size:11px;
}

.label{

font-size:11px;
}




</style>

<head> <title>Movimentações</title> </head>
<?php
$sql = $connect->SQL;
?>

<section class="content-header">
    
    <h1>
        Histórico de Movimentações
        <small>Últimos registros</small>
    </h1>
</section>

<section class="content">

<div class="box">

    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-history"></i>
            Últimas Movimentações
        </h3>
    </div>

    <div class="box-body">

    <div class="filtro-container">

        <form method="GET">

            <div class="row">

                <div class="col-md-2">
                    <div class="form-group">
                        <label>Data inicial</label>

                        <input
                            type="date"
                            name="data_inicio"
                            class="form-control"
                            value="<?php echo htmlspecialchars($data_inicio); ?>"
                        >
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        <label>Data final</label>

                        <input
                            type="date"
                            name="data_fim"
                            class="form-control"
                            value="<?php echo htmlspecialchars($data_fim); ?>"
                        >
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        <label>Usuário</label>

                        <select name="usuario" class="form-control">

                            <option value="">
                                Todos
                            </option>

                            <?php

                            $usuarios = mysqli_query(
                                $sql,
                                "SELECT id_user, username
                                 FROM usuario
                                 ORDER BY username ASC"
                            );

                            while ($u = mysqli_fetch_assoc($usuarios)) {

                                $selected =
                                    ($usuario == $u['id_user'])
                                    ? 'selected'
                                    : '';

                            ?>

                                <option
                                    value="<?php echo $u['id_user']; ?>"
                                    <?php echo $selected; ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $u['username']
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        <label>Item</label>

                        <select name="item" class="form-control">

                            <option value="">
                                Todos
                            </option>

                            <?php

                            $itens = mysqli_query(
                                $sql,
                                "SELECT id_itens, nome_item
                                 FROM itens
                                 ORDER BY nome_item ASC"
                            );

                            while ($i = mysqli_fetch_assoc($itens)) {

                                $selected =
                                    ($item == $i['id_itens'])
                                    ? 'selected'
                                    : '';

                            ?>

                                <option
                                    value="<?php echo $i['id_itens']; ?>"
                                    <?php echo $selected; ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $i['nome_item']
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ação</label>

                        <select name="acao" class="form-control">

                            <option value="">
                                Todas
                            </option>

                            <option
                                value="Entrada"
                                <?php echo $acao == 'Entrada' ? 'selected' : ''; ?>
                            >
                                Entrada
                            </option>

                            <option
                                value="Saída"
                                <?php echo $acao == 'Saída' ? 'selected' : ''; ?>
                            >
                                Saída
                            </option>

                            <option
                                value="Em uso"
                                <?php echo $acao == 'Em uso' ? 'selected' : ''; ?>
                            >
                                Em uso
                            </option>

                            <option
                                value="Devolução"
                                <?php echo $acao == 'Devolução' ? 'selected' : ''; ?>
                            >
                                Devolução
                            </option>

                            <option
                                value="Alteração"
                                <?php echo $acao == 'Alteração' ? 'selected' : ''; ?>
                            >
                                Alteração
                            </option>

                            <option
                                value="Excluiu"
                                <?php echo $acao == 'Excluiu' ? 'selected' : ''; ?>
                            >
                                Excluiu
                            </option>

                        </select>
                    </div>
                </div>


                <div class="col-md-2">
                    <div class="form-group">
                        <label>Local</label>

                        <select name="local" class="form-control">

                            <option value="">
                                Todos
                            </option>

                                                        <option
                                value="FARMACIA"
                                <?php echo $local == 'FARMACIA' ? 'selected' : ''; ?>
                            >
                                Farmácia
                            </option>

                        </select>
                    </div>
                </div>

                <div class="col-md-2">
    <div class="form-group">
        <label>Setor</label><select class="form-control" disabled><option>Farmácia</option></select>

    </div>
</div>

            </div>


            <div class="row">

                <div class="col-md-8">

                    <div class="form-group">

                        <label>Descrição</label>

                        <input
                            type="text"
                            name="descricao"
                            class="form-control"
                            placeholder="Pesquisar na descrição..."
                            value="<?php echo htmlspecialchars($descricao); ?>"
                        >

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="form-group">

                        <label>&nbsp;</label>

                        <div>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="fa fa-search"></i>
                                Filtrar
                            </button>

                            <a
                                href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>"
                                class="btn btn-default"
                            >
                                <i class="fa fa-refresh"></i>
                                Limpar
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>


    <div class="table-responsive">

            <table class="table table-hover table-produtos">

                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Item</th>
                        <th>Local</th>
                        <th>Setor</th>
                        <th>Descrição</th>
                    </tr>
                </thead>

                <tbody>

<?php



if(mysqli_num_rows($historico) > 0){

    while($hist = mysqli_fetch_assoc($historico)){

?>

<tr>

    <td>
        <?php echo date("d/m/Y H:i", strtotime($hist['data_movimentacao'])); ?>
    </td>

    <td>
        <i class="fa fa-user"></i>
        <?php echo !empty($hist['username']) ? $hist['username'] : "Sistema"; ?>
    </td>

    <td>

        <?php

        $acaoBanco = strtoupper(trim($hist['acao'] ?? ''));

        if ($acaoBanco === 'ENTRADA' || $acaoBanco === 'CADASTRO') {
            echo "<span class='label label-success'>Entrada</span>";
        } elseif ($acaoBanco === 'SAIDA' || $acaoBanco === 'SAÍDA') {
            echo "<span class='label label-warning'>Saída</span>";
        } elseif ($acaoBanco === 'EM_USO') {
            echo "<span class='label label-danger'>Em uso</span>";
        } elseif ($acaoBanco === 'DEVOLUCAO') {
            echo "<span class='label label-success'>Devolução</span>";
        } elseif ($acaoBanco === 'EDITOU') {
            echo "<span class='label label-primary'>Alteração</span>";
        } elseif ($acaoBanco === 'EXCLUIU' || $acaoBanco === 'EXCLUI') {
            echo "<span class='label label-danger'>Excluiu</span>";
        } else {
            echo "<span class='label label-default'>" . htmlspecialchars($hist['acao'] ?? '') . "</span>";
        }

        ?>

    </td>

    <td>
    <?php echo !empty($hist['nome_item']) ? htmlspecialchars($hist['nome_item']) : "-"; ?>
</td>

<td>
    <?php echo !empty($hist['local']) ? htmlspecialchars($hist['local']) : "-"; ?>
</td>

<td>
    <?php echo !empty($hist['setor']) ? htmlspecialchars($hist['setor']) : "-"; ?>
</td>

<td>
    <?php echo !empty($hist['descricao']) ? htmlspecialchars($hist['descricao']) : "-"; ?>
</td>

</tr>

<?php

    }

}else{

?>

<tr>
    <td colspan="7" align="center">
        Nenhuma movimentação registrada.
    </td>
</tr>

<?php

}

?>

<div class="voltar">
    <a href="#" onclick="voltarParaOrigem(); return false;">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<script>
function voltarParaOrigem() {
    const paginaAnterior = document.referrer;

    if (paginaAnterior.includes('index_farmacia')) {
        window.location.href = '../views/index_farmacia.php';
    } 
    else if (paginaAnterior.includes('index_farmacia')) {
        window.location.href = '../views/index_farmacia.php';
    } 
    else {
        window.location.href = '../views/index_farmacia.php';
    }
}
</script>

                </tbody>

            </table>

        </div>

    </div>

</div>

</section>