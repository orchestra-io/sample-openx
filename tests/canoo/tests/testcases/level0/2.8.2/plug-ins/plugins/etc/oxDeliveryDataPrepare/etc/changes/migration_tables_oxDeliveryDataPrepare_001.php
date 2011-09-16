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
$Id: migration_tables_oxDeliveryDataPrepare_001.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once(MAX_PATH.'/lib/OA/Upgrade/Migration.php');

/**
 * @package    Plugin
 * @subpackage openxDeliveryLog
 */
class Migration_001 extends Migration
{

    function Migration_001()
    {
        //$this->__construct();

		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_c';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_c';
		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_m_backup';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_m_backup';
		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_m';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_m';
		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_r';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_r';
		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_a';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_a';
		$this->aTaskList_constructive[] = 'beforeAddTable__data_bkt_a_var';
		$this->aTaskList_constructive[] = 'afterAddTable__data_bkt_a_var';


    }



	function beforeAddTable__data_bkt_c()
	{
		return $this->beforeAddTable('data_bkt_c');
	}

	function afterAddTable__data_bkt_c()
	{
		return $this->afterAddTable('data_bkt_c');
	}

	function beforeAddTable__data_bkt_m_backup()
	{
		return $this->beforeAddTable('data_bkt_m_backup');
	}

	function afterAddTable__data_bkt_m_backup()
	{
		return $this->afterAddTable('data_bkt_m_backup');
	}

	function beforeAddTable__data_bkt_m()
	{
		return $this->beforeAddTable('data_bkt_m');
	}

	function afterAddTable__data_bkt_m()
	{
		return $this->afterAddTable('data_bkt_m');
	}

	function beforeAddTable__data_bkt_r()
	{
		return $this->beforeAddTable('data_bkt_r');
	}

	function afterAddTable__data_bkt_r()
	{
		return $this->afterAddTable('data_bkt_r');
	}

	function beforeAddTable__data_bkt_a()
	{
		return $this->beforeAddTable('data_bkt_a');
	}

	function afterAddTable__data_bkt_a()
	{
		return $this->afterAddTable('data_bkt_a');
	}

	function beforeAddTable__data_bkt_a_var()
	{
		return $this->beforeAddTable('data_bkt_a_var');
	}

	function afterAddTable__data_bkt_a_var()
	{
		return $this->afterAddTable('data_bkt_a_var');
	}

}

?>