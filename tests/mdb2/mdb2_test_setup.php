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
$Id: mdb2_test_setup.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once 'MDB2/tests/testcases.php';

// use a user that has full permissions on a database named "driver_test"
$db = array(
    'dsn' => array(
        'phptype' => '%db.type%',
        'username' => '%db.username%',
        'password' => '%db.password%',
        'hostspec' => '%db.host%',
        'port' => %db.port%
    ),
    %options%
);

$dbarray = array($db);


require_once('MDB2/Schema.php');
$schema_path = '../../MDB2_Schema/tests/';

// OPENADS ONLY
$schema_path = MAX_PATH.'/lib/pear/MDB2_Schema/tests/';

function pe($e) {
    die($e->getMessage().' '.$e->getUserInfo());
}
function databaseExists($db, $database_name)
{
    $result = $db->manager->listDatabases();
    if (PEAR::isError($result)) {
        return false;
    }
    return in_array(strtolower($database_name), array_map('strtolower', $result));
}
PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'pe');
foreach ($dbarray as $dbtype) {
    $schema =& MDB2_Schema::factory($dbtype['dsn'], $dbtype['options']);
    if (databaseExists($schema->db, 'driver_test'))
    {
        $schema->db->manager->dropDatabase('driver_test');
    }
    $schema->updateDatabase(
        $schema_path.'driver_test.schema',
        false,
        array('create' => '1', 'name' => 'driver_test')
    );
    $schema->updateDatabase(
        $schema_path.'lob_test.schema',
        false,
        array('create' => '1', 'name' => 'driver_test')
    );
}
PEAR::popErrorHandling();
?>
