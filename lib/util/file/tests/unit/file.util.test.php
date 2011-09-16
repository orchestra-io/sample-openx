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
$Id: file.util.test.php 30820 2009-01-13 19:02:17Z andrew.hill $
*/

require_once MAX_PATH . '/lib/util/file/file.php';

/**
 * A class for testing the filesystem utilities. It requires the permission
 * to write to the filesystem to run properly!
 *
 * @package    Util
 * @subpackage File
 * @author     Andrzej Swedrzynski <andrzej.swedrzynski@openx.org>
 */
class Test_OA_Dal extends UnitTestCase
{
    function testUtil_File_remove()
    {
        $testDirectoryPath = MAX_PATH . '/var/tests';
        mkdir($testDirectoryPath);
        mkdir($testDirectoryPath . '/subdir1');
        mkdir($testDirectoryPath . '/subdir2');
        touch($testDirectoryPath . '/normalfile.txt');
        touch($testDirectoryPath . '/.hiddenfile.txt');
        touch($testDirectoryPath . '/subdir1/normalfile.txt');
        touch($testDirectoryPath . '/subdir1/.hiddenfile.txt');

        $this->assertTrue(Util_File_remove($testDirectoryPath));

        $this->assertFalse(file_exists($testDirectoryPath));
    }
}
?>