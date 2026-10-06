<?php
require_once '../auth.php';
require_once '../Models/representante.class.php';

if(isset($_POST['upload']) == 'Cadastrar'){

//--Representante--//
$NomeRepresentante = $_POST['NomeRepresentante'];
$TelefoneRepresentante = $_POST['TelefoneRepresentante'];
$EmailRepresentante = $_POST['EmailRepresentante'];
$idFabricante = $_POST['idFabricante'];




$iduser = $_POST['iduser'];

if($iduser == $idUsuario && $idFabricante != NULL && $NomeRepresentante != NULL && $TelefoneRepresentante != NULL && $EmailRepresentante != NULL){

		if (isset($_POST['idrepresentante'])){

			$idRepresentante = $_POST['idrepresentante'];

						$representante->UpdateRepresentante($idRepresentante, $NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $idFabricante, $idUsuario);		
			
		}else{
			$representante->InsertRepresentante($NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $idFabricante, $idUsuario);
		}

	}else{
		header('Location: ../../views/representante/index.php?alert=3');
	}


 }else{
	header('Location: ../../views/representante/index.php');
}