<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2022
*/
define ('ACTION_START',   'START');
define ('ACTION_INSERT',  'INSERT');
define ('ACTION_DELETE',  'DELETE');
define ('ACTION_UPDATE',  'UPDATE');
define ('ACTION_COPY',    'COPY');
define ('ACTION_SELECT',  'SELECT');
define ('ACTION_NAME',    'ACTION');
define ('PID_NAME',       'PID');
define ('VALUE_NAME',     'value_');
define ('CALL_NUM',       'call_number');
#
class kal_termine_formulare {
#
#----------------------------------------- Inhaltsuebersicht
#   Terminformulare
#         kal_terminblatt($termin,$datum)
#         kal_proof_termin($termin)
#         kal_select_kategorie($name,$kid,$katids,$all)
#         kal_action($action,$pid)
#         kal_eingabeformular()
#         kal_prepare_action($action,$pid,$datum,$call_num,$error,$str)
#         kal_eingeben()
#         kal_korrigieren()
#         kal_kopieren()
#         kal_loeschen()
#   Terminliste
#         kal_terminliste($termin,$datum_as_link)
#         kal_uhrzeit_string($termin)
#         kal_zusatzzeiten_string($termin)
#
#----------------------------------------- Terminformulare
public static function kal_terminblatt($termin,$datum) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins.
   #   benutzte functions:
   #      kal_termine_menues::kal_terminblatt($termin,$datum,$ruecklinks)
   #
   return kal_termine_menues::kal_terminblatt($termin,$datum,0);
   }
public static function kal_proof_termin($termin) {
   #   Ueberpruefen der Felder eines Termin-Arrays auf
   #   - leere Pflichtfelder
   #   - Format und Zahlen der Datumsangabe
   #   - Format und Zahlen der Zeitangaben
   #   $termin         Termindaten in Form eines assoziativen Arrays
   #   Rueckgabe entsprechender Fehlermeldungen (rote Schrift)
   #   leere Ruckgabe, falls kein Fehler vorliegt
   #   benutzte functions
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $vor='<span class="kal_fail">';
   #
   # --- Pruefen der Restriktionen
   $tage=0;
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==COL_PID) continue;
      $name=$cols[$key][1];
      $pflicht=$cols[$key][3];
      $error=$vor.'Das Terminfeld <tt>\''.$name.'\'</tt> muss <tt>\''.$pflicht.'\'</tt> sein</span>';
      #     Veranstaltungsname/-datum nicht leer
      if(($key==COL_NAME or $key==COL_DATUM) and empty($termin[$key])) return $error;
      #     Dauer in Tagen / Kategorie-Id >=1
      if(($key==COL_TAGE or $key==COL_KATID) and $termin[$key]<1) return $error;
      endfor;
   #
   # --- nicht zugleich mehrtaegige und woechentlich wiederholt
   if($termin[COL_TAGE]>1 and $termin[COL_WOCHEN]>0)
     return $vor.'\'mehrtägig\' und zugleich \'wöchentlich wiederkehrend\' ist nicht vorgesehen</span>';
   #
   # --- Pruefen der Datumsangabe: Standardformat
   $errdat1=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].
            '</tt>\': hat kein Standardformat \'<tt>'.$cols[COL_DATUM][2].'</tt>\'</span>';
   $arr=explode('.',$termin[COL_DATUM]);
   if(count($arr)<>3) return $errdat1;
   #
   # --- Pruefen der Datumsangabe: Jahreszahl
   $jahr=$arr[2];
   $errdat2=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].
            '</tt>\': Jahreszahl kein Integer</span>';
   if(intval($jahr)<=0 and $jahr!='00') return $errdat2;
   #
   # --- Pruefen der Datumsangabe: Monatszahl
   $monat=intval($arr[1]);
   $errdat3=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].
            '</tt>\': Monatszahl nicht zwischen 1 und 12</span>';
   if($monat<1 or $monat>12) return $errdat3;
   #
   # --- Pruefen der Datumsangabe: Tageszahl
   $tag=intval($arr[0]);
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $mt=$mtage[$monat];
   $mon=kal_termine_kalender::kal_monate();
   $moname=$mon[$monat];
   $errdat4=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].
            '</tt>\': Tageszahl im '.$moname.' nicht zwischen 1 und '.$mt.'</span>';
   if($tag<1 or $tag>$mt) return $errdat4;
   #
   # --- Schleife ueber die Zeitangaben
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $arr=explode(' ',$cols[$key][0]);
      if($arr[0]!='time' or empty($termin[$key])) continue;
      #
      # --- Pruefen der Zeitangaben: Standardformat
      $val=$termin[$key];
      $errtim1=$vor.'Zeitangabe \'<tt>'.$val.'</tt>\': hat kein Standardformat \'<tt>'.$cols[$key][2].'</tt>\'</span>';
      $arr=explode(':',$val);
      if(count($arr)>2) return $errtim1;
      #
      # --- Pruefen der Zeitangaben: Stundenzahl
      $std=intval($arr[0]);
      $errtim2=$vor.'Zeitangabe \'<tt>'.$val.'</tt>\': Stundenzahl nicht zwischen 0 und 23</span>';
      if($std<0 or $std>23 or ($std==0 and $arr[0]!='' and $arr[0]!='0' and $arr[0]!='00'))
        return $errtim2;
      #
      # --- Pruefen der Zeitangaben: Minutenzahl
      $strmin='';
      $min=0;
      if(count($arr)==2):
        $strmin=$arr[1];
        $min=intval($strmin);
        endif;
      $errtim3=$vor.'Zeitangabe \'<tt>'.$val.'</tt>\': Minutenzahl nicht zwischen 0 und 59</span>';
      if($min<0 or $min>59 or ($min==0 and $strmin!='' and $strmin!='0' and $strmin!='00'))
        return $errtim3;
      endfor;
   return '';
   }
