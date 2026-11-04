<?php
namespace local_chuspasocial\local;

/**
 * Pruebas unitarias para follow_service::follow_tag() y follow_service::unfollow_tag()
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\follow_service::follow_tag
 * @covers     \local_chuspasocial\local\follow_service::unfollow_tag
 * @covers     \local_chuspasocial\local\follow_service::follow_user
 * @covers     \local_chuspasocial\local\follow_service::unfollow_user
 */
class follow_service_test extends \advanced_testcase {

    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_follow_tag_valido(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $tag = $generator->create_tag(['rawname' => 'moodle']);
        $this->setUser($user);

        follow_service::follow_tag((int) $tag->id);

        $this->assertTrue($DB->record_exists('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'tag',
            'itemid' => $tag->id,
        ]));
    }

    public function test_unfollow_tag_valido(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $tag = $generator->create_tag(['rawname' => 'moodle']);
        $this->setUser($user);

        follow_service::follow_tag((int) $tag->id);
        follow_service::unfollow_tag((int) $tag->id);

        $this->assertFalse($DB->record_exists('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'tag',
            'itemid' => $tag->id,
        ]));
    }

    public function test_unfollow_tag_inexistente(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Dejar de seguir un tag que no existe no debe fallar ni crear datos.
        follow_service::unfollow_tag(999999);

        $this->assertEquals(0, $DB->count_records('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'tag',
        ]));
    }

     public function test_follow_user_valido(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $followed = $generator->create_user();
        $this->setUser($user);
        $sink = $this->redirectMessages();

        follow_service::follow_user((int) $followed->id);

        $this->assertTrue($DB->record_exists('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'user',
            'itemid' => $followed->id,
        ]));
        // Se notifica al usuario seguido.
        $this->assertEquals(1, $sink->count());
    }

    public function test_unfollow_user_valido(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $followed = $generator->create_user();
        $this->setUser($user);
        $this->redirectMessages();

        follow_service::follow_user((int) $followed->id);
        follow_service::unfollow_user((int) $followed->id);

        $this->assertFalse($DB->record_exists('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'user',
            'itemid' => $followed->id,
        ]));
    }

    public function test_follow_user_a_si_mismo(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        try {
            follow_service::follow_user((int) $user->id);
            $this->fail('Se esperaba moodle_exception al seguirse a si mismo');
        } catch (\moodle_exception $e) {
            // No debe quedar ningun seguimiento creado.
            $this->assertEquals(0, $DB->count_records('local_chuspasocial_follow', [
                'userid' => $user->id,
                'itemtype' => 'user',
            ]));
        }
    }

    public function test_follow_user_duplicado(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $followed = $generator->create_user();
        $this->setUser($user);
        $sink = $this->redirectMessages();

        follow_service::follow_user((int) $followed->id);
        follow_service::follow_user((int) $followed->id);

        // Solo un registro y una sola notificacion.
        $this->assertEquals(1, $DB->count_records('local_chuspasocial_follow', [
            'userid' => $user->id,
            'itemtype' => 'user',
            'itemid' => $followed->id,
        ]));
        $this->assertEquals(1, $sink->count());
    }
}
