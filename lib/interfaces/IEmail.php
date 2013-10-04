<?php
/**
 * Email definition interface
 *
 * This interface is designed to enforce abilities each of the
 * custom emailer classes must posses within this plugin.
 *
 * Each of the emailer classes are expected to take a minimal
 * amount of data and then assemble the title and email body.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.interfaces
 * @since     3.0
*/

namespace FFI\BE;

interface IEmail {
/**
 * Build the HTML and plain-text versions of the email body 
 * from the information gathered previously
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	public function buildBody();
	
/**
 * Send the email which was generated by one of the child
 * classes to Mandrill for processing
 *
 * @access public
 * @return void
 * @throws Network_Connection_Error Thrown in the event there is an error while trying to communicate with Mandrill
 * @throws Mandrill_Send_Failed     Thrown in the event that Mandrill cannot send the email
 * @since  3.0
*/

	public function send();
}
?>
