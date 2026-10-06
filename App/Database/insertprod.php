<?php
require_once '../auth.php';
require_once '../Models/produto.class.php';

if(isset($_POST['upload']) == 'Cadastrar'){

$nomeProduto = $_POST['nomeproduto'];

$iduser = $_POST['iduser'];

if($iduser == $idUsuario && $nomeProduto != NULL){

$produtos->InsertProd($nomeProduto, $idUsuario);
}else{
	header('Location: ../../views/prod/index.php?alert=0');
 }
 }else{
	header('Location: ../../views/prod/index.php');
}