public static function kal_select_kategorie($name,$kid,$katids,$all) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der erlaubten Kategorien.
   #   Die erlaubten Kategorien haengen von den Terminkategorie-Rollen ab,
   #   die der Redakteur hat, der den aktuellen Artikel angelegt hat.
   #   $name           Name des select-Formulars
   #   $kid            Id der ggf. schon ausgewaehlten Kategorie,
   #                   falls leer/0, werden alle Kategorien angenommen
   #   $katids         Array der fuer den Redakteur erlaubten Terminkategorien
   #                   (Nummerung ab 1)
   #   $all            =TRUE: es kann auch 'alle Kategorien' ausgewaehlt werden
   #                   sonst: es kann nur genau eine Kategorie ausgewaehlt werden
   #   benutzte functions:
   #      kal_termine_config::kal_get_terminkategorien()
   #
   $selkid=$kid;
   if($katids[1]<SPIEL_KATID):
     #
     # --- Datenbankdaten
     if($selkid<=0) $selkid=0;
     #     verfuegbare Kategorien
     $kat=kal_termine_config::kal_get_terminkategorien();
     #     erlaubte Kategorien, entsprechend den Rollen des Autors des aktuellen Artikels
     $kat_ids=$katids;
     else:
     #
     # --- Spieldaten
     if($selkid<=0) $selkid=SPIEL_KATID;
     #     verfuegbare Kategorien
     $kat=kal_termine_tabelle::kal_get_spielkategorien();
     #    alle Kategorien erlaubt
     $kat_ids=array();
     for($i=1;$i<=count($kat);$i=$i+1) $kat_ids[$i]=$kat[$i-1]['id'];
     endif;
   #
   # --- Einschraenkung auf die erlaubten Kategorien
   $kate=array();
   $m=0;
   for($i=0;$i<count($kat);$i=$i+1)
      for($k=1;$k<=count($kat_ids);$k=$k+1)
         if($kat[$i]['id']==$kat_ids[$k]):
           $m=$m+1;
           $kate[$m]=$kat[$i];
           endif;
   #
   # --- Select-Formular
   $string='
            <select name="'.$name.'" class="kal_col5">';
   #     ALLE Terminkategorien
   if($all and count($kate)>1):   // nur wenn mehr als eine Kategorie zur Wahl steht
     if($katids[1]<SPIEL_KATID):
       $akid=0;
       else:
       $akid=SPIEL_KATID;
       endif;
     if($selkid==$akid):
       $sel='class="kal_col5" selected="selected"';
       else:
       $sel='class="kal_col6"';
       endif;   
     $string=$string.'
                <option value="'.$akid.'" '.$sel.'>(alle)</option>';
     endif;
   #     einzelne Terminkategorien
   for($i=1;$i<=count($kate);$i=$i+1):
      if($kate[$i]['id']==$selkid):
        $sel='class="kal_col5" selected="selected"';
        else:
        $sel='class="kal_col6"';
        endif;
      $option='
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
      $string=$string.$option;
      endfor;
   $string=$string.'
            </select>';
   return $string;
   }
