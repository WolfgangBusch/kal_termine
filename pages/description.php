<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2020
*/
#
# --- Beschreibung
$string='
<div>Die Terminverwaltung beinhaltet diese Funktionen:</div>
<ul>
    <li>Eintragung eines Termins samt zugehörigen Daten in die Datenbanktabelle</li>
    <li>Löschung eines Termins</li>
    <li>Aktualisierung der Daten eines Termins</li>
    <li>Suche eines Termins mittels Kalendermenüs</li>
</ul>

<div>Als Kalendermenüs stehen die folgenden Darstellungen zur Wahl:</div>
<ul>
    <li>Monatsmenü, Tage mit eingetragenen Terminen sind schraffiert</li>
    <li>Monats-, Wochen-, Tagesblatt mit halbgrafischer Darstellung aller Termine</li>
    <li>Liste der Termine eines Zeitabschnitts (Monat/Woche/Tag) mit Filterfunktionen</li>
    <li>tabellarische Darstellung der Daten eines Termins</li>
</ul>

<div>Alle Menüs sind untereinander verlinkt, sodass man von einem zum
anderen wechseln kann. Das Monatsmenü gestattet das Blättern zum Vor- oder
Folgemonat sowie zum gleichen Monat im Vor- oder Folgejahr. Gesetzliche und
christliche Feiertage sind entsprechend markiert.</div>
';
echo $string;
?>
