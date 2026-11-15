# NIDO — Buscador de bienes raíces con PHP

Proyecto final desarrollado a partir del archivo original **Proyecto Base.rar**, conservando su conjunto de datos `data-1.json`, los recursos locales y el complemento para el rango de precios. La interfaz fue rediseñada para ser adaptable, accesible y ejecutable en **Windows y Linux**.

## Qué incluye

- 100 propiedades reales del **archivo de ejemplo** `data-1.json` (datos de práctica, no anuncios verificados).
- Botón **Mostrar todos** que enseña los 100 registros sin filtros.
- Menú Ciudad con los 6 valores únicos encontrados en el JSON.
- Menú Tipo con los 3 valores únicos encontrados en el JSON.
- Filtro de precios con límites inclusivos, de 0 a 100000 para estos datos.
- Búsquedas por precio, ciudad + precio, tipo + precio y ciudad + tipo + precio.
- Ordenar de menor a mayor precio o viceversa, como extra.
- Una interfaz original con tarjetas, diseño responsive y estado sin resultados.
- Un endpoint opcional `buscador.php` que entrega los resultados en JSON.

## Inicio rápido en Windows

### XAMPP

1. Instala XAMPP con **Apache** y **PHP 8.0+**.
2. Descomprime la carpeta `Buscador_Bienes_Raices` en `C:\xampp\htdocs\`.
3. En el panel de XAMPP, inicia **Apache**. **MySQL no es necesario**: los registros están en el archivo JSON.
4. Abre `http://localhost/Buscador_Bienes_Raices/`.

### Laragon

1. Copia `Buscador_Bienes_Raices` dentro de `C:\laragon\www\`.
2. Inicia Apache o Nginx desde Laragon y accede a `http://localhost/Buscador_Bienes_Raices/`.

## Inicio rápido en Linux

Si tienes PHP instalado, no necesitas instalar Apache para probar:

```bash
cd Buscador_Bienes_Raices
php -S 127.0.0.1:8000
```

Después visita `http://127.0.0.1:8000/`.

También puedes colocar la carpeta en la raíz pública de tu servidor web, por ejemplo `/var/www/html/Buscador_Bienes_Raices/`, siempre que PHP esté habilitado. Los permisos deben permitir leer `data-1.json`.

## Prueba de los seis requisitos

| Requisito | Cómo comprobarlo |
| --- | --- |
| 1 Mostrar todos | Haz clic en **Mostrar todos**; aparecen **100 propiedades**. |
| 2 Menús sin duplicados | Abre los desplegables: **6 ciudades** y **3 tipos**, sin repetir opciones. |
| 3 Rango de precios | Indica **$20,000 a $40,000** y pulsa Buscar: **22 resultados**. |
| 4 Ciudad + precio | Elige **Miami**, rango **$20,000 a $60,000**: **5 resultados**. |
| 5 Tipo + precio | Elige **Casa**, rango **$20,000 a $60,000**: **17 resultados**. |
| 6 Ciudad + tipo + precio | Elige **Miami**, **Casa**, rango **$20,000 a $60,000**: **1 resultado**. |

El slider y las casillas de precio se mantienen sincronizados. Los resultados se calculan en PHP; el buscador sigue funcionando aunque JavaScript esté desactivado, usando los campos numéricos.

Para una prueba automatizada, ejecuta:

```bash
php tests/pruebas.php
```

## Cómo funcionan los datos

- `data-1.json` contiene 100 objetos con `Id`, `Direccion`, `Ciudad`, `Telefono`, `Codigo_Postal`, `Tipo` y `Precio`.
- `lib/propiedades.php` convierte precios de texto (como `$30,746`) a enteros para compararlos correctamente.
- `opcionesUnicas` extrae automáticamente los menús de Ciudad y Tipo del JSON.
- `filtrarPropiedades` combina los filtros con la condición lógica **AND** y límites inclusivos.
- `index.php` genera la interfaz y los resultados en el servidor.
- `buscador.php` devuelve los mismos resultados como JSON para usos adicionales.

## Archivos principales

```text
Buscador_Bienes_Raices/
├── index.php               Pantalla principal
├── buscador.php            API opcional de búsqueda
├── lib/propiedades.php     Lectura, normalización y filtros
├── data-1.json             100 propiedades del proyecto base
├── css/app.css             Diseño responsive NIDO
├── js/app.js               Slider y campos de precios sincronizados
├── img/nido-architecture.svg Ilustración arquitectónica vectorial original
├── css, js, font, fonts    Recursos del proyecto base
├── tests/pruebas.php       Pruebas automáticas de lógica
├── github_url.txt          Pendiente de reemplazar por URL real
└── README.md               Este documento
```

## Entrega y GitHub

El archivo `github_url.txt` contiene la dirección del repositorio de GitHub asignado a esta evaluación. La publicación efectiva del código debe comprobarse en dicho repositorio.

Para publicarlo desde la carpeta del proyecto:

```bash
git init
git add .
git commit -m "Implementar buscador inmobiliario en PHP"
```

Usa el repositorio `https://github.com/centeno2/NIDO-Buscador-Inmobiliario-PHP` y publica la rama principal.

El archivo para entrega está nombrado `ProgramacionWebConPHP_Heyner_Centeno.zip`.

## Autor

Heyner Centeno — Programación Web con PHP.

## Privacidad y dependencias

La interfaz funciona con recursos locales, sin cuentas de terceros, sin bases de datos y sin peticiones a APIs externas. Los números telefónicos, direcciones y precios del JSON son material didáctico. No deben confundirse con anuncios inmobiliarios verificados.