public static function kal_action($action,$pid) {
   #   Rueckgabe des HTML-Codes einer 2-spaltigen Tabelle fuer ein Aktionsformular
   #   (kein Formularanfang/-ende enthalten). Es enthaelt einen oder mehrere Radio-
   #   Buttons fuer die Auswahl der gewuenschten Aktion und einen Durchfuehren-Button.
   #   Der erste Radio-Button ermoeglicht jeweils den Abbruch der Aktion.
   #   $action         erste Aktion, die zur Auswahl steht.
   #                   ='':            Abbruch
   #                   =ACTION_INSERT: ein neuer Termin soll eingetragen werden
   #                   =ACTION_DELETE: ein Termin soll geloescht werden
   #                   =ACTION_UPDATE: ein Termin soll korrigiert werden
   #                   =ACTION_COPY:   ein Termin soll kopiert werden
   #                   =ACTION_SELECT: neben dem Abbruch 3 Aktionen zur Auswahl:
   #                                   ACTION_DELETE, ACTION_UPDATE, ACTION_COPY
   #   $pid            Id des Termins, der geloescht/korrigiert/kopiert werden soll
   #   Mit Durchfuehrung der Aktion werden diese POST-Parameter uebergeben:
   #                   ACTION_NAME=ACTION_START  (Abbruch)      oder
   #                   ACTION_NAME=ACTION_INSERT (neuer Termin) oder
   #                   ACTION_NAME=ACTION_UPDATE (korrigieren)  oder
   #                   ACTION_NAME=ACTION_DELETE (loeschen)     oder
   #                   ACTION_NAME=ACTION_COPY   (kopieren)
   #                      in den letzten 3 Faellen zusetzlich:
   #                   PID_NAME=$pid               (hidden)
   #                   KAL_DATUM=$_POST[KAL_DATUM] (hidden)
   #
   # --- Return mit Submit-Button Abbrechen
   if(empty($action) or $action==ACTION_START) return '
<div><br><input type="hidden" name="'.ACTION_NAME.'" value="'.ACTION_START.'">
<button class="btn btn-save" type="submit">Abbrechen</button></div>
';
   #
   # --- POST-Parameter einlesen
   $call_num=1;
   if(!empty($_POST[CALL_NUM])) $call_num=$_POST[CALL_NUM];
   $datum='';
   if(!empty($_POST[KAL_DATUM])) $datum=$_POST[KAL_DATUM];
   #
   # --- Formular
   $str='
<div class="'.CSS_EINFORM.'">
<table class="kal_table">';
   #
   # --- Radio-Button 'Abbrechen' ...
   $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_START.'"></td>
        <td class="td_einf">&nbsp; Abbrechen</td></tr>';
   #
   if($action!=ACTION_SELECT):
     #
     # --- ... und ein weiterer Radion-Button
     $check=' checked="checked"';
     #     Radio-Button 'Eintragen'
     if($action==ACTION_INSERT):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_INSERT.'"'.$check.'></td>
        <td class="td_einf">&nbsp; neuen Termin eintragen</td></tr>';
       endif;
     #     Radio-Button 'Loeschen'
     if($action==ACTION_DELETE):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_DELETE.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin löschen</td></tr>';
       endif;
     #     Radio-Button 'Korrigieren'
     if($action==ACTION_UPDATE):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_UPDATE.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin korrigieren</td></tr>';
       endif;
     #     Radio-Button 'Kopieren'
     if($action==ACTION_COPY):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_COPY.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin kopieren (als Einzeltermin)</td></tr>';
       endif;
     else:
     #
     # --- ... und 3 weitere Radio-Buttons
     #     Radio-Button 'Loeschen'
     $checkd='';
     if($action==ACTION_DELETE) $checkd=' checked="checked"';
     $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_DELETE.'"'.$checkd.'></td>
        <td class="td_einf">&nbsp; Termin löschen</td></tr>';
     #     Radio-Button 'Korrigieren'
     $checku='';
     if($action==ACTION_UPDATE) $checku=' checked="checked"';
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_UPDATE.'"'.$checku.'></td>
        <td class="td_einf">&nbsp; Termin korrigieren</td></tr>';
     #     Radio-Button 'Kopieren'
     $checkc='';
     if($action==ACTION_COPY)   $checkc=' checked="checked"';
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.ACTION_NAME.'" value="'.ACTION_COPY.'"'.$checkc.'></td>
        <td class="td_einf">&nbsp; Termin kopieren (als Einzeltermin)</td></tr>';
     endif;
   #
   # --- Durchfuehren-Button und hidden Parameter
   $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf">
            <input type="hidden" name="'.PID_NAME.'"  value="'.$pid.'">
            <input type="hidden" name="'.KAL_DATUM.'" value="'.$datum.'">
            <input type="hidden" name="'.CALL_NUM.'"  value="'.$call_num.'"></td>
        <td class="td_einf martop">&nbsp; <button class="btn btn-save" type="submit">Durchführen</button></td></tr>';
   $str=$str.'
</table>
</div>
';
   return $str;
   }
public static function kal_eingabeformular() {
   #   Rueckgabe eines HTML-Formular zum Eintragen oder Korrigieren eines Termins in
   #   der Datenbanktabelle in Form einer 2-spaltigen Tabelle (kein Formularanfang/
   #   -ende enthalten, die erste Spalte der Tabelle hat eine feste Breite, passend
   #   zu kal_action). Dabei werden die eingegebenen Datums- und Zeitangaben
   #   weitestgehend standardisiert, d.h. in die Formate 'tt.mm.yyyy' bzw. 'hh:mm'
   #   gebracht (kuerzest moegliche Eingabeformate: 't.m.yy' bzw. 'h').
   #   Mit Durchfuehrung der Aktion werden diese POST-Parameter uebergeben:
   #   VALUE_NAME.$i    ($i=1,2,...,count($cols)-1, $cols = Namen der Tabellenspalten)
   #   benutzte functions:
   #      self::kal_select_kategorie($name,$kid,$katids,$all)
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_config::kal_allowed_terminkategorien()
   #      kal_termine_tabelle::kal_standard_termin_intern($value,$cols)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- erlaubte Kategorien
   $katids=kal_termine_config::kal_allowed_terminkategorien();
   #
   # --- Formulardaten aus den POST-Parametern uebernehmen (nach htmlentities(...))
   $value=array();
   for($i=1;$i<count($keys);$i=$i+1):
      $sti=$i;
      if($i<=9) $sti='0'.$i;
      $value[$i]='';
      if(!empty($_POST[VALUE_NAME.$sti])) $value[$i]=htmlentities($_POST[VALUE_NAME.$sti]);
      endfor;
   #
   # --- Standardisierung der date- und time-Werte
   $value=kal_termine_tabelle::kal_standard_termin_intern($value,$cols);
   #
   # --- Hinweis auf Pflichtfelder
   $str='
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr valign="top">
        <th class="th_einf left">Eingabefelder:</th>
        <th class="th_einf">Felder mit &nbsp * &nbsp; erfordern eine nicht leere Eingabe (Pflichtfelder)</th></tr>';
   #
   # --- Kategorie-Auswahl an die erste Stelle setzen
   for($i=1;$i<count($keys);$i=$i+1)
      if($keys[$i]==COL_KATID):
        $ii=$i;
        break;
        endif;
   $ind=array();
   $ind[1]=$ii;
   for($i=1;$i<$ii;$i=$i+1) $ind[$i+1]=$i;
   for($i=$ii+1;$i<count($keys);$i=$i+1) $ind[$i]=$i;
   #
   # --- Schleife ueber die Formularzeilen
   for($i=1;$i<count($keys);$i=$i+1):
      #
      # --- Namen der Terminparameter
      $k=$ind[$i];
      $key=$keys[$ind[$i]];
      $spname=$cols[$key][1];
      if($key==COL_KATID) $spname='Kategorie';
      #
      # --- Ausgabe einer Zwischenzeile
      if($key==COL_ZEIT2)
        $str=$str.'
    <tr valign="top">
        <th colspan="2" class="th_einf left">
            Falls mehr als eine Uhrzeit angegeben werden soll:</th></tr>';
      #
      # --- Formate der Eingabefelder
      $arr=explode(' ',$cols[$key][0]);
      $type=$arr[0];
      $arr=explode('(',$type);
      $type=$arr[0];
      $form=$cols[$key][2];
      if(!empty($form)) $form=' &nbsp; (<tt>'.$form.'</tt>)';
      if($type=='time') $form=' &nbsp; Uhr'.$form;
      #
      # --- Pflichtfelder anzeigen
      $pflicht='';
      if($key==COL_NAME or $key==COL_DATUM or $key==COL_TAGE or $key==COL_KATID)
        $pflicht='<b>*</b>';
      #
      # --- Restriktionen anzeigen
      $restr='';
      if($key==COL_TAGE) $restr=' &nbsp; <tt>>0</tt>';
      if(empty($form)) $form=$restr;
      #
      # --- bei leerer Eingabe ggf. Defaults einfuegen
      if($key==COL_TAGE   and empty($value[$k])) $value[$k]=1;
      if($key==COL_KATID  and empty($value[$k])) $value[$k]=1;
      if($key==COL_WOCHEN and empty($value[$k])) $value[$k]=0;
      #
      # --- Ausgabe einer Formularzeile
      $zeile='
    <tr valign="top">
        <td class="td_einf left">'.$pflicht.' '.$spname.': &nbsp; </td>';
      $sti=$k;
      if($k<=9) $sti='0'.$k;
      if($key==COL_KATID):
        $zeile=$zeile.'
        <td class="td_einf right">'.self::kal_select_kategorie(VALUE_NAME.$sti,$value[$k],$katids,FALSE).'</td></tr>';
        else:
        $class='text';
        if($type=='date') $class='date right';
        if($type=='time') $class='time right';
        if(substr($type,0,3)=='int') $class='int right';
        $zeile=$zeile.'
        <td class="td_einf"><input name="'.VALUE_NAME.$sti.'" value="'.$value[$k].'" class="'.$class.'">'.$form.'</td></tr>';
        endif;
      $str=$str.$zeile;
      endfor;
   $str=$str.'
</table>
</div>
';
   return $str;
   }
