<?php

if ( ! function_exists('upper') ) {
    function upper($value)
    {
        echo Text\Format::upperText($value);
    }
}

if (!function_exists('lower')) {
    function lower($value)
    {
        echo Text\Format::lowerText($value);
    }
}
