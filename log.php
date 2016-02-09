<?php
/**
 * Log entries for a specific system
 *
 * No description
 *
 * @package EDTB\Main
 * @author Mauri Kujala <contact@edtb.xyz>
 * @copyright Copyright (C) 2016, Mauri Kujala
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 */

 /*
 * ED ToolBox, a companion web app for the video game Elite Dangerous
 * (C) 1984 - 2016 Frontier Developments Plc.
 * ED ToolBox or its creator are not affiliated with Frontier Developments Plc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

/** @var logsystem */
$logsystem = $_GET["system"];
if (!$logsystem) exit("No system set");

$logsystem_id = !isset($_GET["system_id"]) ? "-1" : 0 + $_GET["system_id"];
/**if (!$logsystem_id) exit("No system id set"); */

/** @var pagetitle */
$pagetitle = "ED ToolBox";

/** @require header file */
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/header.php");
?>
<div class="entries">
	<div class="entries_inner">
		<?php
		// get system-specific log
		$log_res = mysqli_query($GLOBALS["___mysqli_ston"], "	SELECT user_log.id, user_log.station_id, user_log.log_entry, user_log.stardate,
																user_log.pinned, user_log.type, user_log.title,
																edtb_stations.name AS station_name
																FROM user_log
																LEFT JOIN edtb_stations ON edtb_stations.id = user_log.station_id
																WHERE user_log.system_id = '" . $logsystem_id . "'
																OR user_log.system_name = '" . mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $logsystem) . "'
																ORDER BY -user_log.pinned, user_log.weight, user_log.stardate DESC") or write_log(mysqli_error($GLOBALS["___mysqli_ston"]));
		$num = mysqli_num_rows($log_res);

		if ($num > 0)
		{
			// check if system has screenshots
			$screenshots = has_screenshots($logsystem) ? '<a href="/Gallery.php?spgmGal=' . urlencode($logsystem) . '" title="View image gallery"><img src="/style/img/image.png" class="icon" alt="Gallery" style="margin-left:5px;vertical-align:top" /></a>' : "";

			echo '<h2><img class="icon" src="/style/img/log.png" alt="log" />System log for <a href="System.php?system_name=' . urlencode($logsystem) . '">' . $logsystem . $screenshots . '</a></h2>';
			echo '<hr>';

			while ($log_arr = mysqli_fetch_assoc($log_res))
			{
				$log_station_name = $log_arr["station_name"];
				$log_text = $log_arr["log_entry"];
				$date = date_create($log_arr["stardate"]);
				$log_added = date_modify($date, "+1286 years");

				// check if log is pinned
				$pinned = $log_arr["pinned"] == "1" ? '<img class="icon" src="/style/img/pinned.png" alt="Pinned" style="margin-right:3px" />' : "";

				// check if log is personal
				$personal = $log_arr["type"] == "personal" ? '<img class="icon" src="/style/img/user.png" alt="Personal" style="margin-right:3px" />' : "";

				$log_title = !empty($log_arr["title"]) ? '&nbsp;&ndash;&nbsp;' . $log_arr["title"] : "";

				echo '<h3>' . $pinned . $personal . '';
				echo '<a href="javascript:void(0)" onclick="toggle_log_edit(\'' . $log_arr["id"] . '\')" style="color:inherit" title="Edit entry">';
				echo date_format($log_added, "j M Y, H:i");

				if (!empty($log_station_name))
				{
					echo "&nbsp;[Station: " . $log_station_name . "]";
				}
				echo $log_title;
				echo '</a></h3><pre class="entriespre">';
				echo $log_text;
				echo '</pre>';
			}
		}
		else
		{
			echo "<h2>No log entries for " . $logsystem . "</h2><br />
					<a href=\"javascript:void(0);\" id=\"toggle\" onclick=\"toggle_log('" . addslashes($logsystem) . "');\" title=\"Add log entry\" style=\"color:inherit;\">Click here to add one</a>";
		}
		?>
	</div>
</div>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/style/footer.php");