public static function kal_prepare_action($action,$pid,$datum,$call_num,$error,$str) {
   #   Hilfsfunktion zu den Funktionen zur Eingabe, Korrektur bzw. Kopie eines Termins.
   #   Folgende Daten werden in dieser Reihenfolge zurueck gegeben, als nummeriertes
   #   Arrays (Nummerierung ab 0):
   #    - aktualisierter Wert von $call_num
   #    - naechste Aktion ($nextaction=$action/ACTION_START)
   #    - aktualisierter Wert von $error
   #    - HTML-Code eines Formulars zur Eingabe/Korrektur des Termins
   #    - Array des aktualisierten Termins (ohne Termin-Id)
   #   Neben dem Formular wird eine Fehlermeldung zurueck gegeben, wenn Pflichtfelder
   #   nicht ausgefuellt sind oder Datums-Zeitangaben formal falsch sind.
   #   Die Formularparameter werden per POST-Parameter VALUE_NAME.$i uebermittelt
   #   ($i=1,...,count($cols)-1, $cols = Namen der Tabellenspalten).
   #   $action         aktuelle Aktion (ACTION_INSERT, ACTION_UPDATE, ACTION_COPY)
   #   $pid            Id des Termins (ggf. auch =0, d.h. Termin noch nicht eingetragen)
   #   $datum          vorgegebenes Datum des Termins ($action==ACTION_INSERT/ACTION_COPY)
   #   $call_num       Nummer des Durchlaufs der Aktionsfunktion, wird ggf.
   #                   von 1 auf 2 gesetzt
   #   $error          ='', wird ggf. durch eine Fehlermeldung ersetzt
   #   $str            ='', wird durch den HTML-Code des Formulars ersetzt
   #   benutzte functions:
   #      self::kal_proof_termin($termin)
   #      self::kal_action($action,$pid)
   #      self::kal_eingabeformular()
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_exist_termin($termin)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nextaction=$action;
   $str='';
   $error='';
   #
   # --- Formular einfuellen
   if($pid<=0):
     #
     # --- Fehlermeldung, falls nicht die Id eines existierenden Termins vorgegeben wurde
     if($action!=ACTION_INSERT):
       $txt='';
       if($action==ACTION_UPDATE) $txt='korrigierender';
       if($action==ACTION_COPY)   $txt='kopierender';
       if(!empty($txt)) $error='<span class="kal_fail">Kein zu '.$txt.' Termin angegeben</span>';
       endif;
     #
     # --- leeren Termin setzen
     $termin=array();
     $termin[COL_PID]=$pid;
     for($i=1;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        $val='';
        #     ggf. vorgegebenes Datum beruecksichtigen
        if($action==ACTION_INSERT and $key==COL_DATUM and !empty($datum)) $val=$datum;
        $termin[$keys[$i]]=$val;
        endfor;
     else:
     #
     # --- zu korrigierenden/kopierenden Termin aus der Datenbanktabelle holen
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0):
       $error='<span class="kal_fail">Der Termin ('.COL_PID.'='.$pid.') wurde nicht gefunden</span>';
       $termin[COL_PID]=$pid;
       for($i=1;$i<count($keys);$i=$i+1) $termin[$keys[$i]]='';
       endif;
     endif;
   #
   # --- alte Termindaten als Formulardaten uebernehmen (nur beim ersten Mal)
   $firstcall=TRUE;
   for($i=1;$i<count($keys);$i=$i+1):
      $sti=$i;
      if($i<=9) $sti='0'.$i;
      if(!empty($_POST[VALUE_NAME.$sti])) $firstcall=FALSE;   // zweiter Durchlauf
      endfor;
   if($firstcall):                        
     for($i=1;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        $val=$termin[$key];
        #     Termin-Kopie: zum eintaegigen Einzeltermin machen und das aktuelle Datum einfuegen
        if($action==ACTION_COPY and $key==COL_TAGE)   $val=1;
        if($action==ACTION_COPY and $key==COL_WOCHEN) $val=0;
        if($action==ACTION_COPY and $key==COL_DATUM)  $val=$datum;
        $sti=$i;
        if($i<=9) $sti='0'.$i;
        $_POST[VALUE_NAME.$sti]=$val;
        endfor;
     endif;
   #
   # --- neue Termindaten aus den Formulardaten uebernehmen
   if(empty($error)):
     $termin=array();
     $termin[$keys[0]]='';
     for($i=1;$i<count($keys);$i=$i+1):
        $sti=$i;
        if($i<=9) $sti='0'.$i;
        $val='';
        if(!empty($_POST[VALUE_NAME.$sti])) $val=$_POST[VALUE_NAME.$sti];
        $termin[$keys[$i]]=$val;
        endfor;
     #
     # --- formale Ueberpruefung der Termindaten
     $error=self::kal_proof_termin($termin);
     #
     # --- kopierter Termin muss sich vom Quell-Termin unterscheiden (ACTION_COPY)
     if(empty($error) and $action==ACTION_COPY):
       $pex=kal_termine_tabelle::kal_exist_termin($termin);
       if($pex==$pid) $error='<span class="kal_fail">Die Kopie braucht '.
                             'ein anderes Datum oder eine andere Uhrzeit!</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if($firstcall or !empty($error)):
     #     Ueberschrift
     $ueber='Eintragen';
     if($action==ACTION_UPDATE) $ueber='Korrigieren';
     if($action==ACTION_COPY)   $ueber='Kopieren';
     $str=$str.'
<h4 align="center">'.$ueber.' eines Termins</h4>
<form method="post">';
     #     Fuellen der Formularfelder
     $str=$str.self::kal_eingabeformular();
     #     Ausgabe einer Fehlermeldung
     if(!empty($error)) $str=$str.'
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="th_einf left"></th>
        <td class="td_einf">'.$error.'</td></tr>
</table>
</div>
';
     # --- Ausgabe der Radio-Buttons (Korrigieren / Abbruch) und Submit-Button
     $str=$str.'<br>'.self::kal_action($nextaction,$pid).'</form>
';
     else:
     $call_num=2;
     endif;
   #
   # --- naechste Aktion: zurueck zum Startmenue
   if(!$firstcall and empty($error)) $nextaction=ACTION_START;
   #
   return array($call_num,$nextaction,$error,$str,$termin);
   }
public static function kal_eingeben() {
   #   Rueckgabe eines HTML-Formulars zur Eintragung, Korrigieren oder Kopieren eines
   #   Termins in die Datenbanktabelle inkl. Formularanfang und -ende (<form> ... </form>).
   #   Zusaetzlich wird eine Fehlermeldung zurueck gegeben, falls der Termin schon in
   #   der Datenbanktabelle enthalten ist oder der Termin aus anderen Gruenden nicht
   #   eingetragen werden konnte.
   #   erster Durchlauf ($_POST[CALL_NUM]=1):
   #      Zur Uebernahme der Daten eines Termins wird ein Formular angezeigt.
   #      Ggf. ist ein Datum des Termins vorgeschlagen, das aber ueberschrieben
   #      werden kann. Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren
   #      ersten Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[CALL_NUM]=2):
   #      Ein Submit fuehrt zum Eintragen des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[ACTION_NAME] = ACTION_INSERT / ACTION_START
   #      $_POST[PID_NAME]    = 0
   #      $_POST[CALL_NUM]    = Nummer des Durchlauf durch diese function (=1/2)
   #      $_POST[KAL_DATUM]   = vorgegebenes Datum (im Formular aenderbar)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num,$error,$str)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $action=ACTION_INSERT;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[ACTION_NAME])) $action=$_POST[ACTION_NAME];
   $pid=0;
   if(!empty($_POST[PID_NAME])) $pid=$_POST[PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[CALL_NUM])) $call_num=$_POST[CALL_NUM];
   #     ggf. vorgegebenes Datum auslesen
   $datum='';
   if(!empty($_POST[KAL_DATUM])) $datum=$_POST[KAL_DATUM];
   #
   # ---------- Aktion durchfuehren
   $pidneu=0;
   $str='';
   $nextaction=$action;
   if($action!=ACTION_START):
     $error='';
     #
     # --- einzugebenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,$datum,$call_num,$error,$str);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $str       =$arr[3];
       $termin    =$arr[4];
       endif;
     #
     # --- Eintragen des Termins
     if($call_num>=2):
       $msg='';
       if(empty($error)):
         $pidneu=kal_termine_tabelle::kal_insert_termin($termin);
         if($pidneu>0):
           $msg='<span class="kal_msg">Der Termin wurde neu angelegt</span>';
           $str=$str.'
<div>'.$msg.'<br>&nbsp;</div>
'.kal_termine_menues::kal_menue(0,0);
           else:
           $error=$pidneu;
           $str=$str.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==ACTION_START):
     $call_num=2;
     $nextaction=ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[ACTION_NAME]=$nextaction;
   $_POST[PID_NAME]   =$pidneu;
   $_POST[CALL_NUM]   =$call_num;
   return $str;
   }
