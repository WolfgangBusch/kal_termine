<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2022
 */
echo '<div><br>Hier werden nur <u>reine Spieldaten</u> benutzt, die nicht in der
Tabelle <tt>'.TAB_NAME.'</tt> enthalten sind. Sie sind in insgesamt '.
count(kal_termine_tabelle::kal_get_spielkategorien()).'
Kategorien gegliedert und werden hier gemeinsam angezeigt. Sie gruppieren sich um das
aktuelle Datum herum und wiederholen sich wöchentlich.</div>
<div><br>Einige der hier angezeigten Menüs können (anstelle des Monatsmenüs) als
Startmenü gewählt werden.</div>
<div align="center"><br>
'.kal_termine_menues::kal_spielmenue().'
<br>&nbsp;</div>';
?>
