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
 * Links and settings
 * @package    local_ltify
 * @copyright  2017 Digital Education Society (http://www.dibig.at)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version  = 2018013005;   // The (date) version of this module + 2 extra digital for daily versions
                                  // This version number is displayed into /admin/forms.php
                                  // TODO: if ever this plugin get branched, the old branch number
                                  // will not be updated to the current date but just incremented. We will
                                  // need then a $plugin->release human friendly date. For the moment, we use
                                  // display this version number with userdate (dev friendly)
$plugin->requires = 2016120500;  // Requires at least Moodle 3.2!
$plugin->component = 'local_ltify';
$plugin->cron     = 0;
$plugin->release = '1.0 (2018013001)';
$plugin->maturity = MATURITY_STABLE;
