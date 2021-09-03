# kal_termine
<h4>Version 3.4</h4>
<ul>
    <li>Redakteuren kann jetzt Verwaltung und Ausgabe von
        Terminen einer oder mehrerer Kategorien in Form von
        Benutzerrollen zugewiesen werden. Damit ist die
        Organisation mehrerer unabhängiger Terminkalender
        in einer Tabelle mit zwei Modulen möglich.</li> 
    <li>Der Modul zur Verwaltung der Termine ist vollständig
        umgebaut. Die Aktionen erfolgen jetzt im Output-Teil
        des Moduls. Startmenü ist dabei immer das Monatsmenü
        des aktuellen Monats. Zum Anlegen eines neuen Termins
        muss jetzt das Tagesblatt des entsprechenden Tages 
        aufgesucht werden; dessen Datum wird ins Formular
        übernommen.</li>
    <li>Die Daten eines Termins können jetzt HTML-Texte
        enthalten, u.a. also auch Links.</li>
    <li>Neu ist die Möglichkeit, einen Termin zu kopieren.
        Das ist u.a. dann interessant, wenn im Falle
        wöchentlich wiederkehrender Termine an einem
        einzelnen Tag eine Ergänzung angebracht werden
        soll ('Ersatztermin').</li>
    <li>Termine werden jetzt nicht nur nach Datum sortiert,
        sondern auch nach Uhrzeit.</li>
    <li>Die Tagesangaben in der linken Spalte des Monats-
        und des Wochensblatts sind jetzt als Links auf das
        zugehörige Tagesblatt ausgebildet.</li>
    <li>Im Termin-Eingabeformular ist die Wahl der
        Terminkategorie in die erste Zeile gerückt.</li>
    <li>Design und Stylesheets von Suchmenü und Terminliste
        sind leicht verändert.</li>
</ul>
<h4>Version 3.3.1</h4>
<ul>
    <li>Die Version enthält eine kleine Verbesserung des
        responsiven Designs für iPhones.</li>
    <li>Im Suchmenü sind die abgelaufenen Termine jetzt
        standardmäßig ausgeblendet.</li>
    <li>Das Design der Terminliste wird jetzt über CSS-Klassen
        gesteuert. Die Defaults können über eigene Stylesheets
        individuell überschrieben und angepasst werden. Die
        Termine sind in der Liste zusätzlich entsprechend ihrer
        Kategorie farblich markierbar; die zugehörige
        Farbpalette kann über ein $GLOBALS-Array eingebracht
        werden.</li>        
</ul>
<h4>Version 3.3</h4>
<ul>
    <li>Die Menüs sind jetzt systematischer strukturiert und
        verlinkt. Außerdem gibt es jetzt nur noch ein Suchmenü
        über jeweils ein Kalenderjahr.</li>
    <li>Die Menüs sind jetzt mittels responsiver CSS-Codes am
        Smartphone genauso gut lesbar wie am Desktop.</li>
    <li>Die Terminkategorien können jetzt vollständig getrennt
        benutzt und verwaltet werden.</li>
</ul>
<h4>Version 3.2.1</h4>
<ul>
    <li>In den Monats-/Wochen-/Tagesblättern im Frontend werden jetzt
        wieder alle Folgetermine von Terminen über mehrere Tage
        angezeigt.</li>
    <li>Wenn der Modul 'Termine anzeigen' mit dem Zeitraumfiltermenü
        gestartet wird, ist jetzt wieder der Übergang in andere Menüs
        möglich.</li>
    <li>Die Darstellung des Terminblatts ist erweitert.</li>
