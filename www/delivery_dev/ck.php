<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2009 OpenX Limited                                     |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: ck.php 60723 2010-08-31 00:10:48Z chris.nutting $
*/

/**
 * @package    MaxDelivery
 * @author     Scott Switzer <scott@switzer.org>
 */

// Require the initialisation file
require_once '../../init-delivery.php';

// Required files
require_once MAX_PATH . '/lib/max/Delivery/querystring.php';

// Prevent click from being cached by browsers
MAX_commonSetNoCacheHeaders();

// Convert specially encoded params into the $_REQUEST variable
MAX_querystringConvertParams();

// Remove any special characters
MAX_commonRemoveSpecialChars($_REQUEST);

// Get the variables
$viewerId = MAX_cookieGetUniqueViewerID();

if (!empty($GLOBALS['_MAX']['COOKIE']['newViewerId']) && empty($_GET[$conf['var']['cookieTest']])) {
    // No previous cookie was found, and we have not tried to force setting one...
    MAX_cookieSetViewerIdAndRedirect($viewerId);
}

$adId       = isset($_REQUEST[$conf['var']['adId']]) ? explode($GLOBALS['_MAX']['MAX_DELIVERY_MULTIPLE_DELIMITER'], $_REQUEST[$conf['var']['adId']]) : array();
$zoneId     = isset($_REQUEST[$conf['var']['zoneId']]) ? explode($GLOBALS['_MAX']['MAX_DELIVERY_MULTIPLE_DELIMITER'], $_REQUEST[$conf['var']['zoneId']]) : array();
$creativeId = isset($_REQUEST[$conf['var']['creativeId']]) ? explode($GLOBALS['_MAX']['MAX_DELIVERY_MULTIPLE_DELIMITER'], $_REQUEST[$conf['var']['creativeId']]) : array();
$lastClick  = isset($_REQUEST[$conf['var']['lastClick']]) ? explode($GLOBALS['_MAX']['MAX_DELIVERY_MULTIPLE_DELIMITER'], $_REQUEST[$conf['var']['lastClick']]) : array();
$aBlockLoggingClick = isset($_REQUEST[$conf['var']['blockLoggingClick']]) ? $_REQUEST[$conf['var']['blockLoggingClick']] : array();

if (!empty($conf['deliveryLog']['enabled'])) {
    foreach ($adId as $k => $v) {
        OX_Delivery_logMessage('$adId['.$k.']='.$v, 7);
    }
    foreach ($zoneId as $k => $v) {
        OX_Delivery_logMessage('$zoneId['.$k.']='.$v, 7);
    }
    foreach ($creativeId as $k => $v) {
        OX_Delivery_logMessage('$creativeId['.$k.']='.$v, 7);
    }
    foreach ($lastClick as $k => $v) {
        OX_Delivery_logMessage('$lastClick['.$k.']='.$v, 7);
    }
    foreach ($aBlockLoggingClick as $k => $v) {
        OX_Delivery_logMessage('$aBlockLoggingClick['.$k.']='.$v, 7);
    }
}

if (empty($adId) && !empty($zoneId)) {
    foreach ($zoneId as $index => $zone) {
        $adId[$index] = _getZoneAd($zone);
        $creativeId[$index] = 0;
    }
}
for ($i = 0; $i < count($adId); $i++) {
    $adId[$i] = intval($adId[$i]);
    $zoneId[$i] = intval($zoneId[$i]);
    if (isset($creativeId[$i])) {
        $creativeId[$i] = intval($creativeId[$i]);
    } else {
        $creativeId[$i] = 0;
    }
    if (($adId[$i] > 0 || $adId[$i] == -1) && ($conf['logging']['adClicks']) && !(isset($_GET['log']) && ($_GET['log'] == 'no'))) {
        if (!MAX_Delivery_log_isClickBlocked($adId[$i], $aBlockLoggingClick)) {
            if (isset($GLOBALS['conf']['logging']['blockAdClicksWindow']) && $GLOBALS['conf']['logging']['blockAdClicksWindow'] != 0) {
                MAX_Delivery_log_setClickBlocked($i, $adId);
            }
            MAX_Delivery_log_logAdClick($adId[$i], $zoneId[$i]);
            MAX_Delivery_log_setLastAction($i, $adId, $zoneId, $lastClick, 'click');
        }
    }
}

// Set the userid cookie
MAX_cookieAdd($conf['var']['viewerId'], $viewerId, time() + $conf['cookie']['permCookieSeconds']);
MAX_cookieFlush();

// Get the URL that we are going to redirect to
$destination = MAX_querystringGetDestinationUrl($adId[0]);

// Redirect to the destination url
if (!empty($destination) && empty($_GET['trackonly'])) {
    // Prevent HTTP response split attacks
    if (!preg_match('/[\r\n]/', $destination)) {
        MAX_redirect($destination);
    }
}

/**
 * Get the ad information when only passed in a zone ID (for email zones)
 *
 * @param int $zoneId The Zone ID of the zone
 * @return int $adId The ad ID of the only linked banner, or 0 if <> 1 active ad linked
 */

function _getZoneAd($zoneId)
{
    $conf = $GLOBALS['conf'];

    $zoneLinkedAds = MAX_cacheGetZoneLinkedAds($zoneId, false);
    if (!empty($zoneLinkedAds['xAds']) && count($zoneLinkedAds['xAds']) == 1) {
        reset($zoneLinkedAds['xAds']); 
        list($adId, $ad) = each($zoneLinkedAds['xAds']);
    } elseif (!empty($zoneLinkedAds['ads']) && count($zoneLinkedAds['ads']) == 1) {
        reset($zoneLinkedAds['ads']);
        // we select the first (and only) banner linked to this email zone
        foreach($zoneLinkedAds['ads'] as $priority => $ads) {
            foreach($ads as $adId => $ad) {
                break;
            }
        }
    } elseif (!empty($zoneLinkedAds['lAds']) && count($zoneLinkedAds['lAds']) == 1) {
        reset($zoneLinkedAds['lAds']); 
        list($adId, $ad) = each($zoneLinkedAds['lAds']);
    }

    if (!empty($ad['url'])) {
        // Store the destination URL to save querying the DB again
        $_REQUEST[$conf['var']['dest']] = $ad['url'];
    }
    return $adId;
}

?>
