<?php

function fn_VerificarTriangulo( $a, $b, $c ) {
    if( $a > abs($b - $c) and $a < ($b + $c) ) {
        $ok1 = TRUE;
    } else {
        $ok1 = FALSE;
    }
    if( $b > abs($a - $c) and $b < ($a + $c) ) {
        $ok2 = TRUE;
    } else {
        $ok2 = FALSE;
    }
    if( $c > abs($a - $b) and $c < ($a + $b) ) {
        $ok3 = TRUE;
    } else {
        $ok3 = FALSE;
    }
    if( $ok1 and $ok2 and $ok3 ) {
        return TRUE;
    } else {
        Return FALSE;
    }
}

function fn_CalcularAreaTriangulo( $a, $b, $c ) {
    $p = ( $a + $b + $c ) / 2;
    $area = sqrt( $p * ($p - $a) * ($p - $b) * ($p - $c) );
    return $area;
}

?>