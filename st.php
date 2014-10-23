<?php
/**
 * Librería que contendrán todas las Funciones del Método 
 * Simplex de Transporte
 */
  
 class SimplexTransporte
 {
	//VARIBLES DE LA CLASE 
	private $name = "SimplexTransporte";
	private $tablaSimplex;// Contendrá la tabla de simplex de transporte
	private $tablaSimplexNoEquilibrada;
	private $numFuentesReales;//número de fuentes sin equilibrar
	private $numDestinosReales;//número de destinos sin equilibrar
	
	/*Array de coeficientes de las variables básicas de Met. Ini. Esquina Noroeste
	 * Cuya clave es la cocatenación de los respectivos indices "i" y "j".
	 *Esto es lo que se usa en la prueba de optimalidad y la posterior prueba de factibilidad
	 */
	private $variables_basicas;
	
	/*Contendrá los índice i y j por separado asociado a las variables básicas
	 * Esto es hecho con el fin de prever casos con valor mayores a 9
	 * 
	 * Cada entrada sería un array con dos campos
	 * 	"i" = valor de i
	 * 	"j" = valor de j
	 */ 
	private $variables_basicas_indices;
	
	/*Variable del MET. DE INICIALIZACIÓN ::ESQUINA NOROESTE::
	 * Se guarda paso a paso el progreso de este método.
	 * 
	 * Es un array que en cada entrada posee un array con la siguiente estructura:
	 * -posición i de la coordenada
	 * -posición j de la coordenada
	 * -coeficiente de la variable básica actual
	 * -array de demanda (con la modificación pertinente)
	 * -array de oferta (con la modificación pertinente)
	 */
	 private $pasosIniNoroeste;
	 
	 
	 /*Variable del MET. DE INICIALIZACIÓN ::COSTO MINIMO::
	  * Contiene la misma estructura de pasosIniNoroeste
	  */
	 private $pasosIniCostoMinimo;
	 
	 
	 /*Variable del MET. DE INICIALIZACIÓN ::VOGEL::
	  * Contiene la misma estructura de pasosIniNoroeste
	  */
	 private $pasosIniVogel;
	 
	 /*Tabla que contiene los vectores de penalización
	  * por cada iteración del Método de Aproximación de Vogel.
	  * La 1era fila de la tabla contiene el vector correspondiente
	  * a la penalización x fila y la 2da fila corresponde a la
	  * penalización por columna
	  */
	 private $tablaPen;
	 
	 
	//Representan las magnitudes sin equilibrar el modelo.
	private $total_oferta=0, $total_demanda=0;
	
	/*Es un array que contendrá la información de el sistema de ecuaciones,
	 * actúa como un diccionario bajo las siguientes claves:
	 * 
	 * "comment" : indica el valor asociado será un comentario
	 * "EcMin" : Función a minimizar
	 * "restricciones": Entrada de que almacenará otro array con las restricciones
	 */
	private $SistemaSimplex;
	
	
	//FIN DE VARIABLES DE LA CLASE
	
	//Constructor
	public function __construct(){ } 
	
	//Destructor
	function __destruct(){}
	
	function getName()
	{
		return $this->name;
	}
	 
	//Retorna la tabla del simplex de transporte, incluyendo las demandas y ofertas
	public function getTablaSimplex(){
		return $this->tablaSimplex;
	}
	
	//Retorna el Sistema de ecuaciones que tiene asociado el problema de transporte
	public function getSistemaSimplex(){
		return $this->SistemaSimplex;
	}
	
	public function getTotalOferta(){
		return $this->total_oferta;
	}
	
	public function getTotalDemanda(){
		return $this->total_demanda;
	}
	
	public function getSolucionInicial(){
		return $this->variables_basicas;
	}
	/**
	 * Sube el caso de prueba y lo almacena en el directorio "MyFiles" 
	 * donde está corriendo el servidor de la página web
	 */ 
	public function SubirCaso()
	{
		$subida_exitosa = true;
		
		//Verificar si se cargó o no el archivo
		if($_FILES["caso"]["size"] > 0)
		{
			//Se obtiene la extensión del archivo
			$extension = end(explode(".", $_FILES["caso"]["name"]));
			
			//Verificando el tipo de archivo
			if( ($_FILES["caso"]["type"] == "application/octet-stream" || 
				$_FILES["caso"]["type"] == "text/plain") && $extension == "in")
			{
				//Si hubo error al cargar el archivo
				if ($_FILES["caso"]["error"] > 0)
				{
					$subida_exitosa = false;
					echo "
					<p class = \"error\">Error al cargar archivo</p>
					
					<h2>Fase de lectura del caso de prueba</h2> <br>
						<form action=\"SubirCasoArchivo.php\" method=\"post\" enctype=\"multipart/form-data\">
							<label for=\"file\">Escoge el archivo:</label>
							<input type=\"file\" name=\"caso\" id=\"file\"><br>
							<input type=\"submit\" name=\"submit\" value=\"Cargar archivo\">
						</form><br><br>
				";
				}else{ //EL ARCHIVO ESTÁ BUENO ENTONCES PROCESARLO
					
					//verificando si el archivo existe
					if(file_exists("MyFiles/".$_FILES["caso"]["name"])){
						echo "El archivo <code>".$_FILES["caso"]["name"]. "</code> fue reemplazado!<br>";
					}else{
						echo "El archivo <code>" . $_FILES["caso"]["name"]. "</code> fue cargado con exito!<br>";
					}
					//mover el arhivo, si existe se reemplaza.
					move_uploaded_file($_FILES["caso"]["tmp_name"],
					"MyFiles/" . $_FILES["caso"]["name"]);
					
					
					//cambiandole los permisos al archivo
					chmod("MyFiles/".$_FILES["caso"]["name"],0777);
					
				}//end-if error carga archivo
			
			}else{//error tipo de archivo inválido
				$subida_exitosa = false;
				echo "
				<h2>Fase de lectura del caso de prueba</h2>
					<p class = \"error\">Error, tipo de archivo inválido, debe ser de extension \".in\"</p>
					
					<form action=\"SubirCasoArchivo.php\" method=\"post\" enctype=\"multipart/form-data\">
						<label for=\"file\">Escoge el archivo:</label>
						<input type=\"file\" name=\"caso\" id=\"file\"><br>
						<input type=\"submit\" name=\"submit\" value=\"Cargar archivo\">
					</form><br><br>
				";
			}//end-if verificando tipo archivo	
		}else{//error archivo no seleccionado
			$subida_exitosa = false;
			echo "
				<h2>Fase de lectura del caso de prueba</h2>
					<p class = \"error\">Error, debe seleccionar un archivo</p>
					
					<form action=\"SubirCasoArchivo.php\" method=\"post\" enctype=\"multipart/form-data\">
						<label for=\"file\">Escoja el archivo:</label>
						<input type=\"file\" name=\"caso\" id=\"file\"><br>
						<input type=\"submit\" name=\"submit\" value=\"Cargar archivo\">
					</form><br><br>
				";
		}
		
		return $subida_exitosa;
	}//end-function SubirCaso
		 
	/**
	 * -- FUNCION PROCESAR DATA ARCHIVO--
	 * PRE-CONDICIÓN: La ecuación a minimizar debe estar estrictamente ordenada,
	 * en forma matricial.
	 * 
	 * Esta función llena la tabla del simplex de transporte y lo equilibra de ser necesario.
	 * Además guarda en una estructura la data que corresponde al sistema original. Modificando con
	 * ello dos variables: "tablaSimplex"  y "SistemaSimplex". 
	 * 
	 * $nombre_dir: contendrá la ruta completa del archivo que se desea abrir
	 * Esta función lee el archivo y lo almacena en la tablaSimplex, este 
	 * sirve de entrada para la posterior inicialización.
	 */
	public function procesarDataArchivo($nombre_dir)
	{
		//Se abre el archivo del caso en modo lectura
		$archivo = fopen($nombre_dir,"r");
		
		//array temporal para el almacenaje de la tabla del Simplex de Transporte
		$tabla_temp = array();
		$tabla_temp[] = array();
		
		$vec_ofertas = array();
		//gettype($var);
		//print_r
		$i = 0; // cantidad de líneas del caso
		$i_lineas_procesadas = 0;
		$i_oferta = 0;
		$j_demanda = 0;
		$i_of = 0;
		$j_dem = 0;
		$this->SistemaSimplex = array(); // asignando un array vacio a la variable
		$this->SistemaSimplex["restricciones"] = array();//asignando una entrada con clave "restricciones"
		$contiene_comentario = false;
		
		$funcion_min_temp = array();
		$num_fila = 0;
		$num_col = 0;
		$i_temp = 0;
		$j_temp = 0;
		
		while(!feof($archivo))// lee hasta fin de archivo
		{	
			$linea_temp = fgets($archivo); // string que contendrá la linea
			
			$linea = explode("\n",$linea_temp);// se particiona la cadena según el token "\n"
			
			//Verificar si contiene COMENTARIOS
			if(substr_count($linea[0],"!") != 0)
			{
				$contiene_comentario = true;
				//Guardando el comentario
				if(!array_key_exists("comment",$this->SistemaSimplex) ){// verificar si ya existe un comentario guardado
					$this->SistemaSimplex["comment"] = substr($linea[0], 1 ); 
				}else{
					$tmp = $this->SistemaSimplex["comment"];
					$this->SistemaSimplex["comment"] = $tmp . ". " . substr($linea[0], 1);
				}
				
			}else{
				$contiene_comentario = false;				
			}//fin de verificación de comentarios
			
			
			if(strlen($linea[0]) > 1 && substr_count($linea[0], "END") == 0
				&& substr_count($linea[0], "ST") == 0 &&  !$contiene_comentario)
			{
				$i_lineas_procesadas++;
				
				if($i_lineas_procesadas != 1 )//Tratamiento de RESTRICCIONES
				{
					$vec_linea = explode(" ", $linea[0]);
					$this->SistemaSimplex["restricciones"][] = $vec_linea; //almacenando la restricción al final
					
					$Bi = (int) end($vec_linea) ;//obteniendo el lado derecho
					
					if($i_of < $i_oferta)
					{
						$tabla_temp[$i_of][] = $Bi;
						$i_of++;
					}else{
						
							if($j_dem == 0)
							{
								//agregar la fila de demanda
								$tabla_temp[] = array();
							}
						
							//teniendo en cuenta que comienzan en cero las filas
							$tabla_temp[$i_oferta][] =  $Bi;
							$j_dem++;
					}
					
				}else{// FUNCIÓN A MINIMIZAR
					$tmp = strtolower($linea[0]); //cambiando todo a minúsculas y almacenándola
					$ec_min = explode(" ", $tmp ); // Pendiente con cambiar CORCHETES por PARÉNTESIS, no son detectados este tipo de errores. ERRORES LOCOS
					
					$cadena_ec  = array();
					foreach($ec_min as $val){
						$array_item_Xi = explode("x",$val);
						
						//tratamiento del término bajo la forma: axij
						//a : coeficiente; x : variable del sistema; ij : posiciones
						if( strcmp ($val, "+") !=  0 && strcmp($val,"min") != 0){//para que no procese el "+" ni el MIN
							
							$coef = (int) ($array_item_Xi[0]);//coeficiente								
							if($coef == 0) // para el caso del coeficiente izquierdo no aparezca, se asume 1
								$coef = 1;
								
							$tmp_cad = $array_item_Xi[1];// i,j : cadena de índices
							
							$i_temp = (int)$tmp_cad[0];// índice i, de la fila
							$j_temp = (int)$tmp_cad[1];// índice j, de la columna
							
							$funcion_min_temp[$i_temp.$j_temp] = $coef; // ingresando la data de MIN temporalmente
							
							if($i_temp > $num_fila) // para actualizar el tope de filas
								$num_fila = $i_temp;
								
							if($j_temp > $num_col)//para actualizar el tope de columnas 
								$num_col = $j_temp;
								
							//representación visual
							$cadena_ec[] = $coef . "x" . "<sub><small>" . $tmp_cad . "</small></sub>";	
							
							$coef = 0; //se resetea por si no es asignado algún valor
							
						}//end-of if: validacion de MIN y + .
						
					}//end-of foreach: Función a minimizar
					$this->SistemaSimplex["EcMin"] = $cadena_ec;
					//llenado de la tabla en las posiciones correctas. Previendo que pudieses estar los datos desordenados	
					for($i = 1 ; $i <= $num_fila; $i++ ){
						for ($j = 1; $j <= $num_col; $j++){
							$key = (string)($i.$j);
							$tabla_temp[$i-1][] = $funcion_min_temp[$key];
						}
						if($i != $num_fila)
						$tabla_temp[] = array();//agregando un array vacío al final de la tabla
					}
					$i_oferta  = count($tabla_temp);
					$j_demanda = count($tabla_temp[0]);
					
				}//end-of if : Tratamiento estricto de restricciones y función a minimizar
				
				//echo $linea[0] . "<br>" ; hace que se muestre en pantalla
			}
			$i++;
			
		}//end-of while: lectura de archivo
		
		//cerrando el archivo
		fclose($archivo);
			
		$n_fuente = count($tabla_temp)-1;
		$n_destino = count($tabla_temp[0])-1;
		
		$this->numFuentesReales = $n_fuente;
		$this->numDestinosReales = $n_destino;
		/**
		 *EQUILIBRANDO el Modelo en caso de ser necesario
		 **/
		$this->equilibrarModelo($tabla_temp,$i_oferta,$j_demanda);
		
	}//end-function procesarDataArchivo
	
	/**FUCTION: Equilibra el modelo en caso de ser necesario.
	 * Modifica: $tablaSimplex.
	 */
	private function equilibrarModelo($tabla_temp, $i_oferta, $j_demanda){
		
		$sum_demanda = array_sum(end($tabla_temp));
		
		$sum_oferta = 0;
		for($k = 0; $k < $i_oferta ; $k++ ){
			$val_oferta = end($tabla_temp[$k]);
			
			//Se agrega al vector por si toca agregar un destino ficticio
			$vec_ofertas[] = $val_oferta;
			$sum_oferta +=  $val_oferta;
		}
		
		$this->total_oferta = $sum_oferta;
		$this->total_demanda = $sum_demanda;
		
		$this->tablaSimplexNoEquilibrada = $tabla_temp; //respaldando la tabla por si no se equilibra
		if($sum_oferta != $sum_demanda)// el modelo está equilibrado
		{
			if($sum_demanda > $sum_oferta)//agregar una fuente ficticia con la respectiva oferta
			{
				$respaldo_demandas = end($tabla_temp);//respaldando la fila de demandas
					
				for( $k = 0; $k < $j_demanda; $k++){
					$tabla_temp[$i_oferta][$k] = 0;
				}
				//agregando la nueva oferta
				$tabla_temp[$i_oferta][] = $sum_demanda - $sum_oferta;
				
				//restaurando las filas de demandas 
				
				$tabla_temp[] = $respaldo_demandas;
			}else{//agregar un destino ficticio con la respectiva demanda 
					
				for($k = 0; $k < $i_oferta; $k++ ){
					$tabla_temp[$k][$j_demanda] = 0;
					$tabla_temp[$k][]   = $vec_ofertas[$k];//rodando la columna de ofertas al final de cada fila
				}
				//agregando la demanda al final de la tabla.
				$tabla_temp[$i_oferta][] = $sum_oferta - $sum_demanda;
			}
				
			//mostrando data borrar - DESPUES DE EQUILIBRAR
			//~ foreach($tabla_temp as $row){ DEJADO INTENCIONALMENTE
			//~ foreach($row  as $item ){
				//~ echo $item . " ";
			//~ }echo "<br>";
			//~ }
		}//end-of if: Equilibrando el modelo en caso de ser necesario
		
		$this->tablaSimplex = $tabla_temp;
	}//end-of function: equilibrarModelo
	
	
	/**FUNCTION: Procesar información del teclado 
	 * 
	 * Crea la tabla de datos del Simplex, la cual se almacena en "$tablaSimplex"
	 * Si se necesita equilibrar la equilibra
	 * Además guarda la información asociada al sistema en la estrutura "$SistemaSimplex".
	 * 
	 */
	public function procesarDataTeclado(/**array:$_POST*/ $p)
	{
		$num_values = count($p)-1;
		$pval = array_values($p);//array pero con solamente los valores sin las claves
		$kval = array_keys($p);// array con todas las claves
		$iv = 0;
		$valido = true;// variable booleana para el control de la data válida
		$num_col=0;
		$ec = array(); //variable donde se almacenará la representación de la ecuación de min
		$res = array();//para almacenar las restricciones
		$tabla_temp = array();
		$contador_fila = 0;//guarda el número total de las filas
		
		//Verificando la validez de los datos suministrados 
		// y contando la cantidad de terminos de la ecuación MIN
		for ($i = 0; $i < $num_values && $valido; $i++)
		{
			if(is_null($pval[$i]) || !is_numeric($pval[$i]))
				$valido = false;
		}
		
		if($valido){//caso VÁLIDO: ENTRADA POR TECLADO
			$contador_fila = 0;
			$item_d = 0; //controla cuantas restricciones son procesadas
			
			foreach($p as $k=>$v){
				$entrada_switch = substr($k,0,1);//f: Ecuación de MIN, d: lados derechos de Restricciones
				$indice_clave = substr($k,1);// IF f: ij ELSE d: i
				
			switch ($entrada_switch)
			{
				case "f"://ECUACIÓN DE MIN
					
					$ec[] = $v . "x<sub><small>" . $indice_clave  . "</small></sub>";
						
					$ind_i = (int)$indice_clave[0];
					$ind_i -= 1;
					
					$contador_fila = $ind_i;
					
					if(array_key_exists($ind_i,$tabla_temp)){
						$tabla_temp[$ind_i][] = $v;//agregando el valor al final de la fila
					}else{
						$tabla_temp[$ind_i] = array();
						$tabla_temp[$ind_i][] = $v;//agregando el valor al final de la fila
					}
					
					break;
					
				case "d": //LADOS DERECHOS
					$ind_i = (int)$indice_clave;
					$ind_i -= 1;
					
					if($ind_i <= $contador_fila){//restricciones de OFERTA
						if($num_col==0)//obteniendo la cantidad de columnas
							$num_col = count($tabla_temp[0]);
							
						$tabla_temp[$ind_i][] = $v;//agregando el lado derecho
						
						//agregarlo al SistemaSimplex
						$ri = array();//restricción i-ésima
						for ($i = 1; $i < $num_col; $i++)
						{
							$ri[] = "x<sub><small>". ($ind_i+1) . $i .
							 "</small></sub>";
							$ri[] = "+"; 
						}
						$i = $num_col;
						$ri[] = "x<sub><small>". ($ind_i+1) . $i .
						"</small></sub>";
						$ri[] = "=";
						$ri[] = $v;//valor del lado derecho
						
						$res[$ind_i] = $ri;
					}else{//restricciones de DEMANDA
					
						//agregarlo al SistemaSimple
						$ri = array();
						for ($i = 0; $i < $contador_fila ; $i++)
						{
							$ri[] = "x<sub><small>". ($i+1) .
							 ($ind_i-$contador_fila). "</small></sub>";
							$ri[] = "+";
						}
						$i = $contador_fila;
						$ri[] = "x<sub><small>". ($i+1) . 
						($ind_i-$contador_fila)  ."</small></sub>";
						$ri[] = "=";
						$ri[] = $v;//valor del lado derecho
						
						$res[$ind_i] = $ri;
						//end-of: agregar el sistemaSimplex
						
						if(array_key_exists($contador_fila+1, $tabla_temp)){
							$tabla_temp[$contador_fila+1][] = $v;
						}else{
							$tabla_temp[$contador_fila+1] = array();//agregando la fila que corresponde a las demandas
							$tabla_temp[$contador_fila+1][] = $v;
						}
							
					}//en-of if: AGREGAR OFERTAS Y DEMANDAS
					break;
					
			}//end-of switch
			
		}//end-of foreach
			$this->SistemaSimplex[] = array();
			$this->SistemaSimplex["EcMin"] = $ec;
			$this->SistemaSimplex["restricciones"] = $res;
			
			$i_oferta = $contador_fila+1;
			$j_demanda = count($tabla_temp[0])-1;
			
			$this->numFuentesReales = $i_oferta;
			$this->numDestinosReales = $j_demanda;
			
			//EQUILIBRANDO el modelo
			$this->equilibrarModelo($tabla_temp,$i_oferta, $j_demanda);
			
		}else{//caso de ERROR: ENTRADA POR TECLADO
			
			$fuentes = 0;
			$destinos = 0;
			
			$claves = array_keys($p);
			//calculando el número de fuentes y origenes
			$vec_fuentes = array();
			$vec_destinos = array();
			
			for($i = 0; $i < $num_values-1; $i++){
				
				if(substr_count($claves[$i],"f" ) > 0){
					$clave_num = explode("f",$claves[$i]);
					$vec_fuentes[]  = $clave_num[1][0];//obteniendo el i
					$vec_destinos[] = $clave_num[1][1];//obteniendo el j
				}
			}
			$fuentes = count(array_unique($vec_fuentes));
			$destinos = count(array_unique($vec_destinos));
			//Creando de nuevo la interfaz para la entrada
			$this->formulariosEntrada($p,$fuentes,$destinos);
		}
		return $valido;
	 }//end-of funtion: procesarDataTeclado

	/**
	*  Verificar si el directorio "MyFiles" existe,sino crearlo.
	*  Aqui se almacenarán el caso de prueba que se suba al servidor
	*  donde se encuentra alojada la página web.
	*/
	public static function crearCarpeta()
	{
		if( !file_exists("MyFiles"))
		{
			mkdir("MyFiles");
			//cambiando los permisos para que php pueda copiar el archivo
			chmod("MyFiles",0777);
		}else{
			if(!is_dir("MyFiles")) // si es un archivo crear la carpeta
			{
				mkdir("MyFiles");
				chmod("MyFiles",0777);
			}
		}
	}//end-fuction crearCarpeta
	
	public function getEsquinaNoroeste(){
		
		if(!isset($this->pasosIniNoroeste) ){//Verifica si ya fue inicializada la variable
			$this->EsquinaNoroeste();
		}
		return $this->pasosIniNoroeste;
	}
	
	///////////
	
	public function costoMinimo(){
		//controlarán la posición actual donde se encuentra el método
		$pos_i = 0;
		$pos_j = 0;
		
		$num_fuentes = count($this->tablaSimplex)-2;//total de fuentes, pero comenzando a contar desde cero
		$num_destinos = count($this->tablaSimplex[0])-2;//total de destinos, pero comenzado 	a contar desde cero
		
		//~ foreach($this->tablaSimplex as $i => $fila){
			//~ foreach ( $fila as $j => $item){
				//~ $clave = $i . $j;	
				//~ $this->variables_basicas_indices[$clave]["i"] = $i;
				//~ $this->variables_basicas_indices[$clave]["j"] = $j;
			//~ }
		//~ }
		
		$vec_ofertas = array();//almacena todas las ofertas
		$vec_demandas = end($this->tablaSimplex);//almacena todas las demandas
		
		//Obtiendo los valores de las ofertas que se encuentran almacenados en "$tablaSimplex"
		for ($i = 0; $i <= $num_fuentes ; $i++)
		{
			$vec_ofertas[] = end($this->tablaSimplex[$i]);
		}
		
		$m = $num_fuentes+1;
		$n = $num_destinos+1;
		
		$numVarBasicas = $m + $n - 1;
		$this->pasosIniCostoMinimo = array();
		$this->auxTablaSimplex = array();
		//~ echo "<br>";
		for($i=0; $i< $m; $i++){
			$this->auxTablaSimplex[$i] = array();
			for($j=0; $j< $n; $j++){
				$this->auxTablaSimplex[$i][$j] = $this->tablaSimplex[$i][$j];
				//~ printf(" | %d | ",$this->auxTablaSimplex[$i][$j]);
			}
			//~ echo "<br>";
		}
		
		
		$max = 0;
		for ($i = 0; $i < $numVarBasicas; $i++)
		{
			$min_max = $this->min_max_mk($this->auxTablaSimplex,$m,$n);
			
			$min = $min_max["min"];
			$max = $min_max["max"];
			
			$posMin_i = $min_max["i_min"];
			$posMin_j = $min_max["j_min"];
			
			$this->pasosIniCostoMinimo[$i] = array();//creando el espacio para el array
			$this->pasosIniCostoMinimo[$i]["i"] = $posMin_i+1;
			$this->pasosIniCostoMinimo[$i]["j"] = $posMin_j+1;
			
			if($vec_ofertas[$posMin_i] < $vec_demandas[$posMin_j]){
				$coeficiente = $vec_ofertas[$posMin_i];
				
				// se resta el menor
				$vec_demandas[$posMin_j] -= $vec_ofertas[$posMin_i];//restando la oferta a la demanda
				$vec_ofertas[$posMin_i] = 0;//asignando cero al menor
				
				//reorganizando el índice de la fila
				for($j=0; $j<=$num_destinos; $j++){
					$this->auxTablaSimplex[$posMin_i][$j] += $max;
				}
				
			}else{
				$coeficiente = $vec_demandas[$posMin_j];
				
				$vec_ofertas[$posMin_i] -= $vec_demandas[$posMin_j];//restando la demanda a la oferta
				$vec_demandas[$posMin_j] = 0;//asignando cero al menor
				//reorganizando el índice de la columna
				
				echo "<br>";
				
				for($j = 0; $j <= $num_fuentes; $j++){
					
					$cosa = $this->auxTablaSimplex[$j][$posMin_j];
					$this->auxTablaSimplex[$j][$posMin_j] = $cosa + $max;
				}
			}
			
			$this->pasosIniCostoMinimo[$i]["coeficiente"] = $coeficiente;
			$this->pasosIniCostoMinimo[$i]["ofertas"] = $vec_ofertas;
			
			$this->pasosIniCostoMinimo[$i]["demandas"] = $vec_demandas;
			
		}//end-of for: Variables básicas
	}//end-of function: EsquinaNoroeste
	
	/////////////////////
	
	/* FUNCTION: Método de INICIALIZACIÓN DE VOGEL.
	 * guarda la información en la variable "$pasosIniVogel"
	 * 
	 * PRECONDICIÓN: la variable "$tablaSimplex" debe estar previamente inicializada
	 */
	public function vogel(){
		
		$num_fuentes = count($this->tablaSimplex)-2;//total de fuentes, pero comenzando a contar desde cero
		$num_destinos = count($this->tablaSimplex[0])-2;//total de destinos, pero comenzado	a contar desde cero
		
		$vec_ofertas = array();//almacena todas las ofertas
		$vec_demandas = end($this->tablaSimplex);//almacena todas las demandas
		
		//Obtiendo los valores de las ofertas que se encuentran almacenados en "$tablaSimplex"
		for ($i = 0; $i <= $num_fuentes ; $i++)
			$vec_ofertas[] = end($this->tablaSimplex[$i]);
		
		
		$aux_ofertas = $vec_ofertas; // Vector auxiliar del vector de ofertas para llevar el control de la penalización
		$aux_demandas = $vec_demandas; // Vector auxiliar del vector de demandas para llevar el control de la penalización
		
		$m = $num_fuentes+1;
		$n = $num_destinos+1;
		
		$numVarBasicas = $m + $n - 1;
		$this->pasosIniVogel = array();
		$auxTablaSimplex = array();
		
		for($i=0; $i< $m; $i++){
			$auxTablaSimplex[$i] = array();
			for($j=0; $j< $n; $j++){
				$auxTablaSimplex[$i][$j] = $this->tablaSimplex[$i][$j];
			}
		}
		
		$max_matriz = $this->min_max_mk($auxTablaSimplex, $m, $n);
		
		$max = $max_matriz["max"];
		$contador_filas_tachadas = 0;
		$contador_columnas_tachadas = 0;	
		$coeficiente = 0;
		
		for ($i = 0; $i < $numVarBasicas; $i++)
		{
			if($contador_filas_tachadas == $num_fuentes || 			//// Aplica el método de Costo Minimo original
				$contador_columnas_tachadas == $num_destinos){ 
								
					$min_max = $this->min_max_mk($auxTablaSimplex,$m,$n);				
					
					$min = $min_max["min"];
					$max = $min_max["max"];
			
					$posMin_i = $min_max["i_min"];
					$posMin_j = $min_max["j_min"];
			
			}else{    //Se aplica el método de Aproximación de Vogel
				
				$this->tablaPen[$i] = array();
				$this->tablaPen[$i] = $this->getPenalizacion($auxTablaSimplex, $m, $n, $aux_ofertas, $aux_demandas);
				
				if($m > $n)
					$t = $m;
				else 
					$t =$n;
					
				$s = 2;
				
				$max_p = $this->min_max_mk($this->tablaPen[$i], $s, $t);
				
				if ($max_p["i_max"] == 0){
					
					$min_max = $this->min_vogel($auxTablaSimplex, $max_p["j_max"], $max_p["i_max"], $n);
					$posMin_i = $max_p["j_max"];
					$posMin_j = $min_max["i_min"];
				}
				else { 
					$min_max = $this->min_vogel($auxTablaSimplex, $max_p["j_max"], $max_p["i_max"], $m);
					$posMin_i = $min_max["i_min"];
					$posMin_j = $max_p["j_max"];
				}
				
			}
			
			$this->pasosIniVogel[$i] = array();//creando el espacio para el array
			$this->pasosIniVogel[$i]["i"] = $posMin_i+1;
			$this->pasosIniVogel[$i]["j"] = $posMin_j+1;
			
			
			if($vec_ofertas[$posMin_i] < $vec_demandas[$posMin_j] || $contador_columnas_tachadas == $num_destinos){
				$coeficiente = $vec_ofertas[$posMin_i];
				
				// se resta el menor
				$vec_demandas[$posMin_j] -= $vec_ofertas[$posMin_i];//restando la oferta a la demanda
				$aux_demandas[$posMin_j] -= $aux_ofertas[$posMin_i];//restando la oferta a la demanda
				$vec_ofertas[$posMin_i] = 0;//asignando cero al menor
				$aux_ofertas[$posMin_i] = -1;//asignando -uno al menor
				$contador_filas_tachadas++;
				
				//reorganizando el índice de la fila
				
				for($j=0; $j<=$num_destinos; $j++){
					$auxTablaSimplex[$posMin_i][$j] += $max;
				}
				
			}else{
				$coeficiente = $vec_demandas[$posMin_j];
				
				$vec_ofertas[$posMin_i] -= $vec_demandas[$posMin_j];//restando la demanda a la oferta
				$aux_ofertas[$posMin_i] -= $aux_demandas[$posMin_j];//restando la demanda a la oferta
				$vec_demandas[$posMin_j] = 0;//asignando cero al menor
				$aux_demandas[$posMin_j] = -1;//asignando -uno al menor
				$contador_columnas_tachadas++;
				
				//reorganizando el índice de la columna	
				
				for($j = 0; $j <= $num_fuentes; $j++){
					
					$cosa = $auxTablaSimplex[$j][$posMin_j];
					$auxTablaSimplex[$j][$posMin_j] = $cosa + $max;
				}
			}
			
			$this->pasosIniVogel[$i]["coeficiente"] = $coeficiente;
			$this->pasosIniVogel[$i]["ofertas"] = $vec_ofertas;
			$this->pasosIniVogel[$i]["demandas"] = $vec_demandas;
		}//end-of for: Variables básicas
	}//end-of function: Vogel
	
	/* Obtiene la posicion minima de la tabla_Simplex tomando en cuenta
	 * el vector de penalizacion.
	 */	
	private function min_vogel($array, $i, $a, $n){
		
		if($a == 0){
			
			$min = $array[$i][0];
			$max = $array[$i][0];	
			
			$i_min =0;
			$i_max =0;

			for ($j = 0; $j < $n; $j++)
			{
				if($array[$i][$j] < $min){
					$i_min = $j;
					$min = $array[$i][$j];
				}
					
				if($array[$i][$j] > $max){
					$max = $array[$i][$j];
					$i_max = $j;
				}
			}
		} else{
			
			$min = $array[0][$i];
			$max = $array[0][$i];	
			$i_min =0;
			$i_max =0;

			for ($j = 0; $j < $n; $j++)
			{
				if($array[$j][$i] < $min){
					$i_min = $j;
					$min = $array[$j][$i];
					
				}
				if($array[$j][$i] > $max){
					$max = $array[$j][$i];
					$i_max = $j;
				}
			}
				
		}
			
			
		$r = array();
		
		$r["min"] = $min;
		$r["max"] = $max;
		$r["i_min"] = $i_min;
		$r["i_max"] = $i_max;
		
		return $r;
		
	}// end-function min_vogel
	
	/*
	 * Crea una tabla con las penalizaciones de cada fila
	 * y cada columna, la fila 1 de la tabla corresponde
	 * al vector de penalizacion de las filas y la fila 2
	 * al vector de penalizacion de las columnas 
	 * */
	private function getPenalizacion($tabla, $m, $n, $oferta, $demanda){
		
		$i_min =0;
		$j_min =0;
		$penalizacion = array();
		
		for ($i = 0; $i < $m; $i++)
		{
			if ($oferta[$i] == -1)
				$penalizacion[0][$i] = 0;
			
			else{
				$min = $tabla[$i][0];
				$min2 = 0;
				for ($j = 1; $j < $n; $j++)
				{
					if($tabla[$i][$j] < $min){
						$min2 = $min;
						$min = $tabla[$i][$j];	
					}
					else{
						if($min2 == 0)
							$min2 = $tabla[$i][$j];
						else{
							if($tabla[$i][$j] < $min2)
								$min2 = $tabla[$i][$j];
						}
					}					
				}
				$penalizacion[0][$i] = $min2 - $min;
			}
		}
		
		for ($j = 0; $j < $n; $j++)
		{
			if($demanda[$j] == -1)
				$penalizacion[1][$j] = 0;
			else {
				$min = $tabla[0][$j];
				$min2 = 0;
				for ($i = 1; $i < $m; $i++)
				{
					if($tabla[$i][$j] < $min){
						$min2 = $min;
						$min = $tabla[$i][$j];	
					}
					else{
						if($min2 == 0)
							$min2 = $tabla[$i][$j];
						else{
							if($tabla[$i][$j] < $min2)
								$min2 = $tabla[$i][$j];
						}
					}					
				}
				$penalizacion[1][$j] = $min2 - $min;
			}
		}
		
		return $penalizacion;
	}
	//end-function getPenalizacion
	
	/*
	 * Obtiene el minimo y maximo valor de la tabla
	 * asignandolos a un array q tambien contendra sus
	 * respectivas posiciones
	 * */
	private function min_max_mk($tabla,$m,$n){
		
		$min = $tabla[0][0];
		$max = $tabla[0][0];
		
		$i_min =0;
		$j_min =0;
		$i_max =0;
		$j_max =0;
		for ($i = 0; $i < $m; $i++)
		{
			for ($j = 0; $j < $n; $j++)
			{
				if($tabla[$i][$j] < $min){
					$i_min = $i;
					$j_min = $j;
					$min = $tabla[$i][$j];
				}
				
				if($tabla[$i][$j] > $max){
					$max = $tabla[$i][$j];
					$i_max = $i;
					$j_max = $j;
				}			
			}
		}
		
		$r = array();
		
		$r["min"] = $min;
		$r["max"] = $max;
		$r["i_min"] = $i_min;
		$r["j_min"] = $j_min;
		$r["i_max"] = $i_max;
		$r["j_max"] = $j_max;
		
		return $r;
		
	}//end-function min_max_mk	
	
	/* FUCTION: Método de INICIALIZACIÓN ESQUINA NOROESTE.
	 * guarda la información en la variable "$pasosIniNororeste"
	 * 
	 * PRECONDICIÓN: la variable "$tablaSimlplex" debe estar previamente inicializada
	 */
	public function EsquinaNoroeste(){
		//controlarán la posición actual donde se encuentra el método
		$pos_i = 0;
		$pos_j = 0;
		
		$num_fuentes = count($this->tablaSimplex)-2;//total de fuentes, pero comenzando a contar desde cero
		$num_destinos = count($this->tablaSimplex[0])-2;//total de destinos, pero comenzado 	a contar desde cero
		
		//~ foreach($this->tablaSimplex as $i => $fila){
			//~ foreach ( $fila as $j => $item){
				//~ $clave = $i . $j;	
				//~ $this->variables_basicas_indices[$clave]["i"] = $i;
				//~ $this->variables_basicas_indices[$clave]["j"] = $j;
			//~ }
		//~ }
		
		
		$vec_ofertas = array();//almacena todas las ofertas
		$vec_demandas = end($this->tablaSimplex);//almacena todas las demandas
		
		//Obtiendo los valores de las ofertas que se encuentran almacenados en "$tablaSimplex"
		for ($i = 0; $i <= $num_fuentes ; $i++)
		{
			$vec_ofertas[] = end($this->tablaSimplex[$i]);
		}
		
		$m = $num_fuentes+1;
		$n = $num_destinos+1;
		$numVarBasicas = $m + $n - 1;
		
		$this->pasosIniNoroeste = array();
		
		for ($i = 0; $i < $numVarBasicas; $i++)
		{
			$this->pasosIniNoroeste[$i] = array();//creando el espacio para el array
			$this->pasosIniNoroeste[$i]["i"] = $pos_i+1;
			$this->pasosIniNoroeste[$i]["j"] = $pos_j+1;
			
			if($vec_ofertas[$pos_i] < $vec_demandas[$pos_j]){
				$coeficiente = $vec_ofertas[$pos_i];
				
				// se resta el menor
				$vec_demandas[$pos_j] -= $vec_ofertas[$pos_i];//restando la oferta a la demanda
				$vec_ofertas[$pos_i] = 0;//asignando cero al menor
				
				//reorganizando el índice de la fila
				$pos_i++;
				
			}else{
				$coeficiente = $vec_demandas[$pos_j];
				
				$vec_ofertas[$pos_i] -= $vec_demandas[$pos_j];//restando la demanda a la oferta
				$vec_demandas[$pos_j] = 0;//asignando cero al menor
				//reorganizando el índice de la columna
				$pos_j++;
			}
			
			$this->pasosIniNoroeste[$i]["coeficiente"] = $coeficiente;
			$this->pasosIniNoroeste[$i]["ofertas"] = $vec_ofertas;
			$this->pasosIniNoroeste[$i]["demandas"] = $vec_demandas;
			
		}//end-of for: Variables básicas
	}//end-of function: EsquinaNoroeste
	
	
	/*FUNCTION: PRUEBA DE OPTIMALIDAD, método de los multiplicadores
	 * Dada la solución inicial y tabla simplex de transporte.
	 * 
	 * $solucion_ini: Es la solución inicial tras aplicar algún método de inicializacion
	 * en la primera llamada se le asigna el propio de la clase,a través
	 * del método $this->getSolucionInicial(). 
	 * 
	 * Ahora la tabla simplex de transporte es una variable de la clase: $this->tablaSimplex
	 *
	 * Retorna: Un vector (long=2) con un tabla de coeficiente y un vector de informacion de la variable de entrada
	 */
	 public function pruebaOptimalidad($solucion_ini){
		$vec_Ui = array();//nota: los elementos en el vector podrían no estar ordenados por la clave
		$vec_Vj = array();
		
		$pos_Ui = 0;
		$pos_Vj = -1;//sino ha sido asignado ningún valor
		
		$vec_pos_Ui = array();
		$vec_pos_Ui[] = 0;
		$vec_pos_Vj = array();
		$cant_Ui = 0;
		$cant_Vj = 0;
		
		if(!isset($solucion_ini)){
			$solucion_ini = $this->variables_basicas;
		}
		
		$tope_Vj = count(end($this->tablaSimplex)) - 1;//tope real iniciando desde cero
		$tope_Ui = count($this->tablaSimplex) - 2;//tope real iniciando desde cero
		
		$vec_Ui[0] = 0;//valor que corresponde la valor arbitrario, porque hay una ecuación que es combinación lineal de otra
		$cant_Ui++;//aumentándolo por el valor ingresado
		
		$fin = false;
		//Llenando los vectores Ui's  y Vj's
		while(!$fin){
			
			//Procesando FILAS de Ui's
			$num_pos_Ui = count($vec_pos_Ui);
			for($k = 0; $k < $num_pos_Ui; $k++){
				$pos_Ui = array_pop($vec_pos_Ui);
				
				for($j = 0; $j <= $tope_Vj ; $j++){
					$clave = $pos_Ui . $j;
				
					if(array_key_exists($clave,$solucion_ini) ){//verificando si es una de las variables iniciales
					
						if(!array_key_exists($j,$vec_Vj)){//verficando que no se haya procesado esa fila
							$vec_Vj[$j] = -1  * ($vec_Ui[$pos_Ui] -
							$this->tablaSimplex[$pos_Ui][$j] );
					
							$vec_pos_Vj[] = $j;//posición del último Vj, en el array
							$cant_Vj++;//se aumenta la cantida de Vj's
						}
					}
				}//end-of for: procesando fila
			}//end-of for: Vector de posiciones de Ui's
			
			//echo "entre filas y columnass....<br>";
			
			//Procesando COLUMNAS Vj's
			$num_pos_Vj = count($vec_pos_Vj);
			for($k = 0; $k < $num_pos_Vj; $k++){
				$pos_Vj = array_pop($vec_pos_Vj);
				
				for($i = 0; $i <= $tope_Ui ; $i++){
					$clave = $i . $pos_Vj;
				
					if(array_key_exists($clave,$solucion_ini)){
					
						if(!array_key_exists($i,$vec_Ui)){//para que no sea procesado de manera repetitiva
							$vec_Ui[$i] = -1 * ($vec_Vj[$pos_Vj] - 
							$this->tablaSimplex[$i][$pos_Vj]);
					
							$vec_pos_Ui[] = $i;//posición del último Ui, en el array
							$cant_Ui++;//se aumenta la cantidad de Ui's
						}
					}
				}//end-of for: procesar columna
			}//end-of for: vector de posiciones Vj's
				
			if($cant_Ui + $cant_Vj == ($tope_Ui+1) + ($tope_Vj+1) )
				$fin = true;
		}//end-of while: llenando vectores de Vi's y Ui's
		
	
		//calculo del RESTO de las variables de la tabla
		$vec_variables = array();//contendrá tanto las variables Básicas como las NO Básicas
		$variable_entrada = array();
		$variable_entrada["valor"] = -1;
		$i_clave = -1;//índices usados para guardar las claves
		$j_clave = -1;
		
		for ($i = 0; $i <= $tope_Ui; $i++)
		{
			for ($j = 0; $j <= $tope_Vj; $j++)
			{
				$clave = $i.$j;
				if(!array_key_exists($clave,$solucion_ini)){
					$var_NoBasica = $vec_Vj[$j] + $vec_Ui[$i] - 
					$this->tablaSimplex[$i][$j];
					
					$vec_variables[$i][$j] = $var_NoBasica;//agregandolo a la matriz de datos
				
					if($var_NoBasica >0 && $var_NoBasica > $variable_entrada["valor"]){
						$variable_entrada["valor"] = $var_NoBasica;
						$variable_entrada["posicion"] = $clave;
						$i_clave = $i;//respaldando los índices i,j de la clave
						$j_clave = $j;
					}
				}else{
					//variables básicas, de la solución inicial
					$vec_variables[$i][$j] = $solucion_ini[$clave];
				}
			}
		}//end-of for: llenado de variables básicas y no básicas de la tabla
		
		// se agregan los índices de la variable de entrada al conjunto
		$this->variables_basicas_indices[$variable_entrada["posicion"]]["i"] = $i_clave; 
		$this->variables_basicas_indices[$variable_entrada["posicion"]]["j"] = $j_clave; 
		
		//Imprimir: INTERFAZ
		$this->imprimirPruebaOptimalidad($tope_Ui,$tope_Vj, $vec_variables, $vec_Ui, $vec_Vj, $solucion_ini,$variable_entrada);
		
		$data = array();
		$data["tabla"] = $vec_variables;
		$data["variable_entrada"] = $variable_entrada;
		$data["tope_Ui"] = $tope_Ui;
		$data["tope_Vj"] = $tope_Vj;
		
		return $data; //tabla de coeficientes y la información de la variable de entrada correspondiente
	 }//end-of function: pruebaOptimalidad 
	
	/*FUNCTION: pruebaFactibilidad
	 * Se realiza la prueba de FACTIBILIDAD, internamente se llama a prueba de 
	 * optimalidad
	 */ 
	public function pruebaFactibilidad(){
		$num_iteracion = 1;
		
		printf("<h2 class \"importante\">Iteración #%d</h2>",$num_iteracion);	
		//iteración inicial de prueba de optimalidad
		$info_optimalidad = $this->pruebaOptimalidad($this->variables_basicas);
		
		//Buscando la solucion ÓPTIMA y FACTIBLE...
		while($info_optimalidad["variable_entrada"]["valor"] != -1){
			$num_iteracion++;
			
			$solucion_ini = $this->variables_basicas;
			$solucion_ini_indices = $this->variables_basicas_indices;
			/* nodos:
			* 		clave: i.j , concatenacion de los índices
			* 		valor: dato	
			* 		padre: i.j , concatenacion de los índices
			* 		estado:true visitado, false no visitado
			* 		operacion: + suma, - resta
			* 		encolado: true, o false según corresponda	
			*
			*pila_nodos_ady : se almacenan los nodos adyacentes posibles en los cuatro costados
			* norte, sur, este, oeste 
			*/ 	
			
			
			$fin = false;//controla el fin del ciclo principal del algoritmo
			$tope_Ui = $info_optimalidad["tope_Ui"];
			$tope_Vj = $info_optimalidad["tope_Vj"];
			$nodos = array();
			$pila_nodos_ady = array();
			
			//Inicialización de nodos, que son las variables básicas iniciales en conjunto con la 
			//variable de entrada.
			$clave = $info_optimalidad["variable_entrada"]["posicion"]; 
			$nodo_entrada = array();
			$nodo_entrada["padre"] = "-1";// el inicial se coloca en -1 para indicar que no posee
			$nodo_entrada["operacion"] = "+";
			$nodo_entrada["estado"] = false;
			$nodo_entrada["clave"] = $clave;//posición i.j
			$nodo_entrada["encolado"] = true;
			$nodo_entrada["valor"] = $info_optimalidad["variable_entrada"]["valor"]; 
			$nodo_entrada["distancia"] = 0;//la distancia desde el nodo de entrada hacia todo los demás.
			$pila_nodos_ady[$clave] = $nodo_entrada;//agregando el nodo de entrada a la pila
		
			$nodos[$clave] = $nodo_entrada;// agregándo al array de nodos 
			
			
			foreach($solucion_ini as $key=>$value){
				$nodos[$key] = array();
				$nodos[$key]["valor"] = $value; 
				$nodos[$key]["estado"] = false;
				$nodos[$key]["clave"] = $key;
				$nodos[$key]["encolado"] = false;
				$nodos[$key]["distancia"] = 0;
			}//Los demás parámetros se agregan  dentro  del otro ciclo
			
			/*Sigue la ideología del BFS mediante la búsqueda de los adyacentes de 
			* cada nodo, y con unas marcas en cada nodo visitado.
			* 
			* Se usa el array como si fuese una pila, lo ideal sería una cola
			* pero para efectos prácticos la pila cumple con el objetivo, además
			* ésta es la que está implementada en el lenguaje.
			*/
			$nodo_actual = null;
			printf("<h2>Prueba de Factibilidad</h2>");
			
			while(count($pila_nodos_ady) > 0 && !$fin)//PRINCIPAL....PRUEBA DE FACTIBILIDAD
			{
				$nodo_actual = array_pop($pila_nodos_ady);
				$clave  = $nodo_actual["clave"];
				//~ printf("valor %d  - clave ACTUAL  %s <br>",$nodo_actual["valor"],$clave);
			
				$i_pos = $solucion_ini_indices[$clave]["i"];//i
				$j_pos = $solucion_ini_indices[$clave]["j"];//j
			
				$clave_padre = $clave;//se guarda el padre
				$encontrado = false; //para que se detenga a penas encuentre el primer nodo	
				$nodo_nuevo = array();//se crea memoria para el nuevo nodo
			
				//izquierda
				for($k = $j_pos-1; $k >= 0 && !$encontrado && !$fin; $k--){
					$clave = $i_pos . $k;
					$this->auxCambiosNodos($fin, $encontrado, $clave,
					$clave_padre,$nodo_entrada, $nodo_actual, $nodo_nuevo,
					$solucion_ini,$nodos, $pila_nodos_ady);
				
				}//end-of for: izquierda
			
				//derecha
				$encontrado = false;
				for($k = $j_pos+1; $k <= $tope_Vj && !$encontrado && !$fin; $k++){
					$clave = $i_pos . $k;
					$this->auxCambiosNodos($fin, $encontrado, $clave,
					$clave_padre,$nodo_entrada, $nodo_actual, $nodo_nuevo,
					$solucion_ini,$nodos, $pila_nodos_ady);
				}
			
				//arriba
				$encontrado = false; 
				for($k = $i_pos - 1; $k >= 0 && !$encontrado && !$fin ; $k-- ){
					$clave = $k.$j_pos;
					$this->auxCambiosNodos($fin, $encontrado, $clave,
					$clave_padre,$nodo_entrada, $nodo_actual, $nodo_nuevo,
					$solucion_ini,$nodos, $pila_nodos_ady);
				}
			
				//abajo
				$encontrado = false; 
				for($k = $i_pos + 1; $k <=  $tope_Ui && !$encontrado && !$fin ; $k++ ){
					$clave = $k.$j_pos;
					$this->auxCambiosNodos($fin, $encontrado, $clave,
					$clave_padre,$nodo_entrada, $nodo_actual, $nodo_nuevo,
					$solucion_ini,$nodos, $pila_nodos_ady);
				}
			
				$nodos[$clave_padre]["estado"] = true;
				//~ echo "---<br>";
			}//end-of while: Prueba de Factibilidad, fin de PRINCIPAL...,se construye el camino
			
			//~ echo "salio del ciclo de factibibilidad<br>";
			//~ echo "RECORRIDO ... - ...<br>";
		
			/*Recorrer y determinar quien suma y quien resta, y ver cuál es el
			*valor de la variable entrante*/ 
			$clave_ini = $info_optimalidad["variable_entrada"]["posicion"];
			$camino = array();
		
			//Primera iteracion
			$temp_clave =  $nodo_actual["clave"];//obtenien el último nodo con su padre actualizado
			$nodo_actual = $nodos[$temp_clave];
		
			$indice_ij_actual = $solucion_ini_indices[$clave_ini];
			$i_anterior = $indice_ij_actual["i"];
			$j_anterior = $indice_ij_actual["j"];
		
			//~ printf("<div class = \"importante\">Nodo de ENTRADA: %s --> pa %s , valor : %d </div><br><br>",$clave_ini,
			//~ $nodos[$clave_ini]["padre"],$nodo_entrada["valor"]);//borrar
		
			$temp_clave = $nodo_actual["clave"];
			$indice_ij_actual = $solucion_ini_indices[$temp_clave];
			$i_actual = $indice_ij_actual["i"]; 
			$j_actual = $indice_ij_actual["j"]; 
		
			$nodo_actual["operacion"] = "-";
		
			$camino[0] = $nodo_actual;
			$menor = $nodo_actual["valor"];//para verificar cual es el menor de los que restan
			$clave_resta_menor = $nodo_actual["clave"];
		
			$clave_padre = $nodo_actual["padre"];
			$nodo_actual = $nodos[$clave_padre];
		
			$suma = false;// para ver quien suma o resta
		
			/*$cambio_fila_col:
			* 	true: verifica los cambios en fila
			* 	false: verifica los cambios en columna
			*/
			$cambio_fila_col = false; 
			if($i_actual == $i_anterior) 
				$cambio_fila_col = true;
			
			$i = 0;
			
			while($clave_padre != $clave_ini){//CAMINO DE VUELTA
			
				$temp_clave = $nodo_actual["clave"];
				$indice_ij_actual = $solucion_ini_indices[$temp_clave];
				
				$i_anterior = $i_actual;//respaldando los índices
				$j_anterior = $j_actual;
			
				$i_actual = $indice_ij_actual["i"]; 
				$j_actual = $indice_ij_actual["j"]; 
			
				if($cambio_fila_col){//verifica si se mantiene la actualización de la fila
				
					if($i_actual == $i_anterior){
						if(!$suma)
							$nodo_actual["operacion"]  = "-";
						else
							$nodo_actual["operacion"] = "+";	
					
					}else{//hay un giro
						$i++;
						if($suma){
							$nodo_actual["operacion"]  = "-";
							$suma = false;
						}else{
							$nodo_actual["operacion"] = "+";
							$suma = true;
						}
					
						$cambio_fila_col = false;
					}
				}else{
					if($j_actual == $j_anterior){
						if(!$suma)
							$nodo_actual["operacion"]  = "-";
						else
							$nodo_actual["operacion"] = "+";
						//~ $camino[$i] = $nodo_actual;	
					}else{
						$i++;
						if($suma){
							$nodo_actual["operacion"]  = "-";
							$suma = false;
						}else{
							$nodo_actual["operacion"] = "+";
							$suma = true;
						}
					
						$cambio_fila_col = true;
					}
				}//end-of if: cambio fila columna
				
				// para el caso en el que se repitan elementos al final
				// de la columna o fila, llegando ya al cierre del circuito.
				if(!($nodo_actual["padre"]==$clave_ini && $nodo_actual["operacion"] == "+")){
					$camino[$i] = $nodo_actual;
				}
				
				if($nodo_actual["operacion"]=="-" && $nodo_actual["valor"] <= $menor){//verificando el mayor de los que restan
					$menor = $nodo_actual["valor"];
					$clave_resta_menor = $nodo_actual["clave"];
				}
				$clave_padre = $nodo_actual["padre"];
				$nodo_actual = $nodos[$clave_padre];
			}//end-of while Viendo quien suma o resta y eliminando nodos intermedios
		
			$camino_cambiado = null;
			$camino_cambiado = array();
			
			
			/*Se COPIA la solución inicial anterior ($solucion_ini)
			 * porque se van a actualizar cietos valores que representan al camino, menos el 
			 * nodo de entrada porque ese va a cambiar
			 */ 
			$var_basicas = array();
			$var_basicas_indices = array();
			foreach($solucion_ini as $key=>$value){
				if($clave_resta_menor != $key){
					$var_basicas[$key] = $value;
					
					//agregando los índices
					$indice_viejo = $solucion_ini_indices[$key]; // i , j por separado
					$var_basicas_indices[$key] = $indice_viejo;
				
				}
			}
			$key = $nodo_entrada["clave"];
			$indice_viejo = $solucion_ini_indices[$key];
			$var_basicas_indices[$key] = $indice_viejo;
			$var_basicas[$key] = $menor; // agregando la nueva variable de entrada
			
			$camino_aux= array(); //se copia el camino original pero con clave de cadena 
			
			foreach($camino as $n){//Modificando los valores de los nodos
				$key = $n["clave"];
				
				$camino_aux[$key]["clave"] = $key;//respaldo del camino original para compatibilidad
				$camino_aux[$key]["valor"] = $n["valor"];
				$camino_aux[$key]["operacion"] = $n["operacion"];
				
				if($n["operacion"] == "-"){
					$val = $n["valor"] - $menor;
				}else
					$val = $n["valor"] + $menor;
			
				if($key != $clave_resta_menor){ // para que no se ingrese la variable que sale
					$camino_cambiado[$key] = array();
					$camino_cambiado[$key]["clave"] = $key;
					$camino_cambiado[$key]["valor"] = $val;	
					$camino_cambiado[$key]["operacion"] = $n["operacion"];
					
					//Se modifican sólo las variable básicas que pertenecen al camino,el resto queda igual.
					$var_basicas[$key] = $val;
				}
			}//end-of foreach: modificando solución inicial
		
			$key = $nodo_entrada["clave"];
			$camino_cambiado[$key] = array();
			$camino_cambiado[$key]["valor"] = $menor;
			$camino_cambiado[$key]["clave"] = $key;
			$camino_cambiado[$key]["operacion"] = "-";
			
			//Interfaz para imprimir
			$clave_var_entrada = $nodo_entrada["clave"];
			
			$camino = null;
			$camino = $camino_aux; 
			
			
			/* $var_basicas son las nuevas con los cambios
			 * $solucion_ini: son las var básicas iniciales sin cambios
			 */ 
			$this->imprimirPruebaFactibilidad($tope_Vj, $tope_Ui, $nodo_entrada,
			$clave_resta_menor,$camino, $camino_cambiado,$solucion_ini,
			$var_basicas, $var_basicas_indices);
			//fin de INTERFAZ imprimir prueba de factibilidad
			
			//Preparando datos para la nueva llamada del ciclo. RESETEANDO VALORES.
			$solucion_ini = null;
			$solucion_ini_indices = null;
			$nodos = null;
			$pila_nodos_ady = null;
			$nodo_entrada = null;
			$camino = null;
			$info_optimalidad = null;
			
			$this->variables_basicas = $var_basicas;
			$this->variables_basicas_indices = $var_basicas_indices;
		
		
			$var_basicas_indices = null;
			
			printf("<h2 class \"importante\">Iteración #%d</h2>",$num_iteracion);	
			
			$info_optimalidad = $this->pruebaOptimalidad($this->variables_basicas);//prueba de optimalidad
			
			$var_basicas = null;
		
		
		}//end-of while: Buscando la solución OPTIMA y FACTIBLE... 
		
		
	}//end-of function: pruebaFactibilidad
	
	
	
	/*FUNCTION: auxCambiosNodos
	 * Se usa en prueba de factibilidad 
	 */ 
	private function auxCambiosNodos(&$fin, &$encontrado, &$clave, &$clave_padre,&$nodo_entrada, &$nodo_actual, &$nodo_nuevo,&$solucion_ini,&$nodos, &$pila_nodos_ady){
		//se verifica que sea básica y que no haya sido procesada	
		if(array_key_exists($clave, $solucion_ini) && 
			!$nodos[$clave]["estado"]){
			
			$nodo_nuevo["valor"] = $solucion_ini[$clave];
			$nodo_nuevo["estado"] = false;
			$nodo_nuevo["padre"] = $clave_padre;
			$nodo_nuevo["clave"] = $clave;
			
			$nodo_padre = $nodos[$clave_padre];
			$nodo_nuevo["distancia"] = $nodo_padre["distancia"] + 1;
			
			//~ printf("ady valor: %d, clave: %s<br>",$nodo_nuevo["valor"],$nodo_nuevo["clave"]);
			
			if(!$nodos[$clave]["encolado"])	//si no ha sido apilado entonces apilarlo
				$pila_nodos_ady[] = $nodo_nuevo;//se agrega a la pila de nodos adyacentes
					
			//se mantiene en falso el estado
			$distancia_anterior = $nodos[$clave]["distancia"];//para verificar si llegó al nodo de entrada y completa el circuito
			$nodos[$clave]["operacion"] = $nodo_nuevo["operacion"];
			$nodos[$clave]["padre"] = $nodo_nuevo["padre"];				
			$nodos[$clave]["encolado"] = true;
			$nodos[$clave]["distancia"] = $nodo_nuevo["distancia"];
			$encontrado = true;	
			
			//~ if($nodo_entrada["clave"] == $nodo_nuevo["clave"]){
				//~ $fin = true; // habremos terminado
				//~ echo "<br>entroo en FINNN...............................<br>";
			//~ }//circuito completado
			
			//~ printf("Distancias anterior %d, actual %d<br>",$distancia_anterior,
			//~ $nodo_nuevo["distancia"]);
			
			if($distancia_anterior == 1){
				//~ echo "Fin por distancia anterior, circuito completadooooo<br>";
				$nodo_actual = $nodo_nuevo;
				$fin = true;
			}//circuito completado
		}//end-of if: básica y que no haya sido procesada	
		
		if(!$encontrado && $nodo_entrada["clave"] == $clave){// para que se evite los saltos del nodo de partida
			$encontrado = true;
		}
	
	}//end-of function: auxCambiosNodos
	
	// I
	// N
	// T
	// E
	// R
	// F
	// A
	// Z
	
	//MÉTODOS EMPLEADOS PARA LA INTERFAZ DE LA PÁGINA  
	public static function getIni(){
		
		echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<title>Método Simplex de Transporte</title>
<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
<link href=\"style.css\" rel=\"stylesheet\" type=\"text/css\" />

</head>
<body>
 <a name=\"inicio\"></a>
 
<div class=\"main\">
  <div class=\"header\">
    <div class=\"header_resize\">
      <div class=\"logo\">
        <h1><a href=\"index.php\"><span>Método Simplex de Transporte</span> <small>Investigación de Operaciones</small></a></h1>
      </div>
      
      <div class=\"menu\">
        <ul>
          <li><a href=\"index.php\" class=\"active\"><span>Inicio</span></a></li>
        </ul>
      </div>
      
      <div class=\"clr\"></div>
    
    </div>
    <div class=\"headert_text_resize\"> <img src=\"images/text_area_img.jpg\" alt=\"\" width=\"395\" height=\"396\" />
      <div class=\"textarea\">
        <h2>Descripción</h2>
        <p>Este Web Site está diseñado como una herramienta enmarcada en el área de <i>Investigación de
        Operaciones</i>. Ya situándonos en el caso específico del Método Simplex de Transporte, usted
        podrá escoger un método de inicialización, y partir de este usted obtendrá la solución del mismo.
        El formato de entrada es el mismo que el del programa LINDO.
        </p>
      </div>
      <div class=\"clr\"></div>
    </div>
   
    <div class=\"clr\"></div>
  </div>


  <div class=\"body\">
    <div class=\"body_resize\">
      <div class=\"left\">
		";
				
		//Creación de la carpeta donde se almacenará el caso de prueba
		$st = "SimplexTransporte";
		$st::crearCarpeta();
	
	}
	
	
	/*FUNCTION: imprimirPruebaOptimalidad
	 */ 
	private function imprimirPruebaOptimalidad($tope_Ui, $tope_Vj, &$tabla_variables, &$vec_Ui, &$vec_Vj,&$solucion_ini, &$variable_entrada){
		printf("<h2>Prueba de Optimalidad</h2>");
		echo "
			<p>Para que una solución básica factible sea óptima debe cumplir 
			con la siguiente condición de optimalidad:</p>
			<p align = \"center\"><i> u<sub>i</sub> + v<sub>j</sub> - c<sub>ij</sub> &nbsp; &le; &nbsp; 0</i></p>
		
			
		"; 
		$clave = $variable_entrada["posicion"];
		$i = $this->variables_basicas_indices[$clave]["i"]+1;
		$j = $this->variables_basicas_indices[$clave]["j"]+1;
		
		//obtener vector oferta
		$vec_ofertas = array();
		foreach($this->tablaSimplex as $fila){
			$vec_ofertas[] = end($fila);
		}
		//obtener vector de demandas
		$vec_demandas = end($this->tablaSimplex);
		
		if($variable_entrada["valor"] != -1){
		printf("<p>Como el problema es de minimización, entrará aquella variable no básica con coeficiente más positivo, en este
caso la variable <b><i>x<sub>%d%d</sub></i></b>. Esto puede hacerse en la tabla directamente de la siguiente manera:
</p>",$i,$j);
		}else{
			printf("<p>Como todas las variables no  básicas poseen coeficientes negativos la solución es <b>óptima</b>.</p>");
		}

		$of = $tope_Ui + 1;
		$de = $tope_Vj + 1;
		
		echo"
			<table align = \"center\" class = \"noroeste\" width = \"80%\" border = \"1\"  cellpadding = \"2\" cellspacing = \"0\">
			<tbody>";
		
		//Vacío de esquina izquierda
		//inicio de fila 1
		echo "<tr>
			  <td class = \"bordes\" rowspan = \"2\" colspan = \"2\"> </td>";
		
		printf("<td class = \"destino\"align = \"center\" colspan = \"%d\"> Destino </td>",$de);
		printf("<td class = \"bordes\" align = \"center\" rowspan = \"2\"> Oferta </td>");
		printf("<td align = \"center\" rowspan = \"2\"> <b><i>U<sub>i</sub></i></b></td>");//etiqueta del vector de Ui's
		echo"
		</tr>
		<tr>";
		
		//Indices de las demandas
		for ($i = 1; $i <= $de; $i++)
		{
			printf("<td align = \"center\"> <b>%d</b></td>",$i);
		}
		echo "</tr>";
		
		printf("<tr> <td class = \"fuente\" align = \"center\" rowspan = \"%d\">Fuente</td> <td> <b>1</b></td>",$of);
		
		//impresión de la 1ra fila de valores
		$valor_Z = 0;
		for ($i = 0; $i < $de; $i++)//costos entre cada par
		{
			echo "<td>";//inicio de celda
			
			printf("<p align = \"right\">%d</p>",$this->tablaSimplex[0][$i]);//costos entre cada par
			
			$temp = '0'.$i;//para imprimir los coeficiente básicos previos
			$clave = (string)$temp;
			
			if(!array_key_exists($clave, $this->variables_basicas) ){//variables no básicas
				if($variable_entrada["posicion"] != $clave){ //si no es la variable de entrada
					echo "<p align = \"center\">";
				}else{
					echo "<p class = \"entrada\" align = \"center\">";
				}
				
				printf("%d</p></td>",
				$tabla_variables[0][$i]);
			}else{//variable básicas
				printf("<p class = \"coeficiente\">%d</p> </td>",$this->variables_basicas[$clave]);
				
				//cálculo de Z
				$coef = $this->tablaSimplex[0][$i];//coeficiente de la variable Xij
				$valor_xij = $tabla_variables[0][$i];
				$valor_Z += $coef*$valor_xij;
			}
		}
		printf("<td align = \"center\"> %d </td>",$vec_ofertas[0]);//valor 0 de la oferta
		printf("<td align = \"center\"> %d </td>",$vec_Ui[0]);//valor 0 del vector de Ui's
		
		printf("</tr>");
		
		//impresión del resto de las filas
		for ($i = 1; $i < $of; $i++)
		{
			printf("<tr>  <td> <b>%d</b></td> ",($i+1));//índices de fuentes
			
			for ($j = 0; $j < $de; $j++)
			{
				echo "<td>";
				
				printf("<p align = \"right\">%d</p>",$this->tablaSimplex[$i][$j]);//costos entre cada par
				
				$temp = $i.$j;//para imprimir los coeficientes básicos previos
				$clave = (string)$temp;
				
				if(!array_key_exists($clave,$this->variables_basicas)){//variables no básicas
					if($variable_entrada["posicion"] != $clave){ //si no es la variable de entrada
						echo "<p align = \"center\">";
					}else{
						echo "<p class = \"entrada\" align = \"center\">";
					}
				
					printf("%d</p></td>",
					$tabla_variables[$i][$j]);
					
				}else{ //variables básicas
					printf("<p class = \"coeficiente\">%d</p> </td>",$this->variables_basicas[$clave]);
				
					//cálculo de Z
					$coef = $this->tablaSimplex[$i][$j];//coeficiente de la variable Xij
					$valor_xij = $tabla_variables[$i][$j];
					$valor_Z += $coef * $valor_xij;
				}
			}
			printf("<td align = \"center\">%d</td>",$vec_ofertas[$i]);//valor la oferta
			printf("<td align = \"center\">%d</td>",$vec_Ui[$i]);//valor del vector Ui
			
			printf("</tr>");//fin de fila
		}
		
		printf("<tr > <td class = \"bordes\" align = \"center\" colspan = \"2\">Demanda</td>");
		
		for ($i = 0; $i < $de; $i++)
		{
			printf("<td align = \"center\">%d</td>",$vec_demandas[$i]);
		}
		
		printf("<td align = \"center\"> <strong>Z</strong><br>%d </td>",$valor_Z);
		printf("<td></td> </tr>"); //celda vacía
		//vector Vj
		printf("<tr> <td align = \"center\" colspan = \"2\"><b><i>V<sub>j</sub></i></b></td>");
		
		foreach($vec_Vj as $elem){
			printf("<td align = \"center\">%d</td>",$elem);
		}
		printf("<td></td>"); //celda vacía
		printf("<td></td> "); //celda vacía
		
		echo "</tr>"; // fin de fila de vj
		//fin de parte repetitiva
		echo "
			</tbody>
			</table> <br> <br>
		";
		
		if($variable_entrada["valor"] == -1){//Es la solución OPTIMA fin
			//Impresión de la solución inicial dada por este método.
			echo "<p> La solución final está dada por:</p>";
			$cad_z = "Z &nbsp &nbsp = &nbsp &nbsp";
			$cad_z_mult = "Z &nbsp &nbsp = &nbsp &nbsp";
			$cant_basicas = count($this->variables_basicas);
			$ind = 0;
			$z = 0;
			foreach($this->variables_basicas as $key=>$value){
				$ind++;
				$i = $this->variables_basicas_indices[$key]["i"]+1;
				$j = $this->variables_basicas_indices[$key]["j"]+1;
				printf("x<sub>%d%d</sub> = %d <br>",$i,$j,$value);
			
				$z += $this->tablaSimplex[$i-1][$j-1]*$value;//calculo de z
				$cad_z .= $this->tablaSimplex[$i-1][$j-1] . "(" . $value . ")";
				$cad_z_mult .= $this->tablaSimplex[$i-1][$j-1] * $value ;
			
				if($ind != $cant_basicas){
					$cad_z .= " + ";
					$cad_z_mult .= " + ";	
				}
			}echo "<br><br>";
		
			printf("%s<br> %s<br> Z &nbsp &nbsp = &nbsp &nbsp %d</br></br>",$cad_z,$cad_z_mult,$z);
		}//end-of if: Es la solución ÓPTIMA ... al fin...
		
	}//end-of function: imprimirPruebaOptimalidad

	
	public function imprimirPruebaFactibilidad($tope_Vj, $tope_Ui,$nodo_entrada,$clave_resta_menor,$camino, $camino_cambiado,$solucion_ini, $var_basicas, $var_basicas_indices){
		
		//camino sin modificar 
		$this->tablaFactibilidad($tope_Vj, $tope_Ui,
		$nodo_entrada,$clave_resta_menor,$camino,$solucion_ini,
		 $var_basicas_indices);
		
		$val_menor = $camino[$clave_resta_menor]["valor"];
		$ii = $this->variables_basicas_indices[$clave_resta_menor]["i"]+1;
		$jj = $this->variables_basicas_indices[$clave_resta_menor]["j"]+1;
		
		echo("<p>Entre las variables a las cuales se les resta, se toma el menor valor, esa es la variable que sale, en este caso");
		printf("<b> x<sub>%d%d</sub> = %d </b> </p>",$ii,$jj,$val_menor);
		
		//camino modificado
		$this->tablaFactibilidad($tope_Vj, $tope_Ui,
		$nodo_entrada,$clave_resta_menor,$camino_cambiado,$var_basicas,
		 $var_basicas_indices);
		
	}
	
	
	private function tablaFactibilidad($tope_Vj, $tope_Ui,$nodo_entrada,$clave_resta_menor,$camino_actual, $var_basicas, $var_basicas_indices){
		$of = $tope_Ui + 1;
		$de = $tope_Vj + 1;
		
		//obtener vector oferta
		$vec_ofertas = array();
		foreach($this->tablaSimplex as $fila){
			$vec_ofertas[] = end($fila);
		}
		//obtener vector de demandas
		$vec_demandas = end($this->tablaSimplex);
		
		echo"
			<table align = \"center\" class = \"noroeste\" width = \"75%\" border = \"1\"  cellpadding = \"2\" cellspacing = \"0\">
			<tbody>";
		
		
		//vacio de esquina izquierda
		echo " <td class = \"bordes\" rowspan = \"2\" colspan = \"2\"> </td>";
		
		printf("<td class = \"destino\"align = \"center\" colspan = \"%d\"> Destino </td>",$de);
		printf("<td class = \"bordes\" align = \"center\" rowspan = \"2\"> Oferta </td> ");
		echo 
		"</tr>
		<tr>";
		
		//Indices de las demandas
		for ($i = 1; $i <= $de; $i++)
		{
			printf("<td align = \"center\"> <b>%d</b></td>",$i);
		}
		
		
		echo "</tr>";
		printf("<tr> <td class = \"fuente\" align = \"center\" rowspan = \"%d\">Fuente</td> <td> <b>1</b></td>",$of);
		
		//parte repetitiva
		//1ra eteracion
		for ($i = 0; $i < $de; $i++)//costos entre cada par
		{
			$temp = '0'.$i;//para imprimir los coeficiente básicos previos
			$clave = (string)$temp;
			
			if(array_key_exists($clave, $camino_actual) || $clave == $nodo_entrada["clave"]){//para resaltar el camino
				echo "<td class = \"camino\">";
			}else
				echo "<td>";
				
			printf("<p align = \"right\">%d</p>",$this->tablaSimplex[0][$i]);//costos entre cada par
			
			
			if(array_key_exists($clave, $var_basicas) ){
				$signo = "";
				//para que se imprima el signo respectivo del camino
				if(array_key_exists($clave,$camino_actual)){
					$signo = "( ". $camino_actual[$clave]["operacion"] . " ) &nbsp; &nbsp;";
				}
					
				printf("<p class = \"coeficiente\">%s %d</p> </td>",$signo,$var_basicas[$clave]);
				
			}else{
					if($clave == $nodo_entrada["clave"] ){//es la variable de entrada
						printf("<p class = \"entrada\">( + ) &nbsp; &nbsp; %d</p> </td>",$nodo_entrada["valor"]);
					}else{
						printf("<p class = \"invisible\">0</p></td>");
					}
			}
		}
		printf("<td align = \"center\"> %d </td>",$vec_ofertas[0]);//valor de la oferta
		
		printf("</tr>");
		
		//resto de iteraciones
		for ($i = 1; $i < $of; $i++)
		{
			printf("<tr>  <td> <b>%d</b></td> ",($i+1));//índices de fuentes
			
			for ($j = 0; $j < $de; $j++)
			{
				$temp = $i.$j;//para imprimir los coeficiente básicos previos
				$clave = (string)$temp;
			
				if(array_key_exists($clave, $camino_actual) || $clave == $nodo_entrada["clave"]){//para resaltar el camino
					echo "<td class = \"camino\">";
				}else
					echo "<td>";
				
				printf("<p align = \"right\">%d</p>",$this->tablaSimplex[$i][$j]);//costos entre cada par
			
			
				if(array_key_exists($clave, $var_basicas) ){
					
					$signo = "";
					//para que se imprima el signo respectivo del camino
					if(array_key_exists($clave,$camino_actual)){
						$signo = "( ". $camino_actual[$clave]["operacion"] . " ) &nbsp; &nbsp;";
					}
					
					printf("<p class = \"coeficiente\">%s %d</p> </td>",
					$signo,$var_basicas[$clave]);
				}else{
						if($clave == $nodo_entrada["clave"]){//es la variable de entrada
							printf("<p class = \"entrada\">( + ) &nbsp; &nbsp; %d</p> </td>",$nodo_entrada["valor"]);
						}else{
							printf("<p class = \"invisible\">0</p></td>");
						}
				}
			}
			printf("<td align = \"center\">%d</td>",$vec_ofertas[$i]);//valor la oferta
			
			printf("</tr>");
		}
		
		printf("<tr > <td class = \"bordes\" align = \"center\" colspan = \"2\">Demanda</td>");
		
		for ($i = 0; $i < $de; $i++)
		{
			printf("<td align = \"center\">%d</td>",$vec_demandas[$i]);
		}
		
		printf("<td class = \"bordes\"></td> </tr>");
		//fin de parte repetitiva
		echo "
			</tbody>
			</table> <br> <br>
		";
	
	}//end-of function: Prueba de Factibilidad
	
	
	/*FUNCTION:imprimirModelo
	 *Permite imprimir el modelo asociado al problema de transporte con el cual se está trabajando
	 */ 
	public function imprimirModelo(){
		printf("<h2>Modelo</h2>");
		
		$ec = $this->SistemaSimplex["EcMin"];
		$num = count($ec);
		$num_demanda = $this->numDestinosReales;
		$salto = 1;
		echo "
		<table class = \"modelo\" align = \"left\" border = \"0\" cellpadding = \"5\">
		<tbody>
		<tr>
		<td valign = \"top\" align = \"left\" rowspan = \"". ($num-1) . "\">
			<b>Minimizar Z</b> &nbsp &nbsp = &nbsp &nbsp 
		</td>
		
		";
		
		for ($i = 0; $i < $num-1; $i++,$salto++)
		{
			printf("<td>%s</td> <td> + </td>", $ec[$i]);
			
			if($salto == $num_demanda){
				echo " </tr> <tr>";
				$salto = 0;
			}
		}
		printf("<td>%s</td> <td> </td> </tr>",$ec[$num-1]);
		
		echo "
		
		</tbody>
		</table>
		";
		
		printf("<br clear = \"all\"><b class = \"modelo\">Sujeto a</b> <br><br>");
		
		echo "
		<table class = \"modelo\" cellpadding = \"6\" border = \"0\">
		<tbody>
		";
		
		$r = $this->SistemaSimplex["restricciones"];
		$num_items = count($r[0]);
		$tmp_items = count(end($r));
		
		if($tmp_items > $num_items)
			$num_items = $tmp_items;
		
		foreach($r as $fila){
			$num_items_actual = count($fila);
			$item_procesados=0;
			
			echo "<tr>";
			if($num_items_actual < $num_items){
				for ($i = 0; $i < ($num_items - $num_items_actual); $i++){
					$item_procesados++;
					printf("<td > </td>");
				}
			}
			
			foreach($fila as $item){
				$item_procesados++;
				
				if($item_procesados != $num_items){
					printf("<td align = \"center\">%s</td>",$item);
				}else{
					printf("<td align = \"right\">%s</td>",$item);
				}
				
			}
			echo "</tr>";
		}
		echo "</tbody> </table> </br clear = \"all\"> </br>";
		
		$js = $this->numDestinosReales;//número de destinos
		$is = $this->numFuentesReales;//numero de fuentes
		
		echo "x<sub><small>ij</small></sub>  &ge; 0;   
		&nbsp; para <i>i</i> = 1, &hellip;, " .$is.
		" &nbsp; y &nbsp; <i>j</i> = 1,&hellip;, " . $js . "<br><br>";
		
		//Imprimiendo la TABLA DE TRANSPORTE ORIGININAL
		echo "<h2>Tabla Simplex de Transporte Original</h2>";
		$of = count($this->tablaSimplexNoEquilibrada)-1;//número de ofertas
		$vec_ofertas = array();
		
		for ($i = 0; $i < $of; $i++)//Obteniendo el vector de ofertas de la tabla
		{
			$vec_ofertas[] = end($this->tablaSimplexNoEquilibrada[$i]);
		}
		$tmp = end($this->tablaSimplexNoEquilibrada);
		$this->tablaCentral(-1,$vec_ofertas,$tmp, -1,-1,-1);
		
	}//end-of fuction: imprimirModelo
	
	/*Para que sea usado tanto en la impresión de los pasos de 
	 * Esquina noroeste y costo mínimo.
	 */ 
	public function imprimirIni_aux($pasosIni, $nombre){
		$of = count($this->tablaSimplex)-1;//número de ofertas
		$vec_ofertas = array();
		
		// para almacenar las variables básicas que se van obteniendo
		$this->variables_basicas = array();
		
		//para almcenar los indices de las variables básicas que se van obteniendo
		$this->variables_basicas_indices = array();
		
		for ($i = 0; $i < $of; $i++)//Obteniendo el vector de ofertas de la tabla
		{
			$vec_ofertas[] = end($this->tablaSimplex[$i]);
		}
		printf("<h2><b>Inicialización</b>: Método %s</h2>",$nombre);
		$this->tablaCentral(0,$vec_ofertas,end($this->tablaSimplex), -1,-1,-1);
		
		$contador = 0;
		foreach($pasosIni as $paso){//Aquí se imprime el MET. de ini paso a paso
			$contador++;
			
			$this->tablaCentral($contador, $paso["ofertas"],$paso["demandas"],
			$paso["i"],$paso["j"],$paso["coeficiente"]);
			
			//Para que se ejecute solamente con el método de ::Aproximación de VOGEL::
			if($nombre == "de la Aproximación de Vogel"){
				$penalizaciones_fila = $this->tablaPen[$contador-1][0];
				$penalizaciones_columna = $this->tablaPen[$contador-1][1];
				echo"
				<table align = \"center\" width = \"50%\"border = \"1\"class = \"noroeste\" cellpadding = \"5\">
					<tbody>
					<tr>
				";
				
				echo "<td align = \"center\" class = \"bordes\"><strong>Penalizaciones<br>COLUMNAS</strong></td>";
				foreach($penalizaciones_columna as $c){
					printf("<td class = \"coeficiente\"> %d </td>",$c);
				}
				echo "</tr>";
				
				echo "<td align = \"center\" class = \"bordes\"><strong>Penalizaciones<br>FILAS</strong></td>";
				foreach($penalizaciones_fila as $f){
					printf("<td class= \"coeficiente\"> %d </td>",$f);
				}
				echo "</tr>";
				
				echo "
					</tbody>
					</table>
					<br clear = \"all\">
				";
			}
		}
		
		printf("<p>En este caso la solución inicial dada por el método %s (sólo las variables básicas) es:</p>",$nombre);
		//Impresión de la solución inicial dada por este método.
		$cad_z = "Z &nbsp &nbsp = &nbsp &nbsp";
		$cad_z_mult = "Z &nbsp &nbsp = &nbsp &nbsp";
		$cant_basicas = count($this->variables_basicas);
		$ind = 0;
		$z = 0;
		foreach($this->variables_basicas as $key=>$value){
			$ind++;
			$i = $this->variables_basicas_indices[$key]["i"]+1;
			$j = $this->variables_basicas_indices[$key]["j"]+1;
			printf("x<sub>%d%d</sub> = %d <br>",$i,$j,$value);
			
			$z += $this->tablaSimplex[$i-1][$j-1]*$value;//calculo de z
			$cad_z .= $this->tablaSimplex[$i-1][$j-1] . "(" . $value . ")";
			$cad_z_mult .= $this->tablaSimplex[$i-1][$j-1] * $value ;
			
			if($ind != $cant_basicas){
				$cad_z .= " + ";
				$cad_z_mult .= " + ";
			}
		}echo "<br><br>";
		
		echo "<p>El resto de las variables (no básicas) son cero, por lo que no se toman en cuenta para el cálculo de Z.
</p>";
		printf("%s<br> %s<br> Z &nbsp &nbsp = &nbsp &nbsp %d</br></br>",$cad_z,$cad_z_mult,$z);
	
	}//end-of function: ImprimirIni_aux
	
	public function imprimirIniEsquinaNoroeste(){
		$this->imprimirIni_aux($this->pasosIniNoroeste,"de la Esquina Noroeste");
	}//end-of function: imprimirIniEsquinaNoroeste
	
	public function imprimirIniMinimo(){
		$this->imprimirIni_aux($this->pasosIniCostoMinimo,"de Costo Mínimo");
	}
	
	public function imprimirIniVogel(){
		$this->imprimirIni_aux($this->pasosIniVogel,"de la Aproximación de Vogel");
	}
	
	/*FUNCTION: TABLA CENTRAL
	 */
	private function tablaCentral($contador,&$vec_ofertas, &$vec_demandas, $i_coef,$j_coef,$coef){
		$of = count($vec_ofertas);
		$de = count($vec_demandas);
		
		echo"
			<table align = \"center\" class = \"noroeste\" width = \"75%\" border = \"1\"  cellpadding = \"2\" cellspacing = \"0\">
			<tbody>";
		
		if($contador != -1)
			printf("<caption>I T E R A C I Ó N #%d </caption> <tr>",$contador);
		
		//vacio de esquina izquierda
		echo " <td class = \"bordes\" rowspan = \"2\" colspan = \"2\"> </td>";
		
		printf("<td class = \"destino\"align = \"center\" colspan = \"%d\"> Destino </td>",$de);
		printf("<td class = \"bordes\" align = \"center\" rowspan = \"2\"> Oferta </td> ");
		echo 
		"</tr>
		<tr>";
		
		//Indices de las demandas
		for ($i = 1; $i <= $de; $i++)
		{
			printf("<td align = \"center\"> <b>%d</b></td>",$i);
		}
		
		
		echo "</tr>";
		printf("<tr> <td class = \"fuente\" align = \"center\" rowspan = \"%d\">Fuente</td> <td> <b>1</b></td>",$of);
		
		//parte repetitiva
		$temp = ($i_coef-1).($j_coef-1);
		$clave = (string)$temp;
		
		if($coef != -1){ //para que no meta los casos iniciales
			$this->variables_basicas[$clave] = $coef;
			//almacenando los índices 
			$this->variables_basicas_indices[$clave]["i"] = $i_coef-1;//i
			$this->variables_basicas_indices[$clave]["j"] = $j_coef-1;//j
		}
		
		
		for ($i = 0; $i < $de; $i++)//costos entre cada par
		{
			if($vec_ofertas[0] == 0 || $vec_demandas[$i] == 0){//para imprimir las celdas tachadas
				echo "<td class = \"tachada\">";
			}else{
				echo "<td>";
			}
				
			printf("<p align = \"right\">%d</p>",$this->tablaSimplex[0][$i]);//costos entre cada par
			
			$temp = '0'.$i;//para imprimir los coeficiente básicos previos
			$clave = (string)$temp;
			if(array_key_exists($clave, $this->variables_basicas) ){
				printf("<p class = \"coeficiente\">%d</p> </td>",$this->variables_basicas[$clave]);
			}else{
				printf("<p class = \"invisible\">0</p></td>");
			}
		}
		printf("<td align = \"center\"> %d </td>",$vec_ofertas[0]);//valor de la oferta
		
		printf("</tr>");
		
		for ($i = 1; $i < $of; $i++)
		{
			printf("<tr>  <td> <b>%d</b></td> ",($i+1));//índices de fuentes
			
			for ($j = 0; $j < $de; $j++)
			{
				if($vec_ofertas[$i] == 0 || $vec_demandas[$j]== 0){//para imprimir las celdas tachadas
					echo "<td class = \"tachada\">";
				}else{
					echo "<td>";
				}
				
				printf("<p align = \"right\">%d</p>",$this->tablaSimplex[$i][$j]);//costos entre cada par
				
				$temp = $i.$j;//para imprimir los coeficientes básicos previos
				$clave = (string)$temp;
				
				if(array_key_exists($clave,$this->variables_basicas)){
					printf("<p class = \"coeficiente\">%d</p> </td>",$this->variables_basicas[$clave]);
				}else{
					printf("<p class = \"invisible\" align = \"center\">0</p> </td>");
				}
			}
			printf("<td align = \"center\">%d</td>",$vec_ofertas[$i]);//valor la oferta
			
			printf("</tr>");
		}
		
		printf("<tr > <td class = \"bordes\" align = \"center\" colspan = \"2\">Demanda</td>");
		
		for ($i = 0; $i < $de; $i++)
		{
			printf("<td align = \"center\">%d</td>",$vec_demandas[$i]);
		}
		
		printf("<td class = \"bordes\"></td> </tr>");
		//fin de parte repetitiva
		echo "
			</tbody>
			</table> <br> <br>
		";
		
	}//end-of function: tablaCentral
	
	private function formulariosEntrada($p,$f,$d){
		echo "
			<h2>Fase de lectura del caso de prueba</h2>  
			 <p class=\"error\">Error, las casillas deben contener sólo caracteres numéricos y no puede dejarlas vacía</p>
			
			<form action = \"SubirCasoTeclado.php\" method = \"post\">";
			
			$d_int = $d;//destinos
			$f_int = $f;//fuentes
			
			printf("Número fuentes: %d , Número de destinos: %d <br>",$f,$d);
			
			echo "<b> <br>Lectura de ecuación de minimización</b> <br> <br>";
			$cad_ec = "Minimizar Z = ";
			$rest_of ="";//variable para almacenar las restricciones de oferta
			$rest_dem=""; // y esta de las demandas.
			
			for($i = 1; $i <= $f_int ; $i++){
				for ($j = 1; $j <= $d_int; $j++)
				{
					$key = "f".$i.$j;//clave para recuepar la data vieja
					
					//para la ECUACIÓN DE MINIMIZACIÓN
					$cad_ec = $cad_ec . "<input type = \"text\" name = \"f". $i . $j . 
					"\" size = \"3\" maxlength = \"6\" value = \"". $p[$key] .
					"\"> &nbsp;" . "x<sub><small>".$i . $j . "</small></sub>";
					
					$rest_of = $rest_of . "x<sub><small>".$i . $j ."</small></sub>";
					
					
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
						$rest_of = $rest_of . " &nbsp;&nbsp; = &nbsp;&nbsp; b<sub><small>" . $i  . "</small></sub>&nbsp;&nbsp; <input type = \"text\" name = \"d".
						$i."\" size = \"3\" maxlength = \"6\" value = \"" .$p[$key]. "\" > <br>";
				}
				
			}
			
			$i_ant_bs = $i - 1;
			
			//RESTRICCIONES para la DEMANDA
			for ($j = 1; $j <= $d_int; $j++)
			{
				for ($i = 1; $i <= $f_int ; $i++)
				{
					$key = "d".($j+$i_ant_bs);//clave para recuperar data vieja
					
					$rest_dem = $rest_dem . "x<sub><small>". $i . $j  . "</small></sub>";
					
					if($i != $f_int){
						$rest_dem = $rest_dem . " &nbsp;&nbsp; + &nbsp;&nbsp;";
					}else{
						$rest_dem = $rest_dem . " &nbsp;&nbsp; = &nbsp;&nbsp; b<sub><small>" .
						 ($j+$i_ant_bs)  . "</small></sub>" .
						"&nbsp;&nbsp; <input type = \"text\" name = \"d".
						($j+$i_ant_bs)."\" size = \"3\" maxlength = \"6\" value = \"" . $p[$key] . "\" > <br>";
					}
				}
			}
			
			echo $cad_ec . "<br>";
			echo "
			<table >
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
	
	}
	
	public static function getFin(){
		echo "
		</div>
      
      <div class=\"clr\"></div>
    </div>
  </div>
  
  
  <div class=\"footer\">
    <div class=\"footer_resize\">
      <p class=\"lf\">Copyright &copy; FaCyT. All Rights Reserved</p>
      
      <p align = \"right\"> <a href = \"#inicio\">Ir al principio de la página</a></p>
      
      <div class=\"clr\"></div>
    </div>
    <div class=\"clr\"></div>
  </div>
</div>
</body>
</html>
		";
	}
	//FIN DE MÉTODOS DE LA INTERFAZ
	
 }//end-of-Class SimplexTransporte
  
	
?>
