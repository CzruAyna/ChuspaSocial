<?php

namespace local_persistent;

use advanced_testcase;
use core\persistent;
use stdClass;

class listing_course extends persistent {
    const TABLE = 'local_chuspasocial_lstcourse';

    protected static function define_properties(): array {
        return [
            'listingid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
}

/**
 * Pruebas para la clase persistente de vinculos entre publicaciones y cursos.
 *
 * @package local_persistent
 * @category test
 * @covers \core\persistent
 */
class listing_course_test extends advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    public function test_creacion_valida(): void {
        $record = new stdClass();
        $record->listingid = 10;
        $record->courseid = 20;

        $persistent = new listing_course(0, $record);

        $this->assertInstanceOf(persistent::class, $persistent);
        $this->assertEquals(10, $persistent->get('listingid'));
        $this->assertEquals(20, $persistent->get('courseid'));
    }

    public function test_valores_por_defecto(): void {
        $persistent = new listing_course(0);

        $this->assertEquals(0, $persistent->get('id'));
        $this->assertEquals(0, $persistent->get('listingid'));
        $this->assertEquals(0, $persistent->get('courseid'));
    }

    public function test_campo_invalido_lanza_excepcion(): void {
        $this->expectException(\coding_exception::class);

        $persistent = new listing_course(0);
        $persistent->get('campo_inexistente');
    }

    /**
     * Verifica que no se permita duplicar la relacion anuncio-curso.
     */
    public function test_no_permite_asociacion_duplicada(): void {
        global $DB;

        $record = new stdClass();
        $record->listingid = 10;
        $record->courseid = 20;

        // Crear la primera asociacion.
        $persistent = new listing_course(0, $record);
        $persistent->create();

        // Verificar que la primera asociacion se guardo.
        $this->assertEquals(
            1,
            $DB->count_records(
                'local_chuspasocial_lstcourse',
                ['listingid' => 10, 'courseid' => 20]
            )
        );

        // Intentar guardar la misma asociacion nuevamente.
        $this->expectException(\dml_write_exception::class);

        $duplicado = new listing_course(0, $record);
        $duplicado->create();
    }
}
