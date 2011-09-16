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
$Id: AbstractTimePlugin.php 39260 2009-07-03 14:20:27Z matteo.beccati $
*/

require_once LIB_PATH . '/Extension/deliveryLimitations/DeliveryLimitations.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';
require_once LIB_PATH . '/Extension/deliveryLimitations/DeliveryLimitationsCommaSeparatedData.php';

/**
 * @package    OpenXPlugin
 * @subpackage DeliveryLimitations
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@m3.net>
 */

/**
 * A Time delivery limitation plugin base class.
 *
 * Works with:
 * A comma separated list of numbers, in the range specified in the constructor.
 *
 * Valid comparison operators:
 * ==, !=
 *
 * @package    OpenXPlugin
 * @subpackage DeliveryLimitations
 * @author     Andrew Hill <andrew@m3.net>
 * @author     Chris Nutting <chris@m3.net>
 */
class Plugins_DeliveryLimitations_AbstractTimePlugin extends Plugins_DeliveryLimitations_CommaSeparatedData
{
    /**
     * Initializes the object with range $min - $max.
     *
     * @param int $min
     * @param int $max
     * @return Plugins_DeliveryLimitations_Time_Base
     */
    function Plugins_DeliveryLimitations_Time_Base($min, $max)
    {
        $this->Plugins_DeliveryLimitations_ArrayData();
        $this->setAValues(range($min, $max));
    }

    /**
     * A method that returnes the currently stored timezone for the limitation
     *
     * @return string
     */
    function getStoredTz()
    {
        $offset = strpos($this->data, '@');
        if ($offset !== false) {
            return substr($this->data, $offset + 1);
        }
        return 'UTC';
    }

    /**
     * A private method that returnes the current timezone as set in the user preferences
     *
     * @return string
     */
    function _getCurrentTz()
    {
        if (isset($GLOBALS['_MAX']['PREF']['timezone'])) {
            $tz = $GLOBALS['_MAX']['PREF']['timezone'];
        } else {
            $tz = 'UTC';
        }

        return $tz;
    }

    function _flattenData($data = null)
    {
        return parent::_flattenData($data).'@'.$this->_getCurrentTz();
    }

    function _expandData($data = null)
    {
        if (!empty($data) && is_string($data)) {
            $offset = strpos($data, '@');
            if ($offset !== false) {
                $data = substr($data, 0, $offset);
            }
        }
        return parent::_expandData($data);
    }
}