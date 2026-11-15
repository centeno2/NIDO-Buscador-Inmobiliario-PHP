<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/propiedades.php';

try {
    $propiedades = cargarPropiedades(__DIR__ . '/data-1.json');
} catch (Throwable $error) {
    http_response_code(500);
    exit('No se pudieron cargar los datos de bienes raíces. Revisa data-1.json.');
}

$ciudades = opcionesUnicas($propiedades, 'Ciudad');
$tipos = opcionesUnicas($propiedades, 'Tipo');
$mayorPrecio = 0;
foreach ($propiedades as $propiedad) {
    $mayorPrecio = max($mayorPrecio, precioNumerico($propiedad['Precio'] ?? 0));
}
$limiteSuperior = max(1000, (int) (ceil($mayorPrecio / 5000) * 5000));

$mostrarTodos = isset($_GET['todos']);
$busqueda = isset($_GET['buscar']);
$ciudadElegida = trim((string) ($_GET['ciudad'] ?? ''));
$tipoElegido = trim((string) ($_GET['tipo'] ?? ''));
$orden = (string) ($_GET['orden'] ?? 'relevancia');
$ordenPermitido = ['relevancia', 'precio_asc', 'precio_desc'];
if (!in_array($orden, $ordenPermitido, true)) {
    $orden = 'relevancia';
}

$minimoSolicitado = filter_input(INPUT_GET, 'precio_min', FILTER_VALIDATE_INT);
$maximoSolicitado = filter_input(INPUT_GET, 'precio_max', FILTER_VALIDATE_INT);
$minimo = is_int($minimoSolicitado) ? max(0, min($minimoSolicitado, $limiteSuperior)) : 0;
$maximo = is_int($maximoSolicitado) ? max(0, min($maximoSolicitado, $limiteSuperior)) : $limiteSuperior;
$mensajeError = '';

if ($busqueda && $minimo > $maximo) {
    $mensajeError = 'El precio mínimo no puede ser mayor que el máximo.';
}
if ($busqueda && $ciudadElegida !== '' && !in_array($ciudadElegida, $ciudades, true)) {
    $mensajeError = 'Selecciona una ciudad incluida en los datos.';
}
if ($busqueda && $tipoElegido !== '' && !in_array($tipoElegido, $tipos, true)) {
    $mensajeError = 'Selecciona un tipo de inmueble incluido en los datos.';
}

if ($mostrarTodos) {
    $resultados = $propiedades;
    $tituloResultados = 'Todos los inmuebles';
    $descripcionResultados = 'El catálogo completo, sin filtros aplicados.';
} elseif ($busqueda) {
    $resultados = $mensajeError === '' ? filtrarPropiedades($propiedades, $ciudadElegida, $tipoElegido, $minimo, $maximo) : [];
    $tituloResultados = 'Resultados de tu búsqueda';
    $descripcionResultados = 'Propiedades que coinciden con los filtros seleccionados.';
} else {
    $resultados = array_slice($propiedades, 0, 6);
    $tituloResultados = 'Para empezar a imaginar';
    $descripcionResultados = 'Una selección inicial del catálogo. Explora todos o usa los filtros.';
}

