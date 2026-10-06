<?php

/*
 Classe Itens
 Cadastro e gerenciamento de itens do estoque
*/

require_once 'connect.php';


class Itens extends Connect
{


    /*
    LISTAR ITENS COM PESQUISA
    */

    public function index($pesquisa = "")
    {


        $this->query = "

        SELECT *

        FROM itens

        WHERE

        nome_item LIKE '%$pesquisa%'

        OR codigo_item LIKE '%$pesquisa%'

        OR marca_item LIKE '%$pesquisa%'

        OR lote LIKE '%$pesquisa%'

        OR local LIKE '%$pesquisa%'

        OR representante LIKE '%$pesquisa%'

        ORDER BY id_itens DESC

        ";



        $this->result = mysqli_query(
            $this->SQL,
            $this->query
        )
        or die(mysqli_error($this->SQL));




        while($row = mysqli_fetch_array($this->result))
        {


            echo '


            <li>


                <span class="handle">

                    <i class="fa fa-ellipsis-v"></i>

                    <i class="fa fa-ellipsis-v"></i>

                </span>




                <span class="text">



                <form action="action.php" method="post" class="badge">


                    <input type="hidden"

                    name="id_itens"

                    value="'.$row['id_itens'].'">



                    <input type="hidden"

                    name="ativo"

                    value="'.$row['ativo'].'">



                    <input type="checkbox"

                    onclick="this.form.submit();"

                    ';


                    if($row['ativo']==1)
                    {

                        echo "checked";

                    }



                    echo '>



                </form>




                <span class="badge">

                    '.$row['id_itens'].'

                </span>




                Código:

                '.$row['codigo_item'].'



                |



                Item:

                '.$row['nome_item'].'



                |



                Quantidade:

                '.$row['quant_itens'].'



                |



                Compra:

                '.date(
                    "d/m/Y",
                    strtotime($row['data_compra'])
                ).'



                |



                Vencimento:

                '.date(
                    "d/m/Y",
                    strtotime($row['data_vencimento'])
                ).'



                |



                Status:

                ';



                if($row['ativo']==1)
                {

                    echo "Ativo";

                }
                else
                {

                    echo "Inativo";

                }



                echo '


                </span>





                <div class="tools">





                    <form action="editItens.php" method="post">


                        <input type="hidden"

                        name="id_itens"

                        value="'.$row['id_itens'].'">



                        <button type="submit"

                        class="btn btn-primary btn-sm">


                            <i class="fa fa-edit"></i>


                        </button>



                    </form>






                    <form action="delItens.php" method="post">


                        <input type="hidden"

                        name="id_itens"

                        value="'.$row['id_itens'].'">



                        <button type="submit"

                        class="btn btn-danger btn-sm">


                            <i class="fa fa-trash"></i>


                        </button>



                    </form>




                </div>



            </li>';

        }


    }





    /*
    CADASTRAR ITEM
    */
/*
CADASTRAR ITEM
*/

public function InsertItens(

    $codigo_item,
    $nome_item,
    $marca_item,
    $quant_itens,
    $local,
    $representante,
    $data_compra,
    $data_vencimento

)
{


    $this->query = "

    INSERT INTO itens

    (

    codigo_item,

    nome_item,

    marca_item,

    quant_itens,

    local,

    representante,

    data_compra,

    data_vencimento,

    ativo

    )


    VALUES


    (

    '$codigo_item',

    '$nome_item',

    '$marca_item',

    '$quant_itens',

    '$local',

    '$representante',

    '$data_compra',

    '$data_vencimento',

    1

    )

    ";



    if(mysqli_query($this->SQL,$this->query))
    {


        header(
            "Location: ../../views/itens/index.php?alert=1"
        );


        exit;


    }
    else
    {


        header(
            "Location: ../../views/itens/index.php?alert=0"
        );


        exit;


    }


}







      /*
    BUSCAR ITEM PARA EDITAR
    */

    public function editItens($id_itens)
    {

        $this->query = "

        SELECT *

        FROM itens

        WHERE id_itens='$id_itens'

        ";


        $this->result = mysqli_query(
            $this->SQL,
            $this->query
        );


        if($row=mysqli_fetch_array($this->result))
        {

            return array(

                "Itens"=>array(

                    "id_itens" => $row['id_itens'],

                    "codigo_item" => $row['codigo_item'],

                    "nome_item" => $row['nome_item'],

                    "lote" => $row['lote'],

                    "quant_itens" => $row['quant_itens'],

                    "marca_item" => $row['marca_item'],

                    "data_compra" => $row['data_compra'],

                    "data_vencimento" => $row['data_vencimento'],

                    "local" => $row['local'],

                    "representante" => $row['representante']

                )

            );

        }

    }

    /*
ATUALIZAR ITEM
*/

