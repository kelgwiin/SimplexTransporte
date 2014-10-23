<?php
	include ("st.php");
	
	$obj =  "SimplexTransporte";
	
	$obj::getIni(); //ini interfaz
	
	$simplex_t = new SimplexTransporte();// creado el objeto de la clase
	$d_int = 0;//número destinos
	$f_int = 0;//número de fuentes 
	
	//Envio de número de FUENTES Y DESTINOS
	if(isset($_POST["enviar_fd"]) ){
		$campos_validos = array();
		
		$f = $_POST["numfuente"] ; //fuente
		$d = $_POST["numdestino"];//destino
	
		if(isset($f)  &&  is_numeric($f)){
			$campos_validos["fuente"] = true;
		}else{
			$campos_validos["fuente"] = false;
		}
	
		//Verificando que haya datos en el campo de númetos de origen
		if(isset($d)  &&  is_numeric($d))	{
			$campos_validos["destino"] = true;
		}else{
			$campos_validos["destino"] = false;
		}
		
		// Si ambos campos no son vacíos y son números - CASO VÁLIDO
		if($campos_validos["fuente"] && $campos_validos["destino"]){
			echo "
			<h2>Fase de lectura del caso de prueba</h2>  
			
			<form action = \"SubirCasoTeclado.php\" method = \"post\">";
			
			$d_int = (int) $d;//destinos
			$f_int = (int) $f;//fuentes
			
			printf("<br>Número fuentes: %d , Número de destinos: %d <br>",$f,$d);
			
			echo "<b> <br>Lectura de ecuación de minimización</b> <br> <br>";
			$cad_ec = "Minimizar Z =&nbsp; &nbsp;";
			$rest_of ="";//variable para almacenar las restricciones de oferta
			$rest_dem=""; // y esta de las demandas.
			
			for($i = 1; $i <= $f_int ; $i++){
				for ($j = 1; $j <= $d_int; $j++)
				{
					//para la ECUACIÓN DE MINIMIZACIÓN
					$cad_ec = $cad_ec . "<input type = \"text\" name = \"f". $i . $j . 
					"\" size = \"3\" maxlength = \"6\"> &nbsp;" . "x". 
					"<sub><small>". $i . $j . "</small></sub>";
					
					$rest_of = $rest_of . "x<sub><small>".$i . $j . "</small></sub>";
					
					
					if($i != $f_int){
						$cad_ec = $cad_ec . " + ";
					}else{
						if($j != $d_int)
							$cad_ec = $cad_ec . " + ";
					}
					
					//para lo de las restriciones
					if($j != $d_int)
						$rest_of = $rest_of . " &nbsp;&nbsp; + &nbsp;&nbsp;";
					else
						$rest_of = $rest_of . " &nbsp;&nbsp; = &nbsp;&nbsp; b<sub><small>" . $i  .
						 "</small></sub> &nbsp;&nbsp; <input type = \"text\" name = \"d".
						$i."\" size = \"3\" maxlength = \"6\"> <br>";
				}
				
			}
			
			$i_ant_bs = $i - 1;
			
			//RESTRICCIONES para la DEMANDA
			for ($j = 1; $j <= $d_int; $j++)
			{
				for ($i = 1; $i <= $f_int ; $i++)
				{
					$rest_dem = $rest_dem . "x<sub><small>". $i . $j . "</small></sub>";
					
					if($i != $f_int){
						$rest_dem = $rest_dem . " &nbsp;&nbsp; + &nbsp;&nbsp;";
					}else{
						$rest_dem = $rest_dem . " &nbsp;&nbsp; = &nbsp;&nbsp; b<sub><small>" . ($j+$i_ant_bs)  .
						"</small></sub> &nbsp;&nbsp; <input type = \"text\" name = \"d".
						($j+$i_ant_bs)."\" size = \"3\" maxlength = \"6\"> <br>";
					}
				}
			}
			
			echo "<div class = \"modelo\">".$cad_ec . "</div><br>";
			echo "
			<table class = \"modelo\">
			<tbody >
			<tr>
			<td align = \"right\">
			";
			
			echo "<br> <br> <b>Lectura de restriciones del modelo: <br> </b>";
			echo $rest_of;
			echo $rest_dem ;
			
			echo "
			</td>
			</tr>
			</tbody>
			</table >
			
			<br> <br>
			<!--Boton Enviar-->
			<input type = \"submit\" name = \"enviar_data_kb\" value = \"enviar\"> <br>
			
			</form>
			";
			
		}else{ //CASO DE ERROR
			echo "
				<h2>Fase de lectura del caso de prueba</h2> 
				
				<p class = \"error\">Error, verifique los campos marcados con * </p>
			
				<form action = \"SubirCasoTeclado.php\" method = \"post\">
					<table>
					<tbody>
					
					<tr>
						<td align = \"left\">  <label for=\"text\" value = \"numFuentes\">Ingresa el número de fuentes: &nbsp;</label>  </td>
						<td align = \"left\">  <input type = \"text\" name = \"numfuente\" size = \"3\" maxlength = \"3\">
					";
					
					
				if(!$campos_validos["fuente"])
					echo "*</td> </tr>";
				else
					echo "</td> </tr>";
				
				echo "
					<tr>
						<td align = \"left\"> <label for=\"text\" value = \"numDestinos\">Ingresa el número de destinos: </label> </td>
						<td align = \"left\"> <input type = \"text\" name = \"numdestino\" size = \"3	\" maxlength = \"3\">";
					
				if(!$campos_validos["destino"])
					echo "*</td> </tr>";
				else
					echo "</td> </tr>";
				
				echo"
					<tr> </tr>
					<tr>
						<td align = \"right\" colspan = \"2\"> <input type = \"submit\" name = \"enviar_fd\" value = \"enviar\" > </td>
					</tr>
				</tbody>
				</table>
				
				</form>
				";
			
		}
	}//end-of if: está seleccionado enviar desde el teclado
	
	//Envio de la DATA que fue cargada por teclado
	if(isset($_POST["enviar_data_kb"])){// si se recibió el post del form enviar
		
		if($simplex_t->procesarDataTeclado($_POST)){//si no hubo error al procesar la data se imprimen los resultados
			
			//Impresión del sistema de ecuaciones asociado, Original
			$SistemaOriginal = $simplex_t->getSistemaSimplex();
			
			
			$simplex_t->imprimirModelo();
			
			//Imprimir el caso de inicialización según corresponda
			$archivo = fopen("metodo.ini","r");
			switch (fgets($archivo))
			{
				case "esquina_noroeste":
					$simplex_t->EsquinaNoroeste();
					$simplex_t->imprimirIniEsquinaNoroeste();
					$simplex_t->pruebaFactibilidad();
					break;
					
				case "minimo":
					$simplex_t->costoMinimo();
					$simplex_t->imprimirIniMinimo();
					$simplex_t->pruebaFactibilidad();
					break;
				
				case "vogel":
					$simplex_t->vogel();
					$simplex_t->imprimirIniVogel();
					$simplex_t->pruebaFactibilidad();
					break;
			
			}//end-of switch
		}//end-of if: sino hubo error en procesar la data desde teclado
	}
	
	$obj::getFin();
?>
