<?php
// This file is part of Moodle - http://moodle.com/
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
 * Version information for the local_apifetch plugin.
 *
 * @package   local_apifetch
 * @copyright 2025 FW
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025102300;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022112800;        // Requires this Moodle version (4.1+ for PHP 8.1+, but adjusted for 8.4 compatibility).
$plugin->component = 'local_apifetch';  // Full name of the plugin (must match the folder name).
$plugin->maturity  = MATURITY_STABLE;   // Plugin maturity level.
$plugin->release   = '0.1';             // Human-readable version name.
