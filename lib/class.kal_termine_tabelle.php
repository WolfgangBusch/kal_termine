<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
define ('TAB_NAME',  'rex_kal_termine');
define ('SPIELTERM', 'Spieldaten');
#
class kal_termine_tabelle {
#
#----------------------------------------- Inhaltsuebersicht
#   Tabellenstruktur
#         kal_define_tabellenspalten()
#         kal_ausgabe_tabellenstruktur()
#   Basis-Funktionen
#         kal_standard_termin($termin)
#         kal_standard_termin_intern($value,$cols)
#         kal_datum_mysql_standard($datum)
#         kal_termin_mysql_standard($termin)
#         kal_datum_standard_mysql($datum)
#   SQL-Grundfunktionen
#         kal_exist_termin($termin)
#         kal_insert_termin($termin)
#         kal_delete_termin($pid)
#         kal_update_termin($pid,$termkor)
#         kal_copy_termin($pid,$datumneu)
#   Auslesen von Termindaten
#         kal_select_termin_by_pid($pid)
#         kal_select_termine($von,$bis,$kategorie,$stichwort)
#         kal_get_tagestermine($datum,$termtyp)
#         kal_get_wochentermine($von,$bis,$termtyp)
#         kal_get_monatstermine($von,$bis,$termtyp)
#         kal_filter_termine_kategorie ($termin,$kategorie)
#   Erzeugen von Spiel-Termindaten
#         kal_get_spieldaten($datum)
#         kal_get_wochenspieldaten($montag)
#         kal_get_monatsspieldaten($erster)
#
#----------------------------------------- Tabellenstruktur
static public function kal_define_tabellenspalten() {
   #   Rueckgabe der Daten zu den Kalender-Tabellenspalten als Array,
   #   - Keys und Typen der Spalten (zur Einrichtung der Tabelle)
   #   - Beschreibung und Hinweise zu den Tabellenspalten
   #
   $cols=array(
      'pid'=>array('int(11) NOT NULL auto_increment',
         'Termin-Id', 'auto_increment', 'Prim&auml;rschl&uuml;ssel'),
      'name'=>array('varchar(255) NOT NULL',
         'Veranstaltung', 'Kurztext', 'nicht leer'),
      'datum'=>array('date NOT NULL',
         'Datum', 'tt.mm.yyyy', 'nicht leer'),
      'beginn'=>array('time NOT NULL',
         'Beginn', 'hh:mm', ''),
      'ende'=>array('time NOT NULL',
         'Ende', 'hh:mm', ''),
      'ausrichter'=>array('varchar(500) NOT NULL',
         'Ausrichter', 'Kurztext', ''),
      'ort'=>array('varchar(255) NOT NULL',
         'Ort', 'Kurztext', ''),
      'link'=>array('varchar(500) NOT NULL',
         'Link', 'Kurztext', ''),
      'komm'=>array('text NOT NULL',
         'Hinweise', 'Text', ''),
      'kategorie'=>array('varchar(255) NOT NULL',
         'Kategorie', 'Kurztext', 'nicht leer'),
      'zeit2'=>array('time NOT NULL',
         'Beginn 2', 'hh:mm', ''),
      'text2'=>array('varchar(255) NOT NULL',
         'Ereignis 2', 'Kurztext', ''),
      'zeit3'=>array('time NOT NULL',
         'Beginn 3', 'hh:mm', ''),
      'text3'=>array('varchar(255) NOT NULL',
         'Ereignis 3', 'Kurztext'),
      'zeit4'=>array('time NOT NULL',
         'Beginn 4', 'hh:mm', ''),
      'text4'=>array('varchar(255) NOT NULL',
         'Ereignis 4', 'Kurztext', ''),
      'zeit5'=>array('time NOT NULL',
         'Beginn 5', 'hh:mm'),
      'text5'=>array('varchar(255) NOT NULL',
         'Ereignis 5', 'Kurztext', ''));
   ###         create table:     'PRIMARY KEY (pid)'
   return $cols;
   }
static public function kal_ausgabe_tabellenstruktur() {
   #   Rueckgabe der Tabellenstrukturen
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #
   $stx='style="padding:0px 5px 0px 5px; white-space:nowrap;"';
   $sty='style="padding:0px 5px 0px 5px; white-space:nowrap; border:solid 1px grey;"';
   #
   # --- Schleife ueber die Tabellenspalten
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $string='
<table style="background-color:inherit;">
    <tr><td colspan="5" align="center">
            <h4>Tabelle \''.TAB_NAME.'\'</h4></td></tr>
    <tr><th '.$sty.'>Spaltenname</th>
        <th '.$sty.'>Spalteninhalt</th>
        <th '.$sty.'>Format</th>
        <th '.$sty.'>Restriktionen</th>
        <th '.$sty.'>Bem.</th></tr>
';
   for($i=0;$i<count($cols);$i=$i+1):
      $inha=$cols[$keys[$i]][1];
      $arr=explode(' ',$cols[$keys[$i]][0]);
      $form=$arr[0];
      $arr=explode('(',$form);
      $form=$arr[0];
      $bedg=$cols[$keys[$i]][3];
      $beme='';
      if($form=='date') $beme='*';
      if($form=='time') $beme='**';
      $string=$string.'
    <tr><td '.$sty.'><tt>'.$keys[$i].'</tt></td>
        <td '.$stx.'>'.$inha.'</td>
        <td '.$stx.'><tt>'.$form.'</tt></td>
        <td '.$stx.'><i>'.$bedg.'</i></td>
        <td '.$stx.'><i>'.$beme.'</i></td></tr>
';
     endfor;
   $string=$string.'
</table><br/>
<table style="background-color:inherit;">
    <tr><td '.$stx.'>&nbsp;(*)</td>
        <td '.$stx.'>Datumsformat: <tt>tt.mm.yyyy</tt>
            (wird für MySQL in <tt>yyyy-mm-tt</tt> gewandelt)</td></tr>
    <tr><td '.$stx.'>(**)</td>
        <td '.$stx.'>Zeitformat: <tt>hh:mm</tt>
            (wird ins MySQL-Format <tt>hh:mm:ss</tt> gewandelt)</td></tr>
    <tr><td '.$stx.'> </td>
        <td '.$stx.'>Kurz-/Langtexte (<tt>varchar</tt> bzw. <tt>text</tt>)
            müssen ohne HTML-Tags formuliert werden</td></tr>
    <tr><td '.$stx.'> </td>
        <td '.$stx.'>Mit <tt>zeit2/text2, ... , zeit5/text5</tt>
            können 4 Teilereignisse genauer beschrieben werden</td></tr>
</table>
';
   return utf8_encode($string);
   }
#
#----------------------------------------- Basis-Funktionen
static public function kal_standard_termin($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   $termin         eingegebenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #      self::kal_standard_termin_intern($value)
   #
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben des eingegebenen Termins standardisieren
   for($i=0;$i<count($cols);$i=$i+1) $value[$i]=$termin[$keys[$i]];
   $val=self::kal_standard_termin_intern($value,$cols);
   for($i=0;$i<count($cols);$i=$i+1) $term[$keys[$i]]=$val[$i];
   return $term;
   }
static public function kal_standard_termin_intern($value,$cols) {
   #   Standardisierung von Datums- und Zeitangaben eines Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   $value          eingegebenes Array
   #                   das standardisierte Array wird zurueck gegeben
   #   $cols           Array der Tabellenspalten-Definition
   #                   (gemaess Funktion kal_define_tabellenspalten()
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_standard_uhrzeit($uhrz)
   #
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben des eingegebenen Arrays standardisieren
   for($i=0;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
      $tk=$value[$i];
      $type=$cols[$key][0];
      $arr=explode(' ',$type);
      $type=$arr[0];
      if($type=='date' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_datum($tk);
      if($type=='time' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_uhrzeit($tk);
      $val[$i]=$tk;
      endfor;
   return $val;
   }
static public function kal_datum_mysql_standard($datum) {
   #   Umformatieren eines MySQL-Datumsstrings 'yyyy-mm-tt'
   #   in einen Standard-Datumsstring 'tt.mm.yyyy'
   #
   return substr($datum,8,2).".".substr($datum,5,2).".".substr($datum,0,4);
   }
static public function kal_termin_mysql_standard($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays
   #   dabei wird so gewandelt:
   #   - Datumsangaben: 'yyyy-mm-tt' nach 'tt.mm.yyyy'
   #   - Zeitangaben: 'hh:mm:ss' nach 'hh:mm'
   #   - Zeitangaben: '00:00:00' nach '' (leer)
   #   $termin         ausgelesenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #      self::kal_datum_mysql_standard($val)
   #
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardisierung der date- und time-Werte des gefundenen Termins
   for($i=0;$i<count($termin);$i=$i+1):
      $key=$keys[$i];
      $val=$termin[$key];
      $term[$key]=$val;
      $type=$cols[$key][0];
      $arr=explode(' ',$type);
      $type=$arr[0];
      if($type=='date') $val=self::kal_datum_mysql_standard($val);
      if($type=='time') $val=substr($val,0,5);
      if($type=='time' and $val=='00:00') $val='';
      $term[$key]=$val;
      endfor;
   return $term;
   }
static public function kal_datum_standard_mysql($datum) {
   #   Umformatieren eines Standard-Datumsstrings 'tt.mm.yyyy'
   #   in einen MySQL-Datumsstring 'yyyy-mm-tt'
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   $dat=kal_termine_kalender::kal_standard_datum($datum);
   return substr($dat,6)."-".substr($dat,3,2)."-".substr($dat,0,2);
   }
#
#----------------------------------------- SQL-Grundfunktionen
static public function kal_exist_termin($termin) {
   #   Suchen eines Termins in der Datenbanktabelle, falls der Termin
   #   gefunden wird, wird dessen Id ($termin[pid]) zurueckgegeben
   #   ('gefunden' heisst: alle Parameter ausser der Id stimmen ueberein)
   #   $termin         gegebenes Termin-Array
   #   benutzte functions
   #      self::kal_define_tabellenspalten()
   #      self::kal_standard_termin($termin)
   #      self::kal_termin_mysql_standard($termin)
   #      self::kal_datum_standard_mysql($datum)
   #
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben des eingegebenen Termins standardisieren
   $stdtermin=self::kal_standard_termin($termin);
   #
   # --- alle Termine zum Datum des gegebenen Termins auslesen
   $datum=$stdtermin[datum];
   $datsql=self::kal_datum_standard_mysql($datum);
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.TAB_NAME.' WHERE datum=\''.$datsql.'\'';
   $term=$sql->getArray($query);
   #
   # --- ausgelesene Termine mit dem gegebenen Termin vergleichen
   for($i=0;$i<count($term);$i=$i+1):
      #
      # --- ausgelesenen Termin standardisieren (MySQL-date- und -time-Werte)
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      #
      # --- unterschiedliche Werte aufsammeln
      $m=-1;
      unset($dif);
      for($k=0;$k<=count($stdtermin);$k=$k+1):
         $ke=$keys[$k];
         if($ke=='pid') continue;
         $val=$term[$i][$ke];
         if($val==$stdtermin[$ke]) continue;
         $m=$m+1;
         $dif[$m]=$stdtermin[$ke].'|'.$val;
         endfor;
      if(count($dif)<=0) return $term[$i][pid];
      endfor;
   }
static public function kal_insert_termin($termin) {
   #   Eintragen eines neuen Termins in die Datenbanktabelle
   #   $termin         Array des Termins
   #   Rueckgabe:      Eintragung erfolgreich:   Id des neuen Termins (pid)
   #                   Termin schon vorhanden:   (-1)*Id des schon vorhandenen
   #                                             Termins mit denselben Daten
   #                   Eintragung nicht erfolgt: Fehlermeldung '... konnte nicht ...'
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #   benutzte functions:
   #      self::kal_standard_termin($termin)
   #      self::kal_exist_termin($termin)
   #      self::kal_define_tabellenspalten()
   #      self::kal_datum_standard_mysql($datum)
   #
   # --- Standardform des Termins herstellen
   $term=self::kal_standard_termin($termin);
   #
   # --- Ueberpruefen, ob der Termin schon eingetragen ist
   $pid=self::kal_exist_termin($term);
   if($pid>0) return intval("-".$pid);
   #
   # --- Terminparameter einzeln in einer Schleife einfuegen
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   for($i=0;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
      if($key=='pid') continue;
      $val=$term[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      $sql->setValue($key,$val);
      endfor;
   #
   # --- Einfuegen durchfuehren
   $sql->insert();
   #
   # --- neuen Termin verifizieren
   $pid=self::kal_exist_termin($term);
   if($pid>0):
     return $pid;
     else:
     return '<span class="kal_fail">Der Termin konnte nicht eingetragen werden</span>';
     endif;
   }
static public function kal_delete_termin($pid) {
   #   Loeschen eines Termins in die Datenbanktabelle
   #   $pid            Id des Termins
   #   Rueckgabe:      leer, falls der Termin geloescht wurde bzw.
   #                   Fehlermeldung in rot (andernfalls)
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   #
   # --- Pruefen, ob der Termin da ist
   if($pid<=0)
     return '<span class="kal_fail">Bitte eine Termin zum L&ouml;schen benennen (pid>0)</span>';
   $row=$sql->getArray('SELECT * FROM '.$table.' WHERE pid='.$pid);
   $colsvor=count($row[0]);
   #
   # --- Durchfuehrung
   $sql->setQuery('DELETE FROM '.$table.' WHERE pid='.$pid);
   #
   # --- Pruefen, ob der Termin wirklich weg ist
   $row=$sql->getArray('SELECT * FROM '.$table.' WHERE pid='.$pid);
   $colsnach=count($row[0]);
   #
   # --- Fehlermeldungen zurueck geben
   $error='';
   if($colsvor>0 and $colsnach>0)
     $error='<span class="kal_fail">Termin konnte nicht gel&ouml;scht werden</span>';
   if($colsvor<=0 and $colsnach<=0)
     $error='<span class="kal_fail">Termin nicht vorhanden</span>';
   return $error;
   }
static public function kal_update_termin($pid,$termkor) {
   #   Korrigieren eines Termins in der Datenbanktabelle
   #   $pid            Id des zu korrigierenden Termins
   #   $termkor        zu korrigierende Daten des Termins
   #                   in Form eines vollstaendigen Termin-Arrays (ohne pid)
   #   Rueckgabe:      leer, falls der Termin korrigiert wurde
   #                   Fehlermeldung (in rot), falls das Update fehlschlug
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #      self::kal_datum_standard_mysql($datum)
   #   die Datenbank nutzende functions:
   #      self::kal_select_termin_by_pid($pid)
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   if(count($termin)<=0) return '<span class="kal_fail">Der Termin ist nicht vorhanden</span>';
   #
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardform der zu korrigierenden Termindaten herstellen
   $korterm=self::kal_standard_termin($termkor);
   #
   # --- Terminparameter einzeln in einer Schleife aktualisieren
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   $sql->setWhere('pid='.$pid);
   for($i=0;$i<count($korterm);$i=$i+1):
      $key=$keys[$i];
      if($key=='pid') continue;
      $val=$korterm[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      $sql->setValue($key,$val);
      endfor;
   #
   # --- Update durchfuehren
   $sql->update();
   #
   # --- erneutes Auslesen des Termins und Vergleich mit eingegebener Korrektur
   $termsel=self::kal_select_termin_by_pid($pid);
   $error='';
   for($i=1;$i<=count($termkor);$i=$i+1):
      $key=$keys[$i];
      if($key=='pid') continue;
      if($termsel[$key]==$korterm[$key]) continue;
      $error='<span class="kal_fail">Termin "'.$termin[name].'" ('.$termin[datum].
         ') konnte nicht korrigiert werden</span>';
      break;
      endfor;
   return $error;
   }
static public function kal_copy_termin($pid,$datumneu) {
   #   Kopieren eines Termins an ein neues Datum in der Datenbanktabelle,
   #   bis auf das Datum werden alle Termindaten uebernommen
   #   $pid            Id des zu kopierenden Termins
   #   $datumneu       Datum des zu kopierenden Termins
   #   Rueckgabe:      Id des kopierten Termins,
   #                   falls das Kopieren fehlschlug:
   #                   - Termin nicht vorhanden (falsche pid):  Fehlerstring (in rot)
   #                   - zu kopierender Termin schon vorhanden: -$pidneu
   #                     ($pidneu = ID des schon vorhandenen Termins)
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #   die Datenbank nutzende functions:
   #      self::kal_select_termin_by_pid($pid)
   #      self::kal_insert_termin($termin)
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   $count=count($termin);
   if($count<=0)
     return '<span class="kal_fail">Der Termin (pid='.$pid.') ist nicht vorhanden</span>';
   #
   # --- neues Termin-Array durch Kopie und das Datum aendern
   $termneu=$termin;
   $termneu[datum]=kal_termine_kalender::kal_standard_datum($datumneu);
   #
   # --- den neuen Termin eintragen
   $pidneu=self::kal_insert_termin($termneu);
   return $pidneu;
   }
#
#----------------------------------------- Auslesen von Termindaten
static public function kal_select_termin_by_pid($pid) {
   #   Auslesen und Rueckgabe eines Termins aus der Datenbanktabelle
   #   $pid            Id des Termins
   #   benutzte functions:
   #      self::kal_termin_mysql_standard($termin)
   #
   # --- aus der Datenbanktabelle auslesen
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $term=$sql->getArray('SELECT * FROM '.$table.' WHERE pid='.$pid);
   #
   # --- Wandeln der Datums- und Zeitformate
   return self::kal_termin_mysql_standard($term[0]);
   }
static public function kal_select_termine($von,$bis,$kategorie,$stichwort) {
   #   Auslesen von Terminen aus der Datenbanktabelle,
   #   gefiltert durch Datumsbereich, Kategorie und Stichwort
   #   Rueckgabe als Array von Terminen (Indizierung bei 1 beginnend)
   #   $von            Datum des ersten Tages (Standardformat)
   #   $bis            Datum des letzten Tages (Standardformat)
   #   $kategorie      falls nicht leer: nur Termine der vorgegebenen Kategorie
   #   $stichwort      falls nicht leer: nur Termine, die das vorgegebene Stichwort
   #                   in [name] oder [komm] oder [ausrichter] oder [ort] enthalten
   #   benutzte functions:
   #      self::kal_termin_mysql_standard($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   # --- Datumsbereich
   $vonsql=kal_termine_kalender::kal_standard_datum($von);
   $bissql=kal_termine_kalender::kal_standard_datum($bis);
   $vonsql=self::kal_datum_standard_mysql($von);
   $bissql=self::kal_datum_standard_mysql($bis);
   $wheredat='datum>=\''.$vonsql.'\' and datum <=\''.$bissql.'\'';
   #
   # --- Restriktion Kategorie
   if(!empty($kategorie)):
     $kat=kal_termine_config::kal_get_terminkategorien();
     for($i=1;$i<=count($kat);$i=$i+1):
        if($kategorie==utf8_encode($kat[$i])) $wherekat='kategorie=\''.$kategorie.'\'';
        endfor;
     endif;
   #
   # --- Restriktion Stichwort
   if(!empty($stichwort)):
     $wherestw='(name LIKE \'%'.$stichwort.'%\' OR '.
        'komm LIKE \'%'.$stichwort.'%\' OR '.
        'ausrichter LIKE \'%'.$stichwort.'%\' OR '.
        'ort LIKE \'%'.$stichwort.'%\')';
     endif;
   #
   # --- alle Termine mit den vorgegebenen Restriktionen auslesen
   $where=$wheredat;
   if(!empty($wherekat)) $where=$where.' AND '.$wherekat;
   if(!empty($wherestw)) $where=$where.' AND '.$wherestw;
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $query='SELECT * FROM '.$table.' WHERE '.$where.' ORDER BY datum';
   $term=$sql->getArray($query);
   #
   # --- Wandeln der Datums- und Zeitformate
   for($i=0;$i<count($term);$i=$i+1):
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      $termin[$i+1]=$term[$i];
      endfor;
   return $termin;
   }
static public function kal_get_tagestermine($datum,$termtyp) {
   #   Auslesen der aller Termindaten eines Tages aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $datum          Datum des Tages
   #   $termtyp        ='Spieldaten': aus den Spieldaten auslesen
   #                   ='':           aus der DB-Tabelle auslesen
   #   benutzte functions:
   #      self::kal_get_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #   die Datenbank nutzende functions:
   #      self::kal_select_termine($von,$bis,$kategorie,$stichwort)
   #
   $stdatum=kal_termine_kalender::kal_standard_datum($datum);
   #
   # --- Datenbankdaten / Spieldaten
   if($termtyp==SPIELTERM):
     $termin=self::kal_get_spieldaten($stdatum);
     else:
     $termin=self::kal_select_termine($stdatum,$stdatum,'','');
     endif;
   return $termin;
   }
static public function kal_get_wochentermine($von,$bis,$termtyp) {
   #   Auslesen der aller Termine einer Kalenderwoche aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $von            Datum des 1. Tages der Woche
   #   $bis            Datum des 7. Tages der Woche
   #   $termtyp        ='Spieldaten': aus den Spieldaten auslesen
   #                   ='':           aus der DB-Tabelle auslesen
   #   benutzte functions:
   #      self::kal_get_wochenspieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #   die Datenbank nutzende functions:
   #      self::kal_select_termine($von,$bis,$kategorie,$stichwort)
   #
   $stvon=kal_termine_kalender::kal_standard_datum($von);
   $stbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Datenbankdaten / Spieldaten
   if($termtyp==SPIELTERM):
     $termin=self::kal_get_wochenspieldaten($stvon);
     else:
     $termin=self::kal_select_termine($stvon,$stbis,'','');
     endif;
   return $termin;
   }
static public function kal_get_monatstermine($von,$bis,$termtyp) {
   #   Auslesen der Termine eines Monats aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $von            Datum des ersten Tages des Monats
   #   $bis            Datum des letzten Tages des Monats
   #   $termtyp        ='Spieldaten': aus den Spieldaten auslesen
   #                   ='':           aus der DB-Tabelle auslesen
   #   benutzte functions:
   #      self::kal_get_monatsspieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #   die Datenbank nutzende functions:
   #      self::kal_select_termine($von,$bis,$kategorie,$stichwort)
   #
   $stvon=kal_termine_kalender::kal_standard_datum($von);
   $stbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Datenbankdaten / Spieldaten
   if($termtyp==SPIELTERM):
     $termin=self::kal_get_monatsspieldaten($stvon);
     else:
     $termin=self::kal_select_termine($stvon,$stbis,'','');
     endif;
   return $termin;
   }
static public function kal_filter_termine_kategorie($termin,$kategorie) {
   #   Herausfiltern der Termine aus einem Termin-Array, die zu einer
   #   vorgegebenen Kategorie gehoeren
   #   $termin         Array der Termine
   #   $kategorie      vorgegebene Kategorie
   #                   (falls leer, werden keine Termine herausgenommen)
   #
   if(empty($kategorie)):
     $term=$termin;
     else:
     $m=0;
     for($i=1;$i<=count($termin);$i=$i+1):
        if($termin[$i][kategorie]==$kategorie):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     endif;
   return $term;
   }
#
#----------------------------------------- Erzeugen von Spiel-Termindaten
static public function kal_get_spieldaten($datum) {
   #   Rueckgabe von kuenstlichen Termindaten eines Tages
   #   $datum          Datum des Tages (standardisiertes Datum)
   #   benutzte functions:
   #      self::kal_define_tabellenspalten()
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_montag_vor($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   # --- alle Wochentage um das Datum herum bestimmen
   $montag=kal_termine_kalender::kal_montag_vor($datum);
   $datumall[1]=$montag;
   for($i=1;$i<=6;$i=$i+1)
      $datumall[$i+1]=kal_termine_kalender::kal_datum_vor_nach($montag,$i);
   #
   # --- Kategorien:
   $kat=kal_termine_config::kal_get_terminkategorien();
   #
   # --- Standard-Hinweissaetze
   $bl='            ';
   $stc=utf8_encode('<tt>(Reine Spieldaten mit festen wöchentlich wiederkehrenden Terminen)</tt>');
   #
   # --- Link-URL
   $page=$_GET[page];
   if(empty($page)):
     #  -  Frontend: echter externer Link
     $linkurl='https://www.brunsviga-kulturzentrum.de/de/kurs-sonstiges.html';
     else:
     #  -  Backend: Ruecklink auf die Beispiel-Startseite
     $url=$_SERVER['REQUEST_URI'];
     $arr=explode('?',$url);
     $url=$arr[0];
     $linkurl=$url.'?page='.$page;
     endif;
   #
   # --- Setzen der (Wochen-)Termine
   $ter[1]=array(
      0=>117,
      1=>utf8_encode('Abendtermin'),
      2=>$datumall[1],
      3=>'19:30',
      4=>'22:00',
      5=>utf8_encode('MTV Quadertal, Thomas Hörlinger, Tel. 05996 65432'),
      6=>utf8_encode('Landkreis-Sporthalle, Wallstraße, 38900 Quade'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[4]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[2]=array(
      0=>205,
      1=>utf8_encode('Kadertraining'),
      2=>$datumall[2],
      3=>'10:30',
      4=>'12:00',
      5=>utf8_encode('Volker Meister, Tel. 0123 4567890'),
      6=>utf8_encode('Stützpunkt 1'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[3]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[3]=array(
      0=>226,
      1=>utf8_encode('Hauptausschusssitzung'),
      2=>$datumall[2],
      3=>'19:30',
      4=>'',
      5=>utf8_encode('TT-Regionsverband Quaderland, Jochen Krause,Tel. 0124 3302456'),
      6=>utf8_encode('Gaststätte \'Grüne Wiese\', Wiesenstr. 79, 38990 Quadertal, Tel. 05996 88776655'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[2]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[4]=array(
      0=>183,
      1=>utf8_encode('Kadertraining'),
      2=>$datumall[4],
      3=>'15:00',
      4=>'17:00',
      5=>utf8_encode('Volker Meister, Tel. 0123 4567890'),
      6=>utf8_encode('Stützpunkt 2'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[3]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[5]=array(
      0=>77,
      1=>utf8_encode('Staffelsitzungen, Spielbereich Quaderberg'),
      2=>$datumall[4],
      3=>'',
      4=>'',
      5=>utf8_encode('Manfred Berger, Tel. 05992 1234567'),
      6=>utf8_encode('Sportheim Groß Brakel'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[1]),
      10=>'18:00', 11=>utf8_encode('Damen: 1. Kreisklasse'),
      12=>'18:30', 13=>utf8_encode('Herren: 1. Kreisklasse, 2. Kreisklasse'),
      14=>'19:00', 15=>utf8_encode('Herren: 3. Kreisklasse, 4. Kreisklasse'),
      16=>'19:30', 17=>utf8_encode('Herren: 5. Kreisklasse, 6. Kreisklasse'));
   #
   $ter[6]=array(
      0=>238,
      1=>utf8_encode('Regionsmeisterschaften Seniorinnen/Senioren'),
      2=>$datumall[5],
      3=>'18:00',
      4=>'',
      5=>utf8_encode('TSV Quaderberg'),
      6=>utf8_encode('Halle am Sportpark, 38985 Quaderberg'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[1]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[7]=array(
      0=>239,
      1=>utf8_encode('Regionsmeisterschaften Jugend und Schüler'),
      2=>$datumall[6],
      3=>'',
      4=>'',
      5=>utf8_encode('TSV Quaderberg'),
      6=>utf8_encode('Halle am Sportpark, 38985 Quaderberg'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[1]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[8]=array(
      0=>54,
      1=>utf8_encode('Wochenendseminar'),
      2=>$datumall[6],
      3=>'16:00',
      4=>'18:30',
      5=>utf8_encode('TT-Regionsverband Quaderland, Jochen Krause, Tel. 05996 44001155'),
      6=>utf8_encode('Brunsviga-Kulturzentrum, Quader Ring 35, 38900 Quade'),
      7=>$linkurl,
      8=>$stc.'<br/>'.
         $bl.utf8_encode('Die Veranstaltung wendet sich an die Vereine in der Region. Es '.
         'werden Hinweise zur Gewinnung von Jugendlichen und Kindern für den Tischtennissport '.
         'gegeben, basierend auf der Kampagne \'Tischtennis: Spiel mit!\' des DTTB.<br/>').
         $bl.utf8_encode('Diese Kampagne des DTTB und seiner Landesverbände fördert die '.
         'Kooperationen zwischen Vereinen und Schulen. Nutznießer sind in erster Linie die '.
         'Kinder und Jugendlichen. Diese haben durch die Kooperation die Möglichkeit, die '.
         'Sportart Tischtennis kennenzulernen und als ihre Sportart zu entdecken. Oberstes Ziel '.
         'von \'Tischtennis: Spiel mit!\' ist die Gewinnung und langfristige Bindung neuer '.
         'Mitglieder für die rund 10.000 Vereine in Deutschland.'),
      9=>utf8_encode($kat[4]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   $ter[9]=array(
      0=>240,
      1=>utf8_encode('Regionsmeisterschaften Jugend und Schüler'),
      2=>$datumall[7],
      3=>'',
      4=>'',
      5=>utf8_encode('TSV Quaderberg'),
      6=>utf8_encode('Halle am Sportpark, 38985 Quaderberg'),
      7=>'',
      8=>$stc,
      9=>utf8_encode($kat[1]),
      10=>'', 11=>'',      12=>'', 13=>'',      14=>'', 15=>'',      16=>'', 17=>'');
   #
   #
   # --- Termin-Arrays: indizierte in assoziative Arrays umspeichern
   $cols=self::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   for($i=1;$i<=count($ter);$i=$i+1):
      for($k=0;$k<count($cols);$k=$k+1):
         $key=$keys[$k];
         $term[$i][$key]=$ter[$i][$k];
         endfor;
      endfor;
   #
   # --- Auswahl der gewuenschten Termine
   $m=0;
   for($i=1;$i<=count($term);$i=$i+1):
      if($term[$i][datum]==$datum):
        $m=$m+1;
        $termin[$m]=$term[$i];
        endif;
      endfor;
   #
   return $termin;
   }
static public function kal_get_wochenspieldaten($montag) {
   #   Erzeugen von kuenstlichen Termindaten einer Woche
   #   $montag         Datum des ersten Tages (Montag)
   #   benutzte functions:
   #      self::kal_get_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   # --- Erstellen des Datums-Arrays der Woche
   $dat[1]=kal_termine_kalender::kal_standard_datum($montag);
   for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
   #
   # --- Tagestermine aller Tage der Woche
   $m=0;
   for($i=1;$i<=count($dat);$i=$i+1):
      $term=self::kal_get_spieldaten($dat[$i]);
      for($k=1;$k<=count($term);$k=$k+1):
         $m=$m+1;
         $termin[$m]=$term[$k];
         endfor;
      unset($term);
      endfor;
   return $termin;
   }
static public function kal_get_monatsspieldaten($erster) {
   #   Erzeugen von kuenstlichen Termindaten eines Monats
   #   $erster         Datum des ersten Tages des Monats
   #   benutzte functions:
   #      self::kal_get_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_monatstage($jahr)
   #
   # --- Monats- und Jahresstring
   $strvon=kal_termine_kalender::kal_standard_datum($erster);
   $mon=substr($strvon,3,2);
   $jahr=substr($strvon,6);
   #
   # --- Tagestermine aller Tage im Monat
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $datum='01.'.$mon.'.'.$jahr;
   $m=0;
   for($k=1;$k<=$mtage[intval($mon)];$k=$k+1):
      $term=self::kal_get_spieldaten($datum);
      for($i=1;$i<=count($term);$i=$i+1):
         $m=$m+1;
         $termin[$m]=$term[$i];
         endfor;
      unset($term);
      $datum=kal_termine_kalender::kal_datum_vor_nach($datum,1);
      endfor;
   return $termin;
   }
}
?>
