<?php

if (! function_exists('current_customer')) {
    function current_customer(): ?array
    {
        return session('customer');
    }
}

if (! function_exists('current_owner')) {
    function current_owner(): ?array
    {
        return session('owner');
    }
}
