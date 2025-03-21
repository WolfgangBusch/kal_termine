<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
$men=kal_termine::kal_post_in(kal_termine::KAL_MENUE,'int');
#
# --- Erlaeuterung nur im Falle des Monatsmenues
if($men<=1):
  $table=cms_interface::name_termin_tabelle();
  $nzkat=count(kal_termine::kal_get_spielkategorien());
  echo '
<div><br>Hier werden nur <b>reine Spieldaten</b> benutzt, die nicht in der Tabelle
<tt>'.$table.'</tt> enthalten sind, gegliedert in insgesamt '.$nzkat.' Kategorien.
Die Termine aller Kategorien werden gemeinsam angezeigt.</div>

<div><br><b>Nur die Termine der aktuellen Woche sind unterschiedlich definiert.</b>
Um nennenswert viele Spieldaten zu erhalten, wiederholen sie sich wöchentlich,
allerdings nur über wenige Wochen vor und nach dem aktuellen Datum. Dabei sind
sie nicht als &quot;ein Termin über mehrere Wochen&quot; definiert, sondern als
einmalige ein- oder zweitägige Termine.</div>

<div><br>Hier wird als Startmenü das <b>Monatsmenü</b> angezeigt. <b>Klicke</b>
hinein, um auch die anderen Menüs kennenzulernen!</div>

<br>';
  endif;
#
# --- Spielmenues
echo kal_termine_menues::kal_spielmenue().'
<div>&nbsp;</div>';
?>
