<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_chuspasocial\local;

use core_tag_tag;
use local_chuspasocial\local\persistent\post;
use moodle_exception;

/**
 * Servicio para la gestion de publicaciones (posts).
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post_service {
    /**
     * Actualiza el contenido y formato de un post existente.
     *
     * @param int $postid ID de la publicacion a actualizar.
     * @param string $content Nuevo contenido.
     * @param int $format Formato del contenido.
     * @param array $tags Tags de la publicacion.
     * @param int $draftitemid Draft area itemid with the images of the post (0 = no changes).
     * @throws moodle_exception Si el usuario actual no es el autor.
     */
    public static function update_post(
        int $postid,
        string $content,
        int $format,
        array $tags = [],
        int $draftitemid = 0
    ): void {
        global $USER;

        // Instanciar la publicacion existente.
        $post = new post($postid);

        // Verificar que el usuario actual sea el autor.
        if ((int) $post->get('userid') !== (int) $USER->id) {
            throw new moodle_exception(
                'cannotupdatepost',
                'local_chuspasocial',
                '',
                null,
                'Solo el autor puede editar esta publicacion.'
            );
        }

        // Actualizar los datos.
        $post->set('content', $content);
        $post->set('contentformat', $format);
        $post->set('timemodified', time());
        $post->set('usermodified', $USER->id);

        // Guardar los cambios.
        $post->update();

        // Determinar el contexto de la publicacion.
        if ((int) $post->get('courseid') > 0) {
            $context = \context_course::instance((int) $post->get('courseid'));
        } else {
            $context = \context_system::instance();
        }

        // Actualizar los tags de la publicacion.
        core_tag_tag::set_item_tags(
            'local_chuspasocial',
            'local_chuspasocial_post',
            $postid,
            $context,
            $tags
        );

        // Save the images of the post.
        self::save_post_images($draftitemid, $context, $postid);
    }

    /**
     * Elimina una publicacion, sus comentarios, reacciones y archivos.
     *
     * @param int $postid ID de la publicacion que se eliminara.
     * @throws \moodle_exception Si la publicacion no existe o el usuario no esta autorizado.
     */
    public static function delete_post(int $postid): void {
        global $DB, $USER;

        // Buscar la publicacion.
        $post = $DB->get_record(
            'local_chuspasocial_post',
            ['id' => $postid],
            '*',
            MUST_EXIST
        );

        // Determinar el contexto de la publicacion.
        if ((int) $post->courseid > 0) {
            $context = \context_course::instance((int) $post->courseid);
        } else {
            $context = \context_system::instance();
        }

        // Verificar si el usuario es el autor.
        $isauthor = (int) $post->userid === (int) $USER->id;

        // Comprobar el permiso de moderacion solo si esta definido.
        $ismoderator = false;
        if (get_capability_info('local/chuspasocial:moderate')) {
            $ismoderator = has_capability(
                'local/chuspasocial:moderate',
                \context_system::instance()
            );
        }

        // Solo el autor o un moderador pueden eliminar la publicacion.
        if (!$isauthor && !$ismoderator) {
            throw new \moodle_exception(
                'nopermissions',
                'error',
                '',
                'delete this post'
            );
        }

        // Iniciar la transaccion.
        $transaction = $DB->start_delegated_transaction();

        try {
            // Eliminar los comentarios de la publicacion.
            $DB->delete_records(
                'local_chuspasocial_comment',
                ['postid' => $postid]
            );

            // Eliminar las reacciones de la publicacion.
            $DB->delete_records(
                'local_chuspasocial_reaction',
                ['postid' => $postid]
            );

            // Eliminar los archivos asociados a la publicacion.
            get_file_storage()->delete_area_files(
                $context->id,
                'local_chuspasocial',
                'post',
                $postid
            );

            // Eliminar los tags de la publicacion.
            core_tag_tag::remove_all_item_tags(
                'local_chuspasocial',
                'local_chuspasocial_post',
                $postid
            );

            // Eliminar la publicacion.
            $DB->delete_records(
                'local_chuspasocial_post',
                ['id' => $postid]
            );

            // Preparar el evento de eliminacion.
            $event = \local_chuspasocial\event\post_deleted::create([
                'objectid' => $postid,
                'context' => $context,
                'userid' => $USER->id,
            ]);

            // Confirmar los cambios de la base de datos.
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            // Revertir los cambios de la transaccion si hay un error.
            $transaction->rollback($e);
        }

        // Registrar el evento despues de confirmar la transaccion.
        $event->trigger();
    }

    /**
     * Crea una publicacion.
     *
     * @param int $courseid ID del curso, o 0 para el muro general.
     * @param string $content Contenido de la publicacion.
     * @param int $format Formato del contenido.
     * @return post Publicacion creada.
     */
    public static function create_post(int $courseid, string $content, int $format): post {
        global $USER;

        // Determinar el contexto de la publicacion.
        if ($courseid === 0) {
            $context = \context_system::instance();
        } else {
            $context = \context_course::instance($courseid);
        }

        // Verificar el permiso para publicar.
        require_capability('local/chuspasocial:post', $context);

        // Exigir inscripcion activa cuando la publicacion pertenece a un curso.
        if ($courseid > 0 && !is_enrolled($context, $USER->id, '', true)) {
            throw new \moodle_exception('notenrolled', 'error');
        }

        // Preparar la nueva publicacion.
        $now = time();
        $post = new post(0, (object) [
            'userid' => $USER->id,
            'courseid' => $courseid,
            'type' => post::TYPE_POST,
            'content' => $content,
            'contentformat' => $format,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        // Guardar y devolver la publicacion.
        $post->create();

        // Registrar el evento de publicacion creada.
        $event = \local_chuspasocial\event\post_created::create([
            'objectid' => $post->get('id'),
            'context' => $context,
            'userid' => $USER->id,
        ]);
        $event->trigger();

        return $post;
    }

    /**
     * Save the images of a draft area in the postimage file area of the post.
     *
     * @param int $draftitemid Draft area itemid with the images (0 = nothing to save).
     * @param \context $context Context of the post.
     * @param int $postid Id of the post, used as itemid of the file area.
     */
    private static function save_post_images(int $draftitemid, \context $context, int $postid): void {
        if (empty($draftitemid)) {
            return;
        }

        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'local_chuspasocial',
            'postimage',
            $postid,
            [
                'accepted_types' => ['jpg', 'png', 'webp'],
                'maxfiles' => (int) get_config('local_chuspasocial', 'maxpostimages') ?: 5,
                'subdirs' => false,
            ]
        );
    }

    /**
     * Indica si un usuario puede ver una publicacion.
     *
     * - courseid 0 (muro general): requiere local/chuspasocial:view en el sistema.
     * - courseid > 0: requiere estar inscrito o tener moodle/course:view.
     * - Si la publicacion esta oculta: solo el autor o quien tenga local/chuspasocial:moderate.
     *
     * @param post $post Publicacion a consultar.
     * @param int $userid Id del usuario que intenta verla.
     * @return bool
     */
    public static function can_view(post $post, int $userid): bool {
        $courseid = (int) $post->get('courseid');

        if ($courseid === 0) {
            // Muro general: se evalua en el contexto del sistema.
            $context = \context_system::instance();
            if (!has_capability('local/chuspasocial:view', $context, $userid)) {
                return false;
            }
        } else {
            // Publicacion de materia: solo inscritos o con acceso al curso.
            $context = \context_course::instance($courseid, IGNORE_MISSING);
            if (!$context) {
                return false;
            }
            if (
                !is_enrolled($context, $userid, '', true)
                && !has_capability('moodle/course:view', $context, $userid)
            ) {
                return false;
            }
        }

        // Publicacion oculta: solo el autor o un moderador.
        if ((int) $post->get('hidden') === 1) {
            if ((int) $post->get('userid') === $userid) {
                return true;
            }
            // La capability puede no estar definida todavia (igual que en delete_post()).
            return get_capability_info('local/chuspasocial:moderate')
                && has_capability('local/chuspasocial:moderate', $context, $userid);
        }

        return true;
    }

    /**
     * Comprueba si el usuario actual puede publicar anuncios oficiales.
     *
     * @param int $courseid ID del curso, o 0 para el contexto del sistema.
     * @return bool true si tiene la capacidad, false en caso contrario.
     */
    public static function can_post_announcement(int $courseid): bool {
        if ($courseid > 0) {
            $context = \context_course::instance($courseid);
        } else {
            $context = \context_system::instance();
        }

        return has_capability('local/chuspasocial:postannouncement', $context);
    }
    /**
     * Obtiene las publicaciones visibles de una materia.
     *
     * @param int $courseid ID de la materia.
     * @param int $page Numero de pagina (base 0).
     * @param int $perpage Publicaciones por pagina.
     * @return array Lista de publicaciones, las mas recientes primero.
     */
    public static function get_course_feed(int $courseid, int $page, int $perpage): array {
        global $DB;

        return array_values($DB->get_records(
            'local_chuspasocial_post',
            ['courseid' => $courseid, 'hidden' => 0],
            'timecreated DESC, id DESC',
            '*',
            $page * $perpage,
            $perpage
        ));
    }

    /**
     * Obtiene el muro general: publicaciones generales y de mis materias.
     *
     * @param int $page Numero de pagina (base 0).
     * @param int $perpage Publicaciones por pagina.
     * @return array Registros de local_chuspasocial_post.
     */
    public static function get_general_feed(int $page, int $perpage): array {
        global $DB;

        // Cursos en los que el usuario esta inscrito, mas el muro general (courseid 0).
        $courseids = array_keys(enrol_get_my_courses('id'));
        $courseids[] = 0;

        [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');

        return array_values($DB->get_records_select(
            'local_chuspasocial_post',
            "courseid $insql",
            $params,
            'timecreated DESC, id DESC',
            '*',
            $page * $perpage,
            $perpage
        ));
    }

    /**
     * Obtiene el feed personalizado: publicaciones de usuarios y tags que sigo.
     *
     * Solo incluye publicaciones que el usuario puede ver segun la misma regla
     * que can_view(): muro general con capacidad, o materias donde esta inscrito.
     *
     * @param int $page Numero de pagina (base 0).
     * @param int $perpage Publicaciones por pagina.
     * @return array Lista de publicaciones, las mas recientes primero.
     */
    public static function get_personal_feed(int $page, int $perpage): array {
        global $DB, $USER;

        $context = \context_system::instance();

        $params = [
            'viewer1' => $USER->id,
            'usertype' => 'user',
            'viewer2' => $USER->id,
            'tagtype' => 'tag',
            'component' => 'local_chuspasocial',
            'itemtype' => 'local_chuspasocial_post',
        ];

        // Visibilidad por curso, igual que can_view().
        $visible = [];
        if (has_capability('local/chuspasocial:view', $context)) {
            $visible[] = 'p.courseid = 0';
        }
        if (has_capability('moodle/course:view', $context)) {
            $visible[] = 'p.courseid > 0 AND EXISTS (
                SELECT 1 FROM {course} c WHERE c.id = p.courseid
            )';
        } else {
            $courseids = array_keys(enrol_get_my_courses('id'));
            if (!empty($courseids)) {
                [$insql, $inparams] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'cid');
                $visible[] = "p.courseid $insql";
                $params += $inparams;
            }
        }
        if (empty($visible)) {
            return [];
        }
        $visiblesql = '(' . implode(') OR (', $visible) . ')';

        $sql = "SELECT p.*
                  FROM {local_chuspasocial_post} p
                 WHERE p.hidden = 0
                   AND ($visiblesql)
                   AND (
                       EXISTS (
                           SELECT 1
                             FROM {local_chuspasocial_follow} fu
                            WHERE fu.userid = :viewer1
                              AND fu.itemtype = :usertype
                              AND fu.itemid = p.userid
                       )
                       OR EXISTS (
                           SELECT 1
                             FROM {local_chuspasocial_follow} ft
                             JOIN {tag_instance} ti ON ti.tagid = ft.itemid
                            WHERE ft.userid = :viewer2
                              AND ft.itemtype = :tagtype
                              AND ti.component = :component
                              AND ti.itemtype = :itemtype
                              AND ti.itemid = p.id
                       )
                   )
              ORDER BY p.timecreated DESC, p.id DESC";

        return array_values($DB->get_records_sql($sql, $params, $page * $perpage, $perpage));
    }
}
