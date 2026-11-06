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

use advanced_testcase;
use local_chuspasocial\local\post_service;

/**
 * Pruebas para el evento post_created.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\event\post_created
 */
class post_created_event_test extends advanced_testcase {
    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Verifica el evento post_created y sus atributos.
     */
    public function test_post_created_atributos(): void {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $autor = $generator->create_user();

        $generator->enrol_user($autor->id, $course->id, 'editingteacher');
        $this->setUser($autor);

        $context = \context_course::instance($course->id);

        $eventsink = $this->redirectEvents();

        $post = post_service::create_post(
            (int) $course->id,
            'Publicacion de prueba',
            FORMAT_PLAIN
        );

        $events = $eventsink->get_events();
        $eventsink->close();

        $creados = array_values(array_filter($events, function ($event) {
            return $event instanceof post_created;
        }));

        $this->assertCount(1, $creados);

        $event = $creados[0];

        $this->assertEquals($post->get('id'), $event->objectid);
        $this->assertEquals($autor->id, $event->userid);
        $this->assertEquals($course->id, $event->courseid);
        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals('local_chuspasocial_post', $event->objecttable);
        $this->assertEventContextNotUsed($event);
    }
}
