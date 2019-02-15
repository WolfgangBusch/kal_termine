<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
echo '<div>Hier werden nur <u>reine Spieldaten</u> benutzt, die nicht in der
Tabelle <tt>'.TAB_NAME.'</tt> enthalten sind.<br/>Sie gruppieren sich um das
aktuelle Datum herum und wiederholen sich w&ouml;chentlich.</div><br/>
<div align="center">
'.kal_termine_menues::kal_menue('',SPIELTERM,'').'
<br/>&nbsp;</div>';
?>
