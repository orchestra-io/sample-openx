<?php
    // $Id: parse_error_test.php 7321 2007-06-05 09:08:01Z andrew.hill@openads.org $
    
    require_once('../unit_tester.php');
    require_once('../reporter.php');

    $test = &new TestSuite('This should fail');
    $test->addTestFile('test_with_parse_error.php');
    $test->run(new HtmlReporter());
?>