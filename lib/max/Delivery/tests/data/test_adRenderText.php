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
$Id: test_adRenderText.php 37440 2009-06-04 10:19:55Z matteo.beccati $
*/

/**
 * @package    MaxDelivery
 * @subpackage TestSuite Data
 * @author     Monique Szpak <monique@m3.net>
 */

$zoneId		=	0;
$source		=	'';
$ct0		=	'';
$withText	=	false;
$logClick	=	true;
$logView	=	true;
$useAlt		=	false;
$loc		=	0;
$referer	= 	'http://some.referrer.com/';
$conf = $GLOBALS['_MAX']['CONF'];

$aBanner    =   array(
    'ad_id' => 5,
    'placement_id' => 1,
    'active' => 't',
    'name' => 'Text ad',
    'type' => 'txt',
    'contenttype' => 'txt',
    'pluginversion' => 0,
    'filename' => '',
    'imageurl' => '',
    'htmltemplate' => '',
    'htmlcache' =>  '',
    'width' => 0,
    'height' => 0,
    'weight' => 1,
    'seq' => 0,
    'target' => '_blank',
    'url' => 'http://www.m3.net',
    'alt' => '',
    'status' => '',
    'bannertext' => 'm3 media services',
    'adserver' => '',
    'block' => 0,
    'capping' => 0,
    'session_capping' => 0,
    'compiledlimitation' => true,
    'append' => '',
    'prepend' => '',
    'bannertype' => 0,
    'alt_filename' => '',
    'alt_imageurl' => '',
    'alt_contenttype' => '',
);

$expectNoBeacon =
"<a href='http://{$GLOBALS['_MAX']['CONF']['webpath']['delivery']}/{$GLOBALS['_MAX']['CONF']['file']['click']}?{$conf['var']['params']}=2__{$conf['var']['adId']}=5__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.m3.net' target='_blank'>m3 media services</a>";
$expect = $expectNoBeacon .
"<div id='beacon_{random}' style='position: absolute; left: 0px; top: 0px; visibility: hidden;'><img src='http://{$GLOBALS['_MAX']['CONF']['webpath']['delivery']}/{$GLOBALS['_MAX']['CONF']['file']['log']}?{$conf['var']['adId']}=5&amp;campaignid=1&amp;{$conf['var']['zoneId']}=0&amp;referer=http%3A%2F%2Fsome.referrer.com%2F&amp;cb={random}' width='0' height='0' alt='' style='width: 0px; height: 0px;' /></div>";

?>
