<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2020
 */
define ('SPIELTERM', 'Spieldaten');
#
class kal_termine_tabelle {
#
#----------------------------------------- Inhaltsuebersicht
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
#         kal_filter_termine_kategorie($termin,$kategorie)
#   Erzeugen von Spiel-Termindaten
#         kal_get_spieldaten($datum)
#         kal_get_wochenspieldaten($montag)
#         kal_get_monatsspieldaten($erster)
#
#----------------------------------------- Basis-Funktionen
public static function kal_standard_termin($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   $termin         eingegebenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_standard_termin_intern($value)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben des eingegebenen Termins standardisieren
   $value=array();
   for($i=0;$i<count($cols);$i=$i+1) $value[$i]=$termin[$keys[$i]];
   $val=self::kal_standard_termin_intern($value,$cols);
   $term=array();
   for($i=0;$i<count($cols);$i=$i+1) $term[$keys[$i]]=$val[$i];
   return $term;
   }
public static function kal_standard_termin_intern($value,$cols) {
   #   Standardisierung von Datums- und Zeitangaben eines Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   $value          eingegebenes Array
   #                   das standardisierte Array wird zurueck gegeben
   #   $cols           Array der Tabellenspalten-Definition
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_standard_uhrzeit($uhrz)
   #
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben des eingegebenen Arrays standardisieren
   $val=array();
   for($i=0;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
      $tk='';
      if(!empty($value[$i])) $tk=$value[$i];
      $type=$cols[$key][0];
      $arr=explode(' ',$type);
      $type=$arr[0];
      if($type=='date' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_datum($tk);
      if($type=='time' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_uhrzeit($tk);
      $val[$i]=$tk;
      endfor;
   return $val;
   }
public static function kal_datum_mysql_standard($datum) {
   #   Umformatieren eines MySQL-Datumsstrings 'yyyy-mm-tt'
   #   in einen Standard-Datumsstring 'tt.mm.yyyy'
   #
   return substr($datum,8,2).'.'.substr($datum,5,2).'.'.substr($datum,0,4);
   }
public static function kal_termin_mysql_standard($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays
   #   dabei wird so gewandelt:
   #   - Datumsangaben: 'yyyy-mm-tt' nach 'tt.mm.yyyy'
   #   - Zeitangaben: 'hh:mm:ss' nach 'hh:mm'
   #   - Zeitangaben: '00:00:00' nach '' (leer)
   #   $termin         ausgelesenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_datum_mysql_standard($val)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardisierung der date- und time-Werte des gefundenen Termins
   $term=array();
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
public static function kal_datum_standard_mysql($datum) {
   #   Umformatieren eines Standard-Datumsstrings 'tt.mm.yyyy'
   #   in einen MySQL-Datumsstring 'yyyy-mm-tt'
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   $dat=kal_termine_kalender::kal_standard_datum($datum);
   return substr($dat,6).'-'.substr($dat,3,2).'-'.substr($dat,0,2);
   }
#
#----------------------------------------- SQL-Grundfunktionen
public static function kal_exist_termin($termin) {
   #   Suchen eines Termins in der Datenbanktabelle, falls der Termin
   #   gefunden wird, wird dessen Id ($termin[COL_PID]) zurueckgegeben
   #   ('gefunden' heisst: alle Parameter ausser der Id stimmen ueberein)
   #   $termin         gegebenes Termin-Array
   #   benutzte functions
   #      self::kal_standard_termin($termin)
   #      self::kal_termin_mysql_standard($termin)
   #      self::kal_datum_standard_mysql($datum)
   #
   $keys=array_keys($termin);
   #
   # --- Datums- und Zeitangaben des eingegebenen Termins standardisieren
   $stdtermin=self::kal_standard_termin($termin);
   #
   # --- alle Termine zum Datum des gegebenen Termins auslesen
   $datum=$stdtermin[COL_DATUM];
   $datsql=self::kal_datum_standard_mysql($datum);
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.TAB_NAME.' WHERE '.COL_DATUM.'=\''.$datsql.'\'';
   $term=$sql->getArray($query);
   #
   # --- ausgelesene Termine mit dem gegebenen Termin vergleichen
   for($i=0;$i<count($term);$i=$i+1):
      #
      # --- ausgelesenen Termin standardisieren (MySQL-date- und -time-Werte)
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      #
      # --- unterschiedliche Werte zaehlen
      $m=0;
      for($k=0;$k<count($stdtermin);$k=$k+1):
         $ke=$keys[$k];
         if($ke==COL_PID) continue;
         $val=$term[$i][$ke];
         if($val==$stdtermin[$ke]) continue;
         $m=$m+1;
         endfor;
      if($m<=0) return $term[$i][COL_PID];
      endfor;
   }
public static function kal_insert_termin($termin) {
   #   Eintragen eines neuen Termins in die Datenbanktabelle
   #   $termin         Array des Termins
   #   Rueckgabe:      Eintragung erfolgreich:   Id des neuen Termins (COL_PID)
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
   #      self::kal_datum_standard_mysql($datum)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   #
   # --- Standardform des Termins herstellen
   $term=self::kal_standard_termin($termin);
   #
   # --- Ueberpruefen, ob der Termin schon eingetragen ist
   $pid=self::kal_exist_termin($term);
   if($pid>0) return intval('-'.$pid);
   #
   # --- Terminparameter einzeln in einer Schleife einfuegen
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keys=array_keys($termin);
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   for($i=1;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
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
public static function kal_delete_termin($pid) {
   #   Loeschen eines Termins in die Datenbanktabelle
   #   $pid            Id des Termins
   #   Rueckgabe:      leer, falls der Termin geloescht wurde bzw.
   #                   Fehlermeldung in rot (andernfalls)
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #
   if($pid<=0)
     return '<span class="kal_fail">Bitte einen Termin zum Löschen benennen (Termin-Id>0)</span>';
   #
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   #
   # --- Pruefen, ob der Termin da ist
   $row=$sql->getArray('SELECT * FROM '.$table.' WHERE '.COL_PID.'='.$pid);
   if(empty($row))
     return '<span class="kal_fail">Termin nicht vorhanden</span>';
   #
   # --- Durchfuehrung
   $sql->setTable($table);
   $sql->setWhere(array(COL_PID=>$pid));
   $sql->delete();
   #
   # --- Pruefen, ob der Termin wirklich weg ist
   $row=$sql->getArray('SELECT * FROM '.$table.' WHERE '.COL_PID.'='.$pid);
   if(!empty($row))
     return '<span class="kal_fail">Termin konnte nicht gelöscht werden</span>';
   }
public static function kal_update_termin($pid,$termkor) {
   #   Korrigieren eines Termins in der Datenbanktabelle
   #   $pid            Id des zu korrigierenden Termins
   #   $termkor        zu korrigierende Daten des Termins
   #                   in Form eines vollstaendigen Termin-Arrays (ohne COL_PID)
   #   Rueckgabe:      leer, falls der Termin korrigiert wurde
   #                   Fehlermeldung (in rot), falls das Update fehlschlug
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #   benutzte functions:
   #      self::kal_datum_standard_mysql($datum)
   #      kal_termine_config::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      self::kal_select_termin_by_pid($pid)
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   if(count($termin)<=0) return '<span class="kal_fail">Der Termin ist nicht vorhanden</span>';
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardform der zu korrigierenden Termindaten herstellen
   $korterm=self::kal_standard_termin($termkor);
   #
   # --- Terminparameter einzeln in einer Schleife aktualisieren
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   $sql->setWhere(COL_PID.'='.$pid);
   for($i=0;$i<count($korterm);$i=$i+1):
      $key=$keys[$i];
      if($key==COL_PID) continue;
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
   for($i=1;$i<count($termkor);$i=$i+1):
      $key=$keys[$i];
      if($key==COL_PID) continue;
      if($termsel[$key]==$korterm[$key]) continue;
      $error='<span class="kal_fail">Termin "'.$termin[COL_NAME].'" ('.$termin[COL_DATUM].
         ') konnte nicht korrigiert werden</span>';
      break;
      endfor;
   return $error;
   }
public static function kal_copy_termin($pid,$datumneu) {
   #   Kopieren eines Termins an ein neues Datum in der Datenbanktabelle,
   #   bis auf das Datum werden alle Termindaten uebernommen
   #   $pid            Id des zu kopierenden Termins
   #   $datumneu       Datum des zu kopierenden Termins
   #   Rueckgabe:      Id des kopierten Termins,
   #                   falls das Kopieren fehlschlug:
   #                   - Termin nicht vorhanden (falsche Id):  Fehlerstring (in rot)
   #                   - zu kopierender Termin schon vorhanden: -$pidneu
   #                     ($pidneu = Id des schon vorhandenen Termins)
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
     return '<span class="kal_fail">Der Termin ('.COL_PID.'='.$pid.') ist nicht vorhanden</span>';
   #
   # --- neues Termin-Array durch Kopie und das Datum aendern
   $termneu=$termin;
   $termneu[COL_DATUM]=kal_termine_kalender::kal_standard_datum($datumneu);
   #
   # --- den neuen Termin eintragen
   $pidneu=self::kal_insert_termin($termneu);
   return $pidneu;
   }
#
#----------------------------------------- Auslesen von Termindaten
public static function kal_select_termin_by_pid($pid) {
   #   Auslesen und Rueckgabe eines Termins aus der Datenbanktabelle
   #   $pid            Id des Termins
   #   benutzte functions:
   #      self::kal_termin_mysql_standard($termin)
   #
   # --- aus der Datenbanktabelle auslesen
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $term=$sql->getArray('SELECT * FROM '.$table.' WHERE '.COL_PID.'='.$pid);
   #
   # --- Wandeln der Datums- und Zeitformate
   if(!empty($term)) return self::kal_termin_mysql_standard($term[0]);
   return $term;
   }
public static function kal_select_termine($von,$bis,$kategorie,$stichwort) {
   #   Auslesen von Terminen aus der Datenbanktabelle,
   #   gefiltert durch Datumsbereich, Kategorie und Stichwort
   #   Rueckgabe als Array von Terminen (Indizierung bei 1 beginnend)
   #   $von            Datum des ersten Tages (Standardformat)
   #   $bis            Datum des letzten Tages (Standardformat)
   #   $kategorie      falls nicht leer: nur Termine der vorgegebenen Kategorie
   #   $stichwort      falls nicht leer: nur Termine, die das vorgegebene Stichwort
   #                   in einem folgenden Parameter enthalten:
   #                   [COL_NAME], [COL_AUSRICHTER], [COL_ORT], [COL_KOMM],
   #                   [COL_TEXT2], [COL_TEXT3], [COL_TEXT4], [COL_TEXT5]
   #   benutzte functions:
   #      self::kal_termin_mysql_standard($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   # --- Datumsbereich
   $vonsql=kal_termine_kalender::kal_standard_datum($von);
   $bissql=kal_termine_kalender::kal_standard_datum($bis);
   $vonsql=self::kal_datum_standard_mysql($vonsql);
   $bissql=self::kal_datum_standard_mysql($bissql);
   $wheredat=COL_DATUM.'>=\''.$vonsql.'\' and '.COL_DATUM.'<=\''.$bissql.'\'';
   #
   # --- Restriktion Kategorie
   if(!empty($kategorie)):
     $kat=kal_termine_config::kal_get_terminkategorien();
     for($i=1;$i<=count($kat);$i=$i+1):
        if($kategorie==$kat[$i]) $wherekat=COL_KATEGORIE.'=\''.$kategorie.'\'';
        endfor;
     endif;
   #
   # --- Restriktion Stichwort
   if(!empty($stichwort)):
     $wherestw='('.
        COL_NAME.      ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_AUSRICHTER.' LIKE \'%'.$stichwort.'%\' OR '.
        COL_ORT.       ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_KOMM.      ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_TEXT2.     ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_TEXT3.     ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_TEXT4.     ' LIKE \'%'.$stichwort.'%\' OR '.
        COL_TEXT5.     ' LIKE \'%'.$stichwort.'%\'   )';
     endif;
   #
   # --- alle Termine mit den vorgegebenen Restriktionen auslesen
   $where=$wheredat;
   if(!empty($wherekat)) $where=$where.' AND '.$wherekat;
   if(!empty($wherestw)) $where=$where.' AND '.$wherestw;
   $table=TAB_NAME;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $query='SELECT * FROM '.$table.' WHERE '.$where.' ORDER BY '.COL_DATUM;
   $term=$sql->getArray($query);
   #
   # --- Wandeln der Datums- und Zeitformate
   $termin=array();
   for($i=0;$i<count($term);$i=$i+1):
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      $termin[$i+1]=$term[$i];
      endfor;
   return $termin;
   }
public static function kal_get_tagestermine($datum,$termtyp) {
   #   Auslesen der aller Termindaten eines Tages aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $datum          Datum des Tages
   #   $termtyp        =SPIELTERM: aus den Spieldaten auslesen
   #                   ='':        aus der DB-Tabelle auslesen
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
public static function kal_get_wochentermine($von,$bis,$termtyp) {
   #   Auslesen der aller Termine einer Kalenderwoche aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $von            Datum des 1. Tages der Woche
   #   $bis            Datum des 7. Tages der Woche
   #   $termtyp        =SPIELTERM: aus den Spieldaten auslesen
   #                   ='':        aus der DB-Tabelle auslesen
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
public static function kal_get_monatstermine($von,$bis,$termtyp) {
   #   Auslesen der Termine eines Monats aus der
   #   zugehoerigen Datenbanktabelle bzw. aus den Spieldaten
   #   $von            Datum des ersten Tages des Monats
   #   $bis            Datum des letzten Tages des Monats
   #   $termtyp        =SPIELTERM: aus den Spieldaten auslesen
   #                   ='':        aus der DB-Tabelle auslesen
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
public static function kal_filter_termine_kategorie($termin,$kategorie) {
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
     $term=array();
     for($i=1;$i<=count($termin);$i=$i+1):
        if($termin[$i][COL_KATEGORIE]==$kategorie):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     endif;
   return $term;
   }
#
#----------------------------------------- Erzeugen von Spiel-Termindaten
public static function kal_get_spieldaten($datum) {
   #   Rueckgabe von kuenstlichen Termindaten eines Tages in Form eines
   #   assoziativen Termin-Arrays (leere Rueckgabe, falls an dem Tage keine
   #   Termine vorliegen)
   #   $datum          Datum des Tages (standardisiertes Datum)
   #   benutzte functions:
   #      kal_termine_kalender::kal_montag_vor($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_config::kal_get_terminkategorien()
   #
   # --- alle Wochentage um das Datum herum bestimmen
   $montag=kal_termine_kalender::kal_montag_vor($datum);
   $datumall=array();
   $datumall[1]=$montag;
   for($i=1;$i<=6;$i=$i+1)
      $datumall[$i+1]=kal_termine_kalender::kal_datum_vor_nach($montag,$i);
   #
   # --- Kategorien:
   $kat=kal_termine_config::kal_get_terminkategorien();
   #
   # --- Standard-Hinweissaetze
   $bl='            ';
   $stc='<tt>(Reine Spieldaten mit festen wöchentlich wiederkehrenden Terminen)</tt>';
   #
   # --- Link-URL
   if(empty($_GET['page'])):
     #  -  Frontend: echter externer Link
     $linkurl='https://www.brunsviga-kulturzentrum.de/de/kurs-sonstiges.html';
     else:
     #  -  Backend: Ruecklink auf die Beispiel-Startseite
     $url=$_SERVER['REQUEST_URI'];
     $arr=explode('?',$url);
     $url=$arr[0];
     $linkurl=$url.'?page='.$_GET['page'];
     endif;
   #
   # --- Setzen der (Wochen-)Termine
   $term=array();
   $term[1]=array(
      COL_PID=>117,
      COL_NAME=>'Abendtermin',
      COL_DATUM=>$datumall[1],
      COL_BEGINN=>'19:30',
      COL_ENDE=>'22:00',
      COL_AUSRICHTER=>'MTV Quadertal, Thomas Hörlinger, Tel. 05996 65432',
      COL_ORT=>'Landkreis-Sporthalle, Wallstraße, 38900 Quade',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[4],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[2]=array(
      COL_PID=>205,
      COL_NAME=>'Kadertraining',
      COL_DATUM=>$datumall[2],
      COL_BEGINN=>'10:30',
      COL_ENDE=>'12:00',
      COL_AUSRICHTER=>'Volker Meister, Tel. 0123 4567890',
      COL_ORT=>'Stützpunkt 1',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[3],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[3]=array(
      COL_PID=>226,
      COL_NAME=>'Hauptausschusssitzung',
      COL_DATUM=>$datumall[2],
      COL_BEGINN=>'19:30',
      COL_ENDE=>'',
      COL_AUSRICHTER=>'TT-Regionsverband Quaderland, Jochen Krause,Tel. 0124 3302456',
      COL_ORT=>'Gaststätte \'Grüne Wiese\', Wiesenstr. 79, 38990 Quadertal, Tel. 05996 88776655',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[2],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[4]=array(
      COL_PID=>183,
      COL_NAME=>'Kadertraining',
      COL_DATUM=>$datumall[4],
      COL_BEGINN=>'15:00',
      COL_ENDE=>'17:00',
      COL_AUSRICHTER=>'Volker Meister, Tel. 0123 4567890',
      COL_ORT=>'Stützpunkt 2',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[3],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[5]=array(
      COL_PID=>77,
      COL_NAME=>'Staffelsitzungen, Spielbereich Quaderberg',
      COL_DATUM=>$datumall[4],
      COL_BEGINN=>'',
      COL_ENDE=>'',
      COL_AUSRICHTER=>'Manfred Berger, Tel. 05992 1234567',
      COL_ORT=>'Sportheim Groß Brakel',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[1],
      COL_ZEIT2=>'18:00', COL_TEXT2=>'Damen: 1. Kreisklasse',
      COL_ZEIT3=>'18:30', COL_TEXT3=>'Herren: 1. Kreisklasse, 2. Kreisklasse',
      COL_ZEIT4=>'19:00', COL_TEXT4=>'Herren: 3. Kreisklasse, 4. Kreisklasse',
      COL_ZEIT5=>'19:30', COL_TEXT5=>'Herren: 5. Kreisklasse, 6. Kreisklasse');
   #
   $term[6]=array(
      COL_PID=>238,
      COL_NAME=>'Regionsmeisterschaften Seniorinnen/Senioren',
      COL_DATUM=>$datumall[5],
      COL_BEGINN=>'18:00',
      COL_ENDE=>'',
      COL_AUSRICHTER=>'TSV Quaderberg',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[1],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[7]=array(
      COL_PID=>239,
      COL_NAME=>'Regionsmeisterschaften Jugend und Schüler',
      COL_DATUM=>$datumall[6],
      COL_BEGINN=>'',
      COL_ENDE=>'',
      COL_AUSRICHTER=>'TSV Quaderberg',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[1],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[8]=array(
      COL_PID=>54,
      COL_NAME=>'Wochenendseminar',
      COL_DATUM=>$datumall[6],
      COL_BEGINN=>'16:00',
      COL_ENDE=>'18:30',
      COL_AUSRICHTER=>'TT-Regionsverband Quaderland, Jochen Krause, Tel. 05996 44001155',
      COL_ORT=>'Brunsviga-Kulturzentrum, Quader Ring 35, 38900 Quade',
      COL_LINK=>$linkurl,
      COL_KOMM=>$stc.'<br/>'.
         $bl.'Die Veranstaltung wendet sich an die Vereine in der Region. Es '.
         'werden Hinweise zur Gewinnung von Jugendlichen und Kindern für den Tischtennissport '.
         'gegeben, basierend auf der Kampagne \'Tischtennis: Spiel mit!\' des DTTB.<br/>'.
         $bl.'Diese Kampagne des DTTB und seiner Landesverbände fördert die '.
         'Kooperationen zwischen Vereinen und Schulen. Nutznießer sind in erster Linie die '.
         'Kinder und Jugendlichen. Diese haben durch die Kooperation die Möglichkeit, die '.
         'Sportart Tischtennis kennenzulernen und als ihre Sportart zu entdecken. Oberstes Ziel '.
         'von \'Tischtennis: Spiel mit!\' ist die Gewinnung und langfristige Bindung neuer '.
         'Mitglieder für die rund 10.000 Vereine in Deutschland.',
      COL_KATEGORIE=>$kat[4],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[9]=array(
      COL_PID=>240,
      COL_NAME=>'Regionsmeisterschaften Jugend und Schüler',
      COL_DATUM=>$datumall[7],
      COL_BEGINN=>'',
      COL_ENDE=>'',
      COL_AUSRICHTER=>'TSV Quaderberg',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATEGORIE=>$kat[1],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   # --- Auswahl der gewuenschten Termine
   $m=0;
   $termin=array();
   for($i=1;$i<=count($term);$i=$i+1):
      if($term[$i][COL_DATUM]==$datum):
        $m=$m+1;
        $termin[$m]=$term[$i];
        endif;
      endfor;
   return $termin;
   }
public static function kal_get_wochenspieldaten($montag) {
   #   Erzeugen von kuenstlichen Termindaten einer Woche
   #   $montag         Datum des ersten Tages (Montag)
   #   benutzte functions:
   #      self::kal_get_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   # --- Erstellen des Datums-Arrays der Woche
   $dat=array();
   $dat[1]=kal_termine_kalender::kal_standard_datum($montag);
   for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
   #
   # --- Tagestermine aller Tage der Woche
   $m=0;
   $termin=array();
   for($i=1;$i<=count($dat);$i=$i+1):
      $term=self::kal_get_spieldaten($dat[$i]);
      for($k=1;$k<=count($term);$k=$k+1):
         $m=$m+1;
         $termin[$m]=$term[$k];
         endfor;
      endfor;
   return $termin;
   }
public static function kal_get_monatsspieldaten($erster) {
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
   $termin=array();
   for($k=1;$k<=$mtage[intval($mon)];$k=$k+1):
      $term=self::kal_get_spieldaten($datum);
      for($i=1;$i<=count($term);$i=$i+1):
         $m=$m+1;
         $termin[$m]=$term[$i];
         endfor;
      $datum=kal_termine_kalender::kal_datum_vor_nach($datum,1);
      endfor;
   return $termin;
   }
}
?>
