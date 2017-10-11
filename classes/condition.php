<?php
namespace availability_progress;

defined('MOODLE_INTERNAL') || die();

class condition extends \core_availability\condition {

  protected $allow;

  public function __construct($structure) {
    $this->allow = $structure->allow;
  }

  public function save() {
    return (object)array('type' => 'progress', 'allow' => $this->allow);
  }

  public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
    $allow = $this->allow;
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