if ($orden === 'precio_asc') {
    usort($resultados, static fn (array $a, array $b): int => precioNumerico($a['Precio'] ?? 0) <=> precioNumerico($b['Precio'] ?? 0));
} elseif ($orden === 'precio_desc') {
    usort($resultados, static fn (array $a, array $b): int => precioNumerico($b['Precio'] ?? 0) <=> precioNumerico($a['Precio'] ?? 0));
}
$cantidad = count($resultados);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Busca propiedades por ciudad, tipo y rango de precio en el catálogo de bienes raíces.">
    <title>NIDO | Encuentra un lugar para ti</title>
    <link rel="stylesheet" href="css/ion.rangeSlider.css">
    <link rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="NIDO, ir al inicio">
                <span class="brand-mark" aria-hidden="true"><span></span></span>
                <span>NIDO<span class="brand-dot">.</span></span>
            </a>
            <nav class="header-nav" aria-label="Navegación principal">
                <a href="#explorar">Explorar</a>
                <a href="#como-funciona">Cómo funciona</a>
                <span class="nav-count"><?= count($propiedades) ?> propiedades</span>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" aria-labelledby="hero-titulo">
            <div class="hero-image" aria-hidden="true"></div>
            <div class="hero-content wrap">
                <span class="eyebrow"><span class="eyebrow-line"></span> TU PRÓXIMO CAPÍTULO COMIENZA AQUÍ</span>
                <h1 id="hero-titulo">Un lugar para <em>llamar hogar.</em></h1>
                <p>Encuentra espacios que conectan con tu estilo de vida. Descubre oportunidades por ubicación, tipo de vivienda y presupuesto.</p>
                <a class="hero-cta" href="#explorar">Explorar propiedades <span aria-hidden="true">↗</span></a>
                <div class="hero-metrics" aria-label="Datos del catálogo">
                    <div><strong><?= count($propiedades) ?></strong><span>Propiedades</span></div>
                    <div><strong><?= count($ciudades) ?></strong><span>Ciudades</span></div>
                    <div><strong><?= count($tipos) ?></strong><span>Tipos de hogar</span></div>
                </div>
            </div>
            <div class="hero-side-note" aria-hidden="true">ESPACIOS QUE INSPIRAN · 2026</div>
        </section>

        <section class="search-section wrap" id="explorar" aria-label="Buscador de bienes raíces">
            <div class="section-intro">
                <span class="micro-label">ENCUENTRA LO QUE BUSCAS</span>
                <h2>Tu próximo hogar, <span>a tu manera.</span></h2>
                <p>Combina los filtros y encuentra esa propiedad que encaja contigo.</p>
            </div>

            <div class="search-layout">
                <aside class="filters-panel" aria-labelledby="filtros-titulo">
                    <div class="filters-heading">
                        <span class="filter-icon" aria-hidden="true">☷</span>
                        <div><span class="small-overline">PERSONALIZA TU BÚSQUEDA</span><h3 id="filtros-titulo">Filtros</h3></div>
                    </div>
                    <form id="formulario" action="index.php#resultados" method="get">
                        <input type="hidden" name="buscar" value="1">
                        <div class="field">
                            <label for="selectCiudad">Ciudad</label>
                            <div class="select-shell">
                                <select name="ciudad" id="selectCiudad">
                                    <option value="">Todas las ciudades</option>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                        <option value="<?= escapar($ciudad) ?>" <?= $ciudad === $ciudadElegida && !$mostrarTodos ? 'selected' : '' ?>><?= escapar($ciudad) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="field">
                            <label for="selectTipo">Tipo de vivienda</label>
                            <div class="select-shell">
                                <select name="tipo" id="selectTipo">
                                    <option value="">Todos los tipos</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= escapar($tipo) ?>" <?= $tipo === $tipoElegido && !$mostrarTodos ? 'selected' : '' ?>><?= escapar($tipo) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <fieldset class="price-group">
                            <legend>Rango de precio</legend>
                            <div class="price-inputs">
                                <label for="precioMin"><span>Desde</span><span class="price-input-wrap"><b>$</b><input type="number" id="precioMin" name="precio_min" min="0" max="<?= $limiteSuperior ?>" step="1" value="<?= $mostrarTodos ? 0 : $minimo ?>"></span></label>
                                <label for="precioMax"><span>Hasta</span><span class="price-input-wrap"><b>$</b><input type="number" id="precioMax" name="precio_max" min="0" max="<?= $limiteSuperior ?>" step="1" value="<?= $mostrarTodos ? $limiteSuperior : $maximo ?>"></span></label>
                            </div>
                            <input type="text" id="rangoPrecio" aria-label="Ajustar el intervalo de precios" data-max="<?= $limiteSuperior ?>" data-min-selected="<?= $mostrarTodos ? 0 : $minimo ?>" data-max-selected="<?= $mostrarTodos ? $limiteSuperior : $maximo ?>">
                            <div class="range-extremes"><span>$0</span><span><?= escapar(dinero($limiteSuperior)) ?></span></div>
                        </fieldset>
                        <div class="field sort-field">
                            <label for="orden">Ordenar resultados</label>
                            <div class="select-shell">
                                <select name="orden" id="orden">
                                    <option value="relevancia" <?= $orden === 'relevancia' ? 'selected' : '' ?>>Orden original</option>
                                    <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Menor precio primero</option>
                                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Mayor precio primero</option>
                                </select>
                            </div>
                        </div>
                        <button class="search-button" type="submit" id="submitButton">Buscar propiedades <span aria-hidden="true">→</span></button>
                        <a class="clear-link" href="index.php#explorar">Restablecer filtros</a>
                    </form>
                    <div class="filters-footnote"><span aria-hidden="true">✦</span> Los mejores lugares empiezan con una búsqueda.</div>
                </aside>

                <div class="results-column" id="resultados">
                    <div class="results-topbar">
                        <div>
                            <span class="micro-label">EXPLORA EL CATÁLOGO</span>
                            <h3><?= escapar($tituloResultados) ?></h3>
                            <p><?= escapar($descripcionResultados) ?></p>
                        </div>
                        <a class="all-button" id="mostrarTodos" href="index.php?todos=1#resultados">Mostrar todos <span aria-hidden="true">↗</span></a>
                    </div>
                    <div class="results-summary" aria-live="polite">
                        <span><strong><?= $cantidad ?></strong> <?= $cantidad === 1 ? 'propiedad encontrada' : 'propiedades encontradas' ?></span>
                        <?php if ($busqueda && !$mostrarTodos): ?><span class="active-label">Filtros activos</span><?php endif; ?>
                    </div>
                    <?php if ($mensajeError !== ''): ?>
                        <div class="notice error" role="alert"><?= escapar($mensajeError) ?></div>
                    <?php elseif (!$resultados): ?>
                        <div class="empty-state"><span aria-hidden="true">⌕</span><h4>No encontramos propiedades con esos filtros</h4><p>Intenta ampliar tu presupuesto o seleccionar otra ciudad o tipo de vivienda.</p><a href="index.php?todos=1#resultados">Ver catálogo completo</a></div>
                    <?php else: ?>
                        <div class="property-grid">
                            <?php foreach ($resultados as $propiedad): ?>
                                <?php
                                    $tipoPropiedad = (string) ($propiedad['Tipo'] ?? 'Vivienda');
                                    $claseVisual = $tipoPropiedad === 'Casa' ? 'art-house' : ($tipoPropiedad === 'Apartamento' ? 'art-apartment' : 'art-country');
                                    $icono = $tipoPropiedad === 'Apartamento' ? '▥' : ($tipoPropiedad === 'Casa de Campo' ? '❀' : '⌂');
                                ?>
                                <article class="property-card">
                                    <div class="property-art <?= $claseVisual ?>">
                                        <span class="art-orb" aria-hidden="true"></span>
                                        <span class="art-symbol" aria-hidden="true"><?= $icono ?></span>
                                        <span class="type-pill"><?= escapar($tipoPropiedad) ?></span>
                                        <span class="property-id">REF <?= (int) ($propiedad['Id'] ?? 0) ?></span>
                                    </div>
                                    <div class="property-info">
                                        <div class="location-line"><span aria-hidden="true">⌖</span> <?= escapar($propiedad['Ciudad'] ?? '') ?></div>
                                        <h4><?= escapar($propiedad['Direccion'] ?? '') ?></h4>
                                        <p class="property-zip">Código postal <?= escapar($propiedad['Codigo_Postal'] ?? '') ?></p>
                                        <div class="property-footer"><div><span class="price-caption">PRECIO</span><strong class="property-price"><?= escapar(dinero(precioNumerico($propiedad['Precio'] ?? 0))) ?></strong></div><a class="phone-link" href="tel:<?= escapar(preg_replace('/[^+0-9]/', '', (string) ($propiedad['Telefono'] ?? ''))) ?>" aria-label="Llamar al <?= escapar($propiedad['Telefono'] ?? '') ?>">☎ <span><?= escapar($propiedad['Telefono'] ?? '') ?></span></a></div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="results-end"><span>✦</span> Cada propiedad puede ser el comienzo de algo grande.</div>
                </div>
            </div>
        </section>

        <section class="how-section" id="como-funciona">
            <div class="wrap"><div class="how-copy"><span class="micro-label">SIMPLE, COMO DEBE SER</span><h2>Menos buscar.<br><em>Más imaginar.</em></h2></div><div class="how-steps"><div><b>01</b><strong>Elige la ubicación</strong><p>Selecciona la ciudad donde te gustaría vivir.</p></div><div><b>02</b><strong>Define tu presupuesto</strong><p>Ajusta un rango de precios que se adapte a ti.</p></div><div><b>03</b><strong>Descubre opciones</strong><p>Combina filtros o explora las <?= count($propiedades) ?> propiedades.</p></div></div></div>
        </section>
    </main>
    <footer class="site-footer"><div class="wrap"><span class="brand footer-brand">NIDO<span class="brand-dot">.</span></span><span>Un proyecto de búsqueda inmobiliaria · PHP + JSON</span><a href="#hero-titulo">Volver arriba ↑</a></div></footer>

    <script src="js/jquery-3.0.0.js"></script>
    <script src="js/ion.rangeSlider.min.js"></script>
    <script src="js/app.js"></script>
    <noscript><style>#rangoPrecio{display:none}</style></noscript>
</body>
</html>
