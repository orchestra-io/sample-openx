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
$Id: processSettings.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

/**
 * A class that deals with configuration settings for this group of components
 *
 */
class oxMemcached_processSettings
{

    /**
     * Method that is called on settings form submission
     * Error messages are appended to the 0 index of the array
     *
     * @return boolean
     */
    function validate(&$aErrorMessage)
    {
        // Store current values from config
        // overwrite it by tested ones
        $storeSettings = array();
        if (isset($GLOBALS['oxMemcached_memcachedServers'])) {
            $storeSettings['memcachedServers'] = $GLOBALS['_MAX']['CONF']['oxMemcached']['memcachedServers'];
            $GLOBALS['_MAX']['CONF']['oxMemcached']['memcachedServers'] = $GLOBALS['oxMemcached_memcachedServers'];
        }
        if (isset($GLOBALS['oxMemcached_memcachedExpireTime'])) {
            $storeSettings['memcachedExpireTime'] = $GLOBALS['_MAX']['CONF']['oxMemcached']['memcachedExpireTime'];
            $GLOBALS['_MAX']['CONF']['oxMemcached']['memcachedExpireTime'] = $GLOBALS['oxMemcached_memcachedExpireTime'];
        }

        // Use memcached plugin getStatus function to validate
        $oPlgOxMemcached = &OX_Component::factory('deliveryCacheStore', 'oxMemcached', 'oxMemcached');
        $result = $oPlgOxMemcached->getStatus();
        if ($result !== true) {
            $aErrorMessage[0] = $result;
            $result = false;
        }

        // Restore config values
        foreach ($storeSettings as $key => $value) {
            $GLOBALS['_MAX']['CONF']['oxMemcached'][$key] = $value;
        }

        return $result;
    }
}


?>