<?php
namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;

/**
 * Pruebas unitarias para el metodo toggle de reaction_service.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\reaction_service::toggle
 */
class reaction_service_test extends advanced_testcase {
    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Crea una publicacion de prueba directamente en la base de datos.
     *
     * @param int $userid Autor de la publicacion.
     * @param int $courseid Curso asociado (0 es el muro general).
     * @param int $hidden 1 si la publicacion esta oculta.
     * @return int Id de la publicacion.
     */
    protected function create_post(int $userid, int $courseid, int $hidden = 0): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Publicacion de prueba',
            'contentformat' => FORMAT_HTML,
            'hidden' => $hidden,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Test: toggle crea la reaccion si el usuario aun no reacciono.
     */
    public function test_toggle_crea(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $postid = $this->create_post($user->id, $course->id);

        $this->setUser($user);

        $result = reaction_service::toggle($postid);

        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_chuspasocial_reaction', [
            'postid' => $postid,
            'userid' => $user->id,
        ]));
    }

    /**
     * Test: toggle elimina la reaccion si el usuario ya habia reaccionado.
     */
    public function test_toggle_elimina(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $postid = $this->create_post($user->id, $course->id);

        $this->setUser($user);

        reaction_service::toggle($postid);
        $result = reaction_service::toggle($postid);

        $this->assertFalse($result);
        $this->assertFalse($DB->record_exists('local_chuspasocial_reaction', [
            'postid' => $postid,
            'userid' => $user->id,
        ]));
    }

    /**
     * Test: toggle no deja una reaccion sobre una publicacion no visible (oculta).
     */
    public function test_toggle_no_visible(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $postid = $this->create_post($user->id, $course->id, 1);

        $this->setUser($user);

        try {
            reaction_service::toggle($postid);
        } catch (\moodle_exception $e) {
            // Rechazar la operacion con una excepcion tambien es valido.
            $this->assertInstanceOf(\moodle_exception::class, $e);
        }

        $this->assertFalse($DB->record_exists('local_chuspasocial_reaction', [
            'postid' => $postid,
            'userid' => $user->id,
        ]));
    }
}