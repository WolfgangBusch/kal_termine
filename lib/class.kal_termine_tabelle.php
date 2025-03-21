<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
class kal_termine_tabelle {
#
#----------------------------------------- Methoden
#   Basismethoden
#      kal_datum_mysql_standard($datum)
#      kal_datum_standard_mysql($datum)
#      kal_termin_mysql_standard($termin)
#   SQL-Grundmethoden
#      kal_select_termin_by_pid($pid)
#      kal_exist_termin($termin)
#      kal_insert_termin($termin)
#      kal_delete_termin($pid)
#      kal_update_termin($pid,$termkor)
#   Auslesen von Termindaten
#      kal_select_dbtermine($von,$bis,$katids,$keyword)
#      kal_expand_multitermin($termin,$keynr)
#      kal_collect_termine($von,$bis,$katids,$keyword,$kontif)
#      kal_sort_termine($termin)
#      kal_get_termine_all($von,$bis,$katids,$keyword,$kontif)
#      kal_subst_wiederholungstermine($termin)
#      kal_get_termine($von,$bis,$katids,$keyword,$kontif)
#   Erzeugen von Spiel-Termindaten
#      kal_set_spieldaten($datum)
#      kal_select_spieltermine($von,$bis,$katids,$keyword)
#      kal_select_spieltermin_by_pid($pid)
#      kal_select_termin($pid)
#   Terminliste
#      kal_uhrzeit_string($termin)
#      kal_zusatzzeiten_string($termin)
#      kal_terminliste_intern($termin)
#      kal_terminliste($von,$bis,$katids)
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Basismethoden
public static function kal_datum_mysql_standard($datum) {
   #   Umformatieren eines MySQL-Datumsstrings 'yyyy-mm-tt' in einen
   #   Standard-Datumsstring 'tt.mm.yyyy'.
   #
   return substr($datum,8,2).'.'.substr($datum,5,2).'.'.substr($datum,0,4);
   }
public static function kal_datum_standard_mysql($datum) {
   #   Umformatieren eines Standard-Datumsstrings 'tt.mm.yyyy' in einen
   #   MySQL-Datumsstring 'yyyy-mm-tt'.
   #
   return substr($datum,6).'-'.substr($datum,3,2).'-'.substr($datum,0,2);
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
#
#----------------------------------------- SQL-Grundmethoden
public static function kal_select_termin_by_pid($pid) {
   #   Auslesen und Rueckgabe eines Termins aus der Datenbanktabelle.
   #   $pid            Id des Termins
   #
   # --- aus der Datenbanktabelle auslesen
   $addon=self::this_addon;
   $addon::$SPIELDATEN=FALSE;
   $keypid=$addon::TAB_KEY[0];   // pid
   $where=$keypid.'='.$pid;
   $termin=cms_interface::select_termin($where,'')[0];
   #
   # --- Wandeln der Datums- und Zeitformate
   if(!empty($termin)) return self::kal_termin_mysql_standard($termin);
   return array();
   }
public static function kal_exist_termin($termin) {
   #   Suchen eines Termins in der Datenbanktabelle (genauer gesagt: gesucht wird in den
   #   Terminen am Datum des gegebenen Termin-Arrays). Falls der Termin gefunden wird,
   #   wird dessen Id zurueckgegeben. Andernfalls leere Rueckgabe.
   #   'gefunden' heisst: alle Parameter ausser der Id stimmen ueberein.
   #   $termin         gegebenes Termin-Array
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keydat=$addon::TAB_KEY[3];   // datum
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keys=array_keys($termin);
   #
   # --- alle Termine zum Datum des gegebenen Termins auslesen
   $datum=$termin[$keydat];
   $datsql=self::kal_datum_standard_mysql($datum);
   $where=$keydat.'=\''.$datsql.'\'';
   $order=$keybeg.' ASC';
   $term=cms_interface::select_termin($where,$order);
   #
   # --- ausgelesene Termine mit dem gegebenen Termin vergleichen
   for($i=0;$i<count($term);$i=$i+1):
      #
      # --- ausgelesenen Termin standardisieren (MySQL-date- und -time-Werte)
      $term[$i]=self::kal_termin_mysql_standard($term[$i]);
      #
      # --- unterschiedliche Werte zaehlen (pid nicht mitgezaehlt)
      $m=0;
      for($k=0;$k<count($termin);$k=$k+1):
         $ke=$keys[$k];
         if($ke==$keypid) continue;
         $val=$term[$i][$ke];
         if($val==$termin[$ke]) continue;
         $m=$m+1;
         endfor;
      if($m<=0) return $term[$i][$keypid];
      endfor;
   }
public static function kal_insert_termin($termin) {
   #   Eintragen eines neuen Termins in die Datenbanktabelle.
   #   $termin           Array des Termins
   #   Rueckgabe:        Eintragung erfolgreich:   Id des neuen Termins (>0)
   #                     andernfalls:              Fehlermeldung
   #   =========================================================
   #   im Content-Managment-System sollte die Methode NUR EINMAL
   #   AUFGERUFEN werden (entweder im Frontend oder im Backend)
   #   =========================================================
   #
   $addon=self::this_addon;
   $keys=$addon::TAB_KEY;
   $keykat=$keys[1];   // kat_id
   $keywch=$keys[7];   // wochen
   $keymon=$keys[8];   // monate
   #
   # --- Standardform des Termins herstellen
   $term=$termin;
   if(empty($term[$keywch])) $term[$keywch]=0;
   if(empty($term[$keymon])) $term[$keymon]=0;
   #
   # --- Ueberpruefen, ob der Termin schon eingetragen ist
   $pid=self::kal_exist_termin($term);
   if(intval($pid)>0)
     return '<span class="kal_fail">Dieser Termin ist schon vorhanden: pid='.$pid.'</span>';
   #
   # --- Terminparameter zusammenstellen
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keyval=array();
   for($i=1;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$term[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      $keyval[$i]['key']  =$key;
      $keyval[$i]['value']=$val;
      endfor;
   #
   # --- Termin einfuegen
   cms_interface::insert_termin($keyval);
   #
   # --- neuen Termin verifizieren
   $pid=self::kal_exist_termin($term);
   if(intval($pid)>0):
     return $pid;
     else:
     return '<span class="kal_fail">Der Termin konnte nur unvollständig eingetragen werden</span>';
     endif;
   }
public static function kal_delete_termin($pid) {
   #   Loeschen eines Termins in der Datenbanktabelle.
   #   $pid            Id des Termins
   #   Rueckgabe:      leer, falls der Termin geloescht wurde bzw.
   #                   Fehlermeldung in rot (andernfalls)
   #   =========================================================
   #   im Content-Managment-System sollte die Methode NUR EINMAL
   #   AUFGERUFEN werden (entweder im Frontend oder im Backend)
   #   =========================================================
   #
   $addon=self::this_addon;
   $addon::$SPIELDATEN=FALSE;
   $keys=$addon::TAB_KEY;
   $keypid=$keys[0];   // pid
   $keykat=$keys[1];   // kat_id
   if(intval($pid)<=0)
     return '<span class="kal_fail">Bitte einen Termin zum Löschen benennen (Termin-Id>0)</span>';
   #
   $term=self::kal_select_termin_by_pid($pid);
   #
   # --- Pruefen, ob der Termin $pid vorhanden ist
   if(count($term)<=0)
     return '<span class="kal_fail">Termin nicht vorhanden</span>';
   #
   # --- Durchfuehrung
   cms_interface::delete_termin($keypid,$pid);
   #
   # --- Pruefen, ob der Termin tatsaechlich weg ist
   $term=self::kal_select_termin_by_pid($pid);
   if(count($term)>0)
     return '<span class="kal_fail">Der Termin konnte nicht gelöscht werden</span>';
   }
public static function kal_update_termin($pid,$termkor) {
   #   Korrigieren eines Termins in der Datenbanktabelle.
   #   $pid            Id des zu korrigierenden Termins
   #   $termkor        zu korrigierende Daten des Termins in Form
   #                   eines vollstaendigen Termin-Arrays (ohne Termin-Id)
   #   Rueckgabe:      leer, falls der Termin korrigiert wurde
   #                   Fehlermeldung (in rot), falls das Update fehlschlug
   #   =========================================================
   #   im Content-Managment-System sollte die Methode NUR EINMAL
   #   AUFGERUFEN werden (entweder im Frontend oder im Backend)
   #   =========================================================
   #
   $addon=self::this_addon;
   $addon::$SPIELDATEN=FALSE;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keypid=$keys[0];   // pid
   $keykat=$keys[1];   // kat_id
   $keynam=$keys[2];   // name
   $keydat=$keys[3];   // datum
   #
   # --- Auslesen des Termins mit der vorgegebenen Id
   $termin=self::kal_select_termin_by_pid($pid);
   if(count($termin)<=0)
     return '<span class="kal_fail">Der Termin ist nicht vorhanden</span>';
   #
   # --- Terminparameter zusammenstellen
   $keyval=array();
   for($i=1;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$termkor[$key];
      $type=substr($cols[$key][0],0,4);
      if($type=='date') $val=self::kal_datum_standard_mysql($val);
      if(substr($type,0,3)=='int' and empty($val)) $val=0;
      $keyval[$i]['key']  =$key;
      $keyval[$i]['value']=$val;
      endfor;
   #
   # --- Termin aktualisieren
   cms_interface::update_termin($keyval,$keypid,$pid);
   #
   # --- erneutes Auslesen des Termins und Vergleich mit eingegebener Korrektur
   $termsel=self::kal_select_termin_by_pid($pid);
   for($i=1;$i<count($termkor);$i=$i+1):
      $key=$keys[$i];
      if($key==$keypid) continue;
      if($termsel[$key]==$termkor[$key]) continue;
      return '<span class="kal_fail">Der Termin "'.$termin[$keynam].'" ('.$termin[$keydat].
         ') konnte nicht korrigiert werden</span>';
      break;
      endfor;
   }
#
#----------------------------------------- Auslesen von Termindaten
public static function kal_select_dbtermine($von,$bis,$katids,$keyword) {
   #   Auslesen von Terminen aus der Datenbanktabelle, gefiltert durch:
   #   Datumsbereich, und eine oder mehrere Kategorien sowie ein Stichwort.
   #   Rueckgabe als nummeriertes Array von Terminen (Nummerierung ab 1).
   #   Die Termine sind NACH DATUM SORTIERT.
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: vom ersten eingetragenen Termin an
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #                   falls leer: bis inkl. dem letzten eingetragenen Termin
   #   $katids         Array der ausgewaehlten Kategorie-Ids (Nummerierung ab 1)
   #                   falls leer: keine Termine zurueck gegeben
   #   $keyword        Stichwort, unabhaengig von Gross-/Kleinschreibung,
   #                   es wird gesucht in den Spalten:
   #                   name, ausrichter, ort, komm, text2, text3, text4, text5
   #
   if(count($katids)<=0) return array();   // keine Kategorien -> keine Termine
   #
   $addon=self::this_addon;
   $addon::$SPIELDATEN=FALSE;
   $keykat=$addon::TAB_KEY[1];   // kat_id
   $keydat=$addon::TAB_KEY[3];   // datum
   $keybeg=$addon::TAB_KEY[4];   // beginn
   #     Spalten, in denen das Stichwort gesucht wird
   $keynam=$addon::TAB_KEY[2];   // name
   $keyaus=$addon::TAB_KEY[9];   // ausrichter
   $keyort=$addon::TAB_KEY[10];  // ort
   $keykom=$addon::TAB_KEY[12];  // komm
   $keytx2=$addon::TAB_KEY[14];  // text2
   $keytx3=$addon::TAB_KEY[16];  // text3
   $keytx4=$addon::TAB_KEY[18];  // text4
   $keytx5=$addon::TAB_KEY[20];  // text5
   #
   #
   # --- Datumsbereich
   $where='';
   if(!empty($von)) $vonsql=self::kal_datum_standard_mysql($von);
   if(!empty($bis)) $bissql=self::kal_datum_standard_mysql($bis);
   if(!empty($von) and  empty($bis)) $where=$keydat.'>=\''.$vonsql.'\'';
   if( empty($von) and !empty($bis)) $where=$keydat.'<=\''.$bissql.'\'';
   if(!empty($von) and !empty($bis))
     $where=$keydat.'>=\''.$vonsql.'\' AND '.$keydat.'<=\''.$bissql.'\'';
   #
   # --- Restriktion Kategorie
   $wh='';
   for($i=1;$i<=count($katids);$i=$i+1) $wh=$wh.' OR '.$keykat.'='.$katids[$i];
   if(!empty($wh)) $wh=substr($wh,4);
   if(!empty($where)):
     if(str_contains($wh,' OR ')) $wh='('.$wh.')';
     $where=$where.' AND '.$wh;
     else:
     $where=$wh;
     endif;
   #
   # --- Restriktion Stichwort
   #     'SQL-LIKE sucht unabhaengig von Gross-/Kleinschreibung
   if(!empty($keyword))
     $where=$where.' AND '.
              '('.$keynam.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keyaus.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keyort.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keykom.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keytx2.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keytx3.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keytx4.' LIKE \'%'.$keyword.'%\''.
           ' OR '.$keytx5.' LIKE \'%'.$keyword.'%\')';
   #
   # --- alle Termine mit den vorgegebenen Restriktionen auslesen
   $order=$keydat.','.$keybeg.' ASC';
   $term=cms_interface::select_termin($where,$order);
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
   #   Wiederholungstermins (woechentlich oder monatlich):
   #      einzelner Folgetermin (mehrtaegig)       --> taegl.     Einzeltermine
   #      einzelner woechentl. Wiederholungstermin --> woechentl. Einzeltermine
   #      einzelner monatl. Wiederholungstermin    --> monatl.    Einzeltermine
   #      mehrtaeg. woechentl. Wiederholungstermin --> woechentl. Folgetermine
   #      mehrtaeg. monatl. Wiederholungstermin    --> monatl.    Folgetermine
   #   Die aufgespaltenen Termine erhalten einen neuen Spaltenwert von datum,
   #   ihre Spaltenwerte tage(>1), wochen(>0), monate(>0) bleiben erhalten.
   #   Sie werden als nummeriertes Array (Nummerierung ab 1) zurueck gegeben.
   #   $termin         Termin (assoziatives Array)
   #   $keynr          Nummer des Schluessels von tage/wochen/monate (6/7/8)
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];         // datum
   $keybeg=$addon::TAB_KEY[4];         // beginn
   $keyend=$addon::TAB_KEY[5];         // ende
   $nrtage=6;   // Key-Nr. tage
   $nrwoch=7;   // Key-Nr. wochen
   $nrmona=8;   // Key-Nr. monate
   $keytag=$addon::TAB_KEY[$nrtage];   // tage
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
public static function kal_collect_termine($von,$bis,$katids,$keyword,$kontif) {
   #   Rueckgabe aller Termine, die in einem Datumsbereich liegen, zu einer oder
   #   mehreren Terminkategorien gehoeren und ein gegebenes Stichwort enthalten,
   #   in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Die Termine sind NICHT nach Datum sortiert.
   #   Woechentlich oder monatlich wiederkehrende Termine (je ein Eintrag in
   #   der Datenbanktabelle) werden aufgespalten und treten im Ergebnis-Array
   #   ggf. mehrfach auf. Mehrtaegige Termine werden wahlweise auch aufgespalten
   #   in Einzeltermine. Von den aufgespaltenen Termine werden nur genau die in
   #   das Ergebnis-Array aufgenommen, die innerhalb des Datumsbereichs liegen.
   #   In den aufgespaltenen Terminen bleiben alle Paramter erhalten, auch
   #   tage>1, wochen>0, monate>0, nur datum erhaelt das Datum des Tages.
   #   $von            Datum des ersten Tages des Datumsbereichs
   #                   ='': Datum des fruehesten eingetragenen Termins
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #                   ='': Datum des spaetesten eingetragenen Termins
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #                   falls leer, werden keine Termine zurueck gegeben
   #   $keyword        Stichwort, unabhaengig von Gross-/Kleinschreibung,
   #                   es wird gesucht in den Werten der Parameter:
   #                   name, ausrichter, ort, komm, text2, text3, text4, text5
   #   $kontif         >0: mehrtaegige Termine werden aufgespalten in ihre
   #                       Einzeltermine (gebraucht in den Kalendermenues)
   #                   =0: mehrtaegige Termine werden NICHT aufgespalten in
   #                       ihre Einzeltermine (gebraucht nur in der Terminliste)
   #
   if(count($katids)<=0) return array();   // keine Kategorien -> keine Termine
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
   # --- zunaechst alle Termine vor $bis
   $stdvon=$von;
   $stdbis=$bis;
   if($katids[1]>=$addon::SPIEL_KATID):
     $ter=self::kal_select_spieltermine('',$stdbis,$katids,$keyword);
     else:
     $ter=self::kal_select_dbtermine('',$stdbis,$katids,$keyword);
     endif;
   if(count($ter)<=0) return array();
   #
   #     $ter ist in beiden Faellen nach Datum sortiert:
   if(empty($stdvon)) $stdvon=$ter[1][$keydat];
   if(empty($stdbis)) $stdbis=$ter[count($ter)][$keydat];
   #
   # --- woechentliche mehrtägige Termine in einzelne mehrtaegige Termine aufspalten
   $m=0;
   $term=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      if($ter[$i][$keywch]>0 and $ter[$i][$keytag]>1):
        #     aufspalten
        $single=self::kal_expand_multitermin($ter[$i],$nrwch);
        for($k=1;$k<=count($single);$k=$k+1):
           $m=$m+1;
           $term[$m]=$single[$k];
           endfor;
        else:
        #     nicht aufspalten
        $m=$m+1;
        $term[$m]=$ter[$i];
        endif;
      endfor;
   $ter=$term; // alle Einzeltermine, aufgesp. woechentl. Termine, monatl. Termine
   #
   # --- monatliche mehrtägige Termine in einzelne mehrtaegige Termine aufspalten
   $m=0;
   $term=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      if($ter[$i][$keymon]>0 and $ter[$i][$keytag]>1):
        #     aufspalten
        $single=self::kal_expand_multitermin($ter[$i],$nrmon);
        for($k=1;$k<=count($single);$k=$k+1):
           $m=$m+1;
           $term[$m]=$single[$k];
           endfor;
        else:
        #     nicht aufspalten
        $m=$m+1;
        $term[$m]=$ter[$i];
        endif;
      endfor;
   $ter=$term;
   #
   # --- Weiter aufspalten, jetzt nur noch vorblieben:
   #     eintaegige Einzeltermine (nicht aufzuspalten)
   #     woechentlich oder monatlich wiederkehrende eintaegige Termine
   #     mehrtaegige Termine ($kontif=0/1: nicht aufzuspalten/aufzuspalten)
   $m=0;
   $term=array();
   for($i=1;$i<=count($ter);$i=$i+1):
      #
      # --- eintaegige Termine (nicht woechentl. oder monatl. wiederkehrend)
      if($ter[$i][$keymon]<=0 and $ter[$i][$keywch]<=0 and $ter[$i][$keytag]<=1):
        $m=$m+1;
        $term[$m]=$ter[$i];
        endif;
      #
      # --- woechentlich wiederkehrende eintaegige Termine
      if($ter[$i][$keywch]>0 and $ter[$i][$keytag]<=1):
        $singles=self::kal_expand_multitermin($ter[$i],$nrwch);
        for($k=1;$k<=count($singles);$k=$k+1):
           $m=$m+1;
           $term[$m]=$singles[$k];
           endfor;
        endif;
      #
      # --- monatlich wiederkehrende eintaegige Termine
      if($ter[$i][$keymon]>0 and $ter[$i][$keytag]<=1):
        $singles=self::kal_expand_multitermin($ter[$i],$nrmon);
        for($k=1;$k<=count($singles);$k=$k+1):
           $m=$m+1;
           $term[$m]=$singles[$k];
           endfor;
        endif;
      #
      # --- mehrtaegige Termine zunaechst nicht aufspalten
      if($ter[$i][$keytag]>1):
        $m=$m+1;
        $term[$m]=$ter[$i];
        endif;
      endfor;
   #
   # --- Heraussuchen von Einzelterminen, die in den Datumsbereich hineinfallen,
   #     bzw. von mehrtaegigen Terminen, die in den Datumsbereich hineinragen
   $ter=array();
   $m=0;
   for($i=1;$i<=count($term);$i=$i+1):
      $tage=$term[$i][$keytag];
      $singles=array();
      if($tage<=1):
        #     Einzeltermine
        $singles[1]=$term[$i];
        else:
        #     mehrtaegige Termine, ragen sie in den Datumsbereich hinein?
        $singles=self::kal_expand_multitermin($term[$i],$nrtag);
        endif;
      for($k=1;$k<=count($singles);$k=$k+1):
         $dat=$singles[$k][$keydat];
         if(kal_termine_kalender::kal_datum1_vor_datum2($dat,$stdvon)) continue; // zu frueh
         if(kal_termine_kalender::kal_datum1_vor_datum2($stdbis,$dat)) continue; // zu spaet
         if($kontif>=1):
           $m=$m+1;
           $ter[$m]=$singles[$k];
           else:
           $m=$m+1;
           $ter[$m]=$term[$i];
           break;
           endif;
         endfor;
      endfor;
   #
   return $ter;
   }
public static function kal_sort_termine($termin) {
   #   Sortieren eines Arrays von Terminen nach Datum (und Uhrzeit des Beginns).
   #   $termin         Array der Termine, Nummerierieng ab 1
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
public static function kal_get_termine_all($von,$bis,$katids,$keyword,$kontif) {
   #   Rueckgabe aller Termine, die in einem Datumsbereich liegen, zu einer oder
   #   mehreren Terminkategorien gehoeren und ein gegebenes Stichwort enthalten,
   #   in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   DIE TERMINE SIND NACH DATUM SORTIERT.
   #   $von            Datum des ersten Tages des Datumsbereichs
   #                   ='': Datum des ersten eingetragenen Termins
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #                   ='': Datum des letzten eingetragenen Termins
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #   $keyword        Stichwort, unabhaengig von Gross-/Kleinschreibung,
   #                   es wird gesucht in den Spalten:
   #                   name, ausrichter, ort, komm, text2, text3, text4, text5
   #   $kontif         mehrtaegige Termine werden (nicht) aufgespalten in
   #                   ihre Einzeltermine (vergl kal_collect_termine)
   #
   $term=self::kal_collect_termine($von,$bis,$katids,$keyword,$kontif);
   #     $term ist noch NICHT SORTIERT (nach Datum)
   return self::kal_sort_termine($term);
   }
public static function kal_subst_wiederholungstermine($termin) {
   #   Aussortieren von Exemplaren von woechentlich/monatlich wiederkehrenden
   #   Terminen in einem Termin-Array, fuer die ein Einzeltermin als Ersatz
   #   vorhanden ist. Der Ersatztermin muss in den Parametern Name, Datum,
   #   Kategorie-Id mit dem Wiederholungstermin uebereinstimmen. Ausserdem muss
   #   der Ersatztermin ein echter Einzeltermin sein (tage<=1).
   #   Rueckgabe eines entsprechend gefilterten Teil-Arrays (Nummerierung ab 1).
   #   DIE TERMINE SIND NACH DATUM SORTIERT.
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
           if($k==$i) continue;
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
public static function kal_get_termine($von,$bis,$katids,$keyword,$kontif) {
   #   Rueckgabe aller Termine, die in einem Datumsbereich liegen, zu einer oder
   #   mehreren Terminkategorien gehoeren und ein gegebenes Stichwort enthalten,
   #   in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Hier werden ggf. woechentlich/monatlich wiederkehrende Termine entfernt,
   #   falls entsprechende Einzeltermine als Ersatztermine vorhanden sind.
   #   Die zurueck gegebenen Termine sind NACH DATUM SORTIERT.
   #   Parameter       (wie in kal_get_termine_all)
   #   $von            Datum des ersten Tages des Datumsbereichs
   #                   ='': Datum des ersten eingetragenen Termins
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #                   ='': Datum des letzten eingetragenen Termins
   #   $katids         Array der Kategorie-Ids (Nummerierung ab 1)
   #   $keyword        Stichwort, unabhaengig von Gross-/Kleinschreibung,
   #                   es wird gesucht in den Spalten:
   #                   name, ausrichter, ort, komm, text2, text3, text4, text5
   #   $kontif         mehrtaegige Termine aufgespalten/nicht aufgespalten
   #
   $termin=self::kal_get_termine_all($von,$bis,$katids,$keyword,$kontif);
   return self::kal_subst_wiederholungstermine($termin);
   }
#
#----------------------------------------- Erzeugen von Spiel-Termindaten
public static function kal_set_spieldaten($datum) {
   #   Rueckgabe von kuenstlichen Termindaten eines Tages in Form eines
   #   assoziativen Termin-Arrays (leere Rueckgabe, falls an dem Tage keine
   #   Termine vorliegen).
   #   $datum          Datum des Tages (standardisiertes Datum)
   #
   $addon=self::this_addon;
   $addon::$SPIELDATEN=TRUE;
   $TAB=$addon::TAB_KEY;
   #
   # --- heutiger Tage
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Radius in Anzahl Wochen um den heutigen Tag, in denen die Termine an den
   #     jeweiligen Wochentagen definiert sind (Mo, Di, Mi, Do, Fr, Sa, So)
   $max=$addon::SPIEL_RAD;  // 12
   $dif=$addon::SPIEL_DIF;  //  3
   $radius=array(1=>$max-3*$dif,  //  3
                 2=>$max-2*$dif,  //  6
                 3=>$max-$dif,    //  9
                 4=>$max,         // 12
                 5=>$max-$dif,    //  9
                 6=>$max-2*$dif,  //  6
                 7=>$max-3*$dif); //  3
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
   $kats=$addon::kal_get_spielkategorien();
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
public static function kal_select_spieltermine($von,$bis,$katids,$keyword) {
   #   Rueckgabe aller Termine, die in einem Datumsbereich liegen, zu einer oder
   #   mehreren Terminkategorien gehoeren und ein gegebenes Stichwort enthalten,
   #   in Form eines nummerierten Arrays (Nummerierung ab 1).
   #   Die Termine sind NACH DATUM SORTIERT. Mehrtaegige Termine werden nicht
   #   in Einzeltermine aufgespalten. Sie werden daher nur dann zurueck gegeben,
   #   wenn ihr Starttermin innerhalb des Datumsbereichs liegt. - Es wird hier
   #   vorausgesetzt, dass Spieltermine nicht woechentlich oder monatlich
   #   wiederkehrend sind.
   #   $von            Datum des ersten Tages (ggf. verkuerztes Standardformat)
   #                   ='': Datum vor dem ersten definierten Termin
   #   $bis            Datum des letzten Tages (ggf. verkuerztes Standardformat)
   #                   ='': Datum nach dem letzten definierten Termin
   #   $katids         Array der Kategorien (Nummerierung ab 1)
   #   $keyword        Stichwort, unabhaengig von Gross-/Kleinschreibung,
   #                   es wird gesucht in den Spalten:
   #                   name, ausrichter, ort, komm, text2, text3, text4, text5
   #
   if(count($katids)<=0)    return array();   // keine Kategorien -> keine Termine
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // kat_id
   #     Spalten, in denen das Stichwort gesucht wird
   $keynam=$addon::TAB_KEY[2];   // name
   $keyaus=$addon::TAB_KEY[9];   // ausrichter
   $keyort=$addon::TAB_KEY[10];  // ort
   $keykom=$addon::TAB_KEY[12];  // komm
   $keytx2=$addon::TAB_KEY[14];  // text2
   $keytx3=$addon::TAB_KEY[16];  // text3
   $keytx4=$addon::TAB_KEY[18];  // text4
   $keytx5=$addon::TAB_KEY[20];  // text5
   #
   $heute=kal_termine_kalender::kal_heute();
   $dif=($addon::SPIEL_RAD+1)*7;
   if(!empty($von)):
     $stdvon=$von;
     else:
     $stdvon=kal_termine_kalender::kal_datum_vor_nach($heute,-$dif);
     endif;
   if(!empty($bis)):
     $stdbis=$bis;
     else:
     $stdbis=kal_termine_kalender::kal_datum_vor_nach($heute,$dif);
     endif;
   #
   # --- Termine mit falscher Kategorie aussortieren
   $term=array();
   $m=0;
   $datum=$stdvon;
   while(!kal_termine_kalender::kal_datum1_vor_datum2($stdbis,$datum)):
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
   #
   # --- Termine ohne das gegebene Stichwort aussortieren
   if(empty($keyword)):
     return $term;
     else:
     $keyw=strtolower($keyword);
     $te=array();
     $m=0;
     for($i=1;$i<=count($term);$i=$i+1)
        if(str_contains(strtolower($term[$i][$keynam]),$keyw) or
           str_contains(strtolower($term[$i][$keyaus]),$keyw) or
           str_contains(strtolower($term[$i][$keyort]),$keyw) or
           str_contains(strtolower($term[$i][$keykom]),$keyw) or
           str_contains(strtolower($term[$i][$keytx2]),$keyw) or
           str_contains(strtolower($term[$i][$keytx3]),$keyw) or
           str_contains(strtolower($term[$i][$keytx4]),$keyw) or
           str_contains(strtolower($term[$i][$keytx5]),$keyw)   ):
          $m=$m+1;
          $te[$m]=$term[$i];
          endif;
     return $te;
     endif;
   }
public static function kal_select_spieltermin_by_pid($pid) {
   #   Auslesen und Rueckgabe eines Spieltermins mittels seiner Id.
   #   $pid            Id des Termins
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   #
   $kats=$addon::kal_get_spielkategorien();
   for($i=0;$i<count($kats);$i=$i+1) $katids[$i+1]=$kats[$i]['id'];
   $term=self::kal_select_spieltermine('','',$katids,'');
   for($i=1;$i<=count($term);$i=$i+1)
      if($term[$i][$keypid]==$pid):
        return $term[$i];
        endif;
   return array();
   }
public static function kal_select_termin($pid) {
   #   Auslesen und Rueckgabe eines Termins als assoziatives Array mittels
   #   seiner Id. Es werden zunaechst die Datenbank-Termine durchsucht.
   #   Falls dort kein entsprechender Termin gefunden wird, wird nach einem
   #   Spieltermin mit dieser Id gesucht und ggf. zurueck gegeben.
   #   $pid            Id des Termins
   #
   $addon=self::this_addon;
   if($addon::$SPIELDATEN):
     $termin=self::kal_select_spieltermin_by_pid($pid);
     else:
     $termin=self::kal_select_termin_by_pid($pid);
     endif;
   return $termin;
   }
#
#----------------------------------------- Terminliste
public static function kal_uhrzeit_string($termin) {
   #   Rueckgabe eines Strings, in dem die Zeitangaben aufbereitet sind,
   #   zu einem gegebenen Termin.
   #   $termin         assoziatives Array des Termins
   #
   $addon=self::this_addon;
   $datsta=$termin[$addon::TAB_KEY[3]];   // datum
   $beginn=$termin[$addon::TAB_KEY[4]];   // beginn
   $ende  =$termin[$addon::TAB_KEY[5]];   // ende
   $tage  =$termin[$addon::TAB_KEY[6]];   // tage
   #
   $uhrz='';
   if($tage<=1):
     #
     # --- bei eintaegigen Terminen
     if(substr($beginn,0,1)=='0') $beginn=substr($beginn,1);
     if(substr($ende,0,1)  =='0') $ende  =substr($ende,1);
     if(!empty($beginn)):
       $uhrz=$beginn;
       if(!empty($ende)):
         $uhrz=$uhrz.' - '.$ende.' Uhr';
         else:
         $uhrz=$uhrz.' Uhr';
         endif;
       else:
       if(!empty($ende)) $uhrz='Ende: '.$ende.' Uhr';
       endif;
     else:
     #
     # --- bei Terminen ueber mehrere Tage
     if(!empty($beginn)) $uhrz='Beginn '.$beginn.' Uhr';
     if(!empty($ende)):
       if(!empty($uhrz)) $uhrz=$uhrz.', ';
       $uhrz=$uhrz.'Ende '.$ende.' Uhr';
       endif;
     endif;
   return $uhrz;
   }
public static function kal_zusatzzeiten_string($termin) {
   #   Rueckgabe eines Strings im HTML-Format, in dem die zusaetzlichen
   #   Zeitangaben zu einem gegebenen Termin zeilenweise dargestellt werden.
   #   $termin         assoziatives Array des Termins
   #
   $addon=self::this_addon;
   $zeit2=$termin[$addon::TAB_KEY[13]];
   $text2=$termin[$addon::TAB_KEY[14]];
   $zeit3=$termin[$addon::TAB_KEY[15]];
   $text3=$termin[$addon::TAB_KEY[16]];
   $zeit4=$termin[$addon::TAB_KEY[17]];
   $text4=$termin[$addon::TAB_KEY[18]];
   $zeit5=$termin[$addon::TAB_KEY[19]];
   $text5=$termin[$addon::TAB_KEY[20]];
   $zusatz='';
   if(!empty($zeit2)):
     $zeit=$zeit2;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text2;
     endif;
   if(!empty($zeit3)):
     $zeit=$zeit3;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text3;
     endif;
   if(!empty($zeit4)):
     $zeit=$zeit4;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text4;
     endif;
   if(!empty($zeit5)):
     $zeit=$zeit5;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text5;
     endif;
   return $zusatz;
   }
public static function kal_terminliste_intern($termin) {
   #   Rueckgabe einer Liste von Terminen mit fast allen Parametern eines
   #   Zeitabschnitts in Form eines HTML-Codes. Leere Parameter sowie die
   #   Terminkategorie werden nicht mit ausgegeben. Die Terminkategorien sind
   #   ggf. farblich markiert.
   #   $termin         Array der Termine (Nummerierung ab 1)
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];
   $keynam=$addon::TAB_KEY[2];
   $keydat=$addon::TAB_KEY[3];
   $keytag=$addon::TAB_KEY[6];
   $keyaus=$addon::TAB_KEY[9];
   $keyort=$addon::TAB_KEY[10];
   $keylnk=$addon::TAB_KEY[11];
   $keykom=$addon::TAB_KEY[12];
   #
   # --- Formular
   $string='
<table class="kal_table">';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      $kat_id=$term[$keykat];
      if($kat_id>$addon::SPIEL_KATID) $kat_id=$kat_id-$addon::SPIEL_KATID;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      #
      # --- Startdatum aufbereiten
      $datum=$term[$keydat];
      $datsta=$datum;
      $arr=explode('.',$datsta);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $jahr1=$arr[2];
      $wot1=kal_termine_kalender::kal_wotag($datsta);
      #
      # --- Enddatum aufbereiten
      $dat2=$jahr1;
      $tage=$term[$keytag];
      if($tage>1):
        $datend=kal_termine_kalender::kal_datum_vor_nach($datsta,$tage-1);
        $arr=explode('.',$datend);
        if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
        if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
        $dat2=$arr[0].'.'.$arr[1].'.'.$arr[2];
        $dat2=kal_termine_kalender::kal_wotag($datend).',&nbsp;'.$dat2;
        $trenn='&nbsp;/&nbsp;';
        if($tage>2) $trenn='&nbsp;-&nbsp;';
        $dat2=$trenn.$dat2;
        if($arr[2]>$jahr1) $dat1=$dat1.$jahr1;
        endif;
      #
      # --- Datumsangabe
      $datstr=$wot1.',&nbsp;'.$dat1.$dat2.':';
      $zeile='
    <tr valign="top">
        <th class="termlist_th">
            '.$datstr.'</th>
        <td class="termlist_td termbord_'.$kat_id.'">';
      $str='';
      #
      # --- Uhrzeiten aufbereiten
      $uhrz=self::kal_uhrzeit_string($term);
      if(!empty($uhrz))
        $str=$str.'
            '.$uhrz.': ';
      #
      # --- Veranstaltungsbezeichnung
      $str=$str.$term[$keynam];
      #
      # --- Ort
      $ort=$term[$keyort];
      if(!empty($ort)):
        if(!empty($str)) $str=$str.', ';
        $str=$str.'
            <span class="termlist_ort">'.$ort.'</span>';
        endif;
      #
      # --- Ausrichter
      $ausrichter=$term[$keyaus];
      if(!empty($ausrichter)):
        if(!empty($str)) $str=$str.', ';
        $str=$str.'
            <span class="termlist_ausrichter">'.$ausrichter.'</span>';
        endif;
      #
      # --- Link
      $link =$term[$keylnk];
      if(!empty($link)):
        $tar='';
        if(substr($link,0,4)=='http' and strpos($link,'://')>0)
          $tar=' target="_blank"';
        if(!empty($str)) $str=$str.', ';
        $str=$str.'<a href="'.$link.'"'.$tar.'>Hinweise des Ausrichters</a>';
        endif;
      #
      # --- Zusatzzeiten aufbereiten
      $zusatz=self::kal_zusatzzeiten_string($term);
      if(!empty($zusatz))
        $str=$str.$zusatz;
      #
      # --- Hinweise zur Veranstaltung
      $hinw=$term[$keykom];
      if(!empty($hinw))
        $str=$str.'<br>
            '.$hinw;
      #
      $zeile=$zeile.$str.'</td></tr>';
   #
      $string=$string.$zeile;
      endfor;
   $string=$string.'
</table>
';
   return $string;
   }
public static function kal_terminliste($von,$bis,$katids) {
   #   Rueckgabe einer Liste von Terminen mit fast allen Parametern eines
   #   Zeitabschnitts in Form eines HTML-Codes. Leere Parameter sowie die
   #   Terminkategorie werden nicht mit ausgegeben. Die Terminkategorien sind
   #   ggf. farblich markiert.
   #   $von            Datum des ersten Tages des Datumsbereichs
   #                   (Format: tt.mm.yyyy)
   #                   ='': Datum des ersten eingetragenen Termins
   #   $bis            Datum des letzten Tages des Datumsbereiches
   #                   ='': Datum des letzten eingetragenen Termins
   #                   (Format: tt.mm.yyyy)
   #   $katids         Array der fuer den Redakteur erlaubten Kategorie-Ids
   #                   (Nummerierung ab 1, Spieldaten: alle Kategorien)
   #
   # --- Termine auslesen
   $keyword='';
   $termin=self::kal_get_termine($von,$bis,$katids,$keyword,0);
   if(count($termin)<=0) return;
   #
   # --- Terminliste erzeugen und zurueck geben
   return self::kal_terminliste_intern($termin);
   }
}
?>
