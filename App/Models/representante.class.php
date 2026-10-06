<?php

/*
 Class produtos
*/

 require_once 'connect.php';

  class Representante extends Connect
 {
 	
 	public function index()
 	{
 		$this->query = "SELECT *FROM `representante`";
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
                  <input type="checkbox" value="'.$row['idRepresentante'].'">
                  <!-- todo text -->
                  <span class="text"><span class="badge left">'.$row['idRepresentante'].'</span> '.$row['NomeRepresentante'].'</span>
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

 	public function listRepresentantes(){

 		$this->query = "SELECT *FROM `representante`";
 		$this->result = mysqli_query($this->SQL, $this->query) or die ( mysqli_error($this->SQL));

 		if($this->result){
 		
 			while ($row = mysqli_fetch_array($this->result)) {
 				echo '<option value="'.$row['idRepresentante'].'">'.$row['NomeRepresentante'].'</option>';
 			}

 	}
 }

 	public function InsertRepresentante($NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $Fabricante_idFabricante, $idUsuario){

 		$this->query = "INSERT INTO `representante`(`idRepresentante`, `NomeRepresentante`, `TelefoneRepresentante`, `EmailRepresentante`, `Fabricante_idFabricante`, `Usuario_idUser`) VALUES (NULL, '$NomeRepresentante', '$TelefoneRepresentante', '$EmailRepresentante', '$Fabricante_idFabricante', '$idUsuario')";
 		if($this->result = mysqli_query($this->SQL, $this->query)){


 			header('Location: ../../views/representante/index.php?alert=1');
 		}else{
 			header('Location: ../../views/representante/index.php?alert=0');
 		}


 	}

  
 }

 $representante = new Representante;