</ul>
<h4>Version 3.2</h4>
<ul>
    <li>Bei wöchentlich wiederkehrenden Terminen kann jetzt für
        jeden zugehörigen Wiederholungstermin ein Alternativtermin
        eingegeben werden. Dieser ersetzt an seinem Datum den
        Wiederholungstermin in allen Menüs und Terminlisten.
        Voraussetzung ist, dass der Alternativtermin in Datum,
        Bezeichnung und Kategorie mit dem zu ersetzenden Termin
        übereinstimmt und selbst kein Wiederholungstermin und
        kein Folgetermin ist.</li>
    <li>Die Auswahlmenüs für die Terminkategorien enthalten
        die Kategorien jetzt in der Reihenfolge, wie sie im
        Konfigurationsformular definiert sind.</li>
    <li>Die Dokumentation ist etwas erweitert, u.a. um
        'erste Schritte'.</li>
    <li>Die Frontendausgabe beim Modul 'Termine anzeigen' liefert
        jetzt das Zeitraumfiltermenü mit korrektem Zeitraum.</li>
    <li>Die vor Version 3.1 angebotenen Module 'Start-Kalendermenü'
        und 'Standard-Terminliste' werden jetzt nicht mehr mit
        installiert. Sie sind seit Version 3.1 obsolet
        (ersetzbar durch Modul 'Termine anzeigen').</li>
</ul>
<h4>Version 3.1.2</h4>
<ul>
    <li>Ein kleines Detail bei der Ausgabe einer Terminliste in einem
        gegebenen Datumsbereich ist geändert. Ein Termin über mehrere
        Tage ('Folgetermin'), der vorher beginnt, aber noch teilweise
        in den Datumsbereich hineinragt, wird jetzt vollständig mit
        aufgelistet. Bisher wurde der Termin in Einzeltermine aufgelöst
        und nur die wirklich im Datumsbereich liegenden Tage aufgeführt.</li>
    <li>Beim Anlegen eines Blocks mit dem Modul 'Termine verwalten'
        konnte es auf einigen Systemen zu einem Syntaxfehler kommen,
        weil die Variable REX_SLICE_ID noch nicht definiert ist. Der
        Fehler ist behoben.</li>
</ul>
<h4>Version 3.1.1</h4>
<ul>
    <li>Eine function im Eingabeteil des Moduls zur Ausgabe eines Kalendermenüs
        oder einer Terminliste wurde korrigiert. Jetzt kann der Startzeitpunkt
        wirklich der jeweils aktuelle Termin sein.</li>
</ul>
<h4>Version 3.1</h4>
<ul>
    <li>Es wird ein zusätzliches Such- und Filtermenü für Termine in einem
        beliebigen Zeitraum angeboten. Es ist mit allen bisherigen Menüs
        verlinkt.</li>
    <li>Die Ausgabe einer Standard-Terminliste lässt sich organisatorisch
        wie die Ausgabe eines Kalendermenüs behandeln. Die entsprechenden
        Funktionen sind jetzt zusammen in einem neuen Modul integriert,
        sodass die bisherigen Module für das Frontend obsolet sind.
        Sobald Letztere durch den neuen Modul ersetzt sind, können sie
        gelöscht werden.</li>
    <li>Die per Modul ausgebbaren Kalendermenüs sind jetzt eingeschränkt
        auf Monatsmenü, Monats-, Wochen-, Tagesblatt sowie auf das neue
        Zeitraumfiltermenü und zusätzlich auf die einfache Terminliste.</li>
    <li>Der Modul zur Terminverwaltung gestattet in der installierten
        Form den Zugriff auf die Termine aller Kategorien ($katid=0 im
        Input-Teil). Er lässt sich aber leicht kopieren und die Kopie mit
        der Einschränkung auf eine spezifische Terminkategorie versehen
        ($katid=1 oder $katid=2 oder ...). Auf diese Weise können Termine
        einzelner Kategorien von unterschiedlichen Redakteuren verwaltet
        werden.</li>
</ul>
    
