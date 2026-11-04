<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Hook callbacks of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use core\hook\navigation\primary_extend;

/**
 * Callbacks de hooks de Moodle para ChuspaSocial.
 *
 * Puntos de extension utilizados (registrados en db/hooks.php):
 * - {@see \core\hook\navigation\primary_extend}: agrega los enlaces del plugin a la
 *   navegacion principal (extend_primary_navigation).
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Agrega el nodo "ChuspaSocial" a la navegacion principal.
     *
     * Solo se muestra a usuarios autenticados (no a invitados).
     *
     * Punto de extension: \core\hook\navigation\primary_extend (ver db/hooks.php).
     *
     * @param primary_extend $hook Hook de extension de la navegacion principal.
     * @return void
     */
    public static function extend_primary_navigation(primary_extend $hook): void {
        if (!isloggedin() || isguestuser()) {
            return;
        }

        $primaryview = $hook->get_primaryview();

        $primaryview->add(
            get_string('pluginname', 'local_chuspasocial'),
            new \moodle_url('/local/chuspasocial/index.php'),
            \navigation_node::TYPE_CUSTOM,
            null,
            'chuspasocial'
        );

        // Enlace "Libros": solo si el marketplace está habilitado y el usuario tiene permiso.
        if (get_config('local_chuspasocial', 'enablemarketplace')
                && has_capability('local/chuspasocial:viewmarketplace', \context_system::instance())) {
            $primaryview->add(
                get_string('books', 'local_chuspasocial'),
                new \moodle_url('/local/chuspasocial/marketplace.php'),
                \navigation_node::TYPE_CUSTOM,
                null,
                'chuspasocial_books'
            );
        }
    }
}
