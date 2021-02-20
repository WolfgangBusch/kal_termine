<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2021
*/
echo '<div>Hier werden nur <u>reine Spieldaten</u> benutzt, die nicht in der
Tabelle <tt>'.TAB_NAME.'</tt> enthalten sind.<br/>Sie gruppieren sich um das
aktuelle Datum herum und wiederholen sich wöchentlich.<br/>
Einige der hier angezeigten Menüs können (anstelle des Monatsmenüs) als Startmenü
gewählt werden.<br/>&nbsp;</div>
<div align="center">
'.kal_termine_menues::kal_menue(SPIEL_KATID,1).'
<br/>&nbsp;</div>';
?>
