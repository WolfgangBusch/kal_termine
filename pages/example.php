<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
$table=rex::getTablePrefix().kal_termine::this_addon;
echo '<div><br>Hier werden nur <b>reine Spieldaten</b> benutzt, die nicht in der
Tabelle <tt>'.$table.'</tt> enthalten sind, gegliedert in insgesamt
'.count(kal_termine_tabelle::kal_get_spielkategorien()).' Kategorien, und werden
hier gemeinsam angezeigt.</div>

<div><br>Es handelt sich um <b>Termine in der aktuellen Woche</b>. Um nennenswert
viele Spieldaten zu erhalten, wiederholen sie sich wöchentlich über wenige Wochen
vor und nach dem aktuellen Datum. Trotzdem sind sie nicht vom Typ \'genau ein
Wochentag über mehrere Wochen\', sondern unabhängige Termine an je einem Tag
oder über 2 Tage.</div>

<div><br>Hier wird als Startmenü das <b>Monatsmenü</b> angezeigt. <b>Klicke</b>
hinein, um auch die anderen Menüs kennenzulernen!</div>

<div align="center"><br>
'.kal_termine_menues::kal_spielmenue().'
<br>&nbsp;</div>';
?>
