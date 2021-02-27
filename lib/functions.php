<?php

function redirect($address) {
    header(
        "Location: " . $address
    );
    exit;
}