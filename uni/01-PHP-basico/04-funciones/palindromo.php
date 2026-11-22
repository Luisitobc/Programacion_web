<?php

function esPalindromo($palabra) {
    $invertida = strrev($palabra);

    if ($palabra == $invertida) {
        return true;
    } else {
        return false;
    }
}

$palabras =["radar", "reconocer", "mundo"];


foreach ($palabras as $palabra) {
    if (esPalindromo($palabra)) {
        echo "$palabra es palíndromo";
    } else {
        echo "$palabra no es palíndromo";
    }
}

?>
