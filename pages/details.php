<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
?>
<div><br><b>Erste Schritte zum Aufbau eines Terminkalenders</b></div>
<ol class="kal_olul">
    <li>Zunächst wird in einem neuen Artikel ein Block mit dem <b>Modul</b>
        <code>Termine verwalten</code> angelegt. Mit diesem werden u. A.
        Termine eingetragen. Der Artikel kann <i>offline</i> bleiben, da keine
        Ausgaben im Frontend gemacht werden.</li>
    <li>In weiteren Artikeln wird je ein Block mit dem <b>Modul</b>
        <code>Termine anzeigen</code> angelegt. Mit diesem lassen sich Termine
        in den unterschiedlichen Menüs im Frontend anzeigen. Z. B. ein Artikel
        zur Anzeige einer Liste der aktuell anstehenden Termine und ein Artikel
        zur Auswahl von Terminen, startend mit dem aktuellen Monatsmenü oder
        der Terminübersicht mit Suchfunktion.</li>
    <li>Mithilfe der <code>Konfiguration</code> lässt sich die Darstellung der
        Menüs gestalten:
        <div>Anpassung der <code class="kal_code2">Menüfarben</code> an das
        Site-Design:</div>
        <div class="kal_indent">Es wird eine Grundfarbe ausgewählt, die auch
           als Schriftfarbe dient. Ausgehend von dieser werden hellere ähnliche
           Farbtöne sowie eine Komplementärfarbe verwendet. - Damit die
           Farbunterschiede nicht verschwimmen, sollten die RGB-Parameter der
           Grundfarbe ≤ 150 sein.</div>
        <div>Anpassung der <code class="kal_code2">Stundenleiste</code> im
        Monats-/Wochen-/Tagesblatt (relevant nur für Desktop-Displays):</div>
        <div class="kal_indent">Gesamtbreite in Anzahl Pixel<br>
           darzustellender Zeitbereich ggf. eingeschränkt auf z.B.
           <tt>9:00 - 22:00</tt> Uhr.</div>
        <div>Definition von <code class="kal_code2">Terminkategorien</code>:</div>
        <div class="kal_indent">Sie dienen dazu, Termine inhaltlich und
           organisatorisch zu klassifizieren, siehe unten. Die Kategorie wird
           als Parameter jedes Termins mitgeführt. Kategorien können jederzeit
           umbenannt werden, da nicht ihre Bezeichnungen, sondern ihre Ids
           (= Nummer in der Konfiguration) in der Termintabelle abgelegt werden.</div>
        </li>
</ol>
<div><br><b>Zuweisung von Terminkategorien an Redakteure</b></div>
<div class="kal_indent">Damit ein Redakteur den Terminkalender nutzen kann, muss
ihm <code>für jede Terminkategorie eine Benutzerrolle</code> ('Terminkategorie 1',
'Terminkategorie 2', ...) zugewiesen werden. In diesen Rollen ist das <code>Recht
auf Nutzung der Kategorie</code> schon ausgewiesen (Markierung unter 'Extras'
in den Rollenparametern). Die Registrierung der Rollen erfolgt in der Datei
<tt>boot.php</tt>. Darüber hinaus braucht der Redakteur den Zugriff auf die
beiden Module des AddOns.<br>
Ein Redakteur kann jede der ihm zugewiesenen Kategorien einzeln als unabhängigen
Terminkalender anbieten. Alternativ kann er auch alle seine Kategorien zu einem
gemeinsamen Terminkalender zusammenfassen.<br>
Andererseits kann eine einzelne Terminkategorie auch von mehreren Redakteuren
verwaltet werden.</div>

<div><br><b>Sonstige Hinweise</b></div>
<ul class="kal_olul">
    <li>Um einen einzelnen aus einer Folge von wöchentlich/monatlich
        wiederkehrenden Terminen zu modifizieren, kann ein zusätzlicher Termin
        als <code class="kal_code2">Ersatztermin</code> eingetragen werden.
        Dieser muss mit dem zu ersetzenden Termin in den wesentlichen Parametern
        übereinstimmen (Bezeichnung, Datum, Kategorie) und darf selbst kein
        wiederkehrender Termin sein.</li>
    <li>Die Icons in den Kalendermenüs sind dem <code class="kal_code2">
        Awesome-Font</code> entnommen. Die zugehörige Stylesheet-Datei muss
        zusätzlich im Seiten-Template und dort im HTML-Header eingebunden werden
        (Pfad: <tt>/assets/addons/kal_termine/fontawesome/css/all.css</tt>).</li>
    <li>In der Terminliste (und nur dort!) können <code class="kal_code2">Termine
        entsprechend ihrer Kategorien farblich markiert</code> werden, um eine
        optische Zuordnung zu Kategorien zu unterstützen. Die zugehörige
        Konfigurierung wird unter dem Reiter &quot;Terminliste&quot;
        vorgenommen.</li>
</ul>
<br>