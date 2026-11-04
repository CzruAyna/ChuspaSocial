<?php

namespace local_persistent;

use advanced_testcase;
use core\persistent;
use stdClass;

/**
 * Clase concreta de prueba para extender core/persistent.
 */
class dummy_persistent extends persistent {

    const TABLE = 'dummy_persistent';

    protected static function define_properties(): array {
        return [
            'name' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'status' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
}

/**
 * Pruebas unitarias para los estados de los reportes.
 *
 * @package local_persistent
 * @category test
 * @covers \core\persistent
 */
class report_test extends advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    public function test_creacion_valida(): void {
        $record = new stdClass();
        $record->name = 'Reporte de prueba';
        $record->status = 1;

        $persistent = new dummy_persistent(0, $record);

        $this->assertInstanceOf(persistent::class, $persistent);
        $this->assertEquals('Reporte de prueba', $persistent->get('name'));
    }

    public function test_valores_por_defecto(): void {
        $persistent = new dummy_persistent(0);

        $this->assertEquals(0, $persistent->get('id'));
        $this->assertEquals('', $persistent->get('name'));
    }

    public function test_campo_invalido_lanza_excepcion(): void {
        $this->expectException(\coding_exception::class);

        $persistent = new dummy_persistent(0);
        $persistent->get('campo_inexistente_que_no_existe');
    }

    /**
     * Verifica la transicion y persistencia de los estados de un reporte.
     */
    public function test_ciclo_vida_estado_reporte(): void {
        global $DB;

        $reporter = $this->getDataGenerator()->create_user();
        $moderator = $this->getDataGenerator()->create_user();

        // Crear un reporte en estado abierto.
        $report = new stdClass();
        $report->itemtype = 'post';
        $report->itemid = 100;
        $report->reporterid = $reporter->id;
        $report->reason = 'Contenido de prueba';
        $report->status = 'open';
        $report->timecreated = time();

        $reportid = $DB->insert_record(
            'local_chuspasocial_report',
            $report
        );

        // Verificar que el estado inicial se guardo.
        $stored = $DB->get_record(
            'local_chuspasocial_report',
            ['id' => $reportid],
            '*',
            MUST_EXIST
        );

        $this->assertEquals('open', $stored->status);

        // Cambiar el estado a resuelto.
        $stored->status = 'resolved';
        $stored->resolvedby = $moderator->id;
        $stored->timeresolved = time();

        $DB->update_record('local_chuspasocial_report', $stored);

        // Recuperar el registro y verificar el estado actualizado.
        $updated = $DB->get_record(
            'local_chuspasocial_report',
            ['id' => $reportid],
            '*',
            MUST_EXIST
        );

        $this->assertEquals('resolved', $updated->status);
        $this->assertEquals($moderator->id, $updated->resolvedby);
        $this->assertNotEmpty($updated->timeresolved);

        // Crear otro reporte para comprobar el estado descartado.
        $discarded = new stdClass();
        $discarded->itemtype = 'post';
        $discarded->itemid = 101;
        $discarded->reporterid = $reporter->id;
        $discarded->reason = 'Segundo reporte de prueba';
        $discarded->status = 'open';
        $discarded->timecreated = time();

        $discardedid = $DB->insert_record(
            'local_chuspasocial_report',
            $discarded
        );

        $discarded->id = $discardedid;
        $discarded->status = 'dismissed';
        $discarded->resolvedby = $moderator->id;
        $discarded->timeresolved = time();

        $DB->update_record('local_chuspasocial_report', $discarded);

        // Comprobar que el estado descartado se conserva en la base de datos.
        $retrieved = $DB->get_record(
            'local_chuspasocial_report',
            ['id' => $discardedid],
            '*',
            MUST_EXIST
        );

        $this->assertEquals('dismissed', $retrieved->status);
        $this->assertEquals($moderator->id, $retrieved->resolvedby);
        $this->assertNotEmpty($retrieved->timeresolved);
    }
}
