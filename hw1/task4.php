<?php

$q = $_REQUEST["age"];

if ( $q >= 1 && $q <= 17 ) {
    echo "Вам ещё рано работать!";
} elseif ( $q > 17 && $q < 65 ) {
    echo "Вам ещё работать и работать!";
}  elseif ( $q >= 65 ) {
    echo "Вам пора на пенсию!";
}