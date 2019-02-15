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
    <li>Eintragung eines Termins samt zugehörigen Daten in die Tabelle</li>
    <li>Löschung eines Termins in der Tabelle</li>
    <li>Aktualisierung der Daten eines Termins</li>
    <li>Kopieren eines Termins auf den Folgetag oder als wöchentliche Wiederholung</li>
</ul>

<div>Als Terminmenüs stehen die folgenden Darstellungen zur Wahl:</div>
<ul>
    <li>Monatsmenü inkl. Darstellung der wesentlichen christlichen Feiertage;
        alle Tage, an denen Termine eingetragen sind, werden durch Schraffur
        gekennzeichnet</li>
    <li>Monatsblatt mit einer halbgrafischen Darstellung der Termine an den
        zugehörigen Tagen</li>
    <li>Wochenblatt mit einer halbgrafischen Darstellung der Termine an den
        zugehörigen Tagen</li>
    <li>Tagesblatt mit einer halbgrafischen Darstellung der Termine an diesem Tage</li>
    <li>Liste der Termine eines Zeitabschnitts (Monat/Woche/Tag) mit Filterfunktionen</li>
    <li>tabellarische Darstellung der Daten eines Termins</li>
</ul>

<div>Alle Menüs sind untereinander verlinkt, sodass man von einem zum
anderen wechseln kann. Das Monatsmenü gestattet das Blättern zum Vor- oder
Folgemonat sowie zum gleichen Monat im Vor- oder Folgejahr.</div>
';
echo utf8_encode($string);
?>