/*
ATUALIZAR ITEM COM CONTROLE DE QUANTIDADE
*/

public function updateItens(

   $id_itens,
$codigo_item,
$nome_item,
$lote,
$marca_item,
$quant_itens,
$local,
$representante,
$data_compra,
$data_vencimento

)
{


    // Busca dados atuais

    $sqlBusca = "

    SELECT *

    FROM itens

    WHERE id_itens='$id_itens'

    ";


    $resultado = mysqli_query(
        $this->SQL,
        $sqlBusca
    );


    $itemAtual = mysqli_fetch_assoc($resultado);



    if(!$itemAtual){

        return false;

    }



    /*
    Atualiza o item selecionado
    */

    $sqlUpdate = "

    UPDATE itens SET

    codigo_item='$codigo_item',

    nome_item='$nome_item',

    lote='$lote',

    marca_item='$marca_item',

    local='$local',

    representante='$representante',

    data_compra='$data_compra',

    data_vencimento='$data_vencimento',

    quant_itens='1'


    WHERE id_itens='$id_itens'

    ";


    mysqli_query(
        $this->SQL,
        $sqlUpdate
    );





    /*
    Conta quantos itens existem
    */

    $sqlConta = "

    SELECT COUNT(*) AS total

    FROM itens

    WHERE codigo_item='$codigo_item'

    AND lote='$lote'

    ";


    $resultado = mysqli_query(
        $this->SQL,
        $sqlConta
    );


    $dados = mysqli_fetch_assoc($resultado);


    $totalAtual = $dados['total'];






    /*
    ADICIONAR UNIDADES
    */

    if($quant_itens > $totalAtual)
    {


        $adicionar = $quant_itens - $totalAtual;



        for($i=0;$i<$adicionar;$i++)
        {


            $sqlInsert = "

            INSERT INTO itens

            (

            codigo_item,

            nome_item,

            lote,

            marca_item,

            quant_itens,

            local,

            representante,

            data_compra,

            data_vencimento,

            ativo

            )


            VALUES

            (

            '$codigo_item',

            '$nome_item',

            '$lote',

            '$marca_item',

            '1',

            '$local',

            '$representante',

            '$data_compra',

            '$data_vencimento',

            '1'

            )

            ";


            mysqli_query(
                $this->SQL,
                $sqlInsert
            );


        }


    }






    /*
    REMOVER UNIDADES
    */

    if($quant_itens < $totalAtual)
    {


        $remover = $totalAtual - $quant_itens;



        $sqlDelete = "

        DELETE FROM itens

        WHERE codigo_item='$codigo_item'

        AND lote='$lote'

        LIMIT $remover

        ";


        mysqli_query(
            $this->SQL,
            $sqlDelete
        );


    }




    header(
        "Location: ../../views/itens_lote.php?alert=1"
    );


    exit;


}





/*
EXCLUIR ITEM
*/

public function deleteItens($id_itens)
{


    $this->query = "

    DELETE FROM itens

    WHERE id_itens='$id_itens'

    ";



    if(mysqli_query($this->SQL,$this->query))
    {

        header(
            "Location: ../../views/itens/index.php?alert=1"
        );

        exit;

    }
    else
    {

        header(
            "Location: ../../views/itens/index.php?alert=0"
        );

        exit;

    }


}






/*
ATIVAR / DESATIVAR ITEM
*/

public function ItensAtivo($ativo,$id_itens)
{


    if($ativo == 1)
    {

        $novo_status = 0;

    }
    else
    {

        $novo_status = 1;

    }



    $this->query = "

    UPDATE itens SET

    ativo='$novo_status'

    WHERE id_itens='$id_itens'

    ";



    mysqli_query(
        $this->SQL,
        $this->query
    );



    header(
        "Location: ../../views/itens/index.php"
    );


    exit;


}

}


?>