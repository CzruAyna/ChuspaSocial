<?php
namespace local_chuspasocial\local;

/**
 * Pruebas unitarias para la notificación nuevofollower.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\follow_service::follow_user
 */
class message_newfollower_test extends \advanced_testcase {

    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_newfollower_notifica_al_destinatario(): void {
        $generator = $this->getDataGenerator();
        $follower = $generator->create_user();
        $followed = $generator->create_user();
        $this->setUser($follower);
        $sink = $this->redirectMessages();

        follow_service::follow_user((int) $followed->id);

        $messages = $sink->get_messages();
        $sink->close();

        // Exactamente una notificacion, dirigida al usuario seguido.
        $this->assertCount(1, $messages);
        $this->assertEquals($followed->id, $messages[0]->useridto);
    }

    public function test_newfollower_no_notifica_al_autor(): void {
        $generator = $this->getDataGenerator();
        $follower = $generator->create_user();
        $followed = $generator->create_user();
        $this->setUser($follower);
        $sink = $this->redirectMessages();

        follow_service::follow_user((int) $followed->id);

        $messages = $sink->get_messages();
        $sink->close();

        // Ninguna notificacion le llega a quien sigue.
        foreach ($messages as $message) {
            $this->assertNotEquals($follower->id, $message->useridto);
        }
    }
}