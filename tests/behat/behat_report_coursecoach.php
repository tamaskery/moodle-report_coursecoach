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
 * Behat steps for the Course Readiness Coach report.
 *
 * @package   report_coursecoach
 * @category  test
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by Behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps for the Course Readiness Coach report.
 *
 * @package   report_coursecoach
 * @category  test
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_coursecoach extends behat_base {
    /**
     * Save a synthetic report screenshot when CI provides an artifact directory.
     *
     * @Then /^I save the Course Readiness Coach screenshot as "(?P<name_string>[a-z-]+)"$/
     * @param string $name Screenshot name.
     */
    public function save_report_screenshot(string $name): void {
        $directory = getenv('COURSECOACH_SCREENSHOT_DIR');
        if (!$directory) {
            return;
        }
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $this->getSession()->resizeWindow(1440, 3200);
        file_put_contents($directory . '/' . $name . '.png', $this->getSession()->getDriver()->getScreenshot());
    }

    /**
     * Verify direct report access is denied for a course identified by short name.
     *
     * Course record IDs are not stable between isolated Behat scenarios, so a
     * standard fixed relative-URL step cannot reliably exercise direct access.
     * Moodle's Behat hook also treats every exception page as an unexpected
     * failure, so this step verifies the expected capability exception before
     * returning to the course page.
     *
     * @When /^direct Course Readiness Coach access for course "(?P<shortname_string>[^"]+)" is denied$/
     * @param string $shortname Course short name.
     */
    public function direct_report_access_is_denied(string $shortname): void {
        global $DB;

        $course = $DB->get_record('course', ['shortname' => $shortname], 'id', MUST_EXIST);
        $url = new moodle_url('/report/coursecoach/index.php', ['id' => $course->id]);

        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));

        $error = $this->getSession()->getPage()->find('css', '[data-rel="fatalerror"]');
        $errorcode = $error?->find('css', '.errorcode a[href$="/nopermissions"]');
        if (!$errorcode) {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Direct report access did not produce the expected Moodle permission exception.',
                $this->getSession()
            );
        }

        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $this->getSession()->visit($this->locate_path($courseurl->out_as_local_url(false)));
    }
    /**
     * Verify course staff cannot access the native criteria settings page.
     *
     * @When /^direct Course Readiness Coach settings access is denied$/
     */
    public function direct_settings_access_is_denied(): void {
        global $CFG;
        $url = new moodle_url('/' . $CFG->admin . '/settings.php', ['section' => 'reportcoursecoach']);
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
        $error = $this->getSession()->getPage()->find('css', '[data-rel="fatalerror"]');
        if (!$error || !$error->find('css', '.errorcode a[href$="/accessdenied"]')) {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Course staff were not denied access to criteria settings.',
                $this->getSession()
            );
        }
        $this->getSession()->visit($this->locate_path('/'));
    }
    /**
     * Exercise Moodle's native settings save with an invalid CSRF token.
     *
     * @When /^I invalidate the Course Readiness Coach settings sesskey$/
     */
    public function invalidate_settings_sesskey(): void {
        $this->getSession()->executeScript("document.querySelector('#adminsettings input[name=sesskey]').value = 'invalid';");
    }
}
