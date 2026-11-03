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
 * Library functions for local_sectionmarker.
 *
 * JS injection is handled via the Moodle 5.1 Hooks API:
 *   \core\hook\output\before_standard_head_html_generation
 * registered in db/hooks.php and implemented in classes/hook_callbacks.php.
 *
 * The legacy local_sectionmarker_before_standard_html_head() callback has been
 * removed; Moodle 5.1 triggers a hard deprecation warning for it.
 *
 * @package    local_sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
