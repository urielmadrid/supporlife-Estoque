<?php


/*
=====================================
CONEXÃO COM O BANCO
=====================================
*/

class Connect
{

    // Configure these values in your server environment.
    // Never commit real credentials to Git.
    var $localhost = null;
    var $root      = null;
    var $password  = null;
    var $database  = null;
    var $SQL;

    public function __construct()
    {
        $this->localhost = getenv('DB_HOST') ?: '';
        $this->root      = getenv('DB_USER') ?: '';
        $this->password  = getenv('DB_PASSWORD') ?: '';
        $this->database  = getenv('DB_NAME') ?: '';

        if ($this->localhost === '' || $this->root === '' || $this->database === '') {
            die('Configuração do banco de dados não definida. Consulte o arquivo .env.example.');
        }

        $this->SQL = mysqli_connect(
            $this->localhost,
            $this->root,
            $this->password,
            $this->database
        );

        if (!$this->SQL) {

            die(
                "Erro ao conectar: " .
                mysqli_connect_error()
            );

        }

    }


    /*
    =====================================
    LOGIN
    =====================================
    */

    public function login($username, $password)
    {

        $username = mysqli_real_escape_string(
            $this->SQL,
            $username
        );

        $this->query =
        "SELECT * FROM usuario
         WHERE username='$username'
         LIMIT 1";

        $this->result =
        mysqli_query(
            $this->SQL,
            $this->query
        );

                if (!$this->result) {

            die(
                "Erro na consulta: " .
                mysqli_error($this->SQL)
            );

        }

        if (mysqli_num_rows($this->result) == 0) {

            return false;

        }

        $this->dados = mysqli_fetch_assoc($this->result);

        if ($password != $this->dados['password']) {

            return false;

        }

        /*
        =====================================
        CRIA A SESSÃO DO USUÁRIO
        =====================================
        */

        $_SESSION['usuario']      = true;
        $_SESSION['id_user']      = $this->dados['id_user'];
        $_SESSION['username']     = $this->dados['username'];
        $_SESSION['password']     = $this->dados['password'];
        $_SESSION['permissao']    = $this->dados['permissao'];
        $_SESSION['tipo_acesso']  = 'farmacia';

                /*
        =====================================
        FINALIZA LOGIN
        =====================================
        */

        return true;

    }


    /*
    =====================================
    BUSCAR USUÁRIO LOGADO
    =====================================
    */

    public function usuarioLogado()
    {

        if(isset($_SESSION['usuario'])){

            return true;

        }

        return false;

    }



    /*
    =====================================
    CADASTRAR USUÁRIO
    =====================================
    */

    public function cadastrarUsuario(
        $username,
        $password,
        $permissao,
        $tipo_acesso
    )
    {

        $password = md5($password);


        $sql = "
        INSERT INTO usuario
        (
            username,
            password,
            permissao,
            tipo_acesso
        )

        VALUES
        (
            '$username',
            '$password',
            '$permissao',
            '$tipo_acesso'
        )
        ";


        $this->result = mysqli_query(
            $this->SQL,
            $sql
        );


        if(!$this->result){

            die(
                "Erro ao cadastrar usuário: " .
                mysqli_error($this->SQL)
            );

        }


        return true;

    }



    /*
    =====================================
    LISTAR USUÁRIOS
    =====================================
    */

    public function listarUsuarios()
    {

        $sql = "
        SELECT *
        FROM usuario
        ORDER BY id_user DESC
        ";


        $this->result = mysqli_query(
            $this->SQL,
            $sql
        );


        if(!$this->result){

            die(
                "Erro ao listar usuários: " .
                mysqli_error($this->SQL)
            );

        }


        return $this->result;

    }




    /*
    =====================================
    EXCLUIR USUÁRIO
    =====================================
    */

    public function excluirUsuario($id)
    {


        $sql = "
        DELETE FROM usuario
        WHERE id_user='$id'
        ";


        $this->result = mysqli_query(
            $this->SQL,
            $sql
        );


        if(!$this->result){

            die(
                "Erro ao excluir usuário: " .
                mysqli_error($this->SQL)
            );

        }


        return true;

    }




    /*
    =====================================
    ALTERAR SENHA
    =====================================
    */

    public function alterarSenha(
        $id,
        $senhaAtual,
        $novaSenha
    )
    {


        $senhaAtual = md5($senhaAtual);
        $novaSenha  = md5($novaSenha);



        $sql = "
        SELECT password
        FROM usuario
        WHERE id_user='$id'
        ";


        $this->result = mysqli_query(
            $this->SQL,
            $sql
        );


        $dados = mysqli_fetch_assoc(
            $this->result
        );


        if($dados['password'] != $senhaAtual){

            return false;

        }



        $sql = "
        UPDATE usuario

        SET password='$novaSenha'

        WHERE id_user='$id'
        ";



        $this->result = mysqli_query(
            $this->SQL,
            $sql
        );


        return true;

    }



    /*
    =====================================
    SAIR DO SISTEMA
    =====================================
    */

    public function logout()
    {

        session_destroy();

        header(
            "Location: login.php"
        );

        exit;

    }



}

$connect = new Connect();