<?php
/**
 * State Details Information class
 *
 * This class is used to fetch data from the MySQL database for 
 * information regarding US states. Some of the capibilities of
 * this class includes:
 *  - Generating a dropdown list of state codes (PA, OH, NY, ...)
 *  - Check if a state or district exists.
 *  - Fetch all available information about a state by the URL.
 *  - Create an HTML <ul> listing of states with the number of
 *    needed and available rides.
 *  - Purify a string for use in a URL.
 * 
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\TA
 * @package   lib.display
 * @since     1.0.0
*/

namespace FFI\TA;

require_once(dirname(dirname(__FILE__)) . "/exceptions/No_Data_Returned.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class State {
/**
 * This method will fetch all of the US states and build a list
 * of HTML <option> elements for each state. This can be useed
 * for generating a dropdown menu of US states.
 *
 * The generated list includes the state code, such as PA, OH, 
 * etc..., not the state full name. Also the <select> tag
 * which encapsulates all dropdown menus is NOT generated by this 
 * method, so that each specific implementation can adjust and
 * style the menu as needed.
 *
 * @access public
 * @param  string $selectedValue The code (PA, OH, etc...) of the state to select
 * @return string                A list of <option> elements for each US state, to place in a dropdown menu
 * @since  1.0.0
 * @static
*/
	
	public static function buildDropDown($selectedValue = "") {
		global $wpdb;
	
	//Fetch the listing of states
		$names = $wpdb->get_col("SELECT `Code` FROM `ffi_ta_states` ORDER BY `Code` ASC");
		$stateMenu = "";
	
	//Build the menu options
		for ($i = 0; $i < count($names); ++$i) {
			if ($selectedValue == $names[$i]) {
				$stateMenu .= "<option selected value=\"" . htmlentities($names[$i]) . "\">" . $names[$i] . "</option>\n";
			} else {
				$stateMenu .= "<option value=\"" . htmlentities($names[$i]) . "\">" . $names[$i] . "</option>\n";
			}
		}
			
		mb_substr($stateMenu, 0, -1); //Remove the trailing "\n"
		
		return $stateMenu;
	}

/**
 * This method will determine whether or not a particular state exists
 * when given the URL of the state. All available information about a
 * state will be returned (see State::getInfo()) on success, or false
 * if the state does not exist.
 *
 * @access public
 * @param  string         $stateURL The URL of the state to check
 * @return boolean|object           State information on success or false if the state does not exist
 * @see                             State::getInfo()
 * @since  1.0.0
 * @static
*/

	public static function exists($stateURL) {
		try {
			$info = self::getInfo($stateURL);
			return $info;
		} catch (No_Data_Returned $e) {
			return false;
		}
	}

/**
 * This method will fetch all available data about a particular state
 * as defined in the ffi_ta_states relation. This method will NOT fetch
 * information such as how many rides are needed or available for a 
 * a particular state, it will ONLY fetch data from the ffi_ta_states
 * relation.
 *
 * @access public
 * @param  string $stateURL The URL of the state to fetch information
 * @return object           All available state information from the ffi_ta_states relation
 * @since  1.0.0
 * @static
 * @throws No_Data_Returned Thrown when the given state does not exist
*/

	public static function getInfo($stateURL) {
		global $wpdb;

	//Fetch the data
		$data = $wpdb->get_results($wpdb->prepare("SELECT ffi_ta_states.Code, `Name`, `Image`, `District`, `URL` FROM `ffi_ta_states` LEFT JOIN (SELECT `Code`, LOWER(REPLACE(`Name`, ' ', '-')) AS `URL` FROM `ffi_ta_states`) `q` ON ffi_ta_states.Code = q.Code WHERE `URL` = %s", $stateURL));

	//Was a state returned?
		if (count($data)) {
			return $data[0];
		}

		throw new No_Data_Returned("The state URL &quot;" . $stateURL . "&quot; does not exist.");
	}
	
/**
 * This method will generate the HTML for the listing of US states,
 * each one of them associated with their respective number of 
 * needed or available rides. This list is broken in to 3 columns of
 * 17 states, for a total of 50 states and the District of Columbia.
 *
 * @access public
 * @param  string $columnLength The default number of states to include in a column
 * @return string               The HTML for rendering a list of US states and associated trip totals
 * @since  1.0.0
 * @static
*/
	
	public static function getStatesList($columnLength = 17) {
		global $wpdb;
		global $essentials;
			
	//Fetch the data from the database
		$count = 0;
		$states = $wpdb->get_results("SELECT ffi_ta_states.Name, COALESCE(q1.Needs, 0) AS `Needs`, COALESCE(q2.Shares, 0) AS `Shares` FROM `ffi_ta_states` LEFT JOIN (SELECT ffi_ta_cities.State, COUNT(ffi_ta_cities.State) AS `Needs` FROM `ffi_ta_need` LEFT JOIN `ffi_ta_cities` ON ffi_ta_need.FromCity = ffi_ta_cities.ID WHERE (ffi_ta_need.Leaving > NOW() AND ffi_ta_need.Fulfilled = 0) OR (ffi_ta_need.Leaving <= NOW() AND ffi_ta_need.EndDate > NOW() AND ffi_ta_need.Fulfilled = 0) GROUP BY ffi_ta_cities.State) `q1` ON ffi_ta_states.Code = q1.State LEFT JOIN (SELECT DISTINCT ffi_ta_cities.State, COUNT(ffi_ta_cities.State) AS `Shares` FROM `ffi_ta_share` LEFT JOIN `ffi_ta_cities` ON ffi_ta_share.FromCity = ffi_ta_cities.ID WHERE (ffi_ta_share.Leaving > NOW() AND ffi_ta_share.Seats > ffi_ta_share.Fulfilled) OR (ffi_ta_share.Leaving <= NOW() AND ffi_ta_share.EndDate > NOW() AND ffi_ta_share.Seats > ffi_ta_share.Fulfilled) GROUP BY ffi_ta_cities.State) `q2` ON ffi_ta_states.Code = q2.State ORDER BY ffi_ta_states.Name ASC");
		$return = "<ul class=\"states\">
<li>
<ul>
";
		foreach($states as $state) {
		//Should a new column be started?
			if ($count % $columnLength == 0 && $count != 0) {
				$return .= "</ul>
</li>

<li>
<ul>
";
			}
			
			
		//Echo the list item content
			$return .= "<li>
<a href=\"" . $essentials->friendlyURL("browse/" . self::URLPurify($state->Name)) . "\">
<h3>" . $state->Name . "</h3>
<p class=\"needed" . ($state->Needs > 0 ? " highlight" : "") . "\">" . $state->Needs . " <span>" . ($state->Needs == 1 ? "Need" : "Needs") . "</span></p>
<p class=\"shares" . ($state->Shares > 0 ? " highlight" : "") . "\">" . $state->Shares . " <span>" . ($state->Shares == 1 ? "Ride" : "Rides") . "</span></p>
</a>
</li>
";

			++$count;
		}
		
		$return .= "</ul>
</li>
</ul>";
		
		return $return;
	}

/**
 * This function will take a string and prepare it for use in a
 * URL by removing any spaces and special characters, and then 
 * making all characters lower case, which is this plugin's
 * convention when placing strings in a URL.
 * 
 * @access public
 * @param  string $name The name of a state
 * @return string       The URL purified version of the string
 * @since  1.0.0
 * @static
*/
	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s\-]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);            //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);                 //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>