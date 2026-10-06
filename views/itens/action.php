<?php
require_once '../../App/auth.php';
require_once '../../App/Models/itens.class.php';

	if( isset( $_POST['status'])){

		$id = $_POST['id'];
		$value = $_POST['status'];
		$itens->ItensAtivo($value, $id);

	}
	