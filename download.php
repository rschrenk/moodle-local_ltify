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
 * @package    local_ltify
 * @copyright  2018 Digital Education Society (http://www.dibig.at)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Download all LTI Tools of a Moodle Instance as a CSV-List
 */

require_once('../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/ltify/download.php', array());

if (!has_capability('local/ltify:manage', context_system::instance())) {
    echo get_string('missing_capability', 'local_ltify');
    exit;
}

header("Content-Type: text/csv");
header('Content-Disposition: attachment; filename="tools.csv"');

define('mark', '"');
define('delim', ';');
define('line', "\n");
$fields = array("courseid", "toolid", "coursename", "catridge_url", "launch_url", "icon", "secret", "description");
$out = fopen('php://output', 'w');
fputcsv($out, $fields, "\t");

//echo mark . implode(mark . delim . mark, $fields) . mark . line;

require_once($CFG->dirroot . "/enrol/lti/classes/helper.php");
$tools = \enrol_lti\helper::get_lti_tools();
foreach($tools AS $tool) {
     $course = $DB->get_record('course', array('id' => $tool->courseid));
     $fields = array(
         $tool->courseid, $tool->id, $course->fullname,
         \enrol_lti\helper::get_cartridge_url($tool),
         \enrol_lti\helper::get_launch_url($tool->id),
         \enrol_lti\helper::get_icon($tool),
         $tool->secret,
         \enrol_lti\helper::get_description($tool)
     );
     fputcsv($out, $fields, "\t");
     //echo mark . implode(mark . delim . mark, $fields) . mark . line;
}
fclose($out);
