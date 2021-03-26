<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2021
*/
$sty='style="color:rgb(0,120,0); background-color:rgb(242,249,244);"';
#
# --- Details
$string='
<div><br/>Ein Termin ist normalerweise ein Zeitabschnitt an einem
einzelnen Tag. Er kann aber auch als Zeitbereich vereinbart werden,
der sich über mehrere Tage erstreckt, oder als Zeitabschnitt eines
Tages, der sich wöchentlich wiederholt.</div>

<div><br/>
Um einen von wöchentlich wiederkehrenden Terminen zu ergänzen
oder zu verändern, muss ein zusätzlicher Termin als Ersatztermin
eingetragen werden. Dieser muss mit dem zu ersetzenden Termin in
den wesentlichen Parametern übereinstimmen (Bezeichnung, Datum,
Kategorie) und darf selbst kein Wiederholungstermin und kein
Folgetermin sein.</div>

<div><br/><b>Erste Schritte zum Aufbau eines Terminkalenders</b></div>
<ol>
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
        <ul>
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
            <li>Definition von <code '.$sty.'>Terminkategorien</code>,
                soweit das nötig ist:<br/>
                Alle Terminlisten und -menüs können auf je eine
                Kategorie eingeschränkt werden oder alternativ
                alle Kategorien umfassen.<br/>
                Jede Kategorie kann nachträglich umbenannt werden, da
                nicht ihre Bezeichnung, sondern ihre Id (= Nr. in der
                Konfiguration) in der Termintabelle abgelegt wird. -
                Ihre Reihenfolge in den Menüs zur Auswahl einer
                Kategorie entspricht der Reihenfolge in der
                Konfiguration.</li>
        </ul></li>
    <li>Ein Redakteur kann <code>auf eine einzelne Terminkategorie
        eingeschränkt</code> werden. Dazu werden ihm Kopien der beiden
        Module zur Verfügung gestellt, in deren Input-Teilen jeweils
        die entsprechende Kategorie-Id gesetzt ist (je 1 Zeile ist
        anzupassen).<br/>
        Beispiel - Einschränkung auf Kategorie 5: &nbsp;
        <code '.$sty.'>$katid=5;</code> &nbsp; anstatt &nbsp;
        <code '.$sty.'>$katid=0;</code> (alle Kategorien).</li>
</ol>
';
echo $string;
?>
