<?php
// Helper functions


function sanizite_user_input($input)
{
    return $input;
}

function createlog($data, $filepath, $logtype = "ERROR")
{
    $date = date('[Y-m-d H:i:s A]');
    // file handler
    try {
        $fh = fopen($filepath, 'a+');
        $towrite = $date . ' [' . $logtype . '] ' . $data . "\n";
        fwrite($fh, $towrite);
        fclose($fh);
    } catch (Exception $e) {
        exit();
    }
}
// createlog('ERROR!!!!', '/sdfsdf/sdf/data.log');
