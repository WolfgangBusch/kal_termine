<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
class kal_termine_tabelle {
#
#----------------------------------------- Inhaltsuebersicht
#   Basis-Funktionen
#      kal_datum_mysql_standard($datum)
#      kal_datum_standard_mysql($datum)
#      kal_termin_mysql_standard($termin)
#      kal_standard_termin_intern($value,$cols)
#      kal_standard_termin($termin)
#   SQL-Grundfunktionen
#      kal_select_termin_by_pid($pid)
#      kal_exist_termin($termin)
#      kal_insert_termin($termin)
#      kal_delete_termin($pid)
#      kal_update_termin($pid,$termkor)
#   Auslesen von Termindaten
#      kal_kategorie_name($katid)
#      kal_select_termine($von,$bis,$katids)
#      kal_expand_multitermin($termin,$keynr)
#      kal_collect_termine($von,$bis,$katids,$kontif)
#      kal_sort_termine($termin)
#      kal_get_termine_all($von,$bis,$katids,$kontif)
#      kal_subst_wiederholungstermine($termin)
#      kal_get_termine($von,$bis,$katids,$kontif)
#   Erzeugen/Zusammenstellen von Spiel-Termindaten
#      kal_get_spielkategorien()
#      kal_set_spieldaten($datum)
#      kal_get_spieltermine($von,$bis,$katids)
#      kal_aktuelle_wochen_spieltermine()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
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
   #      $addon::kal_define_tabellenspalten()
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
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
public static function kal_standard_termin_intern($value,$cols) {
   #   Standardisierung von Datums- und Zeitangaben der Elemente eines Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   - Integer '' nach 0
   #   $value          eingegebenes nummeriertes Array (Nummerierung ab 0),
   #                   enthaelt Datums- und Zeitangaben, das standardisierte
   #                   Array wird zurueck gegeben
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
public static function kal_standard_termin($termin) {
   #   Standardisierung von Datums- und Zeitangaben eines Termin-Arrays:
   #   - Datumsangaben nach 'tt.mm.yyyy'
   #   - Zeitangaben nach 'hh:mm'
   #   - Integer '' nach 0
   #   $termin         eingegebenes Termin-Array
   #                   das standardisierte Array wird zurueck gegeben
   #   benutzte functions:
   #      self::kal_standard_termin_intern($value,$cols)
   #      $addon::kal_define_tabellenspalten()
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Datums- und Zeitangaben sowie leere Integer des eingegebenen Termins standardisieren
   $value=array();
   for($i=0;$i<count($keys);$i=$i+1) $value[$i]=$termin[$keys[$i]];
   $val=self::kal_standard_termin_intern($value,$cols);
   $term=array();
   for($i=0;$i<count($keys);$i=$i+1) $term[$keys[$i]]=$val[$i];
   return $term;
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
   $addon=self::this_addon;
   $table=rex::getTablePrefix().$addon;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $term=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$addon::TAB_KEY[0].'='.$pid);
   #
   # --- Wandeln der Datums- und Zeitformate
   if(count($term)>0) return self::kal_termin_mysql_standard($term[0]);
   return array();
   }
public static function kal_exist_termin($termin) {
   #   Suchen eines Termins in der Datenbanktabelle, falls der Termin
   #   gefunden wird, wird dessen Id zurueckgegeben
   #   ('gefunden' heisst: alle Parameter ausser der Id stimmen ueberein)
   #   $termin         gegebenes Termin-Array
   #   benutzte functions
   #      self::kal_standard_termin($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      self::kal_termin_mysql_standard($termin)
   #
   $addon=self::this_addon;
   $pid=$addon::TAB_KEY[0];   // pid
   $dat=$addon::TAB_KEY[3];   // datum
   $keys=array_keys($termin);
   #
   # --- Datums- und Zeitangaben des eingegebenen Termins standardisieren
   $stdtermin=self::kal_standard_termin($termin);
   #
   # --- alle Termine zum Datum des gegebenen Termins auslesen
   $table=rex::getTablePrefix().self::this_addon;
   $datum=$stdtermin[$dat];
   $datsql=self::kal_datum_standard_mysql($datum);
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.$table.' WHERE '.$dat.'=\''.$datsql.'\'';
   $term=$sql->getArray($query);
   #
   # --- ausgelesene Termine mit dem gegebenen Termin vergleichen
   for($i=0;$i<count($term);$i=$i+1):
      #
      # --- ausgelesenen Termin standardisieren (MySQL-date- und -time-Werte)
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      #
      # --- unterschiedliche Werte zaehlen (pid nicht mitgezaehlt)
      $m=0;
      for($k=0;$k<count($stdtermin);$k=$k+1):
         $ke=$keys[$k];
         if($ke==$pid) continue;
         $val=$term[$i][$ke];
         if($val==$stdtermin[$ke]) continue;
         $m=$m+1;
         endfor;
      if($m<=0) return $term[$i][$pid];
      endfor;
   }
public static function kal_insert_termin($termin) {
   #   Eintragen eines neuen Termins in die Datenbanktabelle.
   #   Aufgerufen in Eingabeformularen.
   #   $termin         Array des Termins
   #   Rueckgabe:      Eintragung erfolgreich:   Id des neuen Termins (>0)
   #                   andernfalls:              Fehlermeldung
   #   -----------------------------------------------------------------
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   -----------------------------------------------------------------
   #   benutzte functions:
   #      self::kal_standard_termin($termin)
   #      self::kal_exist_termin($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      $addon::kal_define_tabellenspalten()
   #
   $addon=self::this_addon;
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
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($termin);
   $table=rex::getTablePrefix().self::this_addon;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   for($i=1;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
      $val=$term[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      $sql->setValue($key,$val);   // offenbar implizit: $val=html_entity_decode($val);
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
     return '<span class="kal_fail">Der Termin konnte nur unvollständig eingetragen werden</span>';
     endif;
   }
public static function kal_delete_termin($pid) {
   #   Loeschen eines Termins in die Datenbanktabelle.
   #   Aufgerufen im Loeschformular.
   #   $pid            Id des Termins
   #   Rueckgabe:      leer, falls der Termin geloescht wurde bzw.
   #                   Fehlermeldung in rot (andernfalls)
   #   -----------------------------------------------------------------
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   -----------------------------------------------------------------
   #
   if($pid<=0)
     return '<span class="kal_fail">Bitte einen Termin zum Löschen benennen (Termin-Id>0)</span>';
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $sql=rex_sql::factory();
   #
   # --- Pruefen, ob der Termin da ist
   $table=rex::getTablePrefix().$addon;
   $arr=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$keypid.'='.$pid);
   if(count($arr)<=0)
     return '<span class="kal_fail">Termin nicht vorhanden</span>';
   #
   # --- Durchfuehrung
   $sql->setTable($table);
   $sql->setWhere(array($keypid=>$pid));
   $sql->delete();
   #
   # --- Pruefen, ob der Termin wirklich weg ist
   $arr=$sql->getArray('SELECT * FROM '.$table.' WHERE '.$keypid.'='.$pid);
   if(count($arr)>0)
     return '<span class="kal_fail">Termin konnte nicht gelöscht werden</span>';
   }
public static function kal_update_termin($pid,$termkor) {
   #   Korrigieren eines Termins in der Datenbanktabelle.
   #   Aufgerufen im Updateformular.
   #   $pid            Id des zu korrigierenden Termins
   #   $termkor        zu korrigierende Daten des Termins
   #                   in Form eines vollstaendigen Termin-Arrays (ohne Termin-Id)
   #   Rueckgabe:      leer, falls der Termin korrigiert wurde
   #                   Fehlermeldung (in rot), falls das Update fehlschlug
   #   -----------------------------------------------------------------
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   -----------------------------------------------------------------
   #   benutzte functions:
   #      self::kal_select_termin_by_pid($pid)
   #      self::kal_standard_termin($termin)
   #      self::kal_datum_standard_mysql($datum)
   #      $addon::kal_define_tabellenspalten()
   #
   $addon=self::this_addon;
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   if(count($termin)<=0)
     return '<span class="kal_fail">Der Termin ist nicht vorhanden</span>';
   #
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keypid=$keys[0];   // pid
   $keynam=$keys[2];   // name
   $keydat=$keys[3];   // datum
   #
   # --- Standardform der zu korrigierenden Termindaten herstellen
   $korterm=self::kal_standard_termin($termkor);
   #
   # --- Terminparameter einzeln in einer Schleife aktualisieren
   $table=rex::getTablePrefix().self::this_addon;
   $sql=rex_sql::factory();
   $sql->setTable($table);
   $sql->setWhere($keypid.'='.$pid);
   for($i=0;$i<count($korterm);$i=$i+1):
      $key=$keys[$i];
      if($key==$keypid) continue;
      $val=$korterm[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      $sql->setValue($key,$val);   // offenbar implizit: $val=html_entity_decode($val);
      endfor;
   #
   # --- Update durchfuehren
   $sql->update();
   #
   # --- erneutes Auslesen des Termins und Vergleich mit eingegebener Korrektur
   $termsel=self::kal_select_termin_by_pid($pid);
   for($i=1;$i<count($termkor);$i=$i+1):
      $key=$keys[$i];
      if($key==$keypid) continue;
      if($termsel[$key]==$korterm[$key]) continue;
      return '<span class="kal_fail">Termin "'.$termin[$keynam].'" ('.$termin[$keydat].
         ') konnte nicht korrigiert werden</span>';
      break;
      endfor;
   }
#
#----------------------------------------- Auslesen von Termindaten
public static function kal_kategorie_name($katid) {
   #   Ermitteln der Kategoriebezeichnung aus der Kategorie-Id. Leere Rueckgabe,
   #   falls die eingegebene Id keiner definierten Kategorie entspricht.
   #   Aufgerufen im Modul 'Termine anzeigen' und im Terminblatt.
   #   $katid          Kategorie-Id
   #   benutzte functions:
   #      self::kal_get_spielkategorien()
   #      $addon::kal_get_terminkategorien()
   #
   if($katid<=0) return;
   #
   # --- Kategorien der Spieltermine / konfigurierte Kategorien
   $addon=self::this_addon;
   if($katid<=$addon::SPIEL_KATID):
     $kat=$addon::kal_get_terminkategorien();
     else:
     $kat=self::kal_get_spielkategorien();
     endif;
   for($i=0;$i<count($kat);$i=$i+1) if($kat[$i]['id']==$katid) return $kat[$i]['name'];
   }
public static function kal_select_termine($von,$bis,$katids) {
   #   Auslesen von Terminen aus der Datenbanktabelle, gefiltert durch Datumsbereich
   #   und eine oder mehrere Kategorien. Rueckgabe als nummeriertes Array von
   #   Terminen (Nummerierung ab 1).
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: vom ersten eingetragenen Termin an
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: bis inkl. dem letzten eingetragenen Termin
   #   $katids         Array der ausgewaehlten Kategorie-Ids (Nummerierung ab 1)
   #                   falls leer: keine Termine zurueck gegeben
   #   benutzte functions:
   #      self::kal_datum_standard_mysql($datum)
   #      self::kal_termin_mysql_standard($termin)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   if(count($katids)<=0) return array();   // keine Kategorien -> keine Termine
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // kat_id
   $keydat=$addon::TAB_KEY[3];   // datum
   $keybeg=$addon::TAB_KEY[4];   // beginn
   #
   $stdvon=kal_termine_kalender::kal_standard_datum($von);
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- Datumsbereich
   $vonsql=self::kal_datum_standard_mysql($stdvon);
   $bissql=self::kal_datum_standard_mysql($stdbis);
   $where=$keydat.'>=\''.$vonsql.'\'';
   if(!empty($bis) and $bissql!='0000-00-00') $where=$where.' AND '.$keydat.'<=\''.$bissql.'\'';
   #
   # --- Restriktion Kategorie
   $wh='';
   for($i=1;$i<=count($katids);$i=$i+1) $wh=$wh.' OR '.$keykat.'='.$katids[$i];
   if(!empty($wh)) $wh=substr($wh,4);
   $where=$where.' AND ('.$wh.')';
   #
   # --- alle Termine mit den vorgegebenen Restriktionen auslesen
   $table=rex::getTablePrefix().self::this_addon;
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.$table.' WHERE '.$where.' ORDER BY '.$keydat.','.$keybeg;
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
public static function kal_expand_multitermin($termin,$keynr) {
   #   Aufspalten eines Folgetermins (taeglich ueber mehrere Tage) oder eines
   #   Wiederholungstermins (woechentlich oder monatlich) in Einzeltermine und
   #   Rueckgabe dieser Einzeltermine als nummeriertes Array (Nummerierung ab 1).
   #   Dabei bekommt jeder Einzeltermin das passende neue Datum. D.h. der erste
   #   Einzeltermin hat noch das urspruengliche Datum des Multi-Termins.
   #   Die Einzeltermine behalten die Werte ihrer Parameter tage, wochen, monate.
   #   $termin         eingegebener Termin (assoziatives Array)
   #   $keynr          Index des Array $addon::TAB_KEY
   #                   =6: es geht um mehrtaegige Termine
   #                   =7: es geht um Wochen-Wiederholungstermine
   #                   =8: es geht um Monats-Wiederholungstermine
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_gleicher_wotag_im_folgemonat($datum)
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   $nrtage=6;         // Key-Nr. tage
   $nrwoch=7;         // Key-Nr. wochen
   $nrmona=8;         // Key-Nr. monate
   $keywch=$addon::TAB_KEY[$nrwoch];   // wochen
   $keymon=$addon::TAB_KEY[$nrmona];   // monate
   #
   $single=array();
   $m=0;
   $datum=$termin[$keydat];
   $anznr=$termin[$addon::TAB_KEY[$keynr]];
   if($keynr==$nrtage) $diff=1;
   if($keynr==$nrwoch) $diff=7;
   $datneu=$datum;
   for($k=1;$k<=$anznr;$k=$k+1):
      if($k>=2):
        if($keynr==$nrtage or $keynr==$nrwoch):
          $datneu=kal_termine_kalender::kal_datum_vor_nach($datneu,$diff);
          else:
          $datneu=kal_termine_kalender::kal_gleicher_wotag_im_folgemonat($datneu);
          endif;
        endif;
      $m=$m+1;
      $single[$m]=$termin;
      #     Datum setzen
      $single[$m][$keydat]=$datneu;
      #
      # --- mehrtaegig: Beginn, Ende des jetzigen einfachen Termins anpassen
      if($keynr==$nrtage):
        if($k>=2) $single[$m][$keybeg]='';
        $single[$m][$keyend]='';
        #     End-Uhrzeit fuer den letzten Einzeltermin uebernehmen
        if($k==$anznr) $single[$m][$keyend]=$termin[$keyend];
        endif;
      endfor;
   return $single;
   }
public static function kal_collect_termine($von,$bis,$katids,$kontif) {
   #   Rueckgabe aller Termine eines Datumsbereichs einer oder mehrerer
   #   Terminkategorien in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Es werden auch Multitermine beruecksichtigt, deren Startdatum vor dem
   #   Datumsbereich, deren Wiederholungen aber innerhalb des Datumsbereichs
   #   liegen. Die Termine sind NICHT nach Datum sortiert.
   #   $von            Datum des ersten Tages des Datumsbereichs
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #                   falls leer: keine Termine zurueck gegeben
   #   $kontif         >0: mehrtaegige Termine werden aufgespalten in ihre
   #                       Einzeltermine (gebraucht in den Kalendermenues)
   #                   =0: mehrtaegige Termine werden NICHT aufgespalten in ihre
   #                       Einzeltermine (gebraucht in den Termin-/Suchlisten)
   #   benutzte functions:
   #      self::kal_get_spieltermine($von,$bis,$katids)
   #      self::kal_select_termine($von,$bis,$katids)
   #      self::kal_expand_multitermin($termin,$keynr)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_datum1_vor_datum2($datum1,$datum2)
   #
   if(count($katids)<=0)    return array();   // keine Kategorien -> keine Termine
   #
   $addon=self::this_addon;
   $nrtag=6;
   $nrwch=7;
   $nrmon=8;
   $keydat=$addon::TAB_KEY[3];        // datum
   $keytag=$addon::TAB_KEY[$nrtag];   // tage
   $keywch=$addon::TAB_KEY[$nrwch];   // wochen
   $keymon=$addon::TAB_KEY[$nrmon];   // monate
   #
   $stdvon=kal_termine_kalender::kal_standard_datum($von);
   $stdbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   # --- zunaechst alle Termine vor $bis
   if($katids[1]>=$addon::SPIEL_KATID):
     #     Spieltermine (ab 5 Tage vor $von)
     $vorvon=kal_termine_kalender::kal_datum_vor_nach($stdvon,-5);
     $ter=self::kal_get_spieltermine($vorvon,$stdbis,$katids);
     else:
     #     Datenbanktermine (alle vor $von)
     $vorvon='';
     $ter=self::kal_select_termine($vorvon,$stdbis,$katids);
     endif;
   #
   # --- woechentliche mehrtägige Termine in einzelne mehrtaegige Termine aufspalten
   $m=0;
   $te=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      if($ter[$i][$keywch]<=0 or $ter[$i][$keytag]<=1):
        $m=$m+1;
        $te[$m]=$ter[$i];
        continue;
        else:
        $single=self::kal_expand_multitermin($ter[$i],$nrwch);
        $dat=$single[1][$keydat];
        for($k=1;$k<=count($single);$k=$k+1):
           $m=$m+1;
           $te[$m]=$single[$k];
           if($k>1) $dat=kal_termine_kalender::kal_datum_vor_nach($dat,7);
           $te[$m][$keydat]=$dat;
           $te[$m][$keywch]=0;
           endfor;
        endif;
      endfor;
   $ter=$te;
   #
   # --- monatliche mehrtägige Termine in einzelne mehrtaegige Termine aufspalten
   $m=0;
   $te=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      if($ter[$i][$keymon]<=0 or $ter[$i][$keytag]<=1):
        $m=$m+1;
        $te[$m]=$ter[$i];
        continue;
        else:
        $single=self::kal_expand_multitermin($ter[$i],$nrmon);
        $dat=$single[1][$keydat];
        for($k=1;$k<=count($single);$k=$k+1):
           $m=$m+1;
           $te[$m]=$single[$k];
           if($k>1):
             $dat=kal_termine_kalender::kal_gleicher_wotag_im_folgemonat($dat);
             endif;
           $te[$m][$keydat]=$dat;
           $te[$m][$keymon]=0;
           endfor;
        endif;
      endfor;
   $ter=$te;
   #
   # --- Aufspalten der Multitermine
   $j=0;
   $k=0;
   $m=0;
   $n=0;
   $term=array();
   for($i=1;$i<=4;$i=$i+1) $term[$i]=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      #
      # --- mehrtaegige Termine (nicht woechentlich/monatlich wiederkehrend)
      if($ter[$i][$keytag]>1 and $ter[$i][$keywch]<=0 and $ter[$i][$keymon]<=0):
        $k=$k+1;
        $term[2][$k]=self::kal_expand_multitermin($ter[$i],$nrtag);
     // $term[2][1]: Basistermin
        continue;
        endif;
      #
      # --- woechentlich wiederkehrende Termine
      if($ter[$i][$keywch]>0):
        $m=$m+1;
        $term[3][$m]=self::kal_expand_multitermin($ter[$i],$nrwch);
        continue;
        endif;
      #
      # --- monatlich wiederkehrende Termine
      if($ter[$i][$keymon]>0):
        $n=$n+1;
        $term[4][$n]=self::kal_expand_multitermin($ter[$i],$nrmon);
        continue;
        endif;
      #
      # --- Einzeltermine
      $j=$j+1;
      $term[1][$j][1]=$ter[$i];
      endfor;
   #
   # --- die Einzeltermine heraussuchen, die in den Datumsbereich hineinfallen
   #     $i==1/2/3/4: Einzeltermine / mehrtaegige Termine / woechentlich
   #                  wiederkehrende Termine / monatlich wiederkehrende Termine
   $terms=array();
   $m=0;
   for($i=1;$i<=4;$i=$i+1):
      for($k=1;$k<=count($term[$i]);$k=$k+1):
         $termin=$term[$i][$k];
         for($j=1;$j<=count($termin);$j=$j+1):
            $dat=$termin[$j][$keydat];
            if(kal_termine_kalender::kal_datum1_vor_datum2($dat,$stdvon)) continue; // zu frueh
            if(kal_termine_kalender::kal_datum1_vor_datum2($stdbis,$dat)) continue; // zu spaet
            $m=$m+1;
            if($kontif>0 or $termin[$j][$keytag]<=1):
              #     $kontif>0 oder eintägig (auch wochen>0 oder monate>0)
              $terms[$m]=$termin[$j];   // Einzeltermin aufsammeln
              else:
              #     $kontif<=0 und mehrtaegig
              $terms[$m]=$termin[1];    // Basistermin aufsammeln
              break;
              endif;
            endfor;
         endfor;
      endfor;
   #
   return $terms;
   }
public static function kal_sort_termine($termin) {
   #   Sortieren eines Arrays von Terminen nach Datum (und Uhrzeit des Beginns).
   #   $termin         Array der Termine, Nummerierieng ab 1
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum1_vor_datum2($datum1,$datum2)
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   $keybeg=$addon::TAB_KEY[4];   // beginn
   #
   $term=$termin;
   for($i=1;$i<=count($term);$i=$i+1):
      for($k=$i+1;$k<=count($term);$k=$k+1):
         $datum2=$term[$i][$keydat];
         $begin2=$term[$i][$keybeg];
         $datum1=$term[$k][$keydat];
         $begin1=$term[$k][$keybeg];
         if(kal_termine_kalender::kal_datum1_vor_datum2($datum1,$datum2) or
            ($datum1==$datum2 and $begin1<$begin2)):
           $zt=$term[$i];
           $term[$i]=$term[$k];
           $term[$k]=$zt;
           endif;           
         endfor;
      endfor;
   return $term;
   }
public static function kal_get_termine_all($von,$bis,$katids,$kontif) {
   #   Rueckgabe aller Termine eines Datumsbereichs einer oder mehrerer
   #   Terminkategorien in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Die Termine werden nach Datum sortiert.
   #   $von            Datum des ersten Tages des Datumsbereichs
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #   $kontif         mehrtaegige Termine werden (nicht) aufgespalten in
   #                   ihre Einzeltermine (vergl kal_collect_termine)
   #   benutzte functions:
   #      self::kal_collect_termine($von,$bis,$katids,$kontif)
   #      self::kal_sort_termine($termin)
   #
   $term=self::kal_collect_termine($von,$bis,$katids,$kontif);
   return self::kal_sort_termine($term);
   }
public static function kal_subst_wiederholungstermine($termin) {
   #   Herausfiltern von Exemplaren von woechentlich/monatlich wiederkehrenden
   #   Terminen in einem Termin-Array, fuer die ein Einzeltermin als Ersatz
   #   vorhanden ist. Der Ersatztermin muss in den Parametern Name, Datum,
   #   Kategorie-Id mit dem Wiederholungstermin uebereinstimmen. Ausserdem muss
   #   der Ersatztermin ein echter Einzeltermin sein, d.h. sein Parameterwert
   #   von Anzahl Tage muss <=1 sein.
   #   Rueckgabe eines entsprechend gefilterten Teil-Arrays.
   #   $termin         Array gegebener Termine in einem Datumsbereich
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // kat_id
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $keytag=$addon::TAB_KEY[6];   // tage
   $keywch=$addon::TAB_KEY[7];   // wochen
   $keymon=$addon::TAB_KEY[8];   // monate
   $nz=count($termin);
   $substterm=array();
   $m=0;
   for($i=1;$i<=$nz;$i=$i+1):
      $term=$termin[$i];
      #
      # --- kein woechentlicher/monatlicher Wiederholungstermin: uebernehmen
      if($term[$keywch]<=0 and $term[$keymon]<=0):
        $m=$m+1;
        $substterm[$m]=$term;
        endif;
      #
      # --- Wiederholungstermin ggf. auslassen, weil Ersatztermin vorhanden
      if($term[$keywch]>0 or $term[$keymon]>0):
        $auslassen=FALSE;
        for($k=1;$k<=$nz;$k=$k+1):
           $ter=$termin[$k];
           if($ter[$keykat]==$term[$keykat] and   // kat_id: Uebereinstimmung
              $ter[$keynam]==$term[$keynam] and   // name:   Uebereinstimmung
              $ter[$keydat]==$term[$keydat] and   // datum:  Uebereinstimmung
              $ter[$keywch]<=0              and   // wochen=0
              $ter[$keymon]<=0              and   // monate=0
              $ter[$keytag]<=1                 ): // tage=1
             $auslassen=TRUE;
             break;
             endif;
           endfor;
        if(!$auslassen):
          $m=$m+1;
          $substterm[$m]=$term;
          endif;
        endif;
      endfor;
   return $substterm;
   }
public static function kal_get_termine($von,$bis,$katids,$kontif) {
   #   Rueckgabe aller Termine eines Datumsbereichs einer oder mehrerer
   #   Terminkategorien in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Hier werden ggf. woechentlich/monatlich wiederkehrende Termine entfernt,
   #   falls entsprechende Einzeltermine als Ersatztermine vorhanden sind.
   #   Parameter       (wie in kal_get_termine_all)
   #   $von            Datum des ersten Tages des Datumsbereichs
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #   $kontif         mehrtaegige Termine aufgespalten/nicht aufgespalten
   #   benutzte functions:
   #      self::kal_get_termine_all($von,$bis,$katids,$kontif)
   #      self::kal_subst_wiederholungstermine($termin)
   #
   $termin=self::kal_get_termine_all($von,$bis,$katids,$kontif);
   return self::kal_subst_wiederholungstermine($termin);
   }
#
#----------------------------------------- Erzeugen von Spiel-Termindaten
public static function kal_get_spielkategorien() {
   #   Rueckgabe der zu den kuenstlichen Termindaten passenden Terminkategorien
   #   in Form eines nummerierten Arrays
   #
   $addon=self::this_addon;
   return array(
      array('id'=>$addon::SPIEL_KATID+1, 'name'=>$addon::FIRST_CATEGORY),
      array('id'=>$addon::SPIEL_KATID+2, 'name'=>'Tischtennisverband'),
      array('id'=>$addon::SPIEL_KATID+3, 'name'=>'Kulturkreis'));
   }
public static function kal_set_spieldaten($datum) {
   #   Rueckgabe von kuenstlichen Termindaten eines Tages in Form eines
   #   assoziativen Termin-Arrays (leere Rueckgabe, falls an dem Tage keine
   #   Termine vorliegen)
   #   $datum          Datum des Tages (standardisiertes Datum)
   #   benutzte functions:
   #      self::kal_get_spielkategorien()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_montag_vor($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_wotag($datum)
   #
   $addon=self::this_addon;
   $TAB=$addon::TAB_KEY;
   #
   # --- heutiger Tage
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Radius in Anzahl Wochen um den heutigen Tag, in denen die Termine an den
   #     jeweiligen Wochentagen definiert sind (Mo, Di, Mi, Do, Fr, Sa, So)
   $radius=array(1=>4, 2=>7, 3=>10, 4=>12, 5=>10, 6=>8, 7=>6);
   #
   # --- alle Wochentage um das Datum herum
   $wotage=array();
   $montag=kal_termine_kalender::kal_montag_vor($datum);
   $wotage[1]=$montag;
   for($wt=2;$wt<=7;$wt=$wt+1)
      $wotage[$wt]=kal_termine_kalender::kal_datum_vor_nach($montag,intval($wt-1));
   $sonntag=$wotage[7];
   #
   # --- Standard-Hinweissaetze
   $stc='<small>Spieldaten, Termin wiederholt sich wöchentlich über einige Wochen um den '.$heute.' herum</small>';
   $std='<small>Spieldaten, Termin (über 2 Tage) wiederholt sich wöchentlich über einige Wochen um den '.$heute.' herum</small>';
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
   $katids=array();
   for($i=0;$i<count($kats);$i=$i+1):
      $k=$i+1;
      $katids[$k]=$kats[$i]['id'];
      endfor;
   #
   # --- leeres Array
   $LEER=array();
   for($i=0;$i<count($addon::TAB_KEY);$i=$i+1) $LEER[$TAB[$i]]='';
   $LEER[$TAB[0]]=0;   // pid
   $LEER[$TAB[1]]=0;   // kat_id
   $LEER[$TAB[6]]=1;   // tage
   $LEER[$TAB[7]]=0;   // wochen
   $LEER[$TAB[8]]=0;   // monate
   #
   # --- Setzen der (Wochen-)Termine
   $term=array();
   $m=0;
   #
   # --- Montag
   $wt=1;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=117;
     $term[$m][$TAB[ 1]]=$katids[3];
     $term[$m][$TAB[ 2]]='Katharina Luther - Filmvorführung und Diskussion';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='19:30';
     $term[$m][$TAB[ 5]]='22:00';
     $term[$m][$TAB[ 9]]='Kulturkreis Quadertal, Thomas Hörlinger, Tel. 05996 65432';
     $term[$m][$TAB[10]]='Dorfgemeinschaftshaus, Wallstraße, 38990 Quadertal';
     $term[$m][$TAB[12]]=$stc;
     endif;
   #
   # --- Dienstag
   $wt=2;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=205;
     $term[$m][$TAB[ 1]]=$katids[2];
     $term[$m][$TAB[ 2]]='Tischtennistraining Bezirkskader';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='10:30';
     $term[$m][$TAB[ 5]]='12:00';
     $term[$m][$TAB[ 9]]='Volker Meister, Tel. 0123 4567890';
     $term[$m][$TAB[10]]='Halle am Sportpark, 38985 Quaderberg';
     $term[$m][$TAB[12]]=$stc;
     endif;
     #
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=226;
     $term[$m][$TAB[ 1]]=$katids[1];
     $term[$m][$TAB[ 2]]='Gemeinderatsssitzung';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='19:30';
     $term[$m][$TAB[ 9]]='Rat der Gemeinde Quadertal, Jochen Krause, Tel. 0124 3302456';
     $term[$m][$TAB[10]]='Gaststätte \'Grüne Wiese\', Wiesenstr. 79, 38990 Quadertal, Tel. 05996 88776655';
     $term[$m][$TAB[12]]=$stc;
     endif;
   #
   # --- Mittwoch
   $wt=3;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=136;
     $term[$m][$TAB[ 1]]=$katids[3];
     $term[$m][$TAB[ 2]]='Spieleabend';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='19:30';
     $term[$m][$TAB[ 5]]='22:30';
     $term[$m][$TAB[ 9]]='Skat- und Kniffelklub Quadertal, Horst Querfurth, Tel. 05996 73540';
     $term[$m][$TAB[10]]='Dorfgemeinschaftshaus Quadertal';
     $term[$m][$TAB[12]]=$stc;
     endif;
   #
   # --- Donnerstag
   $wt=4;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=183;
     $term[$m][$TAB[ 1]]=$katids[2];
     $term[$m][$TAB[ 2]]='Tischtennistraining Kreiskader';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='15:00';
     $term[$m][$TAB[ 5]]='17:00';
     $term[$m][$TAB[ 9]]='Volker Meister, Tel. 0123 4567890';
     $term[$m][$TAB[10]]='Halle am Sportpark, 38985 Quaderberg';
     $term[$m][$TAB[12]]=$stc;
     endif;
     #
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=77;
     $term[$m][$TAB[ 1]]=$katids[2];
     $term[$m][$TAB[ 2]]='Staffelsitzungen, TT-Kreisverband Quaderberg';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 9]]='Manfred Berger, Tel. 05992 1234567';
     $term[$m][$TAB[10]]='Sportheim Quaderberg';
     $term[$m][$TAB[12]]=$stc;
     $term[$m][$TAB[13]]='18:00';
     $term[$m][$TAB[14]]='Damen: 1. Kreisklasse';
     $term[$m][$TAB[15]]='18:30';
     $term[$m][$TAB[16]]='Herren: 1. Kreisklasse, 2. Kreisklasse';
     $term[$m][$TAB[17]]='19:00';
     $term[$m][$TAB[18]]='Herren: 3. Kreisklasse, 4. Kreisklasse';
     $term[$m][$TAB[19]]='19:30';
     $term[$m][$TAB[20]]='Herren: 5. Kreisklasse, 6. Kreisklasse';
     endif;
   #
   # --- Freitag
   $wt=5;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=226;
     $term[$m][$TAB[ 1]]=$katids[1];
     $term[$m][$TAB[ 2]]='Busfahrt in den Harz';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='10:00';
     $term[$m][$TAB[ 5]]='19:00';
     $term[$m][$TAB[ 9]]='Seniorenkreis Quaderberg';
     $term[$m][$TAB[10]]='Abfahrt um 10:00 Uhr an der Kirche Quaderberg';
     $term[$m][$TAB[12]]=$stc;
     endif;
   #
   # --- Samstag
   $wt=6;
   $dat=$wotage[$wt];
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=54;
     $term[$m][$TAB[ 1]]=$katids[3];
     $term[$m][$TAB[ 2]]='Die Maler der \'Brücke\' - Die Werke von Karl Schmidt-Rottluff';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 4]]='16:00';
     $term[$m][$TAB[ 5]]='18:30';
     $term[$m][$TAB[ 9]]='Kulturkreis Quadertal, Ulf Schneider, Tel. 05996 356702';
     $term[$m][$TAB[10]]='Brunsviga-Kulturzentrum, Quader Ring 35, 38990 Quadertal';
     $term[$m][$TAB[11]]=$linkurl;
     $term[$m][$TAB[12]]=$stc;
     endif;
     #
   if(abs(kal_termine_kalender::kal_datumsdifferenz($heute,$dat))<=7*$radius[$wt]):
     $m=$m+1;
     $term[$m]=$LEER;
     $term[$m][$TAB[ 0]]=239;
     $term[$m][$TAB[ 1]]=$katids[2];
     $term[$m][$TAB[ 2]]='Regionsmeisterschaften Jugend und Schüler';
     $term[$m][$TAB[ 3]]=$dat;
     $term[$m][$TAB[ 6]]=2;
     $term[$m][$TAB[ 9]]='TSV Quaderberg';
     $term[$m][$TAB[10]]='Halle am Sportpark, 38985 Quaderberg';
     $term[$m][$TAB[12]]=$std;
     endif;
   #
   # --- Auswahl der gewuenschten Termine
   $m=0;
   $termin=array();
   for($i=1;$i<=count($term);$i=$i+1)
      if($term[$i][$TAB[3]]==$datum):
        $m=$m+1;
        $termin[$m]=$term[$i];
        endif;
   return $termin;
   }
