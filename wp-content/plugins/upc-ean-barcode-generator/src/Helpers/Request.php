<?php

namespace UkrSolution\UpcEanGenerator\Helpers;

class Request
{
    public static function checkNonce($nonceType)
    {
        if (!wp_verify_nonce( $_POST['nonce'], $nonceType)) {
            wp_send_json_error(__('Check failed', "upc-ean-generator"));
            wp_die();
        }
    }
}
