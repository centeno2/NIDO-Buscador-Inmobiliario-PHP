<?php
declare(strict_types=1);

/**
 * API JSON opcional para comprobar los filtros o consumir los resultados
 * desde otras interfaces. La página principal funciona sin AJAX.
 * Ejemplo: buscador.php?ciudad=Miami&tipo=Casa&precio_min=20000&precio_max=60000
 */

require_once __DIR__ . '/lib/propiedades.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $registros = cargarPropiedades(__DIR__ . '/data-1.json');
    $ciudad = trim((string) ($_REQUEST['ciudad'] ?? ''));
    $tipo = trim((string) ($_REQUEST['tipo'] ?? ''));
    $mostrarTodos = isset($_REQUEST['todos']);
    $minInput = filter_var($_REQUEST['precio_min'] ?? 0, FILTER_VALIDATE_INT);
    $precioMaximo = max(array_map(static fn(array $item): int => precioNumerico($item['Precio'] ?? 0), $registros));
    $maxInput = filter_var($_REQUEST['precio_max'] ?? $precioMaximo, FILTER_VALIDATE_INT);

    if (!$mostrarTodos && ($minInput === false || $maxInput === false || $minInput < 0 || $maxInput < $minInput)) {
        http_response_code(422);
        echo json_encode(['error' => 'Rango de precios incorrecto.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $ciudades = opcionesUnicas($registros, 'Ciudad');
    $tipos = opcionesUnicas($registros, 'Tipo');
    if (!$mostrarTodos && (($ciudad !== '' && !in_array($ciudad, $ciudades, true)) || ($tipo !== '' && !in_array($tipo, $tipos, true)))) {
        http_response_code(422);
        echo json_encode(['error' => 'Ciudad o tipo no incluido en los datos.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $filtrados = $mostrarTodos ? $registros : filtrarPropiedades($registros, $ciudad, $tipo, $minInput, $maxInput);
    echo json_encode([
        'total' => count($filtrados),
        'registros' => $filtrados,
        'ciudades' => $ciudades,
        'tipos' => $tipos,
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible procesar los datos de inmuebles.'], JSON_UNESCAPED_UNICODE);
}
