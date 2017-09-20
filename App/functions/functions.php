<?php

function error_404()
{
    header('Location: http://localhost/MVC/public/page/404');
    exit();
}

function redirect($to)
{
    header('Location: '.$to);
    exit();
}