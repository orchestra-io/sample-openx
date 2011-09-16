<?php
    // $Id: spl_examples.php 7321 2007-06-05 09:08:01Z andrew.hill@openads.org $

    class IteratorImplementation implements Iterator {
        function current() { }
        function next() { }
        function key() { }
        function valid() { }
        function rewind() { }
    }

    class IteratorAggregateImplementation implements IteratorAggregate {
        function getIterator() { }
    }
?>