<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version September 2021
*/
$stx='style="padding-left:30px;"';
$sty='style="color:rgb(0,120,0); background-color:rgb(242,249,244);"';
#
# --- Details
$string='
<div><br/><b>Erste Schritte zum Aufbau eines Terminkalenders</b></div>
<ol '.$stx.'>
    <li>In einer ersten Testphase sollte in einem neuen Artikel
        ein Block mit dem <b>Modul</b> <code>Termine verwalten</code>
        angelegt werden. Mit diesem werden Testtermine eingetragen.
        Der Artikel kann <i>offline</i> bleiben, da der Modul keine
        Ausgaben im Frontend macht.</li>
    <li>In einem weiteren Artikel sollte ein Block mit dem
        <b>Modul</b> <code>Termine anzeigen</code> angelegt werden.
        Mit diesem lassen sich die Testtermine in den
        unterschiedlichen Menüs im Frontend anzeigen. Wenn die
        Testtermine in der näheren Zukunft liegen, sollten sie
        von den Menüs &quot;eingefangen&quot; werden. Andernfalls
        ist das eine gute Gelegenheit sie zu korrigieren, d.h. ihr
        Datum geeignet zu ändern (siehe voriger Punkt).</li>
    <li>Daran könnte sich mithilfe der <code>Konfiguration</code>
        der konkrete Aufbau der Terminverwaltung anschließen:
        <ul '.$stx.'>
            <li>Anpassung der <code '.$sty.'>Menüfarben</code>
                an das Site-Design:<br/>
                Es wird eine Grundfarbe ausgewählt. Ausgehend von
                dieser werden hellere ähnliche Farbtöne sowie
                eine Komplementärfarbe verwendet. - Die RGB-Werte
                der Grundfarbe müssen &le;105 sein, damit die
                abgeleiteten Farben fehlerfrei berechnet werden
                können.</li>
            <li>Anpassung der <code '.$sty.'>Stundenleiste</code>
                im Monats- bzw. Wochen- oder Tagesblatt
                (relevant nur für Desktop-Displays):<br/>
                Die Gesamtbreite wird in Pixel angegeben.<br/>
                Der darzustellende Zeitbereich kann auf weniger
                als 24 Stunden eingeschränkt werden, z.B. auf
                <tt>9:00 - 22:00</tt> Uhr.</li>
            <li>Definition von <code '.$sty.'>Terminkategorien</code>:<br/>
                Jede Kategorie kann jederzeit nachträglich umbenannt
                werden, da nicht ihre Bezeichnung, sondern ihre Id
                (= Nummer in der Konfiguration) in der Termintabelle
                abgelegt wird. (Die jeweils letzten) Kategorien können
                in der Konfiguration zwar entfernt werden, aber etwaige
                zugehörige Termine bleiben erhalten und müssen ggf.
                einzeln gelöscht werden.</li>
        </ul></li>
</ol>
<div><br/><b>Zuweisung von Terminkategorien an Redakteure</b></div>
<div '.$stx.'>Damit ein Redakteur den Terminkalender nutzen kann,
muss ihm <code>für jede Kategorie eine Benutzerrolle</code> zugewiesen
werden, in der das <code>Recht auf Nutzung der Kategorie</code>
ausgewiesen ist. Außerdem braucht er den Zugriff auf die beiden
Module des AddOns.<br/>
Die Rechte zur Verwaltung und Ausgabe von Terminen der konfigurierten
Kategorien werden automatisch (im Abschnitt \'Extras\' jeder Rolle)
definiert. Zudem wird je Kategorie automatisch eine Rolle
angelegt, in der das Recht zu ihrer Nutzung bereits markiert ist.
Diese Aktionen erfolgen in der Datei <tt>boot.php</tt> des AddOns.<br/> 
Auf diese Weise können einzelne Kategorien oder Gruppen von
Kategorien als unabhängige Terminkalender genutzt werden.
Andererseits kann eine Terminkategorie auch von mehreren Redakteuren
verwaltet werden.</div>

<div><br/><b>Sonstige Hinweise</b></div>
<ul '.$stx.'>
    <li>Um einen einzelnen aus einer Reihe von wöchentlich
        wiederkehrenden Terminen zu modifizieren, muss ein
        zusätzlicher Termin als <code '.$sty.'>Ersatztermin</code>
        eingetragen werden. Dieser muss mit dem zu ersetzenden
        Termin in den wesentlichen Parametern übereinstimmen
        (Bezeichnung, Datum, Kategorie) und darf selbst kein
        Wiederholungstermin und nicht mehrtägig sein.</li>
    <li>In der Terminliste (und nur dort!) können <code '.$sty.'>
        Kategorien farbig markiert</code> werden, die eine optische
        Zuordnung zur Kategorie unterstützen. Die Farben muss der
        Anwender als nummeriertes Array (Nummerierung ab 1)<br/>
        <tt '.$stx.'>$GLOBALS[\''.PACKAGE.'\'][\'terminliste\']</tt><br/>
        bereitstellen, z.B. im Ausgabeteil des Moduls \'Termine
        anzeigen\' oder im Seiten-Template.</li>
</ul>
<br/>
';
echo $string;
?>
