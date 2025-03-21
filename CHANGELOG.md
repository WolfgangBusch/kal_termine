# kal_termine
<h4>Version 3.7</h4>
<ul>
    <li>Das Suchmenü ist jetzt umgebaut zu einer Terminübersicht im
        Smartphone-Design. Es kann alternativ zum Monatsmenü als
        Startmenü des Terminkalenders dienen.</li>
    <li>Das Eingabemenü für Termine ist umgebaut. Die Festlegung
        der Text-, Datums- und Zeitangaben erfolgt jetzt durch Klicks
        in Untermenüs (Textarea, Monatsmenü, grafisches Stunden- und 
        Minutenmenü). Die Felder für die Zusatzzeiten müssen für
        Eingaben vorher aufgeklappt werden, sie sind per Default
        ausgeblendet.</li>
    <li>In den Monats-/Wochen-/Tagesblättern sowie im Terminblatt
        führen die Rücklinks auf das Monatsmenü jetzt auf den
        zugehörigen Monat, nicht mehr auf den aktuellen Monat</li>
    <li>Beim Experimentieren mit den Spieldaten können jetzt auch
        die Eingabeformulare für neue oder zu korrigierende Termine
        durchlaufen werden. Eine Speicherung von Spieldaten ist
        dabei natürlich nicht möglich.</li>
    <li>Die Konfiguration der farbigen Markierung von Terminen nach
        unterschiedlichen Kategorien in der Terminliste ist jetzt
        reduziert auf zwei Varianten (linker Rand oder vollständiger
        Rahmen). Sie wird auch auf die neue Terminübersicht angewandt.</li>
    <li>Die Beschränkung nach oben für die RGB-Parameter der Grundfarbe
        ist entfallen. Dadurch ändern sich die daraus abgeleiteten
        Farben geringfügig. Außerdem ist die Anzahl der abgeleiteten
        Farben um eine reduziert.</li>
    <li>Die Breite der Monats-/Wochen-/Tagesblatt-Menüs hängt jetzt
        nur noch von der Breite der Stundenleiste ab. Der zugehörige
        Konfigurationsparameter ist entfallen. Außerdem haben diese
        Menüs jetzt ein responsives Design.</li>
    <li>Für die Awesome-Font-Symbole wird jetzt eine aktuelle Version
        von fontawesome.com eingebunden (derzeit Version 6). Die
        zugehörige Stylesheet-Datei muss nun zusätzlich im HTML-Header
        eingebunden werden (.../kal_termine/fontawesome/css/all.css).</li>
</ul>
<h4>Version 3.6</h4>
<ul>
    <li>Wenn eine bestehende Terminkategorie entfernt wird, können
        jetzt die zugehörigen Termine optional gelöscht werden.</li>
    <li>Jetzt wird die Konfiguration der Terminliste nicht mehr
        geändert, wenn die Grundkonfiguration auf Defaultwerte
        zurück gesetzt wird.</li>
</ul>
<h4>Version 3.5</h4>
<ul>
    <li>Ab jetzt ist die Auswahl der Startmenüs beschränkt auf das
        Monatsmenü, das Suchmenü und die Terminliste.</li>
    <li>Es können jetzt auch monatlich wiederkehrende Termine
        definiert werden, die auf den jeweils gleichen Wochentag
        eines Monats fallen. Z.B. 1. Freitag im Monat oder 2.
        Sonntag im Monat. Diese Termine können auch mehrtägig sein,
        z.B. das jeweils 3. Wochenende (Sa/So) im Monat.</li>
    <li>Bisher durften wöchentlich wiederkehrende Termine nicht
        mehrtägig sein. Diese Beschränkung ist entfallen. Jetzt
        sind also z.B. Wochenendtermine (Samstag/Sonntag) möglich,
        die sich wöchentlich wiederholen.</li>
    <li>Die farbige Markierung von Terminen nach unterschiedlichen 
        Kategorien in der Terminliste ist jetzt konfigurierbar,
        bleibt aber optional. Eine zugehörige Farbpalette steht
        als Default-Angebot zur Verfügung, kann aber durch eine
        eigene Farbpalette in Form einer Datei im AddOn-Ordner
        /vendor ersetzt werden.</li>
    <li>Bei Entfernung einer Terminkategorie werden jetzt auch
        die Termine entfernt, die zu dieser Kategorie gehören.
        Auch die Benutzerrolle zur Nutzung dieser Kategorie wird
        jetzt entfernt.</li>
    <li>Das Stylesheet wird (bei der Installation und bei Änderung
        der Konfiguration) jetzt nur noch in den Assets-Ordner des
        AddOns geschrieben. Entsprechend wird der Ordner /assets
        im AddOn-Ordner nicht mehr benötigt.</li>
    <li>Die globalen Variablen sind durch Klassenkonstanten
        ersetzt.</li>
