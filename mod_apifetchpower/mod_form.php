<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_apifetchpower_mod_form extends moodleform_mod {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'apifetchpower'));
        $mform->addElement('text', 'name', get_string('modulename', 'apifetchpower'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Standard coursemodule settings.
        $this->standard_coursemodule_elements();

        // Standard buttons.
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['name'])) {
            $errors['name'] = get_string('missingname', 'error');
        }
        return $errors;
    }
}
