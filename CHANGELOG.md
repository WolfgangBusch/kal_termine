# kal_termine
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