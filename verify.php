<?php

require "../../include/common.php";

$pagetitle  = "Secure Dice Verification";
$keywords   = "secure,dice,roller,roleplaying,role-playing,game";

//$vars = array_merge($_GET, $_POST);
//$data = array();
//$data = moveTrimmedToArray($vars, $data);

if (!isset($data["op"]))
{
    $data["op"] = '';
}

if (!isset($data["verify_id"]) || $data["verify_id"] == '--')
{
	$data["verify_id"] = '';
}

if (!isset($data["verify_hash"]) || $data["verify_hash"] == '--')
{
	$data["verify_hash"] = '0';
}

/**
 * Displays verification form.
 *
 * @access  public
 */
function displayVerifyForm()
{
    global $data;

?>

    <form method="post" action="verify.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>">

    <table border="0">
    <tr>
    <td valign="top">
    Type the Roll ID:
    </td>
    <td valign="top">
    <input type="text" name="verify_id" id="verify_id" size="32" maxlength="255" value="" /><br />
    </td>
    </tr>
    <tr>
    <td valign="top">
    and the MD5 checksum:
    </td>
    <td valign="top">
    <input type="text" name="verify_hash" id="verify_hash" size="32" maxlength="255" value="" />
    </td>
    </tr>
    <tr>
    <td valign="top">
	</td>
    <td valign="top">
    <input type="hidden" name="op" value="results" />
    <input type="submit" name="submit" id="submit" value="Verify" />
    </td>
    </tr>
    </table>

	<input type="button" name="roll_again" value="Roll Again" onclick="location.href='index.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>';" />

    </form>

<?php

}

/**
 * Displays dice roll results.
 *
 * @access  public
 * @param   string  $results        Dice roll results
 * @param   string  $results_html   HTML-formatted dice roll results
 */
function formatDiceResults($results)
{
    global $data;

    $results_html = str_replace("\n\n","</p><p>", $results);
    $results_html = str_replace("\n","<br />", $results_html);
    $results_html = str_replace("</p><p>","</p>\n\n<p>", $results_html);
    $results_html = str_replace("<br />","<br />\n", $results_html);
    $results_html = "<p>" . $results_html . "</p>\n\n";

    $results_html = str_replace("\n","<br />\n", $results);

    return $results_html;
}

/**
 * Displays dice roll verificaton results.
 *
 * @access  public
 * @return  string  $verify_results   Dice roll results
 */
function showVerifyResults()
{
    global $data, $db;

//XXX
    $query = mysqli_query($db, "SELECT `results` FROM `secure_dice` WHERE `id` = \"" . intval($data["verify_id"]) . "\" AND `hash` = \"" . mysqli_real_escape_string($db, $data["verify_hash"]) . "\";") or trigger_error("Query Failed!", E_USER_ERROR);

//XXX
//print_r($query);
//echo($query);
//print_r(mysqli_fetch_array($query));
	// Get the number of rows in the result set
	$row_cnt = $query->num_rows;

	if($row_cnt > 0)
	{

?>

    <table border="0">
    <tr>
    <td valign="top">
    Roll ID:
    </td>
    <td valign="top">
    <?php echo $data["verify_id"]; ?>
    </td>
    </tr>
    <tr>
    <td valign="top">
    MD5 checksum:
    </td>
    <td valign="top">
    <?php echo $data["verify_hash"]; ?>
    </td>
    </tr>
    <tr>
    <td valign="top">
    Verified die roll:
    </td>
    <td valign="top">

<?php

    $data["roll"] = array_shift(mysqli_fetch_array($query));

    echo formatDiceResults($data["roll"]);

?>

    </td>
    </tr>
    </table>

<?php

    }
	else
	{
		printf("Result set has %d rows.\n", $row_cnt);
	}
	
?>

    <p>
    <input type="button" name="roll_again" value="Roll Again" onclick="location.href='index.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>';" />
    <input type="button" name="verify_again" value="Verify Again" onclick="location.href='verify.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>';" />
    </p>

<?php

}

$db = connect_to_db();
make_header($pagetitle, "article", $keywords);

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
    <<?php echo $sitetitle; ?> Secure Dice is a free online dice roller which will generate cryptographically secure pseudo-random integers, generate a MD5 checksum of the results, and email those results to the email address(es) you specify.
    </p>

    <p>
    Email from the dice server includes a MD5 checksum which can be used to verify that the dice roll sent to you has not been modified. You can type the checksum into the <a href="verify.php<?php echo ((!empty($data['bypass']) && $data['bypass'] != '--') ? "?bypass=" . $data['bypass'] : ''); ?>">verification page</a> to ensure that the dice results are genuine.
    </p>

<?php

switch($data["op"])
{
    case "results":
        showVerifyResults();
        break;

    case "verify":
    default:
        displayVerifyForm();
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

?>