<h4>Version 3.0</h4>
<ul>
    <li>Ein Termin wird nicht mehr ausschließlich als eintägig aufgefasst.
        Er kann sich jetzt auch über mehrere aufeinander folgende Tage
        erstrecken oder eine Folge von sich wöchentlich wiederholenden
        eintägigen Veranstaltungen darstellen. Dementsprechend hat die
        Termintabelle 'rex_kal_termine' nun die zusätzlichen Spalten 'tage'
        und 'wochen'. Erstere enthält die Anzahl der aufeinander folgenden
        Tage der Veranstaltung, Letztere die Anzahl der wöchentlichen
        Wiederholungen.<br/>
        Mit dem Upgrade auf Version 3 erhalten alle vorhandenen Termine
        die Spaltenwerte tage=1 (eintägiger Termin) und wochen=0 (keine
        wöchentliche Wiederholung).</li>
    <li>Die Termintabelle enthält jetzt anstelle der Spalte 'kategorie' mit
        der Bezeichnung der Terminkategorie eine Spalte 'kat_id' mit der Id
        der Terminkategorie. Die zugehörige Kategoriebezeichnung wird nur
        noch als Konfigurationsparameter abgelegt.<br/>
        Mit dem Upgrade auf Version 3 wird die Tabelle entsprechend
        angepasst. Die Reihenfolge, in der die vorhandenen Kategorien in
        der Termintabelle auftreten, liefert die Werte der Kategorie-Ids
        (1, 2, ...).</li>
    <li>Die meisten Konfigurationsparameter müssen mit Version 3 neu
        definiert werden.<br/>
        Die Farbtöne zur Darstellung der Termin- und Kalendermenüs werden
        jetzt auf Basis einer einzigen Grundfarbe generiert. Nur noch diese
        Grundfarbe ist zu konfigurieren; sie entspricht der bisherigen
        ersten Farbe ('dunkle Schrift-/Rahmenfarbe').<br/>
        Die Parameter zur halbgrafischen Darstellung des Uhrzeit-Bereichs
        bei Tagesterminen enthalten zunächst Default-Werte und müssen
        angepasst werden.<br/>
        Standardmäßig wird mit einer Neuinstallation jetzt nur genau eine
        Kategorie mit der Bezeichnung 'Allgemein' angelegt. Die vorhandenen
        Kategoriebezeichnungen werden in der neuen Konfiguration ergänzt.</li>
    <li>Als erster Schritt eines Upgrades von einer älteren Version (<=2.2.1)
        sollte diese de-installiert werden. Ein re-install reicht nicht, um
        eine korrekte neue Konfiguration zu bekommen.</li>
</ul>

<h4>Version 2.2.1</h4>
<ul>
    <li>Kleinere Korrekturen am Programmcode zur Vermeidung von PHP-Warnungen.</li>
</ul>

<h4>Version 2.2.0</h4>
<ul>
    <li>Leider war die Version 2.1.0 fehlerhaft und i.w. unbrauchbar.</li>
	 <li>Die Konfiguration war nur in der Default-Version nutzbar. Eine Änderung
        führte in einigen Fällen auf eine fehlerhafte Verschiebung der Parameter.
        Der Fehler ist behoben.</li>
	 <li>Die Suchfunktion im Modul der Terminverwaltung führte nicht zum Ziel.
        Der Fehler ist behoben.</li>
    <li>Die halbgrafische Darstellung der Stundenleiste war evtl. fehlerhaft.
        Der Fehler ist behoben.</li>
</ul>

<h4>Version 2.1.0</h4>
<ul>
    <li>Der Code ist mit 'error_reporting(E_ALL);' überprüft.</li>
	 <li>Die Verwaltung der Termine ist vollständig überarbeitet. Ein Termin,
        der zu löschen, zu korrigieren oder zu kopieren ist, wird nun mittels
        Durchklicken durch die (ja schon vorhandenen) Kalendermenüs gesucht.</li>
</ul>

<h4>Version 2.0.0</h4>
<ul>
    <li>Diese Version ist komplett überarbeitet und auf Redaxo 5 angepasst.</li>
</ul>