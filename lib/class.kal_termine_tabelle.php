<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2020
*/
define ('SPIEL_KATID', 99990);   // Kategorie-Ids der Spieldaten beginnen bei 99991
#
class kal_termine_tabelle {
#
#----------------------------------------- Inhaltsuebersicht
#   Basis-Funktionen
#         kal_datum_mysql_standard($datum)
#         kal_datum_standard_mysql($datum)
#         kal_standard_termin($termin)
#         kal_standard_termin_intern($value,$cols)
#         kal_termin_mysql_standard($termin)
#   SQL-Grundfunktionen
#         kal_select_termin_by_pid($pid)
#         kal_exist_termin($termin)
#         kal_insert_termin($termin)
#         kal_delete_termin($pid)
#         kal_update_termin($pid,$termkor)
#   Auslesen von Termindaten
#         kal_kategorie_name($katid)
#         kal_select_termine($von,$bis,$katid)
#         kal_vorherige_wiederholungstermine($von,$bis,$katid)
#         kal_vorherige_folgetermine($von,$bis,$katid)
#         kal_interne_wiederholungstermine($termin,$von,$bis,$katid)
#         kal_interne_folgetermine($termin,$von,$bis,$katid)
#         kal_get_termine($von,$bis,$katid,$kontif)
#   Erzeugen/Zusammenstellen von Spiel-Termindaten
#         kal_get_spielkategorien()
#         kal_set_spieldaten($datum)
#         kal_get_spieltermine($von,$bis,$katid)
#
#----------------------------------------- Basis-Funktionen
public static function kal_datum_mysql_standard($datum) {
   #   Umformatieren eines MySQL-Datumsstrings 'yyyy-mm-tt'
   #   in einen Standard-Datumsstring 'tt.mm.yyyy'
   #
   return substr($datum,8,2).'.'.substr($datum,5,2).'.'.substr($datum,0,4);
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
public static function kal_termin_mysql_standard($termin) {
   #   Standardisierung von Datums- und Zeitangaben sowie Integer eines Termin-Arrays.
   #   Dabei wird so gewandelt:
   #   - Datumsangaben: 'yyyy-mm-tt' nach 'tt.mm.yyyy'
   #   - Zeitangaben: 'hh:mm:ss'     nach 'hh:mm'
   #   - Zeitangaben: '00:00:00'     nach '' (leer)
   #   - Integer:     '' (leer)      nach 0
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
      if(substr($type,0,3)=='int' and empty($val)) $val=intval($val);
      $term[$key]=$val;
      endfor;
   return $term;
   }
public static function kal_standard_termin($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   - Integer '' nach 0
   #   $termin         eingegebenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_standard_termin_intern($value,$cols)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben sowie leere Integer des eingegebenen Termins standardisieren
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
   #   - Integer '' nach 0
   #   $value          eingegebenes Array
   #                   das standardisierte Array wird zurueck gegeben
   #   $cols           Array der Daten zu den Kalender-Tabellenspalten
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_standard_uhrzeit($uhrz)
   #
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben sowie leere Integer des eingegebenen Arrays standardisieren
   $val=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $tk='';
      if(!empty($value[$i])) $tk=$value[$i];
      $type=$cols[$key][0];
      $arr=explode(' ',$type);
      $type=$arr[0];
      if($type=='date' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_datum($tk);
      if($type=='time' and !empty($tk)) $tk=kal_termine_kalender::kal_standard_uhrzeit($tk);
      if(substr($type,0,3)=='int' and empty($tk)) $tk=intval($tk);
      $val[$i]=$tk;
      endfor;
   return $val;
   }
#
#----------------------------------------- SQL-Grundfunktionen
public static function kal_select_termin_by_pid($pid) {
   #   Auslesen und Rueckgabe eines Termins aus der Datenbanktabelle
   #   $pid            Id des Termins
   #   benutzte functions:
   #      self::kal_termin_mysql_standard($termin)
   #
   # --- aus der Datenbanktabelle auslesen
   $sql=rex_sql::factory();
   $sql->setTable(TAB_NAME);
   $term=$sql->getArray('SELECT * FROM '.TAB_NAME.' WHERE '.COL_PID.'='.$pid);
   #
   # --- Wandeln der Datums- und Zeitformate
   if(count($term)>0) return self::kal_termin_mysql_standard($term[0]);
   return array();
   }
public static function kal_exist_termin($termin) {
   #   Suchen eines Termins in der Datenbanktabelle, falls der Termin
   #   gefunden wird, wird dessen Id ($termin[COL_PID]) zurueckgegeben
   #   ('gefunden' heisst: alle Parameter ausser der Id stimmen ueberein)
   #   $termin         gegebenes Termin-Array
   #   benutzte functions
   #      self::kal_standard_termin($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      self::kal_termin_mysql_standard($termin)
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
   #   Rueckgabe:      Eintragung erfolgreich:   Id des neuen Termins (COL_PID>0)
   #                   andernfalls:              Fehlermeldung
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
   if($pid>0)
     return '<span class="kal_fail">Dieser Termin ist schon vorhanden: pid='.$pid.'</span>';
   #
   # --- Terminparameter einzeln in einer Schleife einfuegen
   $cols=kal_termine_config::kal_define_tabellenspalten();
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
   $sql=rex_sql::factory();
   #
   # --- Pruefen, ob der Termin da ist
   $arr=$sql->getArray('SELECT * FROM '.TAB_NAME.' WHERE '.COL_PID.'='.$pid);
   if(count($arr)<=0)
     return '<span class="kal_fail">Termin nicht vorhanden</span>';
   #
   # --- Durchfuehrung
   $sql->setTable(TAB_NAME);
   $sql->setWhere(array(COL_PID=>$pid));
   $sql->delete();
   #
   # --- Pruefen, ob der Termin wirklich weg ist
   $arr=$sql->getArray('SELECT * FROM '.TAB_NAME.' WHERE '.COL_PID.'='.$pid);
   if(count($arr)>0)
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
   #      self::kal_select_termin_by_pid($pid)
   #      self::kal_standard_termin($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      kal_termine_config::kal_define_tabellenspalten()
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   if(count($termin)<=0)
     return '<span class="kal_fail">Der Termin ist nicht vorhanden</span>';
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
   for($i=1;$i<count($termkor);$i=$i+1):
      $key=$keys[$i];
      if($key==COL_PID) continue;
      if($termsel[$key]==$korterm[$key]) continue;
      return '<span class="kal_fail">Termin "'.$termin[COL_NAME].'" ('.$termin[COL_DATUM].
         ') konnte nicht korrigiert werden</span>';
      break;
      endfor;
   }
#
#----------------------------------------- Auslesen von Termindaten
public static function kal_kategorie_name($katid) {
   #   Ermitteln der Kategoriebezeichnung aus der Kategorie-Id. Leere Rueckgabe,
   #   falls die eingegebene Id keiner definierten Kategorie entspricht.
   #   $katid          Kategorie-Id
   #   benutzte functions:
   #      self::kal_get_spielkategorien()
   #      kal_termine_config::kal_get_terminkategorien()
   #
   if($katid<=0) return;
   #
   # --- Kategorien der Spieltermine / konfigurierte Kategorien
   if($katid<=SPIEL_KATID):
     $kat=kal_termine_config::kal_get_terminkategorien();
     else:
     $kat=self::kal_get_spielkategorien();
     endif;
   for($i=0;$i<count($kat);$i=$i+1) if($kat[$i]['id']==$katid) return $kat[$i]['name'];
   }
public static function kal_select_termine($von,$bis,$katid) {
   #   Auslesen von Terminen aus der Datenbanktabelle,
   #   gefiltert durch Datumsbereich und Kategorie
   #   Rueckgabe als Array von Terminen (Indizierung bei 1 beginnend)
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: vom ersten eingetragenen Termin an
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: bis inkl. dem letzten eingetragenen Termin
   #   $katid          falls >0: nur Termine mit dieser Kategorie-Id
   #   benutzte functions:
   #      self::kal_datum_standard_mysql($datum)
   #      self::kal_termin_mysql_standard($termin)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   $stdvon=kal_termine_kalender::kal_standard_datum($von);
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Datumsbereich
   $vonsql=self::kal_datum_standard_mysql($stdvon);
   $bissql=self::kal_datum_standard_mysql($stdbis);
   $where=COL_DATUM.'>=\''.$vonsql.'\'';
   if(!empty($bis) and $bissql!='0000-00-00') $where=$where.' AND '.COL_DATUM.'<=\''.$bissql.'\'';
   #
   # --- Restriktion Kategorie
   if($katid>0) $where=$where.' AND '.COL_KATID.'='.$katid;
   #
   # --- alle Termine mit den vorgegebenen Restriktionen auslesen
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.TAB_NAME.' WHERE '.$where.' ORDER BY '.COL_DATUM;
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
public static function kal_vorherige_wiederholungstermine($von,$bis,$katid) {
   #   Rueckgabe von woechentlich wiederkehrenden Terminen (Wiederholungstermine,
   #   $term[$i][COL_WOCHEN]>0) einer Kategorie oder aller Kategorien, deren Basistermine
   #   vor einem vorgegebenen Datumsbereich liegen, die aber in den Datumsbereich
   #   hineinfallen. Diese Termine sind als einfache, nicht wiederkehrende Termine
   #   definiert ($term[$i][COL_WOCHEN]=0) und nicht nach Datum sortiert. - Es wird
   #   beruecksichtigt, dass zu Spieldaten keine Wiederholungstermine definiert sind.
   #   $von            Datum des ersten Tages im Standardformat
   #   $bis            Datum des letzten Tages im Standardformat
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   benutzte functions:
   #      self::kal_select_termine($von,$bis,$katid)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_datumsdifferenz($datum1,$datum2)
   #
   if($katid>=SPIEL_KATID) return array();   // Spieldaten ohne Wiederholungstermine
   #
   $stdvon=kal_termine_kalender::kal_standard_datum($von);
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Datenbanktermine (ALLE vor $von)
   $vorbis=kal_termine_kalender::kal_datum_vor_nach($stdvon,-1);
   $ter=self::kal_select_termine('',$vorbis,$katid);
   $m=0;
   $vorterm=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      if($ter[$i][COL_WOCHEN]<=0) continue;   // kein Wiederholungstermin
      $m=$m+1;
      $vorterm[$m]=$ter[$i];
      endfor;
   #
   # --- die Termine heraussuchen, die in den Datumsbereich hineinfallen
   $termin=array();
   $m=0;
   for($i=1;$i<=count($vorterm);$i=$i+1):
      $term=$vorterm[$i];
      $datum=$term[COL_DATUM];
      for($k=1;$k<=$term[COL_WOCHEN];$k=$k+1):
         $datneu=kal_termine_kalender::kal_datum_vor_nach($datum,$k*7);
         if(kal_termine_kalender::kal_datumsdifferenz($datneu,$stdvon)>0) continue; // zu frueh
         if(kal_termine_kalender::kal_datumsdifferenz($stdbis,$datneu)>0) continue; // zu spaet
         $m=$m+1;
         $termin[$m]=$term;
         $termin[$m][COL_DATUM]=$datneu;
         $termin[$m][COL_WOCHEN]=0;   // diese Termine haben keine Wiederholungstermine mehr
         endfor;
      endfor;
   return $termin;
   }
public static function kal_vorherige_folgetermine($von,$bis,$katid) {
   #   Rueckgabe von Terminen ueber mehrere Tage einer Kategorie oder aller Kategorien
   #   (Folgetermine, $term[$i][COL_TAGE]>1), deren Basistermine vor einem vorgegebenen
   #   Datumsbereich liegen, die aber in den Datumsbereich hineinfallen. Diese Termine
   #   sind als einfache Termine ohne Folgetermine definiert ($term[$i][COL_TAGE]=1)
   #   und nicht nach Datum sortiert.
   #   $von            Datum des ersten Tages im Standardformat
   #   $bis            Datum des letzten Tages im Standardformat
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   benutzte functions:
   #      self::kal_set_spieldaten($datum)
   #      self::kal_select_termine($von,$bis,$katid)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_datumsdifferenz($datum1,$datum2)
   #
   $stdvon=kal_termine_kalender::kal_standard_datum($von);
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- zunaechst alle Termine ueber mehrere Tage vor $von
   if($katid>=SPIEL_KATID):
     #     Spieltermine (ab 7 Tage vor $von)
     $mtage=7;
     $datum=kal_termine_kalender::kal_datum_vor_nach($stdvon,-$mtage);
     $m=0;
     $vorterm=array();
     for($k=1;$k<=$mtage;$k=$k+1):
        $ter=self::kal_set_spieldaten($datum);
        for($i=1;$i<=count($ter);$i=$i+1):
           if($ter[$i][COL_TAGE]<=1) continue;   // kein Termin mit Folgeterminen
           if($ter[$i][COL_KATID]!=$katid and $katid>SPIEL_KATID) continue;   // falsche Kategorie
           $m=$m+1;
           $vorterm[$m]=$ter[$i];
           endfor;
        $datum=kal_termine_kalender::kal_datum_vor_nach($datum,1);
        endfor;
     else:
     #     Datenbanktermine (alle vor $von)
     $vorbis=kal_termine_kalender::kal_datum_vor_nach($stdvon,-1);
     $ter=self::kal_select_termine('',$vorbis,$katid);
     $m=0;
     $vorterm=array();
     for($i=1;$i<=count($ter);$i=$i+1):
        if($ter[$i][COL_TAGE]<=1) continue;   // kein Termin mit Folgeterminen
        $m=$m+1;
        $vorterm[$m]=$ter[$i];
        endfor;
     endif;
   #
   # --- die Termine heraussuchen, die in den Datumsbereich hineinfallen
   $termin=array();
   $m=0;
   for($i=1;$i<=count($vorterm);$i=$i+1):
      $term=$vorterm[$i];
      $datum=$term[COL_DATUM];
      for($k=2;$k<=$term[COL_TAGE];$k=$k+1):
         $datneu=kal_termine_kalender::kal_datum_vor_nach($datum,$k-1);
         if(kal_termine_kalender::kal_datumsdifferenz($datneu,$stdvon)>0) continue; // zu frueh
         if(kal_termine_kalender::kal_datumsdifferenz($stdbis,$datneu)>0) continue; // zu spaet
         $m=$m+1;
         $termin[$m]=$term;
         #     Termin als Einzeltermin umwandeln
         $termin[$m][COL_TAGE]=1;
         $termin[$m][COL_DATUM]=$datneu;
         #     Beginn und Ende des jetzigen einfachen Termins anpassen
         $termin[$m][COL_BEGINN]='';
         $termin[$m][COL_ENDE]='';
         if($k==$term[COL_TAGE]) $termin[$m][COL_ENDE]=$term[COL_ENDE];
         endfor;
      endfor;
   return $termin;
   }
public static function kal_interne_wiederholungstermine($termin,$von,$bis,$katid) {
   #   Rueckgabe aller woechentlich wiederkehrenden Termine (Wiederholungstermine,
   #   $term[$i][COL_WOCHEN]>0) einer Kategorie oder aller Kategorien, die in einem
   #   Termin-Array enthalten sind und innerhalb eines vorgegebenen Datumsbereichs
   #   liegen. Der jeweiligen Basistermine fuer die Wiederholungstermine gehoeren
   #   nicht dazu. Diese Termine sind als nicht wiederkehrende Termine definiert
   #   ($term[$i][COL_WOCHEN]=0) und nicht nach Datum sortiert. - Es wird berueck-
   #   sichtigt, dass zu Spieldaten keine Wiederholungstermine definiert sind.
   #   $termin         Array der Termine
   #   $von            Datum des ersten Tages im Standardformat
   #   $bis            Datum des letzten Tages im Standardformat
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum1,$anztage)
   #      kal_termine_kalender::kal_datumsdifferenz($datum1,$datum2)
   #
   if(count($termin)<=0) return $termin;
   if($termin[1][COL_KATID]>=SPIEL_KATID) return array();   // keine Wiederholungstermine bei Spieldaten
   #
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Auswahl der wiederkehrenden Termine
   $term=array();
   $m=0;
   for($i=1;$i<=count($termin);$i=$i+1):
      $ter=$termin[$i];
      if($ter[COL_WOCHEN]<=0) continue;   // kein Wiederholungstermin
      if($ter[COL_KATID]!=$katid and $katid>0) continue;   // falsche Kategorie
      $datum=$ter[COL_DATUM];
      for($k=1;$k<=$ter[COL_WOCHEN];$k=$k+1):
         $datneu=kal_termine_kalender::kal_datum_vor_nach($datum,$k*7);
         if(kal_termine_kalender::kal_datumsdifferenz($datneu,$stdbis)<0) continue; // zu spaet
         $m=$m+1;
         $term[$m]=$ter;
         $term[$m][COL_DATUM]=$datneu;
         $term[$m][COL_WOCHEN]=0;   // diese Termine haben keine Wiederholungstermine mehr
         endfor;
      endfor;
   return $term;
   }
public static function kal_interne_folgetermine($termin,$von,$bis,$katid) {
   #   Rueckgabe von Terminen einer Kategorie oder aller Kategorien ueber mehrere
   #   Tage (Folgetermine, $term[$i][COL_TAGE]>1), die in einem Termin-Array enthalten
   #   sind und innerhalb eines vorgegebenen Datumsbereichs liegen. Die jeweiligen
   #   Basistermine fuer die Folgetermine gehoeren nicht dazu. Diese Termine sind als
   #   einfache Termine ohne Folgetermine definiert ($term[$i][COL_TAGE]=1) und nicht
   #   nach Datum sortiert.
   #   $termin         Array der Termine
   #   $von            Datum des ersten Tages im Standardformat
   #   $bis            Datum des letzten Tages im Standardformat
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum1,$anztage)
   #      kal_termine_kalender::kal_datumsdifferenz($datum1,$datum2)
   #
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   $m=0;
   $term=array();
   for($i=1;$i<=count($termin);$i=$i+1):
      $ter=$termin[$i];
      if($ter[COL_TAGE]<=1) continue;   // kein Termin ueber mehrere Tage
      #     falsche Kategorie
      if($katid<SPIEL_KATID):
        if($ter[COL_KATID]!=$katid and $katid>0) continue;   // DB-Daten
        ;else:
        if($ter[COL_KATID]!=$katid and $katid>SPIEL_KATID) continue;   // Spieldaten
        endif;
      $datum=$ter[COL_DATUM];
      for($k=2;$k<=$ter[COL_TAGE];$k=$k+1):
         $datneu=kal_termine_kalender::kal_datum_vor_nach($datum,$k-1);
         if(kal_termine_kalender::kal_datumsdifferenz($datneu,$stdbis)<0) continue; // zu spaet
         $m=$m+1;
         $term[$m]=$ter;
         #     Termin als Einzeltermin umwandeln
         $term[$m][COL_TAGE]=1;
         $term[$m][COL_DATUM]=$datneu;
         #     Beginn und Ende des jetzigen einfachen Termins anpassen
         $term[$m][COL_BEGINN]='';
         $term[$m][COL_ENDE]='';
         if($k==$ter[COL_TAGE]) $term[$m][COL_ENDE]=$ter[COL_ENDE];
         endfor;
      endfor;
   return $term;
   }
public static function kal_get_termine($von,$bis,$katid,$kontif) {
   #   Rueckgabe der Termine einer Kategorie oder aller Kategorien eines Datumsbereichs:
   #   - Wiederholungstermine aus (Basis-)Terminen vor dem vorgegebenen Datumsbereich
   #   - Folgetermine aus (Basis-)Terminen vor dem vorgegebenen Datumsbereich
   #   - 'einfache' Termine und Basistermine fuer Wiederholungen und Folgetermine
   #     im vorgegebenen Datumsbereich
   #   - interne Wiederholungstermine im vorgegebenen Datumsbereich
   #   - interne Folgetermine im vorgegebenen Datumsbereich
   #   Alle zurueck gegebenen Termine sind einfache Termine, d.h. ohne Wiederholungen
   #   und ohne Folgetermine.
   #   $von            Datum des ersten Tages
   #   $bis            Datum des letzten Tages
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   $kontif         >0:  Termine mit internen Folgeterminen werden aufgeloest zu mehreren
   #                        Einzelterminen (gebraucht in den Kalendermenues)
   #                   <=0: entsprechende Termine werden NICHT aufgeloest
   #                        (gebraucht nur in der Terminliste)
   #   benutzte functions:
   #      self::kal_vorherige_wiederholungstermine($von,$bis,$katid)
   #      self::kal_vorherige_folgetermine($von,$bis,$katid)
   #      self::kal_get_spieltermine($stvon,$stbis,$katid)
   #      self::kal_select_termine($von,$bis,$katid)
   #      self::kal_interne_wiederholungstermine($termin,$von,$bis,$katid)
   #      self::kal_interne_folgetermine($termin,$von,$bis,$katid)
   #      self::kal_datum_standard_mysql($datum)
   #      self::kal_datum_mysql_standard($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   # --- Standardisierung der Eingangs-Datumsangaben
   $stvon=kal_termine_kalender::kal_standard_datum($von);
   $stbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- vorherige Wiederholungstermine
   $term1=self::kal_vorherige_wiederholungstermine($stvon,$stbis,$katid);
   #
   # --- vorherige Wiederholungstermine
   $term2=self::kal_vorherige_folgetermine($stvon,$stbis,$katid);
   #
   # --- 'einfache' Termine und Basistermine fuer Wiederholungen und Folgetermine
   if($katid>=SPIEL_KATID):
     $term3=self::kal_get_spieltermine($stvon,$stbis,$katid);   // Spieldaten
     else:
     $term3=self::kal_select_termine($stvon,$stbis,$katid);   // Datenbankdaten
     endif;
   #
   # --- interne Wiederholungstermine
   $term4=self::kal_interne_wiederholungstermine($term3,$stvon,$stbis,$katid);
   #
   # --- interne Folgetermine
   if($kontif>0) $term5=self::kal_interne_folgetermine($term3,$stvon,$stbis,$katid);
   #
   # --- Korrekturen der 'einfache' Termine und Basistermine
   #     Endzeit des ersten Tages von Folgeterminen anpassen
   for($i=1;$i<=count($term3);$i=$i+1)
      if($kontif>0 and $term3[$i][COL_TAGE]>=2) $term3[$i][COL_ENDE]='';
   #     Wiederholungen und Folgetermine entfernen
   for($i=1;$i<=count($term3);$i=$i+1):
      if($kontif>0) $term3[$i][COL_TAGE]=1;
      $term3[$i][COL_WOCHEN]=0;
      endfor;
   #
   # --- Zusammensetzen der Arrays
   $m=0;
   $termin=array();
   for($i=1;$i<=count($term1);$i=$i+1):
      $m=$m+1;
      $termin[$m]=$term1[$i];
      endfor;
   for($i=1;$i<=count($term2);$i=$i+1):
      $m=$m+1;
      $termin[$m]=$term2[$i];
      endfor;
   for($i=1;$i<=count($term3);$i=$i+1):
      $m=$m+1;
      $termin[$m]=$term3[$i];
      endfor;
   for($i=1;$i<=count($term4);$i=$i+1):
      $m=$m+1;
      $termin[$m]=$term4[$i];
      endfor;
   if($kontif>0)
     for($i=1;$i<=count($term5);$i=$i+1):
        $m=$m+1;
        $termin[$m]=$term5[$i];
        endfor;
   #
   # --- Termin-Array fuer Sortierung nach Datum aufbereiten: $termin --> $dat
   $dat=array();
   for($i=1;$i<=count($termin);$i=$i+1):
      $datum=$termin[$i][COL_DATUM];
      $datsql=self::kal_datum_standard_mysql($datum);
      $dat1=array($datsql.':'.$i=>$termin[$i]);
      $dat=array_merge($dat,$dat1);
      endfor;
   #
   # --- Sortierung nach Datum
   ksort($dat);
   #
   # --- sortiertes Array wieder in ein Termin-Array umwandeln: $dat --> $term
   if(count($dat)<=0) return array();
   $keys=array_keys($dat);
   $term=array();
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $arr=explode(':',$key);
      $datsql=$arr[0];
      $k=$i+1;
      $term[$k]=$dat[$key];
      $term[$k][COL_DATUM]=self::kal_datum_mysql_standard($datsql);
      endfor;
   return $term;
   }
#
#----------------------------------------- Erzeugen von Spiel-Termindaten
public static function kal_get_spielkategorien() {
   #   Rueckgabe der zu den kuenstlichen Termindaten passenden Terminkategorien
   #   in Form eines nummerierten Arrays
   #
   return array(
      array('id'=>SPIEL_KATID+1, 'name'=>'Gemeinde'),
      array('id'=>SPIEL_KATID+2, 'name'=>'Tischtennisverband'),
      array('id'=>SPIEL_KATID+3, 'name'=>'Kulturkreis'));
   }
public static function kal_set_spieldaten($datum) {
   #   Rueckgabe von kuenstlichen Termindaten eines Tages in Form eines
   #   assoziativen Termin-Arrays (leere Rueckgabe, falls an dem Tage keine
   #   Termine vorliegen)
   #   $datum          Datum des Tages (standardisiertes Datum)
   #   benutzte functions:
   #      self::kal_get_spielkategorien()
   #      kal_termine_kalender::kal_montag_vor($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   # --- alle Wochentage um das Datum herum bestimmen
   $montag=kal_termine_kalender::kal_montag_vor($datum);
   $datumall=array();
   $datumall[1]=$montag;
   for($i=1;$i<=6;$i=$i+1)
      $datumall[$i+1]=kal_termine_kalender::kal_datum_vor_nach($montag,$i);
   #
   # --- Standard-Hinweissaetze
   $stc='<tt>Spieldaten, wöchentlich wiederkehrender Termin</tt>';
   $std='<tt>Spieldaten, wöchentlich wiederkehrender Termin über 2 Tage</tt>';
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
   # --- Ids der Spielkategorien
   $kats=self::kal_get_spielkategorien();
   for($i=0;$i<count($kats);$i=$i+1):
      $k=$i+1;
      $katid[$k]=$kats[$i]['id'];
      endfor;
   #
   # --- Setzen der (Wochen-)Termine
   $term=array();
   $term[1]=array(
      COL_PID=>117,
      COL_NAME=>'Katharina Luther - Filmvorführung und Diskussion',
      COL_DATUM=>$datumall[1],
      COL_BEGINN=>'19:30',
      COL_ENDE=>'22:00',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Kulturkreis Quadertal, Thomas Hörlinger, Tel. 05996 65432',
      COL_ORT=>'Dorfgemeinschaftshaus, Wallstraße, 38900 Quade',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[3],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[2]=array(
      COL_PID=>205,
      COL_NAME=>'Tischtennistraining 1 Kreiskader',
      COL_DATUM=>$datumall[2],
      COL_BEGINN=>'10:30',
      COL_ENDE=>'12:00',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Volker Meister, Tel. 0123 4567890',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[2],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[3]=array(
      COL_PID=>226,
      COL_NAME=>'Gemeinderatsssitzung',
      COL_DATUM=>$datumall[2],
      COL_BEGINN=>'19:30',
      COL_ENDE=>'',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Rat der Gemeinde Quaderland, Jochen Krause, Tel. 0124 3302456',
      COL_ORT=>'Gaststätte \'Grüne Wiese\', Wiesenstr. 79, 38990 Quadertal, Tel. 05996 88776655',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[1],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[4]=array(
      COL_PID=>183,
      COL_NAME=>'Tischtennistraining 2 Kreiskader',
      COL_DATUM=>$datumall[4],
      COL_BEGINN=>'15:00',
      COL_ENDE=>'17:00',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Volker Meister, Tel. 0123 4567890',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[2],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[5]=array(
      COL_PID=>77,
      COL_NAME=>'Staffelsitzungen, TT-Kreisverband Quaderberg',
      COL_DATUM=>$datumall[4],
      COL_BEGINN=>'',
      COL_ENDE=>'',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Manfred Berger, Tel. 05992 1234567',
      COL_ORT=>'Sportheim Groß Brakel',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[2],
      COL_ZEIT2=>'18:00', COL_TEXT2=>'Damen: 1. Kreisklasse',
      COL_ZEIT3=>'18:30', COL_TEXT3=>'Herren: 1. Kreisklasse, 2. Kreisklasse',
      COL_ZEIT4=>'19:00', COL_TEXT4=>'Herren: 3. Kreisklasse, 4. Kreisklasse',
      COL_ZEIT5=>'19:30', COL_TEXT5=>'Herren: 5. Kreisklasse, 6. Kreisklasse');
   #
   $term[6]=array(
      COL_PID=>238,
      COL_NAME=>'Busfahrt in den Harz',
      COL_DATUM=>$datumall[5],
      COL_BEGINN=>'10:00',
      COL_ENDE=>'19:00',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Seniorenkreis Quaderberg',
      COL_ORT=>'Start um 10:00 Uhr ab Kirche Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$stc,
      COL_KATID=>$katid[1],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[7]=array(
      COL_PID=>54,
      COL_NAME=>'Die Maler der \'Brücke\' - Die Werke von Karl Schmidt-Rottluff',
      COL_DATUM=>$datumall[6],
      COL_BEGINN=>'16:00',
      COL_ENDE=>'18:30',
      COL_TAGE=>1,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'Kulturkreis Quaderland, Ulf Schneider, Tel. 05996 356702',
      COL_ORT=>'Brunsviga-Kulturzentrum, Quader Ring 35, 38900 Quade',
      COL_LINK=>$linkurl,
      COL_KOMM=>$stc,
      COL_KATID=>$katid[3],
      COL_ZEIT2=>'', COL_TEXT2=>'',      COL_ZEIT3=>'', COL_TEXT3=>'',
      COL_ZEIT4=>'', COL_TEXT4=>'',      COL_ZEIT5=>'', COL_TEXT5=>'');
   #
   $term[8]=array(
      COL_PID=>239,
      COL_NAME=>'Regionsmeisterschaften Jugend und Schüler',
      COL_DATUM=>$datumall[6],
      COL_BEGINN=>'',
      COL_ENDE=>'',
      COL_TAGE=>2,
      COL_WOCHEN=>0,
      COL_AUSRICHTER=>'TSV Quaderberg',
      COL_ORT=>'Halle am Sportpark, 38985 Quaderberg',
      COL_LINK=>'',
      COL_KOMM=>$std,
      COL_KATID=>$katid[2],
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
public static function kal_get_spieltermine($von,$bis,$katid) {
   #   Rueckgabe von Spielterminen einer Kategorie bzw. aller Kategorien in einem
   #   Datumsbereich ohne Beruecksichtigung von Folgeterminen als Array von
   #   Terminen (Indizierung bei 1 beginnend).
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =SPIEL_KATID: Termine aller Kategorien
   #   benutzte functions:
   #      self::kal_set_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datumsdifferenz($datum1,$datum2)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #
   $stvon=kal_termine_kalender::kal_standard_datum($von);
   $stbis=kal_termine_kalender::kal_standard_datum($bis);
   $term=array();
   $m=0;
   $datum=$stvon;
   $mtage=kal_termine_kalender::kal_datumsdifferenz($stvon,$stbis)+1;
   for($k=1;$k<=$mtage;$k=$k+1):
      $ter=self::kal_set_spieldaten($datum);
      for($i=1;$i<=count($ter);$i=$i+1):
         if($ter[$i][COL_KATID]!=$katid and $katid>SPIEL_KATID) continue;
         $m=$m+1;
         $term[$m]=$ter[$i];
         endfor;
      $datum=kal_termine_kalender::kal_datum_vor_nach($datum,1);
      if(kal_termine_kalender::kal_datumsdifferenz($datum,$stbis)<0) break;
      endfor;
   return $term;
   }
}
?>
