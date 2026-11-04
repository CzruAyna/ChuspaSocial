<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/chuspasocial/lib.php');

class local_chuspasocial_lib_test extends \advanced_testcase {

    /**
     * Verifica que pluginfile rechace areas no permitidas.
     */
    public function test_pluginfile_rechaza_area_no_permitida() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $context = \context_course::instance($course->id);

        $this->setUser($user);

        $resultado = local_chuspasocial_pluginfile(
            $course,
            null,
            $context,
            'post',
            ['1', 'imagen_privada.png'],
            false,
            []
        );

        $this->assertFalse(
            $resultado,
            'El plugin debe rechazar areas de archivos no permitidas.'
        );
    }

    /**
     * Verifica que el menu Wall aparezca con el permiso adecuado.
     */
    public function test_navigation_agrega_wall_con_permiso() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $context = \context_course::instance($course->id);

        $roleid = $this->getDataGenerator()->create_role();
        role_assign($roleid, $user->id, $context->id);

        assign_capability(
            'local/chuspasocial:view',
            CAP_ALLOW,
            $roleid,
            $context->id
        );

        $this->setUser($user);

        $navigation = \navigation_node::create(
            'Curso',
            null,
            \navigation_node::TYPE_COURSE
        );

        local_chuspasocial_extend_navigation_course(
            $navigation,
            $course,
            $context
        );

        $node = $navigation->find(
            'local_chuspasocial_wall',
            \navigation_node::TYPE_SETTING
        );

        $this->assertInstanceOf(
            \navigation_node::class,
            $node,
            'El menu Wall debe aparecer si el usuario tiene permiso.'
        );

        $this->assertEquals(
            get_string('wall', 'local_chuspasocial'),
            $node->text
        );
    }

    /**
     * Verifica que el menu Wall no aparezca sin permiso.
     */
    public function test_navigation_no_agrega_wall_sin_permiso() {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $context = \context_course::instance($course->id);

        $roleid = $this->getDataGenerator()->create_role();
        role_assign($roleid, $user->id, $context->id);

        assign_capability(
            'local/chuspasocial:view',
            CAP_PROHIBIT,
            $roleid,
            $context->id
        );

        $this->setUser($user);

        $navigation = \navigation_node::create(
            'Curso',
            null,
            \navigation_node::TYPE_COURSE
        );

        local_chuspasocial_extend_navigation_course(
            $navigation,
            $course,
            $context
        );

        $node = $navigation->find(
            'local_chuspasocial_wall',
            \navigation_node::TYPE_SETTING
        );

        $this->assertFalse(
            $node,
            'El menu Wall no debe aparecer sin el permiso view.'
        );
    }

    /**
     * Verifica que Wall dirija al curso correspondiente.
     */
    public function test_navigation_wall_url_correcta() {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $navigation = \navigation_node::create(
            'Curso',
            null,
            \navigation_node::TYPE_COURSE
        );

        local_chuspasocial_extend_navigation_course(
            $navigation,
            $course,
            $context
        );

        $node = $navigation->find(
            'local_chuspasocial_wall',
            \navigation_node::TYPE_SETTING
        );

        $this->assertInstanceOf(
            \navigation_node::class,
            $node
        );

        $expectedurl = new \moodle_url(
            '/local/chuspasocial/course.php',
            ['id' => $course->id]
        );

        $this->assertEquals(
            $expectedurl->out(false),
            $node->action->out(false),
            'El enlace Wall debe dirigir al curso correcto.'
        );
    }
}
