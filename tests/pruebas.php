<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/propiedades.php';

function verificar(bool $condicion, string $nombre): void
{
    if (!$condicion) {
        fwrite(STDERR, '[ERROR] ' . $nombre . PHP_EOL);
        exit(1);
    }
    echo '[OK] ' . $nombre . PHP_EOL;
}

$datos = cargarPropiedades(dirname(__DIR__) . '/data-1.json');
$ciudades = opcionesUnicas($datos, 'Ciudad');
$tipos = opcionesUnicas($datos, 'Tipo');

verificar(count($datos) === 100, 'Se cargan los 100 registros del JSON');
verificar(count($ciudades) === 6 && count(array_unique($ciudades)) === 6, 'Hay 6 ciudades únicas');
verificar(count($tipos) === 3 && count(array_unique($tipos)) === 3, 'Hay 3 tipos únicos');
verificar(precioNumerico('$30,746') === 30746, 'El formato de precio se convierte correctamente');
verificar(count(filtrarPropiedades($datos, '', '', 0, 100000)) === 100, 'El rango total incluye los 100 registros');

$soloPrecio = filtrarPropiedades($datos, '', '', 20000, 40000);
verificar(count($soloPrecio) > 0, 'El filtro de precio devuelve registros');
foreach ($soloPrecio as $registro) {
    verificar(precioNumerico($registro['Precio']) >= 20000 && precioNumerico($registro['Precio']) <= 40000, 'Precio dentro del rango');
}

$soloCiudad = filtrarPropiedades($datos, 'Miami', '', 20000, 60000);
verificar(count($soloCiudad) > 0, 'La búsqueda por ciudad y precio devuelve registros');
foreach ($soloCiudad as $registro) {
    verificar($registro['Ciudad'] === 'Miami', 'Ciudad correcta');
}

$soloTipo = filtrarPropiedades($datos, '', 'Casa', 20000, 60000);
verificar(count($soloTipo) > 0, 'La búsqueda por tipo y precio devuelve registros');
foreach ($soloTipo as $registro) {
    verificar($registro['Tipo'] === 'Casa', 'Tipo correcto');
}

$combinados = filtrarPropiedades($datos, 'Miami', 'Casa', 20000, 60000);
verificar(count($combinados) > 0, 'La combinación de tres filtros devuelve registros');
foreach ($combinados as $registro) {
    verificar($registro['Ciudad'] === 'Miami' && $registro['Tipo'] === 'Casa'
        && precioNumerico($registro['Precio']) >= 20000
        && precioNumerico($registro['Precio']) <= 60000, 'La combinación respeta los tres criterios');
}

verificar(count(filtrarPropiedades($datos, 'Ciudad inexistente', '', 0, 100000)) === 0, 'No hay coincidencias falsas');
echo 'Todas las pruebas terminaron correctamente.' . PHP_EOL;
