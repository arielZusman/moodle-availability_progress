<?php
namespace availability_progress;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {

  protected function get_javascript_strings() {
    return array();
  }

  protected function get_javascript_init_params($course, \cm_info $cm = null, \section_info $section = null) {
    return array('frog');
  }
  
  protected function allow_add($course, \cm_info $cm = null, \section_info $section = null) {
      return true;
  }
}
