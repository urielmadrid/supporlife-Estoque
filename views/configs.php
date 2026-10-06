                          
 <?php

require_once '../App/Models/connect.php';
require_once '../App/auth.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha_antiga = $_POST['senha_antiga'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Verifica se todos os campos foram preenchidos
    if (empty($senha_antiga) || empty($nova_senha) || empty($confirmar_senha)) {

        $mensagem = 'Preencha todos os campos.';
        $tipo_mensagem = 'danger';

    } elseif ($nova_senha !== $confirmar_senha) {

        $mensagem = 'A nova senha e a confirmação não são iguais.';
        $tipo_mensagem = 'danger';

    } elseif (strlen($nova_senha) < 6) {

        $mensagem = 'A nova senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'danger';

    } else {

        /*
        =====================================
        BUSCAR SENHA ATUAL
        =====================================
        */

        $sql = "
            SELECT password
            FROM usuario
            WHERE id_user = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($connect->SQL, $sql);

        if (!$stmt) {
            die("Erro ao preparar consulta: " . mysqli_error($connect->SQL));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_user
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $usuario = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        /*
        =====================================
        VERIFICA SE USUÁRIO EXISTE
        =====================================
        */

        if (!$usuario) {

            $mensagem = 'Usuário não encontrado.';
            $tipo_mensagem = 'danger';

        } else {

            /*
            =====================================
            COMPARA SENHA ANTIGA
            =====================================
            */

            if (md5($senha_antiga) !== $usuario['password']) {

                $mensagem = 'A senha antiga está incorreta.';
                $tipo_mensagem = 'danger';

            } else {

                /*
                =====================================
                CRIA MD5 DA NOVA SENHA
                =====================================
                */

                $nova_senha_md5 = md5($nova_senha);


                /*
                =====================================
                ATUALIZA SENHA
                =====================================
                */

                $sql_update = "
                    UPDATE usuario
                    SET password = ?
                    WHERE id_user = ?
                ";

                $stmt_update = mysqli_prepare(
                    $connect->SQL,
                    $sql_update
                );

                if (!$stmt_update) {

                    die(
                        "Erro ao preparar atualização: " .
                        mysqli_error($connect->SQL)
                    );

                }

                mysqli_stmt_bind_param(
                    $stmt_update,
                    "si",
                    $nova_senha_md5,
                    $id_user
                );

                $executou = mysqli_stmt_execute($stmt_update);

                mysqli_stmt_close($stmt_update);


                if ($executou) {

                    $mensagem = 'Senha alterada com sucesso!';
                    $tipo_mensagem = 'success';

                } else {

                    $mensagem = 'Erro ao alterar a senha.';
                    $tipo_mensagem = 'danger';

                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Alterar Senha</title>

    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<style>
    
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =====================================
   BODY
===================================== */

body {

    min-height: 100vh;

    width: 100%;

    display: flex;

    justify-content: center;

    align-items: center;

    font-family: "Segoe UI", sans-serif;

    background: linear-gradient(
        135deg,
        #0d6efd 0%,
        #1f6feb 25%,
        #ffffff 50%,
        #dc3545 75%,
        #b71c1c 100%
    );

    background-size: 400% 400%;

    animation: gradient 15s ease infinite;

}


/* =====================================
   ANIMAÇÃO DO FUNDO
===================================== */

@keyframes gradient {

    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }

}


/* =====================================
   CARD
===================================== */

.config-card {

    width: 450px;

    max-width: calc(100% - 40px);

    border: none;

    border-radius: 25px;

    overflow: hidden;

    background: rgba(255, 255, 255, 0.96);

    box-shadow:
        0 25px 50px rgba(0, 0, 0, 0.30);

}


/* =====================================
   CABEÇALHO
===================================== */

.config-card .panel-heading {

    background: linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    color: white;

    text-align: center;

    padding: 30px;

    border: none;

}


/* =====================================
   ÍCONE
===================================== */

.config-icon {

    width: 80px;

    height: 80px;

    border-radius: 50%;

    background: white;

    color: #dc3545;

    font-size: 36px;

    display: flex;

    justify-content: center;

    align-items: center;

    margin: 0 auto 15px;

    box-shadow:
        0 5px 15px rgba(0, 0, 0, 0.20);

}


.config-card .panel-title {

    font-size: 24px;

    font-weight: bold;

    margin: 0;

}


.config-subtitle {

    display: block;

    margin-top: 7px;

    font-size: 14px;

    opacity: 0.9;

}


/* =====================================
   CORPO
===================================== */

.config-card .panel-body {

    padding: 40px;

}


/* =====================================
   LABELS
===================================== */

.config-card .form-group label {

    font-weight: 600;

    color: #333;

    margin-bottom: 8px;

}


/* =====================================
   INPUTS
===================================== */

.config-card .form-control {

    height: 50px;

    border-radius: 10px;

    border: 1px solid #ddd;

    box-shadow: none;

    transition: 0.3s;

}


.config-card .form-control:focus {

    border-color: #0d6efd;

    box-shadow:
        0 0 10px rgba(13, 110, 253, 0.20);

}


/* =====================================
   INPUT GROUP
===================================== */

.config-card .input-group-addon {

    background: white;

    border: 1px solid #ddd;

    border-right: none;

    border-radius: 10px 0 0 10px;

    color: #0056d6;

    min-width: 45px;

    text-align: center;

}


.config-card .input-group .form-control {

    border-left: none;

}


/* =====================================
   BOTÃO
===================================== */

.btn-alterar {

    width: 100%;

    height: 50px;

    border-radius: 10px;

    border: none;

    color: white;

    font-weight: bold;

    font-size: 15px;

    background: linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    transition: 0.3s;

}


.btn-alterar:hover {

    transform: translateY(-2px);

    color: white;

    box-shadow:
        0 8px 20px rgba(220, 53, 69, 0.30);

}


.btn-alterar:focus {

    color: white;

    outline: none;

}


/* =====================================
   ALERTAS
===================================== */

.config-card .alert {

    border-radius: 10px;

    border: none;

    font-weight: 500;

    margin-bottom: 25px;

}


.config-card .alert-success {

    background: #d1e7dd;

    color: #0f5132;

}


.config-card .alert-danger {

    background: #f8d7da;

    color: #842029;

}


/* =====================================
   DIVISOR
===================================== */

.config-divider {

    display: flex;

    align-items: center;

    margin: 25px 0;

    color: #888;

}


.config-divider::before,
.config-divider::after {

    content: "";

    flex: 1;

    border-bottom: 1px solid #ddd;

}


.config-divider::before {

    margin-right: 15px;

}


.config-divider::after {

    margin-left: 15px;

}


/* =====================================
   INFORMAÇÃO DA SENHA
===================================== */

.password-info {

    background: #f5f8fc;

    border-left: 4px solid #0d6efd;

    border-radius: 8px;

    padding: 12px 15px;

    margin-bottom: 25px;

    color: #666;

    font-size: 13px;

}


.password-info i {

    color: #0d6efd;

    margin-right: 5px;

}


/* =====================================
   LINK VOLTAR
===================================== */

.config-footer {

    text-align: center;

    margin-top: 20px;

}


.config-footer a {

    color: #0056d6;

    text-decoration: none;

    font-weight: 600;

    transition: 0.3s;

}


.config-footer a:hover {

    color: #dc3545;

}


/* =====================================
   ANIMAÇÃO DO CARD
===================================== */

.config-card {

    animation: aparecer 0.5s ease;

}


@keyframes aparecer {

    from {

        opacity: 0;

        transform: translateY(20px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


/* =====================================
   MOBILE
===================================== */

@media (max-width: 576px) {

    body {

        padding: 20px;

    }


    .config-card {

        width: 100%;

        max-width: 450px;

    }


    .config-card .panel-body {

        padding: 25px;

    }


    .config-card .panel-heading {

        padding: 25px 20px;

    }


    .config-icon {

        width: 70px;

        height: 70px;

        font-size: 30px;

    }


    .config-card .panel-title {

        font-size: 21px;

    }

}
    
</style>

</head>



<body>

<div class="config-card">

    <div class="panel-heading">

        <div class="config-icon">
            <i class="fa fa-lock"></i>
        </div>

        <h3 class="panel-title">
            Alterar Senha
        </h3>

        <span class="config-subtitle">
            Mantenha sua conta protegida
        </span>

    </div>

       <div class="panel-body">

    <?php if (!empty($mensagem)): ?>

        <div class="alert alert-<?= $tipo_mensagem ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <div class="password-info">

        <i class="fa fa-info-circle"></i>

        Digite sua senha atual e escolha uma nova senha
        com pelo menos 6 caracteres.

    </div>

    <form method="POST">

            


            <form method="POST">

                <!-- Senha antiga -->
                <div class="form-group">

                    <label for="senha_antiga">
                        Senha atual
                    </label>

                    <div class="input-group">

                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>

                        <input
                            type="password"
                            name="senha_antiga"
                            id="senha_antiga"
                            class="form-control"
                            placeholder="Digite sua senha atual"
                            required
                        >

                    </div>

                </div>


                <!-- Nova senha -->
                <div class="form-group">

                    <label for="nova_senha">
                        Nova senha
                    </label>

                    <div class="input-group">

                        <span class="input-group-addon">
                            <i class="fa fa-key"></i>
                        </span>

                        <input
                            type="password"
                            name="nova_senha"
                            id="nova_senha"
                            class="form-control"
                            placeholder="Digite a nova senha"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <!-- Confirmar nova senha -->
                <div class="form-group">

                    <label for="confirmar_senha">
                        Confirmar nova senha
                    </label>

                    <div class="input-group">

                        <span class="input-group-addon">
                            <i class="fa fa-check"></i>
                        </span>

                        <input
                            type="password"
                            name="confirmar_senha"
                            id="confirmar_senha"
                            class="form-control"
                            placeholder="Digite novamente"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <button type="submit" class="btn-alterar">
    <i class="fa fa-save"></i>
    Alterar Senha
            </button>

                <div class="voltar">
    <a href="#" onclick="voltarParaOrigem(); return false;">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<script>
function voltarParaOrigem() {
    const paginaAnterior = document.referrer;

    window.location.href = '../views/index_farmacia.php';
}
</script>

            </form>

        </div>

    </div>

</div>

</body>

</html>