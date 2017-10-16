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
* Condition file.
*
* @package    availability_progress
* @author     Ariel Zusman
* @copyright  2017 Ariel Zusman
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

namespace availability_progress;

defined('MOODLE_INTERNAL') || die();

/**
* Condition Class
*
* @package availability_progress
* @copyright  2017 Ariel Zusman
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class condition extends \core_availability\condition {

    /** @var int the percentage required **/
    protected $allow;

/** 
* Constructor
*
* @param stdClass $structure Saved data
*/
public function __construct($structure) {
    $this->allow = $structure->allow;
}

/**
* Saves tree data back to a structure object.
*
* @return stdClass Structure object (ready to be made into JSON format)
*/
public function save() {
    return (object) array('type' => 'progress', 'allow' => $this->allow);
}
/**
* This is available in Moodle 3.3
* https://github.com/moodle/moodle/blob/master/completion/classes/progress.php#L48
*
* Returns the course percentage completed by a certain user, returns null if no completion data is available.
*
* @param \stdClass $course Moodle course object
* @param int $userid The id of the user, 0 for the current user
* @return null|float The percentage, or null if completion is not supported in the course,
*         or there are no activities that support completion.
*/
protected function get_progress($course, $userid = 0) {
    global $USER;

// Make sure we continue with a valid userid.
    if (empty($userid)) {
        $userid = $USER->id;
    }

    $completion = new \completion_info($course);

// First, let's make sure completion is enabled.
    if (!$completion->is_enabled()) {
        return null;
    }
// Before we check how many modules have been completed see if the course has.
    if ($completion->is_course_complete($userid)) {
        return 100;
    }

// Get the number of modules that support completion.
    $modules = $completion->get_activities();
    $count = count($modules);
    if (!$count) {
        return null;
    }
// Get the number of modules that have been completed.
    $completed = 0;
    foreach ($modules as $module) {
        $data = $completion->get_data($module, false, $userid);
        $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
    }
    return ($completed / $count) * 100;
}

/**
* Determines whether a particular item is currently available
* according to this availability condition.
*
* @param bool $not Set true if we are inverting the condition.
* @param info $info Item we're checking.
* @param bool $grabthelot Performance hint: if true, caches information
*   required for all course-modules, to make the front page and similar
*   pages work more quickly (works only for current user).
* @param int $userid User ID to check availability for.
* @return bool True if available.
*/
public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
    $allow = (int)$this->allow;
    $progress = $this->get_progress($info->get_course(), $userid);                

    $allow = ($progress >= $allow) ? true : false;
    if ($not) {
        $allow = !$allow;
    }

    return $allow;
}
/**
* Obtains a string describing this restriction (whether or not
* it actually applies). Used to obtain information that is displayed to
* students if the activity is not available to them, and for staff to see
* what conditions are.
*
* @param bool $full Set true if this is the 'full information' view.
* @param bool $not Set true if we are inverting the condition.
* @param info $info Item we're checking.
* @return string Information string (for admin) about all restrictions on
*   this item.
*/
public function get_description($full, $not, \core_availability\info $info) {
    if ($not) {
        return get_string('allowed', 'availability_progress');
    } else {
        return get_string('notallowed', 'availability_progress') . ' ' . $this->allow . '% of the course';
    }    
}
/**
* Obtains a representation of the options of this condition as a string,
* for debugging.
*
* @return string Text representation of parameters.
*/
public function get_debug_string() {
    return $this->allow ? 'YES' : 'NO';
}

}
