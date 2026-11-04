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

namespace local_chuspasocial\external;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use context_course;
use core_external\external_api;

/**
 * Pruebas para la funcion externa toggle_reaction.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\toggle_reaction
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class toggle_reaction_test extends advanced_testcase {

    /**
     * Crea una publicacion de prueba.
     *
     * @param int $userid Identificador del usuario.
     * @param int $courseid Identificador del curso.
     * @return int Identificador de la publicacion.
     */
    protected function create_post(int $userid, int $courseid): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Publicacion de prueba',
            'contentformat' => FORMAT_HTML,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Comprueba que una llamada valida agrega una reaccion.
     */
    public function test_execute_valid_call(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_post($user->id, $course->id);

        $this->setUser($user);

        $result = toggle_reaction::execute($postid);

        $this->assertTrue($result['reacted']);

        $this->assertTrue($DB->record_exists('local_chuspasocial_reaction', [
            'postid' => $postid,
            'userid' => $user->id,
        ]));
    }

    /**
     * Comprueba que un usuario sin permiso no puede reaccionar.
     */
    public function test_execute_without_permission(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_post($user->id, $course->id);

        $studentroleid = $DB->get_field(
            'role',
            'id',
            ['shortname' => 'student'],
            MUST_EXIST
        );

        role_change_permission(
            $studentroleid,
            context_course::instance($course->id),
            'local/chuspasocial:react',
            CAP_PROHIBIT
        );

        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);

        toggle_reaction::execute($postid);
    }

    /**
     * Comprueba que la respuesta cumple con la estructura declarada.
     */
    public function test_execute_returnvalue_is_clean(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_post($user->id, $course->id);

        $this->setUser($user);

        $result = toggle_reaction::execute($postid);

        $clean = external_api::clean_returnvalue(
            toggle_reaction::execute_returns(),
            $result
        );

        $this->assertSame($result, $clean);
    }
}
