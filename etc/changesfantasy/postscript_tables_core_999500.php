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
$Id: postscript_tables_core_999500.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/
require_once MAX_PATH.'/etc/changesfantasy/script_tables_core_parent.php';

class postscript_tables_core_999500 extends script_tables_core_parent
{
    function postscript_tables_core_999500()
    {
    }

    function execute_destructive($aParams)
    {
        $this->init($aParams);
        $this->_log('*********** destructive ****************');
        $this->_logActual();
        return true;
    }

    function _logActual()
    {
        $aExistingTables = $this->oDBUpgrade->_listTables();
        $prefix = $this->oDBUpgrade->prefix;
        if (in_array($prefix.'klapaucius', $aExistingTables))
        {
            $aDef = $this->oDBUpgrade->_getDefinitionFromDatabase('klapaucius');
            $msg = $this->_testName('A');
            if (!isset($aDef['tables']['klapaucius']['fields']['text_field']))
            {
                $this->_log($msg.' removed field text_field from table '.$prefix.'klapaucius');
            }
            else
            {
                $this->_log($msg.' failed to remove field text_field from table'.$prefix.'klapaucius');
            }
            $msg = $this->_testName('B');
            if (!isset($aDef['tables']['klapaucius']['indexes']['klapaucius_pkey']))
            {
                $this->_log($msg.' removed primary key constraint klapaucius_pkey from table '.$prefix.'klapaucius');
            }
            else
            {
                $this->_log($msg.' failed to remove primary key constraint klapaucius_pkey from table'.$prefix.'klapaucius');
            }
        }
    }
}

?>