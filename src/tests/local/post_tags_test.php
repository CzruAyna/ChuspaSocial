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

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use local_chuspasocial\local\persistent\post;

/**
 * Pruebas unitarias del guardado de etiquetas (tags) de las publicaciones.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\post_service
 */
final class post_tags_test extends advanced_testcase {

    /** @var string Componente de las etiquetas. */
    private const COMPONENT = 'local_chuspasocial';

    /** @var string Tipo de item de las etiquetas (ver db/tag.php). */
    private const ITEMTYPE = 'local_chuspasocial_post';

    /** @var string Contenido de las publicaciones de prueba. */
    private const CONTENT = 'Vendo mi libro de Calculo I.';

    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Crea un curso, inscribe a un estudiante, inicia sesion con el y crea una publicacion sin tags.
     *
     * @return post Publicacion creada con post_service::create_post().
     */
    private function create_post_as_author(): post {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $author = $generator->create_user();
        $generator->enrol_user($author->id, $course->id, 'student');
        $this->setUser($author);

        return post_service::create_post((int) $course->id, self::CONTENT, FORMAT_HTML);
    }

    /**
     * Guarda las etiquetas de una publicacion con post_service::update_post().
     *
     * @param post $post Publicacion.
     * @param string[] $tags Nombres de las etiquetas.
     */
    private function save_tags(post $post, array $tags): void {
        post_service::update_post((int) $post->get('id'), self::CONTENT, FORMAT_HTML, $tags);
    }

    /**
     * Devuelve los nombres de las etiquetas de una publicacion, ordenados.
     *
     * @param post $post Publicacion.
     * @return string[] Nombres de las etiquetas.
     */
    private function get_tags(post $post): array {
        $tags = array_values(\core_tag_tag::get_item_tags_array(
            self::COMPONENT,
            self::ITEMTYPE,
            (int) $post->get('id')
        ));
        sort($tags);
        return $tags;
    }

    /**
     * Test: una publicacion recien creada no tiene etiquetas y al guardarlas quedan asociadas.
     *
     * create_post() ya no recibe tags: las etiquetas se asignan con update_post().
     */
    public function test_create_post_with_tags(): void {
        $post = $this->create_post_as_author();
        $this->assertSame([], $this->get_tags($post));

        $this->save_tags($post, ['calculo', 'venta']);

        $this->assertSame(['calculo', 'venta'], $this->get_tags($post));
    }

    /**
     * Test: al editar las etiquetas, se reemplazan las anteriores.
     */
    public function test_edit_post_tags(): void {
        $post = $this->create_post_as_author();
        $this->save_tags($post, ['calculo', 'venta']);

        $this->save_tags($post, ['venta', 'fisica']);

        $this->assertSame(['fisica', 'venta'], $this->get_tags($post));
    }

    /**
     * Test: al eliminar la publicacion, se eliminan sus etiquetas.
     */
    public function test_delete_post_removes_tags(): void {
        global $DB;

        $post = $this->create_post_as_author();
        $postid = (int) $post->get('id');
        $this->save_tags($post, ['calculo', 'venta']);
        $this->assertCount(2, $this->get_tags($post));

        post_service::delete_post($postid);

        $this->assertSame([], $this->get_tags($post));
        $this->assertSame(0, $DB->count_records('tag_instance', [
            'component' => self::COMPONENT,
            'itemtype' => self::ITEMTYPE,
            'itemid' => $postid,
        ]));
    }
}
