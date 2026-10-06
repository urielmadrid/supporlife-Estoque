<?php
session_start();

if (isset($_SESSION['movimentacoes_autorizado']) && $_SESSION['movimentacoes_autorizado'] === true) {
    header("Location: ../views/movimentacoes.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha = $_POST['senha'] ?? '';

    if (hash_equals((string)(getenv('MOVEMENTS_ACCESS_PASSWORD') ?: ''), $senha)) {
        $_SESSION['movimentacoes_autorizado'] = true;

        header("Location: ../views/movimentacoes.php");
        exit;
    } else {
        $erro = 'Senha incorreta!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso às Movimentações</title>

    <link rel="stylesheet" href="../views/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
       * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    font-family: "Segoe UI", sans-serif;

    background: linear-gradient(
        135deg,
        #0d6efd,
        #1f6feb,
        #ffffff,
        #dc3545,
        #b71c1c
    );

    background-size: 400% 400%;

    animation: gradient 15s ease infinite;
}

/* =========================
   ANIMAÇÃO DO FUNDO
========================= */

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

/* =========================
   CONTAINER DO LOGIN
========================= */

.login-container {
    width: 400px;
    max-width: 95%;

    margin: 0 auto;
}

/* =========================
   CARD
========================= */

.login-box {
    background: rgba(255, 255, 255, 0.95);

    border-radius: 25px;

    overflow: hidden;

    padding: 0;

    box-shadow:
        0 25px 50px rgba(0, 0, 0, 0.3);
}

/* =========================
   CABEÇALHO
========================= */

.login-title {
    background: linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    padding: 35px;

    text-align: center;

    color: white;

    margin: 0;
}

.login-title i {
    width: 80px;
    height: 80px;

    background: white;

    color: #dc3545;

    border-radius: 50%;

    display: flex;

    justify-content: center;
    align-items: center;

    font-size: 40px;

    margin: 0 auto 15px;

    box-shadow:
        0 5px 15px rgba(0, 0, 0, 0.2);
}

.login-title h2,
.login-title h3,
.login-title h4 {
    font-weight: bold;
    margin: 0;
}

/* =========================
   ÁREA DO FORMULÁRIO
========================= */

.login-box form {
    padding: 40px;
}

.login-box label {
    font-weight: 600;
    margin-bottom: 7px;
}

.form-control {
    width: 100%;

    height: 50px;

    border-radius: 10px;

    border: 1px solid #ced4da;

    padding: 10px 15px;

    transition: 0.3s;
}

.form-control:focus {
    border-color: #0056d6;

    box-shadow:
        0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

/* =========================
   INPUT GROUP
========================= */

.input-group-text {
    background: white;

    border-radius: 10px 0 0 10px;

    border-right: none;
}

.input-group .form-control {
    border-radius: 0 10px 10px 0;
}

/* =========================
   BOTÃO ACESSAR
========================= */

.btn-acessar {
    width: 100%;

    height: 50px;

    border: none;

    border-radius: 10px;

    color: white;

    font-weight: bold;

    background: linear-gradient(
        90deg,
        #0056d6,
        #dc3545
    );

    transition: 0.3s;

    cursor: pointer;
}

.btn-acessar:hover {
    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(220, 53, 69, 0.3);

    color: white;
}

/* =========================
   LINKS
========================= */

.login-box a {
    color: #0056d6;

    text-decoration: none;

    font-weight: 600;
}

.login-box a:hover {
    color: #dc3545;
}

/* =========================
   RESPONSIVIDADE
========================= */

@media (max-width: 768px) {

    .login-container {
        width: 95%;
    }

    .login-title {
        padding: 30px 20px;
    }

    .login-title i {
        width: 70px;
        height: 70px;

        font-size: 35px;
    }

    .login-box form {
        padding: 30px 25px;
    }

}

@media (max-width: 480px) {

    body {
        font-size: 14px;
    }

    .login-container {
        width: 95%;
    }

    .login-box {
        border-radius: 18px;
    }

    .login-title {
        padding: 25px 15px;
    }

    .login-title i {
        width: 60px;
        height: 60px;

        font-size: 30px;
    }

    .login-box form {
        padding: 25px 20px;
    }

    .form-control {
        height: 48px;
    }

    .btn-acessar {
        height: 48px;
    }

}
    </style>
</head>

<body>

<div class="login-container">

    <div class="login-box">

        <div class="login-title">
            <i class="fa fa-lock"></i>
            <h3>Acesso restrito</h3>
            <p>Digite a senha para acessar as movimentações.</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="senha">Senha</label>

                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-key"></i>
                    </span>

                    <input
                        type="password"
                        name="senha"
                        id="senha"
                        class="form-control"
                        placeholder="Digite a senha"
                        required
                        autofocus
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-acessar">
                <i class="fa fa-sign-in"></i>
                Acessar
            </button>

        </form>

    </div>

</div>

<script src="../views/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>