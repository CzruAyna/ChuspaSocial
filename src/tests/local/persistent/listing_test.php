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
 * Pruebas unitarias para la clase persistente listing.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\persistent\listing
 */
final class listing_test extends advanced_testcase {
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
        $record->title = 'Calculo I';
        $record->author = 'Ana Perez';
        $record->price = 25.5;

        $listing = new listing(0, $record);

        $this->assertTrue($listing->is_valid());
        $this->assertEquals(5, $listing->get('userid'));
        $this->assertEquals('Calculo I', $listing->get('title'));
        $this->assertEquals('Ana Perez', $listing->get('author'));
        $this->assertEquals(25.5, $listing->get('price'));
    }

    /**
     * Verifica los valores por defecto al instanciar sin registro.
     */
    public function test_valores_por_defecto(): void {
        $listing = new listing(0);

        $this->assertEquals(0, $listing->get('id'));
        $this->assertEquals(0, $listing->get('userid'));
        $this->assertEquals(0, $listing->get('price'));
        $this->assertEquals(FORMAT_HTML, $listing->get('descriptionformat'));
        $this->assertEquals(listing::STATUS_AVAILABLE, $listing->get('status'));
        $this->assertNull($listing->get('description'));
        $this->assertNull($listing->get('externalcontact'));
    }

    /**
     * Verifica que leer un campo inexistente lanza una excepcion.
     */
    public function test_campo_invalido_lanza_excepcion(): void {
        $this->expectException(coding_exception::class);

        $listing = new listing(0);
        $listing->get('campo_inexistente');
    }

    /**
     * Verifica que un estado fuera de los permitidos no es valido.
     */
    public function test_status_no_permitido_no_es_valido(): void {
        $record = new stdClass();
        $record->userid = 5;
        $record->title = 'Calculo I';
        $record->author = 'Ana Perez';
        $record->status = 'reservado';

        $listing = new listing(0, $record);

        $this->assertFalse($listing->is_valid());
        $this->assertArrayHasKey('status', $listing->get_errors());
    }
}
