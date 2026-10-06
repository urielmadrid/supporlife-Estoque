<?php

/*
 Class produtos
*/

 require_once 'connect.php';

  class Produtos extends Connect
 {
 	
 	public function index()
 	{
 		$this->query = "SELECT *FROM `produto`";
 		$this->result = mysqli_query($this->SQL, $this->query) or die ( mysqli_error($this->SQL));

 		if($this->result){
 		
 			while ($row = mysqli_fetch_array($this->result)) {
 				echo '<li>
                  <!-- drag handle -->
                      <span class="handle">
                        <i class="fa fa-ellipsis-v"></i>
                        <i class="fa fa-ellipsis-v"></i>
                      </span>
                  <!-- checkbox -->
                  <input type="checkbox" value="'.$row['CodRefProduto'].'">
                  <!-- todo text -->
                  <span class="text"><span class="badge left">'.$row['CodRefProduto'].'</span> '.$row['NomeProduto'].'</span>
                  <!-- Emphasis label -->
                  <!-- <small class="label label-danger"><i class="fa fa-clock-o"></i> 2 mins</small> -->
                  <!-- General tools such as edit or delete-->
                  <div class="tools">
                    <i class="fa fa-edit"></i>
                    <i class="fa fa-trash-o"></i>
                  </div>
                </li>';
                 				
 			}
 			
 		}

 	}

 	public function listProdutos($value = NULL){

 		$this->query = "SELECT *FROM `produto`";
 		$this->result = mysqli_query($this->SQL, $this->query) or die ( mysqli_error($this->SQL));

 		if($this->result){
 		
 			while ($row = mysqli_fetch_array($this->result)) {
        if($value == $row['CodRefProduto']){ 
          $selected = "selected";
        }else{
          $selected = "";
        }
 				echo '<option value="'.$row['CodRefProduto'].'" '.$selected.' >'.$row['NomeProduto'].'</option>';
 			}

 	}
 }

 	public function InsertProd($nomeProduto, $idUsuario){

 		$this->query = "INSERT INTO `produto`(`CodRefProduto`, `NomeProduto`, `Usuario_idUser`) VALUES (NULL,'$nomeProduto','$idUsuario')";
 		if($this->result = mysqli_query($this->SQL, $this->query) or die(mysqli_error($this->SQL))){

 			header('Location: ../../views/prod/index.php?alert=1');
 		}else{
 			header('Location: ../../views/prod/index.php?alert=0');
 		}


 	}
 }

 $produtos = new Produtos;