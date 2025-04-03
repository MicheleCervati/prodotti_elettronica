<?php


function isActive(string $pageName){

    return (($_SERVER['PHP_SELF'] === '/Uda5f/test2/'.$pageName.'.php') ? 'active' : '');
}

function logError(Exception $e){
    echo "error, try again";
    error_log($e->getMessage().'--'.date('Y-m-d H:i:s') ."\n", 3, 'log/dberror.log');
}
