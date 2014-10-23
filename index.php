<?php 
	include("st.php");
	$st = "SimplexTransporte";
	
	$st::getIni();// cabecera de la interfaz
	
	
	
	echo " <h2>Fase inicial</h2> <br>
			
			
			<form  action = \"tipo_de_caso.php\" method = \"post\" >
				<i>¿Desea ingresar los datos por teclado o por archivo? </i><br>	
				<input type = \"radio\" name = \"tipo_entrada\" value = \"keyboard\"> Teclado<br>
				<input type = \"radio\" name = \"tipo_entrada\" value = \"file\"> Archivo<br> <br> <br>
				
				<i>¿Qué método de Inicialización desea? </i><br>
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"esquina_noroeste\"> Método de la Esquina Noroeste <br>	
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"minimo\"> Método del Costo Mínimo <br>	
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"vogel\"> Método de Vogel <br> <br>	
			
				<input type = \"submit\" name = \"boton_enviar\" value = \"enviar respuesta\">
			</form><br><br>
			
			</small>
			
			";
	
	$st::getFin();// pie de página de la interfaz	
	
?>
	
	
		
		

