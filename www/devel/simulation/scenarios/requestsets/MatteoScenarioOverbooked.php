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
$Id: MatteoScenarioOverbooked.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

$oRequest = new stdClass();

$oRequest->what     = 'zone:1';
$oRequest->target   = '';
$oRequest->source   = '';
$oRequest->withText = false;
$oRequest->context  = 0;
$oRequest->richMedia = true;
$oRequest->ct0      = '';
$oRequest->loc      = 'http://www.beccati.com';
$oRequest->referer  = '';

if (version_compare(phpversion(), '5.0.0', '>='))
	$oRequest2 = clone $oRequest;
else
	$oRequest2 = $oRequest;

$oRequest2->what = 'zone:2';

$aIterations[1]['request_objects'][1]   = $oRequest;
$aIterations[1]['request_objects'][2]   = $oRequest2;
$aIterations[1]['shuffle_requests']     = false;
$aIterations[1]['max_requests']         = 40;

for($i=2;$i<25;$i++)
{
    $aIterations[$i] = $aIterations[1];
}

$iterations = count($aIterations);

$precis=    '<pre>'."Matteo scenario with overbooking

Zones (960 impressions):
1.	40 hourly impressions (480 total)
2.	40 hourly impressions (480 total)

Campaigns:
2.	High (pri 7) 600 impressions target	linked to zone 1
3.	High (pri 5) 600 impressions target 	linked to zone 1 and zone 2
4.	Low priority	linked to zone 1 and zone 2
".'</pre>';

$precis.=    print_r($oRequest, true);

$GLOBALS['_MAX']['CONF']['sim']['precis']       = $precis;
$GLOBALS['_MAX']['CONF']['sim']['iterations']   = $iterations;
$GLOBALS['_MAX']['CONF']['sim']['oRequest']     = $oRequest;
$GLOBALS['_MAX']['CONF']['sim']['aIterations']  = $aIterations;
$GLOBALS['_MAX']['CONF']['sim']['startdate']    = "2007-02-06 00:00:00";
$GLOBALS['_MAX']['CONF']['sim']['startday']     = "2007-02-06";
$GLOBALS['_MAX']['CONF']['sim']['starthour']    = "0";
?>