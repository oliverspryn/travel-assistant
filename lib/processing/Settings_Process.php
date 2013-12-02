<?php
/**
 * Update Plugin Settings Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the plugin settings
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's settings.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\TA\Processor_Base
 * @license   MIT
 * @namespace FFI\TA
 * @package   lib.processing
 * @since     1.0.0
*/

namespace FFI\TA;

require_once(dirname(__FILE__) . "/Processor_Base.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");

class Settings_Process extends Processor_Base {
/**
 * Hold the automated email from address.
 *
 * @access private
 * @type   string
*/
	
	private $address;

/**
 * Hold the automated email from name.
 *
 * @access private
 * @type   string
*/
	
	private $name;
	
/**
 * Hold the plugin's time zone.
 *
 * @access private
 * @type   string
*/
	
	private $timeZone;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the plugin settings
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's settings.
 * 
 * @access public
 * @return void
 * @since  1.0.0
*/
	
	public function __construct() {
		parent::__construct();
		$this->hasAdminPrivileges();
		
	//Check to see if the user has submitted the form
		if ($this->userSubmittedForm()) {
			$this->validateAndRetain();
			$this->update();
		}
	}
	
/**
 * Determine whether or not the user has submitted the form by
 * checking to see if all required data is present (but not
 * necessarily valid).
 *
 * @access private
 * @return bool     Whether or not the user has submitted the form
 * @since  1.0.0
*/
	
	private function userSubmittedForm() {
		if (is_array($_POST) && count($_POST) &&
			isset($_POST['email-name']) && isset($_POST['email-address']) && isset($_POST['timezone']) &&
			!empty($_POST['email-name']) && !empty($_POST['email-address']) && !empty($_POST['timezone'])) {
			return true;
		}
		
		return false;
	}
	
/**
 * Determine whether or not all of the required information has been
 * submitted and is completely valid. If validation has succeeded, then
 * store the data within the class for later database entry.
 *
 * @access private
 * @return void
 * @since  1.0.0
 * @throws Validation_Failed Thrown when ANY portion of the validation process fails
*/

	private function validateAndRetain() {
	//Retain the automated email from name
		$this->name = $_POST['email-name'];
		
	//Validate and retain the automated email from address
		if (!filter_var($_POST['email-address'], FILTER_VALIDATE_EMAIL)) {
			throw new Validation_Failed("The plugin's email address is invalid");
		}
		
		$this->address = $_POST['email-address'];
		
	//Validate and retain the plugin's time zone
		$zones = array (
			"America/New_York",
			"America/Chicago",
			"America/Denver",
			"Amercia/Los_Angeles",
			"America/Anchorage",
			"Pacific/Honolulu"
		);
		
		if (!in_array($_POST['timezone'], $zones)) {
			throw new Validation_Failed("The plugin's time zone is invalid");
		}
		
		$this->timeZone = $_POST['timezone'];
	}
	
/**
 * Update the plugin's settings.
 *
 * @access private
 * @return void
 * @since  1.0.0
*/

	private function update() {
		global $wpdb;
		
		$wpdb->update("ffi_ta_settings", array (
			"EmailName"    => $this->name,
			"EmailAddress" => $this->address,
			"TimeZone"     => $this->timeZone
		), array (
			"ID" => 1
		), array (
			"%s", "%s", "%s"
		), array (
			"%d"
		));
		
		wp_redirect(admin_url() . "admin.php?page=travel-assistant/admin/settings.php&updated=1");
		exit;
	}
}
?>