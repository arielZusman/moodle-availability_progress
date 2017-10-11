<?php

namespace availability_progress;

defined('MOODLE_INTERNAL') || die();

class condition extends \core_availability\condition {

    protected $allow;

    public function __construct($structure) {
        $this->allow = $structure->allow;
    }

    public function save() {
        return (object) array('type' => 'progress', 'allow' => $this->allow);
    }
    /*
     * This is available in Moodle 3.3
     * https://github.com/moodle/moodle/blob/master/completion/classes/progress.php#L48
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

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $allow = (int)$this->allow;
        $progress = $this->get_progress($info->get_course(), $userid);                
        
        $allow = ($progress >= $allow) ? true : false;
        if ($not) {
            $allow = !$allow;
        }
        
        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        $allow = $not ? !$this->allow : $this->allow;
        return $allow ? 'Users are allowed' : 'Users not allowed';
    }

    public function get_debug_string() {
        return $this->allow ? 'YES' : 'NO';
    }

}
