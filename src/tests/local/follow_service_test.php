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
 * @covers     \local_chuspasocial\local\follow_service::get_followers
 * @covers     \local_chuspasocial\local\follow_service::get_following
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
        public function test_get_followers_lista(): void {
        $generator = $this->getDataGenerator();
        $target = $generator->create_user();
        $follower1 = $generator->create_user();
        $follower2 = $generator->create_user();
        $this->redirectMessages();

        foreach ([$follower1, $follower2] as $follower) {
            $this->setUser($follower);
            follow_service::follow_user((int) $target->id);
        }

        $result = follow_service::get_followers((int) $target->id, 0, 10);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(
            [(int) $follower1->id, (int) $follower2->id],
            array_column($result, 'id')
        );
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('fullname', $result[0]);
        $this->assertArrayHasKey('profileimageurl', $result[0]);
    }

    public function test_get_followers_sin_seguidores(): void {
        $user = $this->getDataGenerator()->create_user();

        $result = follow_service::get_followers((int) $user->id, 0, 10);

        $this->assertSame([], $result);
    }

    public function test_get_followers_orden_mas_reciente_primero(): void {
        $generator = $this->getDataGenerator();
        $target = $generator->create_user();
        $first = $generator->create_user();
        $second = $generator->create_user();
        $this->redirectMessages();

        $this->setUser($first);
        follow_service::follow_user((int) $target->id);
        $this->setUser($second);
        follow_service::follow_user((int) $target->id);

        $result = follow_service::get_followers((int) $target->id, 0, 10);

        $this->assertSame(
            [(int) $second->id, (int) $first->id],
            array_column($result, 'id')
        );
    }

    public function test_get_followers_excluye_usuarios_eliminados(): void {
        $generator = $this->getDataGenerator();
        $target = $generator->create_user();
        $active = $generator->create_user();
        $deleted = $generator->create_user();
        $this->redirectMessages();

        foreach ([$active, $deleted] as $follower) {
            $this->setUser($follower);
            follow_service::follow_user((int) $target->id);
        }
        delete_user($deleted);

        $result = follow_service::get_followers((int) $target->id, 0, 10);

        $this->assertSame([(int) $active->id], array_column($result, 'id'));
    }

    public function test_get_followers_paginacion(): void {
        $generator = $this->getDataGenerator();
        $target = $generator->create_user();
        $this->redirectMessages();

        for ($i = 0; $i < 5; $i++) {
            $this->setUser($generator->create_user());
            follow_service::follow_user((int) $target->id);
        }

        $page0 = follow_service::get_followers((int) $target->id, 0, 2);
        $page1 = follow_service::get_followers((int) $target->id, 1, 2);
        $page2 = follow_service::get_followers((int) $target->id, 2, 2);
        $page3 = follow_service::get_followers((int) $target->id, 3, 2);

        $this->assertCount(2, $page0);
        $this->assertCount(2, $page1);
        $this->assertCount(1, $page2);
        $this->assertCount(0, $page3);

        $ids = array_merge(
            array_column($page0, 'id'),
            array_column($page1, 'id'),
            array_column($page2, 'id')
        );
        $this->assertCount(5, array_unique($ids));
    }

    public function test_get_following_lista(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $followed1 = $generator->create_user();
        $followed2 = $generator->create_user();
        $this->setUser($user);
        $this->redirectMessages();

        follow_service::follow_user((int) $followed1->id);
        follow_service::follow_user((int) $followed2->id);

        $result = follow_service::get_following((int) $user->id, 0, 10);

        $this->assertCount(2, $result);
        $this->assertEqualsCanonicalizing(
            [(int) $followed1->id, (int) $followed2->id],
            array_column($result, 'id')
        );
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('fullname', $result[0]);
        $this->assertArrayHasKey('profileimageurl', $result[0]);
    }

    public function test_get_following_sin_seguidos(): void {
        $user = $this->getDataGenerator()->create_user();

        $result = follow_service::get_following((int) $user->id, 0, 10);

        $this->assertSame([], $result);
    }

    public function test_get_following_excluye_usuarios_eliminados(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $active = $generator->create_user();
        $deleted = $generator->create_user();
        $this->setUser($user);
        $this->redirectMessages();

        follow_service::follow_user((int) $active->id);
        follow_service::follow_user((int) $deleted->id);
        delete_user($deleted);

        $result = follow_service::get_following((int) $user->id, 0, 10);

        $this->assertSame([(int) $active->id], array_column($result, 'id'));
    }

    public function test_get_following_paginacion(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);
        $this->redirectMessages();

        for ($i = 0; $i < 5; $i++) {
            follow_service::follow_user((int) $generator->create_user()->id);
        }

        $page0 = follow_service::get_following((int) $user->id, 0, 2);
        $page1 = follow_service::get_following((int) $user->id, 1, 2);
        $page2 = follow_service::get_following((int) $user->id, 2, 2);
        $page3 = follow_service::get_following((int) $user->id, 3, 2);

        $this->assertCount(2, $page0);
        $this->assertCount(2, $page1);
        $this->assertCount(1, $page2);
        $this->assertCount(0, $page3);

        $ids = array_merge(
            array_column($page0, 'id'),
            array_column($page1, 'id'),
            array_column($page2, 'id')
        );
        $this->assertCount(5, array_unique($ids));
    }
}
