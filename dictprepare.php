<?php

$strings = file('./dicts/zdf-win.txt');

$stringsDividedByLen = [];
foreach ($strings as $string) {
    $string = trim($string);
    $stringLen = mb_strlen($string, 'UTF-8');

    $stringsDividedByLen[$stringLen][] = $string;
}

foreach ($stringsDividedByLen as $stringLen => $strings) {
    $filename = sprintf('./dicts/dict_%s.txt', $stringLen);
    sort($strings);
    file_put_contents($filename, implode("\r\n", $strings));
}