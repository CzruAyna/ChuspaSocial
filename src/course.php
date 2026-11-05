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
 * Muro de publicaciones de una materia.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Cargar la configuracion de Moodle.
require_once(__DIR__ . '/../../config.php');
use local_chuspasocial\external\post_exporter;
use local_chuspasocial\form\post_form;
use local_chuspasocial\local\persistent\post;
use local_chuspasocial\local\post_service;

// Obtener el identificador de la materia.
$courseid = required_param('id', PARAM_INT);
$page = max(0, optional_param('page', 0, PARAM_INT));
$perpage = 20;

// Comprobar que la materia exista.
$course = get_course($courseid);

// Exigir autenticacion y acceso al curso.
require_login($course);

$context = context_course::instance($courseid);

// Verificar que el usuario este inscrito activamente.
if (!is_enrolled($context, $USER->id, '', true)) {
    throw new moodle_exception('notenrolled', 'error');
}

// Configurar la pagina.
$pageurl = new moodle_url('/local/chuspasocial/course.php', [
    'id' => $courseid,
]);

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

// Crear el formulario de publicaciones.
$form = new post_form($pageurl, [
    'courseid' => $courseid,
    'can_post_announcement' => post_service::can_post_announcement($courseid),
]);

// Procesar la publicacion enviada.
if ($data = $form->get_data()) {
    require_capability('local/chuspasocial:post', $context);

    // Usar el curso de la URL, no el campo oculto del formulario.
    $content = $data->content['text'];
    $format = $data->content['format'];

    post_service::create_post($courseid, $content, $format);

    // Evitar que se duplique la publicacion al actualizar la pagina.
    redirect($pageurl);
}

// Obtener las publicaciones visibles de la materia.
$records = post_service::get_course_feed($courseid, $page, $perpage);
// Obtener las reacciones de las publicaciones.
$counts = [];
$mine = [];

if (!empty($records)) {
    $postids = array_map(
        fn($record) => (int) $record->id,
        $records
    );

    [$insql, $inparams] = $DB->get_in_or_equal(
        $postids,
        SQL_PARAMS_NAMED,
        'pid'
    );

    $counts = $DB->get_records_sql_menu(
        "SELECT postid, COUNT(1)
           FROM {local_chuspasocial_reaction}
          WHERE postid $insql
       GROUP BY postid",
        $inparams
    );

    $mine = array_flip($DB->get_fieldset_select(
        'local_chuspasocial_reaction',
        'postid',
        "userid = :userid AND postid $insql",
        $inparams + ['userid' => (int) $USER->id]
    ));
}
// Preparar las publicaciones para la plantilla.
$posts = [];

foreach ($records as $record) {
    $record = ($record instanceof \core\persistent)
        ? $record->to_record()
        : (object) $record;

    $author = core_user::get_user($record->userid);

    if (!$author) {
        continue;
    }

       $exporter = new post_exporter($record, [
        'context' => $context,
        'author' => $author,
        'reactions' => (int) ($counts[$record->id] ?? 0),
        'hasreacted' => isset($mine[$record->id]),
       ]);

    $data = (array) $exporter->export($OUTPUT);
    $data['postid'] = $record->id;

    // Dar formato al contenido.
    $data['content'] = format_text(
        $data['content'],
        $data['contentformat'],
        ['context' => $context]
    );

    // Preparar los campos requeridos por post.mustache.
    $data['authorname'] = $data['authorfullname'];
    $data['isannouncement'] = (
        $record->type === post::TYPE_ANNOUNCEMENT
    );

    $posts[] = $data;
}

// Comprobar si existe una pagina siguiente.
$nextrecords = post_service::get_course_feed(
    $courseid,
    $page + 1,
    $perpage
);

$hasnext = !empty($nextrecords);

// Mostrar la pagina.
echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($course->fullname));

// Mostrar el formulario solamente si el usuario puede publicar.
if (has_capability('local/chuspasocial:post', $context)) {
    $form->display();
}

// Mostrar las publicaciones.
echo $OUTPUT->render_from_template(
    'local_chuspasocial/wall',
    ['posts' => $posts]
);

// Mostrar navegacion entre paginas.
if ($page > 0 || $hasnext) {
    echo html_writer::start_div('d-flex justify-content-between mt-3');

    if ($page > 0) {
        $previousurl = new moodle_url($pageurl, [
            'page' => $page - 1,
        ]);

        echo html_writer::link(
            $previousurl,
            get_string('previous'),
            ['class' => 'btn btn-secondary']
        );
    }

    if ($hasnext) {
        $nexturl = new moodle_url($pageurl, [
            'page' => $page + 1,
        ]);

        echo html_writer::link(
            $nexturl,
            get_string('next'),
            ['class' => 'btn btn-secondary ml-auto']
        );
    }

    echo html_writer::end_div();
}

echo $OUTPUT->footer();
