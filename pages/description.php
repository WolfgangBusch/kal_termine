<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
#
# --- Beschreibung
$string='
<div>Die Terminverwaltung beinhaltet diese Funktionen:</div>
<ul>
    <li>Eintragung eines Termins samt zugeh�rigen Daten in die Tabelle</li>
    <li>L�schung eines Termins in der Tabelle</li>
    <li>Aktualisierung der Daten eines Termins</li>
    <li>Kopieren eines Termins auf den Folgetag oder als w�chentliche Wiederholung</li>
</ul>

<div>Als Terminmen�s stehen die folgenden Darstellungen zur Wahl:</div>
<ul>
    <li>Monatsmen�, Tage mit eingetragenen Termine sind schraffiert</li>
    <li>Monats-, Wochen-, Tagesblatt mit halbgrafischer Darstellung aller Termine</li>
    <li>Liste der Termine eines Zeitabschnitts (Monat/Woche/Tag) mit Filterfunktionen</li>
    <li>tabellarische Darstellung der Daten eines Termins</li>
</ul>

<div>Alle Men�s sind untereinander verlinkt, sodass man von einem zum
anderen wechseln kann. Das Monatsmen� gestattet das Bl�ttern zum Vor- oder
Folgemonat sowie zum gleichen Monat im Vor- oder Folgejahr. Gesetzliche und
christliche Feiertage sind entsprechend markiert.</div>
';
echo utf8_encode($string);
?>
