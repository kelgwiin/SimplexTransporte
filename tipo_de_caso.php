<?php
	include ("st.php");
	
	$obj =  "SimplexTransporte";
	
	$obj::getIni(); //ini interfaz
	
	// Verificando si escogió entrada por teclado o por archivo, viene de index.php
	if(	!isset($_POST["tipo_entrada"]) || !isset($_POST["metodo_inicializacion"])){
		echo " <h2>Fase inicial</h2>
			
			<small> 
			<p style = \"color:red\">Debes seleccionar una opción de cada una por favor!</p>
			
			
			
			<form action = \"tipo_de_caso.php\" method = \"post\" >
				<i>¿Desea ingresar los datos por teclado o por archivo? </i> <br>
				<input type = \"radio\" name = \"tipo_entrada\" value = \"keyboard\"> Teclado<br>
				<input type = \"radio\" name = \"tipo_entrada\" value = \"file\"> Archivo<br> <br>
				
				<i>¿Qué método de Inicialización desea? </i><br>
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"esquina_noroeste\"> Método de la Esquina Noroeste <br>	
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"minimo\"> Método del Costo Mínimo <br>	
				<input type = \"radio\" name = \"metodo_inicializacion\" value = \"vogel\"> Método de Vogel <br> <br>	
				
				<input type = \"submit\" name = \"boton_enviar\" value = \"enviar respuesta\">
			</form> <br> <br>
			
			
			
			</small>
			";
	}else{
		
		switch($_POST["tipo_entrada"]){
			//Opción de lectura de caso por TECLADO
			
			case "keyboard":
				echo "
				<h2>Fase de lectura del caso de prueba</h2> <br> <br>
			
				<form action = \"SubirCasoTeclado.php\" method = \"post\">
					<table>
					<tbody>
					<tr>
					
						<td align = \"left\"> <label for=\"text\" value = \"numFuentes\">Ingresa el número de fuentes: </label>  </td>
					
						<td align = \"left\"> <input type = \"text\" name = \"numfuente\" size = \"3\" maxlength = \"3\"> <br> </td>
					
					</tr>
					
					<tr>
						<td align = \"left\"> <label for=\"text\" value = \"numDestinos\">Ingresa el número de destinos: </label> </td> 
						<td align = \"left\"> <input type = \"text\" name = \"numdestino\" size = \"3	\" maxlength = \"3\">  </td>
					</tr>
					
					<tr> </tr>
					<tr>
						<td align = \"right\" colspan = \"2\"> <input type = \"submit\" name = \"enviar_fd\" value = \"enviar\" > </td>
					</tr>
					</tbody>
					</table>
				</form> <br> <br>
				";
				break;
			
			//Opción de lectura de caso por ARCHIVO
			case "file":
				echo "
				<h2>Fase de lectura del caso de prueba</h2> <br>
				<form action=\"SubirCasoArchivo.php\" method=\"post\" enctype=\"multipart/form-data\">
					<label for=\"file\">Escoja el archivo:</label>
					<input type=\"file\" name=\"caso\" id=\"file\"><br>
				
					<input type=\"submit\" name=\"submit\" value=\"Cargar archivo\">
				</form><br><br>
				";
				break;
		}//end-of switch: tipo de entrada de lectura de caso 
		
		//guardar Tipo de método en un archivo
		if(file_exists("metodo.ini")){//ver si existe el archivo, si existe se borra
			unlink("metodo.ini");
		}
		$archivo = fopen("metodo.ini","a");
		
		switch ($_POST["metodo_inicializacion"])
		{
			case "esquina_noroeste":
				fwrite($archivo,"esquina_noroeste");
				break;
				
			case "minimo":
				fwrite($archivo,"minimo");
				break;
			
			case "vogel":
				fwrite($archivo,"vogel");
				break;
				
		}
		
	}//end-of if: seleccionó o no alguna entrada
	
	
	$obj::getFin();//fin de interfaz
?>
