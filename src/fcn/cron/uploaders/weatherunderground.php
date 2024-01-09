<?php
/**
 * Acuparse - AcuRite Access/smartHUB and IP Camera Data Processing, Display, and Upload.
 * @copyright Copyright (C) 2015-2023 Maxwell Power
 * @author Maxwell Power <max@acuparse.com>
 * @link http://www.acuparse.com
 * @license AGPL-3.0+
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this code. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * File: src/fcn/cron/uploaders/weatherunderground.php
 * Weather Underground Updater
 */

/**
 * @var mysqli $conn Global MYSQL Connection
 * @var object $config Global Config
 * @var object $atlas Atlas Data
 * @var object $data Weather Data
 * @var object $appInfo Global Application Info
 * @var string $utcDate Date
 */

syslog(LOG_NOTICE, "(EXTERNAL){WU}: Starting Update ...");

// Build and send update
$wuQueryUrl = $config->upload->wu->url . '?ID=' . $config->upload->wu->id . '&PASSWORD=' . $config->upload->wu->password;
$wuQuery = '&dateutc=' . $utcDate . '&tempf=' . $data->tempF . '&winddir=' . $data->windDEG . '&windspeedmph=' . $data->windSpeedMPH . '&baromin=' . $data->pressure_inHg . '&humidity=' . $data->relH . '&dewptf=' . $data->dewptF . '&rainin=' . $data->rainIN . '&dailyrainin=' . $data->rainTotalIN_today . '&windspdmph_avg2m=' . $data->windAvgMPH;
if ($config->station->device === 0) {
    $wuQuery = $wuQuery . '&windgustmph=' . $data->windGustMPH . '&windgustdir=' . $data->windGustDEG;
    if ($config->station->primary_sensor === 0) {
        $wuQuery = $wuQuery . '&UV=' . $atlas->uvIndex;
    }
}
$wuQueryStatic = '&softwaretype=' . ucfirst($appInfo->name) . '&action=updateraw';
$wuQueryResult = file_get_contents($wuQueryUrl . $wuQuery . $wuQueryStatic);
// Save to DB
mysqli_query($conn, "INSERT INTO `wu_updates` (`query`,`result`) VALUES ('$wuQuery', '$wuQueryResult')");

// Log it
syslog(LOG_NOTICE, "(EXTERNAL){WU}: Query = $wuQuery | Response = $wuQueryResult");
