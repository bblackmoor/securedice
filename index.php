<?php

ini_set( 'display_errors', 1 );
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load composer's autoloader to load PHPMailer
require '../phpmailer/vendor/autoload.php';

// Load site's database settings
require "../../include/common.php";

$pagetitle  = "Secure Dice";
$keywords   = "secure,dice,roller,roleplaying,role-playing,game";

//$vars = array_merge($_GET, $_POST);
//$data = array();
//$data = moveTrimmedToArray($vars, $data);

if (isset($data["bypass"]) && $data["bypass"] != '--')
{
//	$data["bypass"] = $data["bypass-captcha"];
}
else
{
	$data["bypass"] = '';
}

if (!isset($data["op"]) || $data["op"] == '--')
{
	$data["op"] = '';
}

// dice_quantity
if (!isset($data["dq"]) || $data["dq"] == '--')
{
	$data["dq"] = '4';
}

// minus_dice_quantity
if (!isset($data["mdq"]) || $data["mdq"] == '--')
{
	$data["mdq"] = '0';
}

// fudge_dice_quantity
if (!isset($data["fdq"]) || $data["fdq"] == '--')
{
	$data["fdq"] = '0';
}

// dice_size
if (!isset($data["ds"]) || $data["ds"] == '--')
{
	$data["ds"] = '6';
}

// minus_dice_size
if (!isset($data["mds"]) || $data["mds"] == '--')
{
	$data["mds"] = '6';
}

// dice_modify
if (!isset($data["dm"]) || $data["dm"] == '--')
{
	$data["dm"] = '0';
}

// minus_dice_modify
if (!isset($data["mdm"]) || $data["mdm"] == '--')
{
	$data["mdm"] = '0';
}

// dice_deviation
if (!isset($data["dd"]) || $data["dd"] == '--')
{
	$data["dd"] = 'none';
}

// minus_dice_deviation
if (!isset($data["mdd"]) || $data["mdd"] == '--')
{
	$data["mdd"] = 'none';
}

// dice_sets
if (!isset($data["dt"]) || $data["dt"] == '--')
{
	$data["dt"] = '7';
}

// sort dice_sets
if (!isset($data["sdt"]) || $data["sdt"] == '--')
{
	$data["sdt"] = false;
}

// email to
if (!isset($data["to"]) || $data["to"] == '--')
{
	$data["to"] = '';
}

// cc to
if (!isset($data["gm"]) || $data["gm"] == '--')
{
	$data["gm"] = '';
}

// email subject
if (!isset($data["sub"]) || $data["sub"] == '--')
{
	$data["sub"] = '';
}

/**
 * Retrieves the current tally for dice rolled to date.
 *
 * @access  public
 * @return  string  $dice_rolled	Dice rolled to date
 */
function getDiceRolled()
{
	global $db;

	$query = mysqli_query($db, "SELECT SUM(`dice_rolled`) FROM `secure_dice`;");
	$dice_rolled = array_shift(mysqli_fetch_array($query));
	$dice_rolled = number_format($dice_rolled);

	return $dice_rolled;
}

/**
 * Stores dice roll results in database.
 *
 * @access  public
 * @param   string  $message			Dice roll message
 */
function storeDiceResults($message)
{
	global $data, $db;

	$query = mysqli_query($db, "INSERT INTO `secure_dice` SET `results` = \"" . mysqli_real_escape_string($db, $message) . "\";");

	$data["roll_id"] = mysqli_insert_id($db);
}

/**
 * Stores tally for dice rolled in database.
 *
 * @access  public	
 * @param   array	$data		Dice roll data
 */
function storeDiceRolled($data)
{
	global $data, $db;

	$current_dice = (($data["dice_quantity"] + $data["minus_dice_quantity"]) * $data["dice_sets"]);

	$query = mysqli_query($db, "UPDATE `secure_dice` SET `dice_rolled` = " . intval($current_dice) . " WHERE `id` = \"" . intval($data["roll_id"]) . "\";");
}

/**
 * Stores dice roll hash in database.
 *
 * @access  public
 * @param   string  $message	Dice roll results
 */
function storeDiceHash($message)
{
	global $data, $db;

	$data["hash"] = md5($message);
	$query = mysqli_query($db, "UPDATE `secure_dice` SET `hash` = \"" . mysqli_real_escape_string($db, $data["hash"]) . "\" WHERE `id` = \"" . intval($data["roll_id"]) . "\";");
}

/**
 * Displays dice roll form.
 *
 * @access  public
 * @param   array	$data		Dice roll data
 */
