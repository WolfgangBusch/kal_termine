<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
$intro=file_get_contents(__DIR__.'/README.md');
$start=strpos($intro,'<div>');
echo substr($intro,$start);
?>