public static function kal_korrigieren() {
   #   Rueckgabe eines HTML-Formulars zur Korrektur eines Termins in der
   #   Datenbanktabelle inkl. Formularanfang und -ende (<form> ... </form>).
   #   Zusaetzlich wird eine Fehlermeldung zurueck gegeben, falls der Termin
   #   nicht korrigiert werden konnte.
   #   erster Durchlauf ($_POST[CALL_NUM]=1):
   #      Zur Uebernahme der Daten des Termins wird ein Formular angezeigt.
   #      Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren ersten
   #      Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[CALL_NUM]=2):
   #      Ein Submit fuehrt zur Korrektur des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[ACTION_NAME] = ACTION_UPDATE / ACTION_START
   #      $_POST[PID_NAME]    = Id des Termins
   #      $_POST[CALL_NUM]    = Nummer des Durchlauf durch diese function (=1/2)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num,$error,$str)
   #      kal_termine_tabelle::kal_update_termin($pid,$termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $action=ACTION_UPDATE;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[ACTION_NAME])) $action=$_POST[ACTION_NAME];
   $pid=0;
   if(!empty($_POST[KAL_PID]))  $pid=$_POST[KAL_PID];
   if(!empty($_POST[PID_NAME])) $pid=$_POST[PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[CALL_NUM])) $call_num=$_POST[CALL_NUM];
   #
   # ---------- Aktion durchfuehren
   $str='';
   $nextaction=$action;
   if($action!=ACTION_START):
     $error='';
     #
     # --- zu korrigierenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,'',$call_num,$error,$str);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $str       =$arr[3];     
       $termin    =$arr[4];
       endif;
     #
     # --- Korrigieren des Termins in der Datenbanktabelle
     if($call_num>=2):
       $msg='';
       if($pid>0 and empty($error)):
         $ret=kal_termine_tabelle::kal_update_termin($pid,$termin);
         if(empty($ret)):
           $msg='<span class="kal_msg">Der Termin wurde korrigiert</span>';
           $str=$str.'
