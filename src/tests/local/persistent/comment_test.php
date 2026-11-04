<?php
namespace local_chuspasocial\local\persistent;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use local_chuspasocial\local\persistent\comment;

/**
 * Pruebas unitarias para la clase persistente comment.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\persistent\comment
 */
class comment_test extends advanced_testcase {

    /**
     * Configuracion inicial antes de cada prueba.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test 1: Creacion valida pasando el objeto de datos en el segundo argumento ($record).
     */
    public function test_creacion_valida(): void {
        $record = new \stdClass();
        $record->userid = 1;
        $record->postid = 1;
        $record->content = 'Comentario de prueba en el muro';

        // Se pasa 0 como primer argumento ($id) y $record como el segundo argumento
        $comment = new comment(0, $record);

        $this->assertInstanceOf(comment::class, $comment);
        $this->assertEquals('Comentario de prueba en el muro', $comment->get('content'));
    }

    /**
     * Test 2: Verificacion de valores por defecto al instanciar sin registro.
     */
    public function test_valores_por_defecto(): void {
        $comment = new comment(0);

        // En persistent, el id por defecto antes de guardar es 0
        $this->assertEquals(0, $comment->get('id'));
    }

    /**
     * Test 3: Validacion al intentar obtener un campo invalido.
     */
    public function test_campo_invalido_lanza_excepcion(): void {
        $this->expectException(\coding_exception::class);

        $comment = new comment(0);
        $comment->get('campo_inexistente_que_no_existe');
    }

    /**
     * Test 4: Crear un comentario y leerlo desde la base de datos.
     */
    public function test_crear_y_leer_comentario_en_bd(): void {
        $record = new \stdClass();
        $record->userid = 2;
        $record->postid = 5;
        $record->content = 'Comentario persistido';

        $comment = new comment(0, $record);
        $comment->create();

        $this->assertGreaterThan(0, $comment->get('id'));

        $comentarioleido = new comment($comment->get('id'));
        $this->assertEquals('Comentario persistido', $comentarioleido->get('content'));
    }

    /**
     * Test 5: Eliminar un comentario persistido de la base de datos.
     */
    public function test_eliminar_comentario(): void {
        global $DB;

        $record = new \stdClass();
        $record->userid = 2;
        $record->postid = 5;
        $record->content = 'Comentario a eliminar';

        $comment = new comment(0, $record);
        $comment->create();
        $id = $comment->get('id');

        $comment->delete();

        $this->assertFalse($DB->record_exists(comment::TABLE, ['id' => $id]));
    }
}
