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
 * Publish Courses as LTI Tool in bulk
 */

require_once('../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ltify/index.php', array());
$PAGE->set_title(get_string('title', 'local_ltify'));
$PAGE->set_heading(get_string('title', 'local_ltify'));

echo $OUTPUT->header();

?>
<style type="text/css">
     .haslti { background-color: rgba(0,255,0,0.3); }
     td.active { background-color: rgba(254,147,67,0.4); }
     td { text-align: center; }
</style>
<?php

if (!has_capability('local/ltify:manage', context_system::instance())) {
    echo "<p class=\"alert alert-error\">" . get_string('missing_capability', 'local_ltify') . "</p>";
    echo $OUTPUT->footer();
    exit;
}

$action = optional_param('action', 'publish', PARAM_TEXT);

?>
<table border="0" width="100%">
    <tr>
        <td<?php if($action === 'publish') { echo " class=\"active\""; } ?>><a href="?action=publish"><?php echo get_string('ltify:manage', 'local_ltify'); ?></a></td>
        <td<?php if($action === 'show') { echo " class=\"active\""; } ?>><a href="?action=show"><?php echo get_string('show_list', 'local_ltify'); ?></a></td>
        <td><a href="download.php"><?php echo get_string('download', 'local_ltify'); ?></a></td>
    </tr>
</table>
<br />
<?php

$store = optional_param('store', 0, PARAM_INT);
if ($store === 1) {
    $role_instructor = required_param('role_instructor', PARAM_INT);
    $role_student = required_param('role_student', PARAM_INT);
    $courses = required_param_array('courses', PARAM_RAW);

    $records = array();
    foreach ($courses AS $course) {
        $enrol = (object) array(
            "enrol" => 'lti',
            "courseid" => $course,
            "status" => 0,
            "name" => "LTI Enrolment",
            "timecreated" => time(),
            "timemodified" => time(),
        );
        $enrolid = $DB->insert_record('enrol', $enrol);
        $context = $DB->get_record('context', array('instanceid' => $course, 'contextlevel' => 50));
        $secret = substr(md5(date("Y-m-d H:i:s") . rand(0,1000)),0,30);
        $records[] = (object) array(
            "enrolid" => $enrolid,
            "contextid" => $context->id,
            "institution" => '',
            "lang" => 'de',
            "timezone" => 99,
            "maxenrolled" => 0,
            "maildisplay" => 0,
            "city" => '',
            "country" => '',
            "gradesync" => 1,
            "gradesynccompletion" => 0,
            "membersync" => 1,
            "membersyncmode" => 1,
            "roleinstructor" => $role_instructor,
            "rolelearner" => $role_student,
            "secret" => $secret,
            "timecreated" => time(),
            "timemodified" => time()
        );
    }
    $DB->insert_records("enrol_lti_tools", $records);
    echo "<p class=\"alert alert-success\">" . get_string('stored_successfully', 'local_ltify') . "</p>";
}

if ($action === 'publish') {
    $roles = $DB->get_records_sql('SELECT id,name,shortname FROM {role} ORDER BY shortname ASC', array());
    $courses = $DB->get_records_sql('SELECT co.id,co.category,ca.name AS categoryname,co.fullname,con.id AS contextid FROM {course} AS co,{course_categories} AS ca,{context} AS con WHERE con.instanceid=co.id AND con.contextlevel=? AND co.category=ca.id ORDER BY co.category,co.fullname ASC', array(50));
?>

<form method="POST" action="" enctype="mulitpart/form-data">
<input type="hidden" name="store" value="1" />
<label for="role_instructor"><?php echo get_string('role_instructor', 'local_ltify'); ?></label>
<select id="role_instructor" name="role_instructor">
<?php
foreach ($roles as $role) {
    echo "<option value=\"" . $role->id . "\">" . (($role->name !== "")?$role->name:$role->shortname) . "</option>\n";
}
?>
</select>
<br /><br />
<label for="role_student"><?php echo get_string('role_student', 'local_ltify'); ?></label>
<select id="role_student" name="role_student">
<?php
foreach ($roles as $role) {
    echo "<option value=\"" . $role->id . "\">" . (($role->name !== "")?$role->name:$role->shortname) . "</option>\n";
}
?>
</select>
<br /><br />
<label for="select_courses"><?php echo get_string('select_courses', 'local_ltify'); ?></label><br />
<select id="select_courses" name="courses[]" multiple="<?php echo count($courses); ?>" size="<?php echo count($courses); ?>">
<?php
$curcat = "";
foreach ($courses as $course) {
    if ($course->category !== $curcat) {
        if ($curcat > 0) echo "</optgroup>";
        $curcat = $course->category;
        echo "<optgroup label=\"" . $course->categoryname . " (" . $course->category . ")\">\n";
    }
    $haslti_enrol = $DB->record_exists('enrol', array('courseid' => $course->id, 'enrol' => 'lti'));
    $haslti_tool = $DB->record_exists('enrol_lti_tools', array('contextid' => $course->contextid));
    echo "<option value=\"" . $course->id . "\"" . (($haslti_enrol || $haslti_tool)?"class=\"haslti\"":"") . ">" . $course->fullname . "</option>\n";
}
?>
    </optgroup>
</select>
<br /><br />
<input type="submit" value="<?php echo get_string('submit', 'local_ltify'); ?>" />
</form>
<?php
} // if action == publish
if ($action === 'show') {
?>
<table border="0">
    <thead>
        <tr>
            <td>ID</td>
            <td>Coursename</td>
            <td>Cartridge-URL</td>
            <td>Launch-URL</td>
            <td>Secret</td>
            <td>Icon</td>
            <td>Description</td>
        </tr>
    </thead>
    <tbody>
<?php

require_once($CFG->dirroot . "/enrol/lti/classes/helper.php");
$tools = \enrol_lti\helper::get_lti_tools();
foreach($tools AS $tool) {
     $course = $DB->get_record('course', array('id' => $tool->courseid));
     ?>
        <tr>
            <td><?php echo $tool->courseid; ?></td>
            <td><?php echo $tool->id; ?></td>
            <td><?php echo $course->fullname; ?></td>
            <td><?php echo \enrol_lti\helper::get_cartridge_url($tool); ?></td>
            <td><?php echo \enrol_lti\helper::get_launch_url($tool->id); ?></td>
            <td><?php echo \enrol_lti\helper::get_icon($tool); ?></td>
            <td><?php echo $tool->secret; ?></td>
            <td><?php echo \enrol_lti\helper::get_description($tool); ?></td>
        </tr>
     <?php
}

?>
    </tbody>
</table>
<?php
} // if action == show
echo $OUTPUT->footer();
