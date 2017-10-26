<?php

function error_404()
{
    header('Location: '.SITE_ADDR.'/public/page/404');
    exit();
}

function redirect($to)
{
    header('Location: '.$to);
    exit();
}