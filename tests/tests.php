<?php
require_once('../axenia/functions.php');
//test2
//print_r(isInEnum("343434,434234,1", 434234), false);
//print_r(isInEnum("343434,434234,1", "434234"), false);
//print_r(false === isInEnum("343434,434234,1", "4342324"));

if(isInEnum("343434,434234,1", "434234")){
    print_r(true);
}