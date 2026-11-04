<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Script CLI que genera datos de ejemplo para local_chuspasocial.
 *
 * Crea publicaciones, comentarios y anuncios de libros usando los servicios
 * del plugin. Pensado unicamente para sitios de desarrollo.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

use local_chuspasocial\local\comment_service;
use local_chuspasocial\local\listing_service;
use local_chuspasocial\local\post_service;

[$options, $unrecognized] = cli_get_params(
    [
        'posts' => 5,
        'comments' => 2,
        'listings' => 3,
        'help' => false,
    ],
    [
        'p' => 'posts',
        'c' => 'comments',
        'l' => 'listings',
        'h' => 'help',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln("Genera datos de ejemplo para el plugin ChuspaSocial.

Crea publicaciones, comentarios y anuncios de libros en el sitio actual.
Usar solamente en sitios de desarrollo: los registros se crean de verdad.

Opciones:
  -p, --posts=N      Cantidad de publicaciones a crear. Por defecto: 5.
  -c, --comments=N   Comentarios por cada publicacion. Por defecto: 2.
  -l, --listings=N   Cantidad de anuncios de libros a crear. Por defecto: 3.
  -h, --help         Muestra esta ayuda.

Ejemplo:
  php local/chuspasocial/cli/generate_sample_data.php --posts=10 --comments=3 --listings=5");
    exit(0);
}

$numposts = (int) $options['posts'];
$numcomments = (int) $options['comments'];
$numlistings = (int) $options['listings'];

if ($numposts < 0 || $numcomments < 0 || $numlistings < 0) {
    cli_error('Las cantidades no pueden ser negativas.');
}

$admin = get_admin();
if (!$admin) {
    cli_error('No se encontro un usuario administrador en este sitio.');
}
\core\session\manager::set_user($admin);

cli_writeln('Generando datos de ejemplo para local_chuspasocial...');

$postids = [];
for ($i = 1; $i <= $numposts; $i++) {
    $postids[] = post_service::create_post("Publicacion de ejemplo numero {$i}.", FORMAT_HTML);
}
cli_writeln('Publicaciones creadas: ' . count($postids));

$totalcomments = 0;
foreach ($postids as $postid) {
    for ($j = 1; $j <= $numcomments; $j++) {
        comment_service::add_comment($postid, $admin->id, "Comentario de ejemplo numero {$j}.");
        $totalcomments++;
    }
}
cli_writeln('Comentarios creados: ' . $totalcomments);

$totallistings = 0;
if (!get_config('local_chuspasocial', 'enablemarketplace')) {
    cli_problem('El marketplace esta desactivado: se omiten los anuncios de libros.');
} else {
    for ($i = 1; $i <= $numlistings; $i++) {
        $data = new stdClass();
        $data->title = "Libro de ejemplo {$i}";
        $data->author = "Autor de ejemplo {$i}";
        $data->description = "Descripcion del libro de ejemplo numero {$i}.";
        $data->descriptionformat = FORMAT_HTML;
        $data->price = 50 + $i;
        $data->externalcontact = 'ejemplo@chuspasocial.test';
        listing_service::create_listing($data);
        $totallistings++;
    }
    cli_writeln('Anuncios creados: ' . $totallistings);
}

cli_writeln('Listo.');
exit(0);
