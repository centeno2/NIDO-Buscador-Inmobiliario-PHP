<?php
declare(strict_types=1);

/**
 * Funciones puras del buscador. Trabajan únicamente con el JSON proporcionado.
 * Las rutas relativas se resuelven desde index.php/buscador.php con __DIR__.
 */

function cargarPropiedades(string $rutaJson): array
{
    if (!is_file($rutaJson) || !is_readable($rutaJson)) {
        throw new RuntimeException('No se encontró data-1.json.');
    }

    $contenido = file_get_contents($rutaJson);
    if ($contenido === false) {
        throw new RuntimeException('No fue posible leer los datos de inmuebles.');
    }

    $registros = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($registros)) {
        throw new RuntimeException('La estructura JSON no contiene un listado.');
    }

    return array_values(array_filter($registros, 'is_array'));
}

/** Convierte importes como "$30,746" a 30746. */
function precioNumerico(mixed $precio): int
{
    if (is_int($precio) || is_float($precio)) {
        return max(0, (int) round($precio));
    }

    $digitos = preg_replace('/[^0-9]/', '', (string) $precio);
    return $digitos === '' || $digitos === null ? 0 : max(0, (int) $digitos);
}

/** Produce un menú con cada valor del JSON exactamente una vez. */
function opcionesUnicas(array $registros, string $campo): array
{
    $valores = [];
    foreach ($registros as $registro) {
        $valor = trim((string) ($registro[$campo] ?? ''));
        if ($valor !== '') {
            $valores[] = $valor;
        }
    }
    $valores = array_values(array_unique($valores));
    natcasesort($valores);
    return array_values($valores);
}

/**
 * Todos los criterios se combinan con AND. Los límites son inclusivos.
 * Un valor vacío en ciudad o tipo significa "cualquiera".
 */
function filtrarPropiedades(array $registros, string $ciudad, string $tipo, int $minimo, int $maximo): array
{
    return array_values(array_filter($registros, static function (array $registro) use ($ciudad, $tipo, $minimo, $maximo): bool {
        $precio = precioNumerico($registro['Precio'] ?? 0);
        if ($precio < $minimo || $precio > $maximo) {
            return false;
        }
        if ($ciudad !== '' && (string) ($registro['Ciudad'] ?? '') !== $ciudad) {
            return false;
        }
        if ($tipo !== '' && (string) ($registro['Tipo'] ?? '') !== $tipo) {
            return false;
        }
        return true;
    }));
}

function escapar(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dinero(int $monto): string
{
    return '$' . number_format($monto, 0, '.', ',');
}
