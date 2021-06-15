<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2021
*/
#
# --- Beschreibung
$string='
<div><br/>Die Terminverwaltung beinhaltet diese Funktionen:</div>
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
    <li>Liste der Termine eines Kalenderjahres mit Filterfunktionen</li>
    <li>tabellarische Darstellung der Daten eines Termins</li>
    <li>tabellarische Liste der Termine eines Zeitabschnitts</li>
</ul>

<div>Alle Kalendermenüs gestatten das Blättern zum vorherigen und nachfolgenden
Zeitabschnitt, das Monatsmenü zusätzlich zum gleichen Monat im Vor- oder Folgejahr.
Zudem sind sie untereinander verlinkt, sodass man von einem zum anderen wechseln
kann. Die Kalenderfunktionen liefern in Form von tooltips gesetzliche und
christliche Feiertage.</div>
';
echo $string;
?>
