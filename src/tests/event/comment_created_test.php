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

namespace local_chuspasocial\event;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use local_chuspasocial\local\comment_service;

/**
 * Pruebas unitarias para el evento comment_created.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\event\comment_created
 */
class comment_created_test extends advanced_testcase {
    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Crea un post de prueba del muro general directamente en la base de datos.
     *
     * @param int $userid Autor del post.
     * @return int Id del post creado.
     */
    private function crear_post(int $userid): int {
        global $DB;

        $post = new \stdClass();
        $post->userid = $userid;
        $post->courseid = 0;
        $post->type = 'post';
        $post->content = 'Post de prueba';
        $post->contentformat = FORMAT_PLAIN;
        $post->hidden = 0;
        $post->timecreated = time();
        $post->timemodified = time();

        return $DB->insert_record('local_chuspasocial_post', $post);
    }

    /**
     * Test: Comentar un post dispara exactamente un evento comment_created
     * con el objectid correcto.
     */
    public function test_comentar_dispara_un_evento_comment_created(): void {
        $generator = $this->getDataGenerator();
        $autor = $generator->create_user();
        $comentarista = $generator->create_user();
        $postid = $this->crear_post((int) $autor->id);

        $this->setUser($comentarista);
        $sink = $this->redirectEvents();

        $comment = comment_service::add_comment($postid, 'Buen aporte');

        $events = $sink->get_events();
        $sink->close();

        $creados = array_values(array_filter($events, function ($event) {
            return $event instanceof comment_created;
        }));

        // Exactamente un evento comment_created.
        $this->assertCount(1, $creados);

        $event = $creados[0];

        // El objectid es el id del comentario creado.
        $this->assertEquals($comment->get('id'), $event->objectid);
        $this->assertEquals('local_chuspasocial_comment', $event->objecttable);
        $this->assertEquals($comentarista->id, $event->userid);
        $this->assertEquals($postid, $event->other['postid']);
        $this->assertEquals(\context_system::instance()->id, $event->contextid);
        $this->assertEventContextNotUsed($event);
    }

    /**
     * Test: El nombre y la descripcion del evento identifican al usuario y al comentario.
     */
    public function test_descripcion_del_evento(): void {
        $event = comment_created::create([
            'objectid' => 15,
            'context' => \context_system::instance(),
            'userid' => 7,
        ]);

        $this->assertNotEmpty(comment_created::get_name());
        $this->assertStringContainsString("'7'", $event->get_description());
        $this->assertStringContainsString("'15'", $event->get_description());
    }
}
