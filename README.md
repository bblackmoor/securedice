# Secure Dice
Copyright © 2005-2023 Brandon Blackmoor &lt;<bblackmoor@blackgate.net>&gt;<br />
Licensed under the GNU General Public License v3.0: https://www.gnu.org/licenses/gpl-3.0.en.html<br />
Source: https://github.com/bblackmoor/securedice

RPG Library Secure Dice is a free online dice roller which will generate random numbers, generate a MD5 checksum of the results, and email those results to the email address(es) you specify.

Email from the dice server includes a MD5 checksum which can be used to verify that the dice roll sent to you has not been modified. You can type the checksum into the verification page to ensure that the dice results are genuine.

Never accept a roll sent from anyone other than the RPG Library server. If the other player forgot to include your email address when generating the dice roll, ask them to roll again: it's the only way to be sure that they sent you the results of a single die roll, rather than the best of many rolls.

You can access Secure Dice at RPG Library: https://www.rpglibrary.org/software/securedice/

## Server Requirements

If you wish to run Secure Dice on your server, it requires:

* PHP: https://www.php.net/</a></li>
* PHPMailer: https://github.com/PHPMailer/PHPMailer
