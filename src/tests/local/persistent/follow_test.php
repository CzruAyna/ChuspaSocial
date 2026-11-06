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

namespace local_chuspasocial\local\persistent;

use advanced_testcase;
use coding_exception;
use stdClass;

/**
 * Pruebas unitarias para la clase persistente follow.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\persistent\follow
 */
final class follow_test extends advanced_testcase {
    /**
     * Configuracion inicial antes de cada prueba.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Verifica la creacion con datos validos.
     */
    public function test_creacion_valida(): void {
        $record = new stdClass();
        $record->userid = 5;
        $record->itemtype = 'tag';
        $record->itemid = 12;

        $follow = new follow(0, $record);

        $this->assertTrue($follow->is_valid());
        $this->assertEquals(5, $follow->get('userid'));
        $this->assertEquals('tag', $follow->get('itemtype'));
        $this->assertEquals(12, $follow->get('itemid'));
    }

    /**
     * Verifica los valores por defecto al instanciar sin registro.
     */
    public function test_valores_por_defecto(): void {
        $follow = new follow(0);

        $this->assertEquals(0, $follow->get('id'));
        $this->assertEquals(0, $follow->get('userid'));
        $this->assertEquals('user', $follow->get('itemtype'));
        $this->assertEquals(0, $follow->get('itemid'));
    }

    /**
     * Verifica que leer un campo inexistente lanza una excepcion.
     */
    public function test_campo_invalido_lanza_excepcion(): void {
        $this->expectException(coding_exception::class);

        $follow = new follow(0);
        $follow->get('campo_inexistente');
    }

    /**
     * Verifica que un tipo de elemento fuera de los permitidos no es valido.
     */
    public function test_itemtype_no_permitido_no_es_valido(): void {
        $record = new stdClass();
        $record->userid = 5;
        $record->itemtype = 'curso';
        $record->itemid = 12;

        $follow = new follow(0, $record);

        $this->assertFalse($follow->is_valid());
        $this->assertArrayHasKey('itemtype', $follow->get_errors());
    }
}
