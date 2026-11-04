<?php
// Este archivo forma parte de Moodle: https://moodle.org/
//
// Moodle es software libre: puedes redistribuirlo o modificarlo
// bajo los términos de la Licencia Pública General GNU publicada por
// la Free Software Foundation, ya sea la versión 3 de la licencia
// o cualquier versión posterior.
//
// Este software se distribuye con la esperanza de que sea útil,
// pero SIN NINGUNA GARANTÍA; ni siquiera la garantía implícita de
// COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO PARTICULAR.
//
// Deberías haber recibido una copia de la Licencia Pública General GNU
// junto con Moodle. Si no es así, consulta https://www.gnu.org/licenses/.

namespace local_chuspasocial\local\persistent;

use core\persistent;

/**
 * Clase persistente para la relación entre anuncios de libros y materias.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class listing_course extends persistent {
    /** Tabla de la base de datos. */
    const TABLE = 'local_chuspasocial_lstcourse';

    /**
     * Define las propiedades de esta clase persistente.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'listingid' => [
                'type' => PARAM_INT,
                'default' => null,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'default' => null,
            ],
        ];
    }

    /**
     * Valida que el identificador del anuncio sea mayor que cero.
     *
     * @param int|null $value Identificador del anuncio.
     * @return bool|string
     */
    protected function validate_listingid($value) {
        if (empty($value) || $value <= 0) {
            return 'El identificador del anuncio debe ser mayor que cero.';
        }
        return true;
    }

    /**
     * Valida que el identificador de la materia sea mayor que cero.
     *
     * @param int|null $value Identificador de la materia.
     * @return bool|string
     */
    protected function validate_courseid($value) {
        if (empty($value) || $value <= 0) {
            return 'El identificador de la materia debe ser mayor que cero.';
        }
        return true;
    }
}
