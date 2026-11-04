<?php
namespace local_chuspasocial\local;

/**
 * Pruebas unitarias para report_service::create_report()
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\report_service::create_report
 */
class report_service_test extends \advanced_testcase {

    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    public function test_create_report_valido(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'post',
            'itemid' => 101,
            'reason' => 'Contenido inapropiado',
        ];

        $report = report_service::create_report($data);

        $this->assertNotEmpty($report);
        $this->assertEquals($user->id, $report->userid);
        $this->assertEquals('post', $report->itemtype);
    }

    public function test_create_report_duplicado(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'post',
            'itemid' => 101,
            'reason' => 'Primer reporte',
        ];

        // Se crea el primer reporte válido
        report_service::create_report($data);

        // Intentar crear exactamente el mismo reporte debe lanzar una excepción
        $this->expectException(\moodle_exception::class);
        report_service::create_report($data);
    }

    public function test_create_report_itemtype_invalido(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $data = (object) [
            'userid' => $user->id,
            'itemtype' => 'itemtype_no_existente',
            'itemid' => 101,
            'reason' => 'Prueba itemtype inválido',
        ];

        $this->expectException(\invalid_parameter_exception::class);
        report_service::create_report($data);
    }

    /**
     * Create a report record for the tests, without going through create_report().
     *
     * @param string $itemtype Type of the reported item.
     * @param int $itemid Id of the reported item.
     * @param int $reporterid Id of the user who reports.
     * @return int Id of the new report.
     */
    private function create_test_report(string $itemtype, int $itemid, int $reporterid): int {
        global $DB;

        return (int) $DB->insert_record('local_chuspasocial_report', (object) [
            'itemtype' => $itemtype,
            'itemid' => $itemid,
            'reporterid' => $reporterid,
            'reason' => 'Contenido inapropiado',
            'status' => 'open',
            'timecreated' => time(),
        ]);
    }

    /**
     * Register the moderate capability (not yet defined in db/access.php)
     * and give it to a new user through a role at system level.
     *
     * @return \stdClass The moderator user.
     */
    private function create_moderator(): \stdClass {
        global $DB;

        if (!get_capability_info('local/chuspasocial:moderate')) {
            $DB->insert_record('capabilities', (object) [
                'name' => 'local/chuspasocial:moderate',
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'component' => 'local_chuspasocial',
                'riskbitmask' => 0,
            ]);
            \cache::make('core', 'capabilities')->purge();
        }

        $moderator = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        $systemcontext = \context_system::instance();
        assign_capability('local/chuspasocial:moderate', CAP_ALLOW, $roleid, $systemcontext->id);
        role_assign($roleid, $moderator->id, $systemcontext->id);

        return $moderator;
    }

    /**
     * A moderator can resolve a report and the reported post gets hidden.
     *
     * @covers \local_chuspasocial\local\report_service::resolve_and_hide
     */
    public function test_resolve_and_hide_by_moderator(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $reporter = $generator->create_user();
        $postid = (int) $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $reporter->id,
            'courseid' => $course->id,
            'type' => 'post',
            'content' => 'Test post',
            'contentformat' => FORMAT_HTML,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $reportid = $this->create_test_report('post', $postid, $reporter->id);
        $moderator = $this->create_moderator();

        $this->setUser($moderator);
        report_service::resolve_and_hide($reportid);

        $report = $DB->get_record('local_chuspasocial_report', ['id' => $reportid]);
        $this->assertEquals('resolved', $report->status);
        $this->assertEquals($moderator->id, $report->resolvedby);
        $this->assertNotEmpty($report->timeresolved);
        $this->assertEquals(1, $DB->get_field('local_chuspasocial_post', 'hidden', ['id' => $postid]));
    }

    /**
     * A normal user without the moderate capability cannot resolve a report.
     *
     * @covers \local_chuspasocial\local\report_service::resolve_and_hide
     */
    public function test_resolve_and_hide_by_normal_user_throws_exception(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $reporter = $generator->create_user();
        $other = $generator->create_user();
        $reportid = $this->create_test_report('post', 101, $reporter->id);

        $this->setUser($other);
        try {
            report_service::resolve_and_hide($reportid);
            $this->fail('Expected required_capability_exception was not thrown.');
        } catch (\required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

        $report = $DB->get_record('local_chuspasocial_report', ['id' => $reportid]);
        $this->assertEquals('open', $report->status);
    }

}