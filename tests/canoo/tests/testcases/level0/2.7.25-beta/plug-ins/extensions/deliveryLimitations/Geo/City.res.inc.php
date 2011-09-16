<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2008 OpenX Limited                                     |
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
$Id: City.res.inc.php 22781 2008-07-16 16:52:20Z chris.nutting@openx.org $
*/

/**
 * @package    OpenXPlugin
 * @subpackage DeliveryLimitations
 * @author     Chris Nutting <chris.nutting@openx.org>
 * @author     Matteo Beccati <matteo.beccati@openx.org>
 */

if (!isset($GLOBALS['_MAX']['_GEOCACHE']['city'])) {
    $pathPlugins = MAX_PATH.$GLOBALS['_MAX']['CONF']['pluginPaths']['plugins'].'/deliveryLimitations/Geo/';
    require $pathPlugins.'data/res-iso3166.inc.php';
    require $pathPlugins.'Country.res.inc.php';

    foreach ($OA_Geo_ISO3166_MaxMind as $k => $v) {
        unset($res[$k]);
    }

    $res = array('' => MAX_Plugin_Translation::translate('---Any---', $this->extension, $this->group)) +
        $res;

    $GLOBALS['_MAX']['_GEOCACHE']['city'] = $res;
} else {
    $res = $GLOBALS['_MAX']['_GEOCACHE']['city'];
}

?>