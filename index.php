<?php
// 1. Leer el archivo JSON
$datos_json = file_get_contents('data-1.json');
$propiedades = json_decode($datos_json, true);

// 2. Llenar arreglos de ciudades y tipos sin repetir
$ciudades = [];
$tipos = [];

foreach ($propiedades as $prop) {
    if (!in_array($prop['Ciudad'], $ciudades)) {
        $ciudades[] = $prop['Ciudad'];
    }
    if (!in_array($prop['Tipo'], $tipos)) {
        $tipos[] = $prop['Tipo'];
    }
}

// 3. Lógica para procesar los filtros
$resultados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si el usuario hace clic en "Mostrar Todos"
    if (isset($_POST['mostrar_todos'])) {
        $resultados = $propiedades;
    } 
    // Si el usuario hace clic en "Buscar"
    elseif (isset($_POST['buscar'])) {
        $ciudad_elegida = $_POST['ciudad'] ?? '';
        $tipo_elegido = $_POST['tipo'] ?? '';
        
        // Obtener rango de precios
        $rango = $_POST['precio'] ?? '0;100000'; 
        $precios_separados = explode(";", $rango);
        $precio_min = isset($precios_separados[0]) ? intval($precios_separados[0]) : 0;
        $precio_max = isset($precios_separados[1]) ? intval($precios_separados[1]) : 100000;

        foreach ($propiedades as $prop) {
            // Limpiar el string del precio para poder compararlo matemáticamente
            $precio_limpio = str_replace(['$', ','], '', $prop['Precio']);
            $precio_num = intval($precio_limpio);

            // Verificar si cumple con los filtros
            $cumple_precio = ($precio_num >= $precio_min && $precio_num <= $precio_max);
            $cumple_ciudad = ($ciudad_elegida === '' || $prop['Ciudad'] === $ciudad_elegida);
            $cumple_tipo = ($tipo_elegido === '' || $prop['Tipo'] === $tipo_elegido);

            if ($cumple_precio && $cumple_ciudad && $cumple_tipo) {
                $resultados[] = $prop;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/customColors.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/index.css"  media="screen,projection"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Formulario</title>
</head>

<body>
  <video src="img/video.mp4" id="vidFondo"></video>

  <div class="contenedor">
    <div class="card rowTitulo">
      <h1>Buscador</h1>
    </div>
    <div class="colFiltros">
      <!-- El action="" hace que el formulario se envíe a este mismo archivo (index.php) -->
      <form action="" method="post" id="formulario">
        <div class="filtrosContenido">
          <div class="tituloFiltros">
            <h5>Realiza una búsqueda personalizada</h5>
          </div>
          <div class="filtroCiudad input-field">
            <label for="selectCiudad" style="position: relative;">Ciudad:</label>
            <!-- Agregamos class="browser-default" para que Materialize lo muestre -->
            <select name="ciudad" id="selectCiudad" class="browser-default" style="margin-top: 10px; margin-bottom: 20px;">
              <option value="" selected>Elige una ciudad</option>
              <!-- Ciclo para las Ciudades -->
              <?php foreach($ciudades as $ciudad): ?>
                  <option value="<?php echo $ciudad; ?>"><?php echo $ciudad; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroTipo input-field">
            <label for="selectTipo" style="position: relative;">Tipo:</label>
            <!-- Agregamos class="browser-default" para que Materialize lo muestre -->
            <select name="tipo" id="selectTipo" class="browser-default" style="margin-top: 10px; margin-bottom: 20px;">
              <option value="" selected>Elige un tipo</option>
              <!-- Ciclo para los Tipos -->
              <?php foreach($tipos as $tipo): ?>
                  <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroPrecio">
            <label for="rangoPrecio">Precio:</label>
            <input type="text" id="rangoPrecio" name="precio" value="" />
          </div>
          <div class="botonField">
            <input type="submit" class="btn white" value="Buscar" id="submitButton" name="buscar">
          </div>
        </div>
      </form>
    </div>

    <div class="colContenido">
      <div class="tituloContenido card">
        <h5>Resultados de la búsqueda:</h5>
        <div class="divider"></div>
        <!-- Se envuelve el botón Mostrar Todos en un form para procesarlo con PHP -->
        <form action="" method="post" style="margin-top: 15px;">
            <button type="submit" name="mostrar_todos" class="btn-flat waves-effect" id="mostrarTodos">Mostrar Todos</button>
        </form>
      </div>

      <!-- AQUÍ SE IMPRIMEN LOS RESULTADOS -->
      <?php if (!empty($resultados)): ?>
          <?php foreach($resultados as $item): ?>
              <div class="tituloContenido card">
                  <div class="itemMostrado" style="display: flex; align-items: center; padding: 15px;">
                      <img src="img/home.jpg" alt="Casa" style="width: 250px; margin-right: 20px;">
                      <div class="card-stacked">
                          <p><strong>Dirección: </strong><?php echo $item['Direccion']; ?></p>
                          <p><strong>Ciudad: </strong><?php echo $item['Ciudad']; ?></p>
                          <p><strong>Teléfono: </strong><?php echo $item['Telefono']; ?></p>
                          <p><strong>Código Postal: </strong><?php echo $item['Codigo_Postal']; ?></p>
                          <p><strong>Tipo: </strong><?php echo $item['Tipo']; ?></p>
                          <p><strong>Precio: </strong><span class="precioTexto"><?php echo $item['Precio']; ?></span></p>
                      </div>
                  </div>
              </div>
          <?php endforeach; ?>
      <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
          <div class="tituloContenido card">
              <p style="padding: 20px;">No se encontraron propiedades con esos filtros.</p>
          </div>
      <?php endif; ?>

    </div>
  </div>

  <script type="text/javascript" src="js/jquery-3.0.0.js"></script>
  <script type="text/javascript" src="js/ion.rangeSlider.min.js"></script>
  <script type="text/javascript" src="js/materialize.min.js"></script>
  <script type="text/javascript" src="js/index.js"></script>
</body>
</html>