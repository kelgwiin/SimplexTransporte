<?php
	include("st.php");
	$simplex_t = new SimplexTransporte();
	
	$simplex_t::getIni();//interfaz
	
	//LEER TIPO DE MÉTODO DE UNA ARCHIVO
	//Sube el caso al servidor
	if($simplex_t->SubirCaso())// subirlo y comprobar si fue exitosa la carga.
	{
		//lee y procesa la entrada que se acabo de subir
		//Se guarda la tabla de simplex de transporte
		//Se guarda el Sistema Simplex Original
		$simplex_t->procesarDataArchivo("MyFiles/".$_FILES["caso"]["name"]);
	
	
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
		}
		
	}
	$simplex_t::getFin();//Interfaz
?>
