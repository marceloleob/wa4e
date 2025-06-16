<?php

function isUrlAccessible($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 400);
}

function validatePositions($post, $total) :string
{
    $count = 1;

    for ($i = 1; $i <= $total; $i++) {

        if (!isset($post['year' . $i]) || !isset($post['desc' . $i])) {
            continue;
        }

        if ($count > 9) {
            return "Maximum of 9 position entries exceeded!";
        }

        if ( strlen($post['year' . $i]) < 4 || strlen($post['desc' . $i]) == 0 ) {
            return "All positions are required";
        }

        if ( !is_numeric($post['year' . $i]) ) {
            return "Position year must be numeric";
        }

        $count++;
    }

    return '';
}