<div>'.$msg.'<br>&nbsp;</div>
'.kal_termine_menues::kal_menue(0,0);
           else:
           $error='<span class="kal_fail">Der Termin konnte nicht korrigiert werden</span>';
           $str=$str.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==ACTION_START):
     $call_num=2;
     $nextaction=ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[ACTION_NAME]=$nextaction;
   $_POST[PID_NAME]   =$pid;
   $_POST[CALL_NUM]   =$call_num;
   return $str;
   }
public static function kal_kopieren() {
   #   Rueckgabe eines HTML-Formulars zum Kopieren eines Termins in der Datenbanktabelle
   #   inkl. Formularanfang und -ende (<form> ... </form>). Zusaetzlich wird eine
   #   Fehlermeldung zurueck gegeben, falls der Termin nicht kopiert werden konnte.
   #   erster Durchlauf ($_POST[CALL_NUM]=1):
   #      Zur Uebernahme der Daten des Quell-Termins wird ein Formular angezeigt.
   #      Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren ersten
   #      Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[CALL_NUM]=2):
   #      Ein Submit fuehrt zur Kopie des Quell-Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[ACTION_NAME] = ACTION_COPY / ACTION_START
   #      $_POST[PID_NAME]    = Id des Quell-Termins
   #      $_POST[CALL_NUM]    = Nummer des Durchlauf durch diese function (=1/2)
   #      $_POST[KAL_DATUM]   = vorgegebenes Datum (im Formular aenderbar)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num,$error,$str)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $action=ACTION_COPY;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[ACTION_NAME])) $action=$_POST[ACTION_NAME];
   $pid=0;
   if(!empty($_POST[KAL_PID]))  $pid=$_POST[KAL_PID];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[CALL_NUM])) $call_num=$_POST[CALL_NUM];
   #     ggf. vorgegebenes Datum auslesen
   $datum='';
   if(!empty($_POST[KAL_DATUM])) $datum=$_POST[KAL_DATUM];
   #
   # ---------- Aktion durchfuehren
   $pidneu=0;
   $str='';
   $nextaction=$action;
   if($action!=ACTION_START):
     $error='';
     #
     # --- zu kopierenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,$datum,$call_num,$error,$str);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $str       =$arr[3];
       $termin    =$arr[4];
       endif;
     #
     # --- Kopieren des Termins
     if($call_num>=2):
       $msg='';
       if($pid>0 and empty($error)):
         $pidneu=kal_termine_tabelle::kal_insert_termin($termin);
         if($pidneu>0):
           $msg='<span class="kal_msg">Der Termin wurde als Kopie neu angelegt</span>';
           $str=$str.'
<div>'.$msg.'<br>&nbsp;</div>';
           else:
           $error=$pidneu;
           $str=$str.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         $str=$str.'
'.kal_termine_menues::kal_menue(0,0);
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==ACTION_START):
     $call_num=2;
     $nextaction=ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[ACTION_NAME]=$nextaction;
   $_POST[PID_NAME]   =$pidneu;
   $_POST[CALL_NUM]   =$call_num;
   return $str;
   }