function displayDiceForm($data)
{
	global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

?>
	<p>
	<?php echo getDiceRolled(); ?> dice rolled since 2005-11-06.
	</p>

<?php

if ($data['bypass'] == $data["bypass-captcha"])
{ 
	echo '<form method="post" action="index.php" onsubmit="return true;">';
} 
else
{ 
	echo '<form method="post" action="index.php" onsubmit="return validateRecaptcha();">';
}

?>

		<table border="0" cellpadding="4">
		<tr>
			<td align="right" valign="top">
				Roll &nbsp;
			</td>
			<td valign="top">
				<select name="dice_quantity" id="dice_quantity">

<?php

for ($i = 1000; $i >= 1; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["dq"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";

	if ($i > 100) 
	{ 
		$i = $i - 899; 
	} 
	else if ($i > 50) 
	{ 
		$i = $i - 9; 
	} 
	else if ($i > 20) 
	{ 
		$i = $i - 4; 
	} 
}

?>

				</select>
				d<select name="dice_size" id="dice_size">

<?php

for ($i = 1000; $i >= 2; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["ds"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";

	if ($i > 100) 
	{ 
		$i = $i - 899; 
	} 
	else if ($i > 50) 
	{ 
		$i = $i - 9; 
	} 
	else if ($i > 20) 
	{ 
		$i = $i - 4; 
	} 
}

?>

				</select>
				<select name="dice_modify" id="dice_modify">

<?php

for ($i = 40; $i >= -40; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["dm"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}

	if ($i > -1)
	{
		$sign = '+';
	}
	else
	{
		$sign = '';
	}

	echo '>' . $sign . $i . '</option>' . "\n";
}

?>

				</select>,

				and <select name="dice_deviation" id="dice_deviation">
				<option value="lowest" <?php if ($data["dd"] == "lowest") { echo 'selected="selected"'; } ?>>drop the lowest die.</option>
				<option value="--" <?php if ($data["dd"] == "none") { echo 'selected="selected"'; } ?>>sum them all (normal roll).</option>
				<option value="highest" <?php if ($data["dd"] == "highest") { echo 'selected="selected"'; } ?>>drop the highest die.</option>
				<!-- <option value="expertise" <?php if ($data["dd"] == "expertise") { echo 'selected="selected"'; } ?>>use expertise rule from Bulletproof Blues.</option> -->
				<option value="wild" <?php if ($data["dd"] == "wild") { echo 'selected="selected"'; } ?>>use one of them as a wild die.</option>
				<option value="stunt" <?php if ($data["dd"] == "stunt") { echo 'selected="selected"'; } ?>>use one of them as a stunt die.</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				then roll and subtract &nbsp;
			</td>
			<td valign="top">
				<select name="minus_dice_quantity" id="minus_dice_quantity">

<?php

for ($i = 1000; $i >= 0; $i--)
{
	echo '<option value="' . $i . '" ';

	if ($data["mdq"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";

	if ($i > 100) 
	{ 
		$i = $i - 899; 
	} 
	else if ($i > 50) 
	{ 
		$i = $i - 9; 
	} 
	else if ($i > 20) 
	{ 
		$i = $i - 4; 
	} 
}

?>

				</select>
				d<select name="minus_dice_size" id="minus_dice_size">

<?php

for ($i = 1000; $i >= 2; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["mds"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";

	if ($i > 100) 
	{ 
		$i = $i - 899; 
	} 
	else if ($i > 50) 
	{ 
		$i = $i - 9; 
	} 
	else if ($i > 20) 
	{ 
		$i = $i - 4; 
	} 
}

?>

				</select>

				<select name="minus_dice_modify" id="minus_dice_modify">

<?php

for ($i = 40; $i >= -40; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["mdm"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}

	if ($i > -1)
	{
		$sign = '+';
	}
	else
	{
		$sign = '';
	}

	echo '>' . $sign . $i . '</option>' . "\n";
}

?>

	</select>,

				and <select name="minus_dice_deviation" id="minus_dice_deviation">
				<option value="lowest" <?php if ($data["mdd"] == "lowest") { echo 'selected="selected"'; } ?>>drop the lowest die.</option>
				<option value="--" <?php if ($data["mdd"] == "none") { echo 'selected="selected"'; } ?>>sum them all (normal roll).</option>
				<option value="highest" <?php if ($data["mdd"] == "highest") { echo 'selected="selected"'; } ?>>drop the highest die.</option>
				<option value="wild" <?php if ($data["mdd"] == "wild") { echo 'selected="selected"'; } ?>>use one of them as a wild die.</option>
				</select></p>
			</td>
		</tr>
		<tr>
			<td align="center" valign="top" colspan="2">
				<b>OR...</b>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				Roll &nbsp;
			</td>
			<td valign="top">
				<select name="fudge_dice_quantity" id="fudge_dice_quantity">

<?php

for ($i = 5; $i >=0; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["fdq"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";
}

?>

				</select>
				<a href="http://en.wikipedia.org/wiki/Fudge_%28role-playing_game_system%29#Fudge_dice">Fudge (or FATE) dice</a><br />
				(If you select Fudge dice, the section above will be ignored.)
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				<p>Roll this set of dice &nbsp;</p>
			</td>
			<td valign="top">
				<p><select name="dice_sets">

<?php

for ($i = 1000; $i >=1; $i--)
{
	echo '<option value="' . $i . '" ';
	
	if ($data["dt"] == $i) 
	{ 
		echo 'selected="selected"'; 
	}
	
	echo '>' . $i . '</option>' . "\n";

	if ($i > 100) 
	{ 
		$i = $i - 899; 
	} 
	else if ($i > 50) 
	{ 
		$i = $i - 9; 
	} 
	else if ($i > 20) 
	{ 
		$i = $i - 4; 
	} 
}

?>

				</select> times.
				<input type="checkbox" name="sort_dice_sets" id="sort_dice_sets" <?php if ($data["sdt"]) { echo 'checked="checked"'; } ?> /> Sort dice sets?</p>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				Send the signed results of this roll to yourself: &nbsp;
			</td>
			<td valign="top">
				<input type="text" name="dice_mailto" id="dice_mailto" size="30" maxlength="255" value="<?php echo (htmlspecialchars($data["to"])); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				and the GM: &nbsp;
			</td>
			<td valign="top">
				<input type="text" name="dice_mailgm" id="dice_mailgm" size="30" maxlength="255" value="<?php echo (htmlspecialchars($data["gm"])); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
				with this subject: &nbsp; 
			</td>
			<td valign="top">
				<input type="text" name="dice_subject" id="dice_subject" size="30" maxlength="255" value="<?php echo (htmlspecialchars($data["sub"])); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
			&nbsp;
			</td>
			<td valign="top">
<?php

if ($data['bypass'] == $data["bypass-captcha"])
{ 
	echo '<input type="hidden" name="bypass" value="' . $data["bypass-captcha"] . '" />';
}
else
{
	echo '<div class="g-recaptcha" data-sitekey="' . $data['data-sitekey'] . '"></div>';
}

?>
				
				<input type="hidden" name="op" value="roll" />
				<input type="submit" name="submit" id="submit" value="Roll The Dice" />
				<input type="button" name="verify_roll" value="Verify A Roll" onclick="location.href='verify.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>';" />
				<input type="button" name="restart" value="Start Over" onclick="location.href='<?php echo $siteurl; ?>/software/securedice/index.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>';" />
			</td>
		</tr>
		</table>

	</form>

	<p>You can pre-populate these values by adding them to the URL. The first value you set must be preceded with a question mark (?). Values after the first must be preceded by an ampersand (&). Here are permitted options, and their allowed values:</p>

	<table border="0">
		<tr>
			<th>dq= 
			<td>dice quantity: any integer from 1 to 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100, 1000
		</tr>
		<tr>
			<th>ds= 
			<td>dice sides: 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 18, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100, 1000
		</tr>
		<tr>
			<th>dm= 
			<td>dice modifier: any integer from -40 to 40 (for numbers greater than -1, do <b>not</b> include a plus sign)
		</tr>
		<tr>
			<th>dd= 
			<td>dice deviation: lowest, none, highest, <a href="http://en.wikipedia.org/wiki/D6_System#The_Wild_Die">wild</a>, <a href="https://www.blackgate.net/blog/fantasy-age-the-stunt-die/">stunt</a>
		</tr>
		<tr>
			<th>mdq= 
			<td>minus dice quantity: any integer from 0 to 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100, 1000
		</tr>
		<tr>
			<th>mds= 
			<td>minus dice sides: 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 18, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100, 1000
		</tr>
		<tr>
			<th>mdm= 
			<td>minus dice modifier: any integer from -40 to 40 (for numbers greater than -1, do <b>not</b> include a plus sign)
		</tr>
		<tr>
			<th>mdd= 
			<td>minus dice deviation: lowest, none, highest, <a href="http://en.wikipedia.org/wiki/D6_System#The_Wild_Die">wild</a>
		</tr>
		<tr>
			<th>dt= 
			<td>dice sets: any integer from 1 to 20
		</tr>
		<tr>
			<th>sdt= 
			<td>sort dice sets: 1 (sorts the rolls in each dice set from largest to smallest)
		</tr>
		<tr>
			<th>to= 
			<td>Your email address: one valid email address
		</tr>
		<tr>
			<th>gm= 
			<td>GM's email address: one valid email address 
		</tr>
		<tr>
			<th>sub= 
			<td>email subject: a brief message, with "%20" instead of spaces (example: This%20is%20a%20test)
		</tr>
	</table>

	<p>Here is an example of a URL which pre-sets some of these values:</p>
	<pre><a href="<?php echo $siteurl; ?>"/software/securedice/?dq=7&amp;ds=4&amp;dt=5&amp;to=yourname@example.com&amp;sub=This%20is%20a%20test"><?php echo $siteurl; ?>/software/securedice/?dq=7&amp;ds=4&amp;dt=5&amp;to=yourname@example.com&amp;sub=This%20is%20a%20test</a></pre>
	
<?php

}

/**
 * Checks if email address is potentially valid.
 *
 * @access  public
 * @param   string  $email_address  Email address of recipient
 * @return  boolean $validity	   True if potentially valid; otherwise false
 */
function isEmailValid($email_address = '')
{
	$validity = false;

	if (empty($email_address))
	{
		$validity = false;
	}
	else
	{
		list($mailName, $mailDomain) = preg_split("/@/", $email_address);

		if (empty($mailName) || empty($mailDomain))
		{
			$validity = false;
		}
		else if (checkdnsrr($mailDomain, "MX"))
		{
			if (preg_match("/\r/i", $email_address) || preg_match("/\n/i", $email_address))
			{
				$validity = false;
			}
			else
			{
				$validity = true;
			}
		}
	}
	
	return $validity;
}

/**
 * Checks if subject is potentially valid.
 *
 * @access  public
 * @param   string  $email_subject  Email subject
 * @return  boolean $validity	   True if potentially valid; otherwise false
 */
function isSubjectValid($email_subject = '')
{
	$validity = false;

	if (preg_match("/\r/i", $email_subject) || preg_match("/\n/i", $email_subject))
	{
		$validity = false;
	}
	else
	{
		$validity = true;
	}

	return $validity;
}

/**
 * Formats dice result email with appropriate header and footer.
 *
 * @access  public
 * @param   string  $email_message	  Email message
 * @return  string  $formatted_message  Formatted email message
 */
function formatEmailMessage($email_message = '')
{
	global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

	$formatted_message = $sitetitle . " Secure Dice generated the following rolls";

	if (isset($data["dice_mailto"]) && !empty($data["dice_mailto"]))
	{
		$formatted_message .= " for " . $data["dice_mailto"];
	}

	$formatted_message .= ".\n\n";

	$formatted_message .= $data["message"];
	$formatted_message .= "\n\n-- \n";
	$formatted_message .= "Dice rolls generated by " . $sitetitle . " Secure Dice\n";
	$formatted_message .= $siteurl . "/software/securedice/";

	$data["formatted_message"] = $formatted_message;

	$formatted_message  = "--- verified message begins here ---\n" . $formatted_message;
	$formatted_message .= "\n--- verified message ends here ---\n";
	$formatted_message .= "Roll ID: " . $data["roll_id"] . "\n";
	$formatted_message .= "MD5 checksum: " . $data["hash"] . "\n";

	return $formatted_message;
}

/**
 * Generates dice roll results.
 *
 * @access  public
 * @param   array	$data		Dice roll data
 */
function generateDiceResults($data)
{
	global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

	$data["message"] = $data["dice_quantity"] . "d" . $data["dice_size"];

	if ($data["dice_modify"] >= 1)
	{
		$data["message"] .= "+" . $data["dice_modify"];
	}
	else if ($data["dice_modify"] <= -1)
	{
		$data["message"] .= $data["dice_modify"];
	}

	if (($data["dice_deviation"] == "highest") || ($data["dice_deviation"] == "lowest"))
	{
		$data["message"] .= ", with the " . $data["dice_deviation"] . " die dropped";
	}
	else if ($data["dice_deviation"] == "expertise")
	{
		$data["message"] .= ", with [expertise] (re-rolling 1s and 2s)";
	}
	else if ($data["dice_deviation"] == "wild")
	{
		$data["message"] .= ", with a [wild die]";
	}
	else if ($data["dice_deviation"] == "stunt")
	{
		$data["message"] .= ", using one of them as a [stunt die]";
	}

	if (!isset($data["minus_dice_quantity"]))
	{
		$data["minus_dice_quantity"] = 0;
	}

	if ($data["minus_dice_quantity"] > 0)
	{
		$data["message"] .= ', subtract ' . $data["minus_dice_quantity"] . "d" . $data["minus_dice_size"];

		if ($data["minus_dice_modify"] >= 1)
		{
			$data["message"] .= "+" . $data["minus_dice_modify"];
		}
		else if ($data["minus_dice_modify"] <= -1)
		{
			$data["message"] .= $data["minus_dice_modify"];
		}

		if (($data["minus_dice_deviation"] == "highest") || ($data["minus_dice_deviation"] == "lowest"))
		{
			$data["message"] .= ", with the " . $data["minus_dice_deviation"] . " die dropped";
		}
		else if ($data["minus_dice_deviation"] == "wild")
		{
			$data["message"] .= ", with a [wild die]";
		}

	}
	
	$data["message"] .= ", rolled ";

	if ($data["dice_sets"] > 1)
	{
		$data["message"] .= $data["dice_sets"] . " times.\n";
	}
	else
	{
		$data["message"] .= " once.\n";
	}

	if (isset($data["sort_dice_sets"]) && $data["sort_dice_sets"])
	{
		$data["message"] .= "Each set of dice rolls is sorted from high to low.\n";
	}
	
	$data["message"] .= "\n";

	for ($i = 1 ; $i <= $data["dice_sets"]; $i++)
	{
		$data["rawroll"] = array();
		$data["roll"] = array();
		$data["minus_roll"] = array();
		$data["message"] .= "Roll set " . $i . "\n";
		$data["message"] .= "	Die rolls: ";

		$data["rawroll"][0] = random_int(1, $data["dice_size"]);
		$data["roll"][0] = $data["rawroll"][0];
		$wild_roll = $data["roll"][0];
		$wild_rolls = '';
		$duplicates_message = '';
		
		if ($data["dice_deviation"] == "expertise" && $data["roll"][0] < 3)
		{
			while ($data["roll"][0] < 3)
			{
				$data["roll"][0] = random_int(1, $data["dice_size"]);
			}

			$data["roll"][0] .= 'e';
		}

		if ($data["dice_deviation"] == "wild" && $wild_roll == $data["dice_size"])
		{
			$wild_rolls = $wild_roll;

			while ($wild_roll == $data["dice_size"])
			{
				$wild_roll = random_int(1, $data["dice_size"]);
				$data["rawroll"][0] += $wild_roll;
				$data["roll"][0] = $data["rawroll"][0];
				$wild_rolls .= '+' . $wild_roll;
			}

			$wild_rolls .= '=';
			$data["roll"][0] .= 'w';
		}

		for ($j = 1 ; $j <= ($data["dice_quantity"] - 1); $j++)
		{
			$data["rawroll"][$j] = random_int(1, $data["dice_size"]);
			$data["roll"][$j] = $data["rawroll"][$j];

			if ($data["dice_deviation"] == "expertise" && $data["roll"][$j] < 3)
			{
				while ($data["roll"][$j] < 3)
				{
					$data["roll"][$j] = random_int(1, $data["dice_size"]);
				}

				$data["roll"][$j] .= 'e';
			}

			if ($j == ($data["dice_quantity"] - 1) && $data["dice_deviation"] == "stunt")
			{
				$data["roll"][$j] .= 's';
			}
		}

		if (isset($data["sort_dice_sets"]) && $data["sort_dice_sets"])
		{
			rsort($data["roll"]);
		}

		$max_roll = max($data["rawroll"]);
		$max_key = array_search($max_roll, $data["roll"]);
		$min_roll = min($data["rawroll"]);
		$min_key = array_search($min_roll, $data["roll"]);

		$duplicates = array_count_values($data["rawroll"]);
		sort($duplicates);

		for ($k = 0 ; $k <= count($duplicates) - 1; $k++)
		{
			if ($duplicates[$k] > 1)
			{
				$duplicates_message = ' (Duplicates!)';
			}

			if ($duplicates[$k] == 2)
			{
				$duplicates_message = ' (Doubles!)';
			}

			if ($duplicates[$k] == 3)
			{
				$duplicates_message = ' (Triples!)';
			}
		}

		for ($j = 0 ; $j <= $data["dice_quantity"] - 1; $j++)
		{
			if (($data["dice_deviation"] == "highest") && ($j == $max_key))
			{
				$data["message"] .= "[" . intval($data["roll"][$j]) . "]";
			}
			else if (($data["dice_deviation"] == "lowest") && ($j == $min_key))
			{
				$data["message"] .= "[" . intval($data["roll"][$j]) . "]";
			}
			else if (($data["dice_deviation"] == "expertise") && (substr($data["roll"][$j], -1) ==  'e'))
			{
				$data["message"] .= "[" . intval($data["roll"][$j]) . "]";
			}
			else if (($data["dice_deviation"] == "wild") && (substr($data["roll"][$j], -1) ==  'w'))
			{
				$data["message"] .= "[" . $wild_rolls . intval($data["roll"][$j]) . "]";
			}
			else if (($data["dice_deviation"] == "stunt") && (substr($data["roll"][$j], -1) ==  's'))
			{
				$data["message"] .= "[" . intval($data["roll"][$j]) . "]";
			}
			else
			{
				$data["message"] .= intval($data["roll"][$j]);
			}

			if ($j < ($data["dice_quantity"] - 1))
			{
				$data["message"] .= ", ";
			}
		}

		if ($data["dice_modify"] > 0)
		{
			$data["message"] .= ' + ' . $data["dice_modify"];
		}
		else if ($data["dice_modify"] < 0)
		{
			$data["message"] .= ' - ' . abs($data["dice_modify"]);
		}

		$data["message"] .= $duplicates_message . "\n";
		
		$data["message"] .= "	Roll subtotal: ";

		for ($j = 0 ; $j <= ($data["dice_quantity"] - 1); $j++)
		{
			if (!(($data["dice_deviation"] == "highest") && ($j == $max_key)) 
					&& !(($data["dice_deviation"] == "lowest") && ($j == $min_key)))
			{
				if (isset($data["roll"]["subtotal"]))
				{
					$data["roll"]["subtotal"] += intval($data["roll"][$j]);
				} else {
					$data["roll"]["subtotal"] = intval($data["roll"][$j]);
				}
			}
		}

		if (isset($data["roll"]["subtotal"]))
		{
			$data["roll"]["subtotal"] += intval($data["dice_modify"]);
		} else {
			$data["roll"]["subtotal"] = intval($data["dice_modify"]);
		}

		$data["message"] .= $data["roll"]["subtotal"] . "\n";

		if ($data["minus_dice_quantity"] > 0)
		{
			$data["message"] .= "	Minus die rolls: ";
			
			$data["minus_roll"][0] = random_int(1, $data["minus_dice_size"]);
			$wild_roll = $data["minus_roll"][0];
			$wild_rolls = '';
			
			if ($data["minus_dice_deviation"] == "wild" && $wild_roll == $data["minus_dice_size"])
			{
				$wild_rolls = $wild_roll;
				while ($wild_roll == $data["minus_dice_size"])
				{
					$wild_roll = random_int(1, $data["minus_dice_size"]);
					$data["minus_roll"][0] += $wild_roll;
					$wild_rolls .= '+' . $wild_roll;
				}
				$wild_rolls .= '=';
			}
			$data["minus_roll"][0] .= 'w';

			for ($j = 2 ; $j <= $data["minus_dice_quantity"]; $j++)
			{
				$data["minus_roll"][$j - 1] = random_int(1, $data["minus_dice_size"]);
			}

			if (isset($data["sort_dice_sets"]) && $data["sort_dice_sets"])
			{
				rsort($data["minus_roll"]);
			}

			$max_minus_roll = max($data["minus_roll"]);
			$max_minus_key = array_search($max_roll, $data["minus_roll"]);
			$min_minus_roll = min($data["minus_roll"]);
			$min_minus_key = array_search($min_roll, $data["minus_roll"]);

			for ($j = 0; $j <= ($data["minus_dice_quantity"] - 1); $j++)
			{
				if (($data["minus_dice_deviation"] == "highest") && ($j == $max_minus_key))
				{
					$data["message"] .= "[" . intval($data["minus_roll"][$j]) . "]";
				}
				else if (($data["minus_dice_deviation"] == "lowest") && ($j == $min_minus_key))
				{
					$data["message"] .= "[" . intval($data["minus_roll"][$j]) . "]";
				}
				else if (($data["minus_dice_deviation"] == "wild") && (substr($data["minus_roll"][$j], -1) ==  'w'))
				{
					$data["message"] .= "[" . $wild_rolls . intval($data["minus_roll"][$j]) . "]";
				}
				else
				{
					$data["message"] .= intval($data["minus_roll"][$j]);
				}

				if ($j < ($data["minus_dice_quantity"] - 1))
				{
					$data["message"] .= ", ";
				}
			}

			if ($data["minus_dice_modify"] > 0)
			{
				$data["message"] .= ' + ' . $data["minus_dice_modify"] . "\n";
			}
			else if ($data["minus_dice_modify"] < 0)
			{
				$data["message"] .= ' - ' . abs($data["minus_dice_modify"]) . "\n";
			}
			else 
			{
				$data["message"] .= "\n";
			}
			
			$data["message"] .= "	Minus roll subtotal: ";

			for ($j = 0 ; $j <= ($data["minus_dice_quantity"] - 1); $j++)
			{
				if (!(($data["minus_dice_deviation"] == "highest") && ($j == $max_minus_key)) 
						&& !(($data["minus_dice_deviation"] == "lowest") && ($j == $min_minus_key)))
				{
					if (!isset($data["minus_roll"][$j]))
					{
						$data["minus_roll"][$j] = 0;
					}

					if (isset($data["minus_roll"]["subtotal"]))
					{
						$data["minus_roll"]["subtotal"] += intval($data["minus_roll"][$j]);
					} else {
						$data["minus_roll"]["subtotal"] = intval($data["minus_roll"][$j]);
					}
				}
			}

			if (isset($data["minus_roll"]["subtotal"]))
			{
				$data["minus_roll"]["subtotal"] += intval($data["minus_dice_modify"]);
			} else {
				$data["minus_roll"]["subtotal"] = intval($data["minus_dice_modify"]);
			}

			$data["message"] .= $data["minus_roll"]["subtotal"] . "\n";
			
			$data["roll"]["total"] = $data["roll"]["subtotal"] - intval($data["minus_roll"]["subtotal"]);
			$data["message"] .= "	Roll total: ";
			$data["message"] .= $data["roll"]["subtotal"] . ' - ' . $data["minus_roll"]["subtotal"] . ' = ' . 
					$data["roll"]["total"] . "\n\n";

		}
		else 
		{
			$data["message"] .= "	Roll total: ";
			$data["roll"]["total"] = intval($data["roll"]["subtotal"]);
			$data["message"] .= $data["roll"]["total"] . "\n\n";
		}
	}

	$data["message"] = trim($data["message"]);
}

/**
 * Generates Fudge dice roll results.
 *
 * @access  public
 * @param   array	$data		Dice roll data
 */
function generateFudgeDiceResults($data)
{
	global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

	$data["dice_size"] = 3;

	$data["message"] = $data["fudge_dice_quantity"] . " Fudge dice";

	$data["message"] .= ", rolled ";

	if ($data["dice_sets"] > 1)
	{
		$data["message"] .= $data["dice_sets"] . " times.\n\n";
	}
	else
	{
		$data["message"] .= " once.\n\n";
	}

	for ($i = 1 ; $i <= $data["dice_sets"]; $i++)
	{
		$data["roll"] = array();
		$data["message"] .= "Roll set " . $i . "\n";
		$data["message"] .= "	Die rolls: ";

		for ($j = 1 ; $j <= $data["fudge_dice_quantity"]; $j++)
		{
			if (isset($data["roll"][$j - 1]))
			{
				$data["roll"][$j - 1] .= random_int(1, $data["dice_size"]);
			} else {
				$data["roll"][$j - 1] = random_int(1, $data["dice_size"]);
			}
		}

		for ($j = 1 ; $j <= $data["fudge_dice_quantity"]; $j++)
		{
			$current_roll = (intval($data["roll"][$j - 1]) - 2);

			if (isset($data["roll"]["total"]))
			{
				$data["roll"]["total"] += $current_roll;
			} else {
				$data["roll"]["total"] = $current_roll;
			}
			
			if ($current_roll >= 0)
			{
			
				$data["message"] .= '+' . $current_roll;
			
			} else {
			
				$data["message"] .= $current_roll;

			}

			if ($j < $data["fudge_dice_quantity"])
			{
				$data["message"] .= ", ";
			}
		}

		$data["message"] .= "\n";
		

		$data["message"] .= "	Roll total: ";
		$data["message"] .= $data["roll"]["total"] . "\n\n";
	}

	$data["message"] = trim($data["message"]);
}

/**
 * Mails dice roll results to selected recipients.
 *
 * @access  public
 * @param   array	$data		Dice roll data
 */
function mailDiceResults($data)
{
	global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

	// variables defined in common.php
	//$data["smtp_server"]
	//$data["from_name"]
	//$data["from_email"]
	//$data["from_password"]

	$data["body"]      = formatEmailMessage($data["message"]);

	// Verify email addresses
	if (isset($data["dice_mailto"]) && isEmailValid($data["dice_mailto"]))
	{
		$data["to"] = $data["dice_mailto"];

		if (isset($data["dice_mailgm"]) && isEmailValid($data["dice_mailgm"]))
		{
			$data["gm"] = $data["dice_mailgm"];
		}
	}
	else if (isset($data["dice_mailgm"]) && isEmailValid($data["dice_mailgm"]))
	{
		$data["to"] = $data["dice_mailgm"];
		$data["gm"] = '';
	}

	if (isset($data["dice_subject"]) && isSubjectValid($data["dice_subject"]))
	{
		$data["subject"] = $data["dice_subject"];
	}
	else
	{
		$data["subject"] = 'no subject';
	}

	if (strlen($data["to"]) > 4)
	{
		$mail = new PHPMailer(true);                              // Passing `true` enables exceptions

		try
		{
			// Server settings
			$mail->SMTPDebug = 0;                                 // 1: client; 2: client and server; 3: client, server, and connection; 4: low-level information.
			//$mail->Debugoutput = 'echo';
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $data["smtp_server"];                   // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = $data["from_email"];                // SMTP username
			$mail->Password = $data["from_password"];             // SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption, PHPMailer::ENCRYPTION_SMTPS also accepted
			$mail->Port = 587;                                    // TCP port to connect to

			// Recipients
			$mail->setFrom($data["from_email"], $data["from_name"]);

			if (isset($data["to"]) && isEmailValid($data["to"]))
			{
				$mail->addAddress($data["to"]);                   // Add a recipient
			}

			//$mail->addAddress('contact@example.com');           // Name is optional
			//$mail->addReplyTo('info@example.com', 'Information');

			if (isset($data["gm"]) && isEmailValid($data["gm"]))
			{
				$mail->addCC($data["gm"]);
			}

			//$mail->addBCC('bcc@example.com');

			// Attachments
			//$mail->addAttachment('/var/tmp/file.tar.gz');       // Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');  // Optional name

			// Content
			$mail->isHTML(false);                                 // Set email format to plain text
			$mail->Subject = $data["subject"];
			$mail->Body    = $data["body"];
			//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
		}
		catch (Exception $e)
		{
			echo 'Dice roll results could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		}
	}
}

/**
 * Displays dice roll results.
 *
 * @access  public
 * @param   string  $results        Dice roll results
 * @return  string  $results_html   HTML-formatted dice roll results
 */
function formatDiceResults($results)
{
    global $data;
    global $webmaster, $webmaster_email, $sitetitle, $pagetitle, $siteurl, $webroot;

    $results_html = str_replace("\n\n","</p><p>", $results);
    $results_html = str_replace("\n","<br />", $results_html);
    $results_html = str_replace("</p><p>","</p>\n\n<p>", $results_html);
    $results_html = str_replace("<br />","<br />\n", $results_html);
    $results_html = "<p>" . $results_html . "</p>\n\n";

    return $results_html;
}

/**
 * Displays dice roll results.
 *
 * @access  public
 * @param   array	$data		Dice roll data
 */
function showDiceResults($data)
{
	global $data, $db;

?>

	<table border="0">
    <tr>
    <td valign="top">
    Roll ID:&nbsp;
    </td>
    <td valign="top">
    <?php echo $data['roll_id']; ?>
    </td>
    </tr>
    <tr>
    <td valign="top">
    MD5 checksum:&nbsp;
    </td>
    <td valign="top">
    <?php echo $data['hash']; ?>
    </td>
    </tr>
    <tr>
    <td valign="top" colspan="2">
    Verified die roll:
    </td>
    </tr>
    <tr>
    <td valign="top" colspan="2">

<?php

    $query = mysqli_query($db, "SELECT `results` FROM `secure_dice` WHERE `id` = \"" . intval($data["roll_id"]) . "\" AND `hash` = \"" . mysqli_real_escape_string($db, $data["hash"]) . "\";");
    $data["roll"] = array_shift(mysqli_fetch_array($query));

    echo formatDiceResults($data["roll"]);

?>

    </td>
    </tr>
    </table>

	<p>
	<input type="button" name="roll_again" value="Roll Again" onclick="location.href='<?php echo $data['page_url']; ?>';" />
	</p>

<?php

}

$db = connect_to_db();

if ($data["op"] == 'roll')
{
	if ($data["fudge_dice_quantity"] > 0) 
	{
		generateFudgeDiceResults($data);		
	} else {
		generateDiceResults($data);	
	}
	storeDiceResults($data["message"]);
	storeDiceHash($data["message"]);
	storeDiceRolled($data);
	mailDiceResults($data);

	$data['page_url']  = $siteurl . "/software/securedice/?op=";

	$data['page_url'] .= ((!empty($data['dice_quantity'])		&& $data['dice_quantity'] != '--')		? "&amp;dq=" . $data['dice_quantity']		 : '');
	$data['page_url'] .= ((!empty($data['minus_dice_quantity'])  && $data['minus_dice_quantity'] != '--')  ? "&amp;mdq=" . $data['minus_dice_quantity']  : '');
	$data['page_url'] .= ((!empty($data['fudge_dice_quantity'])  && $data['fudge_dice_quantity'] != '--')  ? "&amp;fdq=" . $data['fudge_dice_quantity']  : '');
	$data['page_url'] .= ((!empty($data['dice_size'])			&& $data['dice_size'] != '--')			? "&amp;ds=" . $data['dice_size']			 : '');
	$data['page_url'] .= ((!empty($data['minus_dice_size'])	  && $data['minus_dice_size'] != '--')	  ? "&amp;mds=" . $data['minus_dice_size']	  : '');
	$data['page_url'] .= ((!empty($data['dice_modify'])		  && $data['dice_modify'] != '--')		  ? "&amp;dm=" . $data['dice_modify']		   : '');
	$data['page_url'] .= ((!empty($data['minus_dice_modify'])	&& $data['minus_dice_modify'] != '--')	? "&amp;mdm=" . $data['minus_dice_modify']	: '');
	$data['page_url'] .= ((!empty($data['dice_deviation'])	   && $data['dice_deviation'] != '--')	   ? "&amp;dd=" . $data['dice_deviation']		: '');
	$data['page_url'] .= ((!empty($data['minus_dice_deviation']) && $data['minus_dice_deviation'] != '--') ? "&amp;mdd=" . $data['minus_dice_deviation'] : '');
	$data['page_url'] .= ((!empty($data['dice_sets'])			&& $data['dice_sets'] != '--')			? "&amp;dt=" . $data['dice_sets']			 : '');
	$data['page_url'] .= ((!empty($data['sort_dice_sets'])	   && $data['sort_dice_sets'] != '--')	   ? "&amp;sdt=" . $data['sort_dice_sets']			 : '');
	$data['page_url'] .= ((!empty($data['dice_mailto'])		  && $data['dice_mailto'] != '--')		  ? "&amp;to=" . $data['dice_mailto']		   : '');
	$data['page_url'] .= ((!empty($data['dice_mailgm'])		  && $data['dice_mailgm'] != '--')		  ? "&amp;gm=" . $data['dice_mailgm']		   : '');
	$data['page_url'] .= ((!empty($data['dice_subject'])		 && $data['dice_subject'] != '--')		 ? "&amp;sub=" . $data['dice_subject']		 : '');
	$data['page_url'] .= ((!empty($data['bypass'])		 && $data['bypass'] != '--')		 ? "&amp;bypass=" . $data['bypass']		 : '');

	mysqli_close($db);
	header('Location: index.php?op=results&roll_id=' . $data["roll_id"] . '&hash=' . $data["hash"] . '&page_url=' . urlencode($data['page_url']));
}
else 
{

	make_header($pagetitle, "form", $keywords);

?>

	<h1><?php echo $pagetitle; ?></h1>

	<p>
	Copyright &copy; 2005-2024 Brandon Blackmoor <a href="mailto:bblackmoor@blackgate.net?subject=Secure%20Dice">&lt;bblackmoor@blackgate.net&gt;</a><br />
	Licensed under the GNU General Public License v3.0:
	<a href="https://www.gnu.org/licenses/gpl-3.0.en.html">https://www.gnu.org/licenses/gpl-3.0.en.html</a><br />
	Source: <a href="https://github.com/bblackmoor/securedice">https://github.com/bblackmoor/securedice</a><br />
	Last updated: <?php echo date("Y-m-d", filemtime(__FILE__)); ?>
    </p>

    <p>
    <?php echo $sitetitle; ?> Secure Dice is a free online dice roller which will generate cryptographically secure pseudo-random integers, generate a MD5 checksum of the results, and email those results to the email address(es) you specify.
    </p>

    <p>
    Email from the dice server includes a MD5 checksum which can be used to verify that the dice roll sent to you has not been modified. You can type the checksum into the <a href="verify.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>">verification page</a> to ensure that the dice results are genuine.
    </p>
	
<?php

	switch($data["op"])
	{
		case "roll":
			// this should never happen
			break;

		case "results":
			showDiceResults($data);
			break;

		case "select":
		default:
			displayDiceForm($data);
			break;
	}

?>

    <p>
    Never accept a roll sent from anyone other than the <?php echo $sitetitle; ?> server. If the other player forgot to include your email address when generating the dice roll, ask them to roll again: it's the only way to be sure that they sent you the results of a single die roll, rather than the best of many rolls.
    <p>

	<p>
	Please whitelist or subscribe <b><?php echo $data['from_email']; ?></b> to ensure delivery of your dice rolls.
	</p>

	<h2>Server Requirements</h2>

    <p>
    If you wish to run Secure Dice on your server, it requires:
    </p>
	
	<ul>
		<li>PHP: <a href="https://www.php.net/">https://www.php.net/</a></li>
		<li>PHPMailer: <a href="https://github.com/PHPMailer/PHPMailer">https://github.com/PHPMailer/PHPMailer</a></li>
	</ul>

<?php

	mysqli_close($db);
	make_footer(349, "article");

}

?>