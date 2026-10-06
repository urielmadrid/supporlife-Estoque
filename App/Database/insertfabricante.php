<?php
require_once '../auth.php';
require_once '../Models/fabricante.class.php';

if(isset($_POST['upload']) == 'Cadastrar'){

$NomeFabricante = $_POST['NomeFabricante'];


//---Fabricante---//
$CNPJFabricante = $_POST['CNPJFabricante'];
$EmailFabricante = $_POST['EmailFabricante'];
$EnderecoFabricante = $_POST['EnderecoFabricante'];
$TelefoneFabricante = $_POST['TelefoneFabricante'];

//--Representante--//


$NomeRepresentante = $_POST['NomeRepresentante'];
$TelefoneRepresentante = $_POST['TelefoneRepresentante'];
$EmailRepresentante = $_POST['EmailRepresentante'];



$iduser = $_POST['iduser'];

if($iduser == $idUsuario && $NomeFabricante != NULL && $NomeRepresentante != NULL && $TelefoneRepresentante != NULL && $EmailRepresentante != NULL){

		if (isset($_POST['idFabricante'])){
			$idFabricante = $_POST['idFabricante'];
			$fabricante->UpdateFabricante($idFabricante, $NomeFabricante, $CNPJFabricante, $EmailFabricante, $EnderecoFabricante, $TelefoneFabricante, $idUsuario);		
			
		}else{
			$fabricante->InsertFabricante($NomeFabricante, $CNPJFabricante, $EmailFabricante, $EnderecoFabricante, $TelefoneFabricante, $idUsuario,  $NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante);
		}

	}else{
		header('Location: ../../views/prod/index.php?alert=3');
	}


 }else{
	header('Location: ../../views/prod/index.php');
}