public static function kal_loeschen() {
   #   Rueckgabe eines HTML-Formulars zum Loeschen eines Termins in der Datenbanktabelle
   #   inkl. Formularanfang und -ende (<form> ... </form>). Zusaetzlich wird eine
   #   Fehlermeldung zurueck gegeben, falls der Termin nicht geloescht werden kann.
   #   erster Durchlauf ($_POST[CALL_NUM]=1):
   #      Die Daten des ausgewaehlten Termins werden angezeigt. Ein Submit fuehrt
   #      zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[CALL_NUM]=2):
   #      Ein Submit fuehrt zum Loeschen des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[ACTION_NAME] = ACTION_COPY / ACTION_START
   #      $_POST[PID_NAME]    = Id des Termins
   #      $_POST[CALL_NUM]    = Nummer des Durchlauf durch diese function (=1/2)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_terminblatt($termin,$datum)
   #      self::kal_action($action,$pid)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_delete_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$mennr)
   #
   $action=ACTION_DELETE;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[ACTION_NAME])) $action=$_POST[ACTION_NAME];
   $pid=0;
   if(!empty($_POST[KAL_PID]))  $pid=$_POST[KAL_PID];
   if(!empty($_POST[PID_NAME])) $pid=$_POST[PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[CALL_NUM])) $call_num=$_POST[CALL_NUM];
   #
   # ---------- Aktion durchfuehren
   $str='';
   $nextaction=$action;
   if($action!=ACTION_START):
     #
     # --- Daten des zu loeschenden Termins aus der Datenbanktabelle holen
     $error='';
     $datum='';
     if($pid>0):
       $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
       if(count($termin)<=0):
         $error='<span class="kal_fail">Der Termin ('.COL_PID.'='.$pid.') wurde nicht gefunden</span>';
         else:
         $datum=$termin[COL_DATUM];
         endif;
       else:
       $error='<span class="kal_fail">Kein zu entfernender Termin angegeben</span>';
       $termin=array();
       endif;
     #
     # --- Terminausgabe zur Loeschungsbestaetigung
     if($call_num<=1 or !empty($error)):
       #     Ueberschrift
       $str=$str.'
<form method="post">
<h4 align="center">Soll dieser Termin wirklich in der Datenbanktabelle gelöscht werden?</h4>
<div class="'.CSS_EINFORM.'">
<table class="kal_table">';
       #     zu loeschender Termin
       $str=$str.'
    <tr><th class="left">Termin:</td>
        <td>'.self::kal_terminblatt($termin,$datum).'
        </td></tr>';
       if(!empty($error)) $str=$str.'
    <tr><th class="left"></th>
        <td><br>'.$error.'</td></tr>';
       $str=$str.'
</table>
</div>
';
       #     Ausgabe der Radio-Buttons (Loeschen / Abbruch) und Submit-Button
       $_POST[CALL_NUM]=2;
       $str=$str.'<br>'.self::kal_action($action,$pid).'</form>
';
       #
       else:
       #
       # --- Loeschen des Termins in der Datenbanktabelle
       $msg='';
       if($pid>0):
         $ret=kal_termine_tabelle::kal_delete_termin($pid);
         if(empty($ret)):
           $msg='<span class="kal_msg">Der Termin wurde gelöscht</span>';
           else:
           $msg=$ret;
           endif;
         endif;
       #
       #     Erfolgsmeldung ausgeben
       if(!empty($msg)) $str=$str.'
<div>'.$msg.'<br>&nbsp;</div>';
       #
       # --- zurueck zum Startmenue
       $str=$str.'
'.kal_termine_menues::kal_menue(0,0);
       $nextaction=ACTION_START;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==ACTION_START):
     $call_num=2;
     $nextaction=ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[ACTION_NAME]=$nextaction;
   $_POST[PID_NAME]   =$pid;
   $_POST[CALL_NUM]   =$call_num;
   return $str;
   }
