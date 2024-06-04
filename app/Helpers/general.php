<?php

function greeting()
{
    date_default_timezone_set('Asia/Jakarta'); // Set the timezone to Jakarta

    $currentHour = date('G'); // Get the current hour (24-hour format)

    if ($currentHour >= 0 && $currentHour < 12) {
        $greeting = "Selamat pagi";
    } elseif ($currentHour >= 12 && $currentHour < 18) {
        $greeting = "Selamat siang";
    } else {
        $greeting = "Selamat malam";
    }

    echo $greeting;
}
