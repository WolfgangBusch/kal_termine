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
    <li>Monatsmen� inkl. Darstellung der wesentlichen christlichen Feiertage;
        alle Tage, an denen Termine eingetragen sind, werden durch Schraffur
        gekennzeichnet</li>
    <li>Monatsblatt mit einer halbgrafischen Darstellung der Termine an den
        zugeh�rigen Tagen</li>
    <li>Wochenblatt mit einer halbgrafischen Darstellung der Termine an den
        zugeh�rigen Tagen</li>
    <li>Tagesblatt mit einer halbgrafischen Darstellung der Termine an diesem Tage</li>
    <li>Liste der Termine eines Zeitabschnitts (Monat/Woche/Tag) mit Filterfunktionen</li>
    <li>tabellarische Darstellung der Daten eines Termins</li>
</ul>

<div>Alle Men�s sind untereinander verlinkt, sodass man von einem zum
anderen wechseln kann. Das Monatsmen� gestattet das Bl�ttern zum Vor- oder
Folgemonat sowie zum gleichen Monat im Vor- oder Folgejahr.</div>
';
echo utf8_encode($string);
?>
