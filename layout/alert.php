<?php

if(isset($_GET['alert'])){

echo '<div class="box-body">';
	switch ($_GET['alert']) {
		case '0':
			echo '<div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-warning"></i> Alerta!</h4>
                Error...
              </div>';
			break;
			
		case '1':
			echo '<div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> Alerta!</h4>
                Success...
              </div>';
			break;
		
		}//switch
	echo'</div>';
}