public static function kal_get_spieltermine($von,$bis,$katids) {
   #   Rueckgabe von Spielterminen einer oder mehrerer Kategorien in einem
   #   Datumsbereich als Array von Terminen (Nummerierung ab 1). Mehrtaegige
   #   Termine werden nicht in Einzeltermine aufgespalten. Sie werden daher nur
   #   dann zurueck gegeben, wenn ihr Starttermin innerhalb des Datumsbereichs
   #   liegt. - Es wird hier vorausgesetzt, dass Spieltermine nicht woechentlich
   #   oder monatlich wiederkehrend sind.
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #   $katids         Array der Kategorien (Nummerierung ab 1)
   #   benutzte functions:
   #      self::kal_set_spieldaten($datum)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_datum1_vor_datum2($datum1,$datum2)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #
   if(count($katids)<=0)    return array();   // keine Kategorien -> keine Termine
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // kat_id
   $stvon=kal_termine_kalender::kal_standard_datum($von);
   $stbis=kal_termine_kalender::kal_standard_datum($bis);
   #
   $term=array();
   $m=0;
   $datum=$stvon;
   while(!kal_termine_kalender::kal_datum1_vor_datum2($stbis,$datum)):
        $ter=self::kal_set_spieldaten($datum);
        for($i=1;$i<=count($ter);$i=$i+1):
           $kat=FALSE;
           for($j=1;$j<=count($katids);$j=$j+1)
              if($ter[$i][$keykat]==$katids[$j]):
                $kat=TRUE;   // richtige Kategorie
                break;
                endif;
           if(!$kat) continue;
           $m=$m+1;
           $term[$m]=$ter[$i];
           endfor;
        $datum=kal_termine_kalender::kal_datum_vor_nach($datum,1);
        endwhile;
   return $term;
   }
public static function kal_aktuelle_wochen_spieltermine() {
   #   Rueckgabe der Spieltermine (Spieldaten) der aktuellen Woche in allen
   #   Kategorien als Array von Terminen (Nummerierung ab 1). Mehrtaegige Termine
   #   werden nicht in Einzeltermine aufgespalten. Daher werden sie nur dann
   #   zurueck gegeben, wenn ihr Starttermin innerhalb der Woche liegt.
   #   Aufgerufen nur in der Konfiguration der Terminliste.
   #   benutzte functions:
   #      self::kal_get_spieltermine($von,$bis,$katids)
   #      self::kal_get_spielkategorien()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_montag_vor($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #
   $heute=kal_termine_kalender::kal_heute();
   $von  =kal_termine_kalender::kal_montag_vor($heute);
   $bis  =kal_termine_kalender::kal_datum_vor_nach($von,6);
   $kats =self::kal_get_spielkategorien();
   $katids=array();
   for($i=0;$i<count($kats);$i=$i+1) $katids[$i+1]=$kats[$i]['id'];
   $termin=self::kal_get_spieltermine($von,$bis,$katids);
   return $termin;
   }
}
?>