#
#----------------------------------------- Terminliste
public static function kal_terminliste($termin,$datum_as_link=FALSE) {
   #   Rueckgabe einer Liste von Terminen in Form eines HTML-Codes.
   #   Falls ein Link vorgegeben ist, wird der Terminname $termin[$i][COL_NAME]
   #   mit diesem Link unterlegt. Leere Parameter sowie die Terminkategorie
   #   werden nicht mit ausgegeben.
   #   Die rechte Spalte der Termintabelle kann entsprechend der Kategorie
   #   des Termins (z.B. farbig) charakterisiert werden. Die zugehoerigen
   #   CSS-Klassen liefern per Default einen leeren linken Rand. Diese Styles
   #   koennen vor einem 'Termine anzeigen'-Block oder in einer eigenen
   #   Stylsheet-Datei in Eigenregie neu definiert werden.   
   #   $termin         Array der auszugebenden Termine (Indizierung ab 1)
   #   $datum_as_link  ==TRUE:  Die Datumsangabe in der linken Tabellenspalte
   #                            der Liste wird als Link auf den ersten Tag
   #                            des Termins ausgegeben.
   #                   ==FALSE: Die Datumsangabe wird als Text ausgegeben.
   #   benutzte functions:
   #      self::kal_uhrzeit_string($termin)
   #      self::kal_zusatzzeiten_string($termin)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_tabelle::kal_get_spielkategorien()
   #      kal_termine_menues::kal_define_menues()
   #      kal_termine_menues::kal_link($par,$mennr,$linktext,$modus)
   #
   if(count($termin)<=0) return;
   #
   $kat_id=$termin[1][COL_KATID];
   if($kat_id<SPIEL_KATID):
     $kateg=kal_termine_config::kal_get_terminkategorien();
     else:
     $kateg=kal_termine_tabelle::kal_get_spielkategorien();
     endif;
   $kat=array();
   for($i=0;$i<count($kateg);$i=$i+1):
      $k=$i+1;
      $kat[$k]=$kateg[$i]['id'];
      if($kat[$k]>SPIEL_KATID) $kat[$k]=$kat[$k]-SPIEL_KATID;
      endfor;
   $anzkat=count($kat);
   #
   if($datum_as_link):   // ggf. Datumsangabe als Link auf das Tagesblatt
     $menues=kal_termine_menues::kal_define_menues();
     for($i=1;$i<=count($menues);$i=$i+1)
        if(strpos($menues[$i]['name'],'gesblatt')>0) $men=$i;   // Tagesblatt
     endif;
   #
   # --- Formular
   $string='
<table class="kal_table">';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      $kat_id=$term[COL_KATID];
      if($kat_id>SPIEL_KATID) $kat_id=$kat_id-SPIEL_KATID;
      #
      # --- Startdatum aufbereiten
      $datsta=$term[COL_DATUM];
      $arr=explode('.',$datsta);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $jahr1=$arr[2];
      $wot1=kal_termine_kalender::kal_wotag($datsta);
      #
      # --- Enddatum aufbereiten
      $dat2=$jahr1;
      $tage=$term[COL_TAGE];
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
      if($datum_as_link)   // ggf. Datumsangabe als Link auf das Tagesblatt
        $datstr=kal_termine_menues::kal_link(KAL_DATUM.'='.$datsta,$men,$datstr,3);
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $clbord='termlist_border'.$kat_id;
      $zeile='
    <tr><th class="termlist_th">
            '.$datstr.'</th>
        <td class="termlist_td '.$clbord.'">';
      $str='';
      #
      # --- Uhrzeiten aufbereiten
      $uhrz=self::kal_uhrzeit_string($term);
      $str=$str.'
            '.$uhrz;
      #
      # --- Veranstaltungsbezeichnung (ggf. als Link)
      $veran=$term[COL_NAME];
      $veran='<span class="termlist_textattr">'.$veran.'</span>';
      #  -  Link
      $link=$term[COL_LINK];
      if(!empty($link)):
        $tar='';
        if(substr($link,0,4)=='http' and strpos($link,'://')>0)
          $tar=' target="_blank"';
          $veran='<a href="'.$link.'"'.$tar.'>'.$veran.'</a>';
        endif;
      $str=$str.$veran;
      #
      # --- Ort
      $ort=$term[COL_ORT];
      if(!empty($ort)):
        $str=$str.'
            <span class="termlist_ort">'.$ort.'</span>';
        endif;
      #
      # --- Ausrichter
      $ausrichter=$term[COL_AUSRICHTER];
      if(!empty($ausrichter)):
        $str=$str.'
            <span class="termlist_ausrichter">'.$ausrichter.'</span>';
        endif;
      #
      # --- Zusatzzeiten aufbereiten
      $zusatz=self::kal_zusatzzeiten_string($term);
      if(!empty($zusatz)) $str=$str.$zusatz;
      #
      # --- Hinweise zur Veranstaltung
      $hinw=$term[COL_KOMM];
      if(!empty($hinw)):
        $str=$str.'
            <span class="termlist_komm">'.$hinw.'</span>';
        endif;
      #
      $zeile=$zeile.$str.'</td></tr>';
   #
   # ---
      $string=$string.$zeile;
      endfor;
   $string=$string.'
</table>';
   return $string;
   }
public static function kal_uhrzeit_string($termin) {
   #   Rueckgabe eines Strings, in den die Zeitangaben aufbereitet sind,
   #   zu einem gegebenen Termin.
   #   Der String endet mit: ': &nbsp; '.
   #   $termin         assoziatives Array des Termins
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum_vor_nach($datsta,$anztage)
   #
   $beginn=$termin[COL_BEGINN];
   $ende  =$termin[COL_ENDE];
   $datsta=$termin[COL_DATUM];
   $tage  =$termin[COL_TAGE];
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
         $uhrz=$uhrz.' - '.$ende.' Uhr: ';
         else:
         $uhrz=$uhrz.' Uhr: ';
         endif;
       else:
       if(!empty($ende)) $uhrz='Ende: '.$ende.' Uhr: ';
       endif;
     else:
     #
     # --- bei Terminen ueber mehrere Tage
     $datend=kal_termine_kalender::kal_datum_vor_nach($datsta,$tage-1);
     if(!empty($beginn)) $uhrz='Beginn '.$beginn.' Uhr';
     if(!empty($ende)):
       if(!empty($uhrz)) $uhrz=$uhrz.' ('.substr($datsta,0,6).'), ';
       $uhrz=$uhrz.'Ende '.$ende.' Uhr ('.substr($datend,0,6).'): ';
       else:
       if(!empty($uhrz)) $uhrz=$uhrz.' ('.substr($datsta,0,6).'): ';
       endif;
     endif;
   return $uhrz;
   }
public static function kal_zusatzzeiten_string($termin) {
   #   Rueckgabe eines Strings, in dem die zusaetzlichen Zeitangaben aufbereitet
   #   sind, zu einem gegebenen Termin.
   #   $termin         assoziatives Array des Termins
   #
   $zusatz='';
   if(!empty($termin[COL_ZEIT2])):
     $zeit=$termin[COL_ZEIT2];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$termin[COL_TEXT2];
     endif;
   if(!empty($termin[COL_ZEIT3])):
     $zeit=$termin[COL_ZEIT3];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$termin[COL_TEXT3];
     endif;
   if(!empty($termin[COL_ZEIT4])):
     $zeit=$termin[COL_ZEIT4];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$termin[COL_TEXT4];
     endif;
   if(!empty($termin[COL_ZEIT5])):
     $zeit=$termin[COL_ZEIT5];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$termin[COL_TEXT5];
     endif;
   return $zusatz;
   }
}
?>