</ul>
<h4>Version 3.4.4</h4>
<ul>
    <li>Ein Fehler im Zusammenhang mit der zeitlichen Sortierung
        der Termine ist korrigiert. Bei Terminen mit gleichem
        Datum und gleicher Startzeit wurde nur einer von diesen
        angezeigt.</li>
</ul>
<h4>Version 3.4.3</h4>
<ul>
    <li>Die Ausgabe der Terminliste erfolgt jetzt nicht nur
        in der Reihenfolge des Datums, sondern ist bei gleichem
        Datum auch nach Uhrzeit des Beginns der Termine
        sortiert.</li>
</ul>
<h4>Version 3.4.2</h4>
<ul>
    <li>Im Modul 'Termine anzeigen' werden jetzt im Falle der
        Terminliste die Default-Werte Startdatum und Dauer
        korrekt berücksichtigt.</li>
</ul>
<h4>Version 3.4.1</h4>
<ul>
    <li>Bisher konnte jeder Redakteur die Kalendermenüs
        nur mit allen Terminen aus allen Terminkategorien
        aus seinem Verantwortungsbereich zugleich unterlegen.
        Ab jetzt kann er die Menüs auf jede einzelne seiner
        Kategorien beschränken. D.h. Jede Terminkategorie
        stellt einen eigenständigen Terminkalender dar.</li>
    <li>Auch im Backend, d.h. bei der Terminverwaltung, ist
        jetzt das Suchmenü verwendbar. Die Datumsangabe in
        der Terminliste ist als Link auf das entsprechende
        Tagesblatt ausgebildet.</li>
    <li>In der Terminliste können jetzt Termine entsprechend
        ihrer Kategorie markiert werden, z.B. durch Farben.
        Die Markierung erfolgt über CSS-Klassen für den linken
        Rand der rechten Tabellenspalte. Der Default ist 'none',
        d.h. keine Markierung. Die zugehörigen Klassen werden
        sinnvollerweise vor einem Block, der mit dem Modul
        'Termine anzeigen' erzeugt wird, geeignet definiert.<br>
        Das Einbringen einer entsprechenden Farbpalette über
        ein $GLOBALS-Array (vergl. Version 3.3.1) ist nicht
        mehr möglich.</li>
    <li>Die Icons in den Kalendermenüs sind durch Icons aus
        dem Awesome-Font ersetzt.</li>
    <li>Das Eingabeformular für den Ersatztermin eines
        Wiederholungstermins enthält nun bereits das
        gewählte Datum.</li>
    <li>Zwei kleine Fehler sind behoben (im Suchmenü bzw. im
        Terminblatt).</li>
</ul>
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
        Wiederholungen.<br>
        Mit dem Upgrade auf Version 3 erhalten alle vorhandenen Termine
        die Spaltenwerte tage=1 (eintägiger Termin) und wochen=0 (keine
        wöchentliche Wiederholung).</li>
    <li>Die Termintabelle enthält jetzt anstelle der Spalte 'kategorie' mit
        der Bezeichnung der Terminkategorie eine Spalte 'kat_id' mit der Id
        der Terminkategorie. Die zugehörige Kategoriebezeichnung wird nur
        noch als Konfigurationsparameter abgelegt.<br>
        Mit dem Upgrade auf Version 3 wird die Tabelle entsprechend
        angepasst. Die Reihenfolge, in der die vorhandenen Kategorien in
        der Termintabelle auftreten, liefert die Werte der Kategorie-Ids
        (1, 2, ...).</li>
    <li>Die meisten Konfigurationsparameter müssen mit Version 3 neu
        definiert werden.<br>
        Die Farbtöne zur Darstellung der Termin- und Kalendermenüs werden
        jetzt auf Basis einer einzigen Grundfarbe generiert. Nur noch diese
        Grundfarbe ist zu konfigurieren; sie entspricht der bisherigen
        ersten Farbe ('dunkle Schrift-/Rahmenfarbe').<br>
        Die Parameter zur halbgrafischen Darstellung des Uhrzeit-Bereichs
        bei Tagesterminen enthalten zunächst Default-Werte und müssen
        angepasst werden.<br>
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