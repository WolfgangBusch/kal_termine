<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
*/
#
class kal_termine_formulare extends kal_termine_menues {
#
#----------------------------------------- Inhaltsuebersicht
#   Terminformulare
#      kal_proof_termin($termin)
#      kal_action($action,$pid)
#      kal_eingabeformular()
#      kal_prepare_action($action,$pid,$datum,$call_num)
#      kal_eingeben()
#      kal_korrigieren()
#      kal_kopieren()
#      kal_loeschen()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Terminformulare
public static function kal_proof_termin($termin) {
   #   Ueberpruefen der Felder eines Termin-Arrays auf
   #   - leere Pflichtfelder
   #   - Format und Zahlen der Datumsangabe
   #   - Format und Zahlen der Zeitangaben
   #   $termin         Termindaten in Form eines assoziativen Arrays
   #   Rueckgabe entsprechender Fehlermeldungen (rote Schrift)
   #   leere Ruckgabe, falls kein Fehler vorliegt
   #   benutzte functions
   #      $addon::kal_define_tabellenspalten()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_wochentag($datum)
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keypid=$addon::TAB_KEY[0];   // pid
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $keytag=$addon::TAB_KEY[6];   // tage
   $keywch=$addon::TAB_KEY[7];   // wochen
   $keymon=$addon::TAB_KEY[8];   // monate
   $datum =$termin[$keydat];
   $tage  =$termin[$keytag];
   $wochen=$termin[$keywch];
   $monate=$termin[$keymon];
   #
   $vor='<span class="kal_fail">';
   #
   # --- Pruefen der Restriktionen
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      if($key==$keypid) continue;   // pid wird nicht geprueft
      $name=$cols[$key][1];
      $pflicht=$cols[$key][3];
      $error=$vor.'Das Terminfeld <tt>\''.$name.'\'</tt> muss <tt>\''.$pflicht.'\'</tt> sein</span>';
      #     Veranstaltungsname und -datum nicht leer
      if(($key==$keynam or $key==$keydat) and empty($termin[$key]))
        return $error;
      #     Dauer in Tagen >=1
      if($key==$keytag and $tage<1)
        return $error;
      #     Wiederholung in Anzahl Wochen >=0
      if($key==$keywch and !empty($wochen) and $wochen<0)
        return $error;
      endfor;
   #
   # --- Pruefen der Datumsangabe: Standardformat
   $errdat1=$vor.'Datumsangabe \'<tt>'.$datum.
            '</tt>\': hat kein Standardformat \'<tt>'.$cols[$keydat][2].'</tt>\'</span>';
   $arr=explode('.',$datum);
   if(count($arr)<>3) return $errdat1;
   #
   # --- Pruefen der Datumsangabe: Jahreszahl
   $jahr=$arr[2];
   $errdat2=$vor.'Datumsangabe \'<tt>'.$datum.
            '</tt>\': falsche Jahreszahl</span>';
   if((strlen($jahr)>4 or intval($jahr)<=0) and $jahr!='00') return $errdat2;
   #
   # --- Pruefen der Datumsangabe: Monatszahl
   $mon=intval($arr[1]);
   $errdat3=$vor.'Datumsangabe \'<tt>'.$datum.
            '</tt>\': Monatszahl nicht zwischen 1 und 12</span>';
   if($mon<1 or $mon>12) return $errdat3;
   #
   # --- Pruefen der Datumsangabe: Tageszahl
   $tag=intval($arr[0]);
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $mt=$mtage[$mon];
   $mon=kal_termine_kalender::kal_monate();
   $moname=$mon[$monat];
   $errdat4=$vor.'Datumsangabe \'<tt>'.$datum.
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
   #
   # --- Ueberpruefen, dass beim monatl. Termin kein 5. Wochentag auftritt
   if($monate>0):
     if(intval(substr($datum,0,2))>28):
       $wotag=kal_termine_kalender::kal_wochentag($datum);
       $errmon=$vor.'monatlicher Termin, Datum \''.$datum.'\': Tageszahl &gt;28 (wäre 5. '.$wotag.')</span>';
       return $errmon;
       endif;
     endif;
   #
   return '';
   }
public static function kal_action($action,$pid) {
   #   Rueckgabe des HTML-Codes einer 2-spaltigen Tabelle fuer ein Aktionsformular
   #   (kein Formularanfang/-ende enthalten). Es enthaelt einen oder mehrere Radio-
   #   Buttons fuer die Auswahl der gewuenschten Aktion und einen Durchfuehren-Button.
   #   Der erste Radio-Button ermoeglicht jeweils den Abbruch der Aktion.
   #   $action         erste Aktion, die zur Auswahl steht.
   #                   ='':            Abbruch
   #                   =$addon::ACTION_INSERT: ein neuer Termin einzutragen
   #                   =$addon::ACTION_DELETE: ein Termin zu geloeschen
   #                   =$addon::ACTION_UPDATE: ein Termin zu korrigieren
   #                   =$addon::ACTION_COPY:   ein Termin zu kopieren
   #                   =$addon::ACTION_SELECT: neben dem Abbruch 3 Aktionen
   #                       zur Auswahl:
   #                          $addon::ACTION_DELETE
   #                          $addon::ACTION_UPDATE
   #                          $addon::ACTION_COPY
   #   $pid            Id des Termins, der geloescht/korrigiert/kopiert werden soll
   #   Mit Durchfuehrung der Aktion werden diese POST-Parameter uebergeben:
   #                   $addon::ACTION_NAME=$addon::ACTION_START  (Abbruch)      oder
   #                   $addon::ACTION_NAME=$addon::ACTION_INSERT (neuer Termin) oder
   #                   $addon::ACTION_NAME=$addon::ACTION_UPDATE (korrigieren)  oder
   #                   $addon::ACTION_NAME=$addon::ACTION_DELETE (loeschen)     oder
   #                   $addon::ACTION_NAME=$addon::ACTION_COPY   (kopieren)
   #                      in den letzten 3 Faellen zusaetzlich:
   #                   $addon::PID_NAME =$pid                      (hidden)
   #                   $addon::KAL_DATUM=$_POST[$addon::KAL_DATUM] (hidden)
   #
   $addon=self::this_addon;
   #
   # --- Return mit Submit-Button Abbrechen
   if(empty($action) or $action==$addon::ACTION_START) return '
<div><br><input type="hidden" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_START.'">
<button class="btn btn-save" type="submit">Abbrechen</button></div>
';
   #
   # --- POST-Parameter einlesen
   $call_num=1;
   if(!empty($_POST[$addon::CALL_NUM])) $call_num=$_POST[$addon::CALL_NUM];
   $datum='';
   if(!empty($_POST[$addon::KAL_DATUM])) $datum=$_POST[$addon::KAL_DATUM];
   #
   # --- Formular
   $str='
<div class="'.$addon::CSS_EINFORM.'">
<table class="kal_table">';
   #
   # --- Radio-Button 'Abbrechen' ...
   $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_START.'"></td>
        <td class="td_einf">&nbsp; Abbrechen</td></tr>';
   #
   if($action!=$addon::ACTION_SELECT):
     #
     # --- ... und ein weiterer Radion-Button
     $check=' checked';
     #     Radio-Button 'Eintragen'
     if($action==$addon::ACTION_INSERT):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_INSERT.'"'.$check.'></td>
        <td class="td_einf">&nbsp; neuen Termin eintragen</td></tr>';
       endif;
     #     Radio-Button 'Loeschen'
     if($action==$addon::ACTION_DELETE):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_DELETE.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin löschen</td></tr>';
       endif;
     #     Radio-Button 'Korrigieren'
     if($action==$addon::ACTION_UPDATE):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_UPDATE.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin korrigieren</td></tr>';
       endif;
     #     Radio-Button 'Kopieren'
     if($action==$addon::ACTION_COPY):
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_COPY.'"'.$check.'></td>
        <td class="td_einf">&nbsp; Termin kopieren (als Einzeltermin)</td></tr>';
       endif;
     else:
     #
     # --- ... und 3 weitere Radio-Buttons
     #     Radio-Button 'Loeschen'
     $checkd='';
     if($action==$addon::ACTION_DELETE) $checkd=' checked';
     $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_DELETE.'"'.$checkd.'></td>
        <td class="td_einf">&nbsp; Termin löschen</td></tr>';
     #     Radio-Button 'Korrigieren'
     $checku='';
     if($action==$addon::ACTION_UPDATE) $checku=' checked';
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_UPDATE.'"'.$checku.'></td>
        <td class="td_einf">&nbsp; Termin korrigieren</td></tr>';
     #     Radio-Button 'Kopieren'
     $checkc='';
     if($action==$addon::ACTION_COPY)   $checkc=' checked';
       $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf"><input type="radio" name="'.$addon::ACTION_NAME.'" value="'.$addon::ACTION_COPY.'"'.$checkc.'></td>
        <td class="td_einf">&nbsp; Termin kopieren (als Einzeltermin)</td></tr>';
     endif;
   #
   # --- Durchfuehren-Button und hidden Parameter
   $str=$str.'
    <tr><th class="th_einf left"></th>
        <td class="td_einf">
            <input type="hidden" name="'.$addon::PID_NAME.'"  value="'.$pid.'">
            <input type="hidden" name="'.$addon::KAL_DATUM.'" value="'.$datum.'">
            <input type="hidden" name="'.$addon::CALL_NUM.'"  value="'.$call_num.'"></td>
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
   #   $addon::VALUE_NAME.$i  ($i=01,02,...,count($cols)-1, $cols=Tabellenspalten-Namen)
   #   benutzte functions:
   #      $addon::kal_select_kategorie($name,$kid,$katids,$all)
   #      $addon::kal_define_tabellenspalten()
   #      $addon::kal_allowed_terminkategorien()
   #      kal_termine_tabelle::kal_standard_termin_intern($value,$cols)
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- erlaubte Kategorien
   $katids=$addon::kal_allowed_terminkategorien();
   #
   # --- Formulardaten aus den POST-Parametern uebernehmen (nach htmlentities(...))
   $value=array();
   for($i=1;$i<count($keys);$i=$i+1):
      $sti=$i;
      if($i<=9) $sti='0'.$i;
      $value[$i]='';
      $vni=$addon::VALUE_NAME.$sti;
      if(!empty($_POST[$vni])) $value[$i]=htmlentities($_POST[$vni]);
      endfor;
   #
   # --- Standardisierung der date- und time-Werte
   $value=kal_termine_tabelle::kal_standard_termin_intern($value,$cols);
   #
   # --- Hinweis auf Pflichtfelder
   $str='
<div class="'.$addon::CSS_EINFORM.'">
<table class="kal_table">
    <tr valign="top">
        <th class="th_einf left">Eingabefelder:</th>
        <th class="th_einf">Felder mit &nbsp * &nbsp; erfordern eine nicht-leere Eingabe (Pflichtfelder)</th></tr>';
   #
   # --- Schleife ueber die Formularzeilen
   $star='<b>*</b> ';
   for($i=1;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      #
      # --- Zeilen-/Parameterdaten
      $type=$cols[$key][0];
         $type=explode(' ',$type)[0];
         $type=explode('(',$type)[0];
      $titel=$cols[$key][1];
         if(str_contains($titel,'Kateg')) $titel='Kategorie';
      $restr=$cols[$key][3];
         if(!empty($restr)) $restr=' &nbsp; <tt>'.$restr.'</tt>';
      $form=$cols[$key][2];
         if(!empty($form))  $form=' &nbsp; (<tt>'.$form.'</tt>)';
         if($type=='time')  $form=' &nbsp; Uhr'.$form;
         if(str_contains($restr,'&ge;')) $form=$restr.' &nbsp; '.$form;;
      #
      # --- Ausgabe einer Zwischenzeile
      if(str_contains($titel,'2, Beginn'))
        $str=$str.'
    <tr valign="top">
        <th colspan="2" class="th_einf left">
            Falls mehr als eine Uhrzeit angegeben werden soll:</th></tr>';
      #
      # --- Pflichtfelder anzeigen
      $spname=$titel;
      if(str_contains($titel,'Kateg') or
         str_contains($restr,'nicht leer')) $spname=$star.$titel;
      #
      # --- bei leerer Eingabe ggf. Defaults einfuegen
      if(str_contains($titel,'Kateg')  and empty($value[$i])) $value[$i]=1;
      if(str_contains($titel,'Tage')   and empty($value[$i])) $value[$i]=1;
      if(str_contains($titel,'Wieder') and empty($value[$i])) $value[$i]=0;
      #
      # --- Ausgabe einer Formularzeile
      $zeile='
    <tr valign="top">
        <td class="td_einf left">
            '.$spname.':</td>';
      $sti=$i;
      if($i<=9) $sti='0'.$i;
      $vni=$addon::VALUE_NAME.$sti;
      if(str_contains($titel,'Kateg')):
        $zeile=$zeile.'
        <td class="td_einf right">
            '.$addon::kal_select_kategorie($vni,$value[$i],$katids,FALSE).'</td></tr>';
        else:
        $class='text';
        if($type=='date') $class='date right';
        if($type=='time') $class='time right';
        if(substr($type,0,3)=='int') $class='int right';
        $zeile=$zeile.'
        <td class="td_einf">
            <input name="'.$vni.'" value="'.$value[$i].'" class="'.$class.'">'.$form.'</td></tr>';
        endif;
      $str=$str.$zeile;
      endfor;
   $str=$str.'
</table>
</div>
';
   return $str;
   }
public static function kal_prepare_action($action,$pid,$datum,$call_num) {
   #   Hilfsfunktion zu den Funktionen zur Eingabe, Korrektur bzw. Kopie eines Termins.
   #   Folgende Daten werden in dieser Reihenfolge zurueck gegeben, als nummeriertes
   #   Arrays (Nummerierung ab 0):
   #    - aktualisierter Wert von $call_num
   #    - naechste Aktion ($nextaction=$action/$addon::ACTION_START)
   #    - ggf. eine Fehlermeldung
   #    - HTML-Code eines Formulars zur Eingabe/Korrektur/Kopie des Termins
   #    - Array des aktualisierten Termins (ohne Termin-Id)
   #   Neben dem Formular wird eine Fehlermeldung zurueck gegeben, wenn Pflichtfelder
   #   nicht ausgefuellt sind oder Datums-Zeitangaben formal falsch sind.
   #   Die Formularparameter werden per POST-Parameter $addon::VALUE_NAME.$i uebermittelt
   #   ($i=01,02,...,count($cols)-1, $cols=Tabellenspalten-Namen).
   #   $action         aktuelle Aktion ($addon::ACTION_INSERT, $addon::ACTION_UPDATE,
   #                   $addon::ACTION_COPY)
   #   $pid            Id des Termins (ggf. auch =0, d.h. Termin noch nicht eingetragen)
   #   $datum          vorgegebenes Datum des Termins
   #                   ($action==$addon::ACTION_INSERT/$addon::ACTION_COPY)
   #   $call_num       Nummer des Durchlaufs der Aktionsfunktion, wird ggf.
   #                   von 1 auf 2 gesetzt
   #   benutzte functions:
   #      self::kal_proof_termin($termin)
   #      self::kal_action($action,$pid)
   #      self::kal_eingabeformular()
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_exist_termin($termin)
   #
   $addon=self::this_addon;
   $keys=$addon::TAB_KEY;
   $keypid=$keys[0];   // pid
   $keydat=$keys[3];   // datum
   $keytag=$keys[6];   // tage
   $keywch=$keys[7];   // wochen
   $nextaction=$action;
   $formular='';
   $error='';
   #
   # --- Formular einfuellen
   if($pid<=0):
     #
     # --- Fehlermeldung, falls nicht die Id eines existierenden Termins vorgegeben wurde
     if($action!=$addon::ACTION_INSERT):
       $txt='';
       if($action==$addon::ACTION_UPDATE) $txt='korrigierender';
       if($action==$addon::ACTION_COPY)   $txt='kopierender';
       if(!empty($txt)) $error='<span class="kal_fail">Kein zu '.$txt.' Termin angegeben</span>';
       endif;
     #
     # --- leeren Termin setzen
     $termin=array();
     $termin[$keypid]=$pid;
     for($i=1;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        $val='';
        #     ggf. vorgegebenes Datum beruecksichtigen
        if($action==$addon::ACTION_INSERT and $key==$keydat and !empty($datum))
          $val=$datum;
        $termin[$keys[$i]]=$val;
        endfor;
     else:
     #
     # --- zu korrigierenden/kopierenden Termin aus der Datenbanktabelle holen
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0):
       $error='<span class="kal_fail">Der Termin ('.$keypid.'='.$pid.') wurde nicht gefunden</span>';
       $termin[$keypid]=$pid;
       for($i=1;$i<count($keys);$i=$i+1) $termin[$keys[$i]]='';
       endif;
     endif;
   #
   # --- alte Termindaten als Formulardaten uebernehmen (nur beim ersten Mal)
   $firstcall=TRUE;
   for($i=1;$i<count($keys);$i=$i+1):
      $sti=$i;
      if($i<=9) $sti='0'.$i;
      if(!empty($_POST[$addon::VALUE_NAME.$sti])) $firstcall=FALSE;   // zweiter Durchlauf
      endfor;
   if($firstcall):
     for($i=1;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        $val=$termin[$key];
        #     Termin-Kopie: zum eintaegigen Einzeltermin machen und das aktuelle Datum einfuegen
        if($action==$addon::ACTION_COPY and $key==$keytag) $val=1;
        if($action==$addon::ACTION_COPY and $key==$keywch) $val=0;
        if($action==$addon::ACTION_COPY and $key==$keydat) $val=$datum;
        $sti=$i;
        if($i<=9) $sti='0'.$i;
        $_POST[$addon::VALUE_NAME.$sti]=$val;
        endfor;
     endif;
   #
   # --- neue Termindaten aus den Formulardaten uebernehmen
   #     Formulardaten = $_POST[$addon::VALUE_NAME.$i] ($i = 0, 1, 2, ...)
   if(empty($error)):
     $termin=array();
     $termin[$keypid]='';
     for($i=1;$i<count($keys);$i=$i+1):
        $sti=$i;
        if($i<=9) $sti='0'.$i;
        $val='';
        $vni=$addon::VALUE_NAME.$sti;
        if(!empty($_POST[$vni])) $val=$_POST[$vni];
        $termin[$keys[$i]]=$val;
        endfor;
     #
     # --- formale Ueberpruefung der Termindaten
     $error=self::kal_proof_termin($termin);
     #
     # --- kopierter Termin muss sich vom Quell-Termin unterscheiden ($addon::ACTION_COPY)
     if(empty($error) and $action==$addon::ACTION_COPY):
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
     $zwtxt='neuen ';
     if($action==$addon::ACTION_UPDATE):
       $ueber='Korrigieren';
       $zwtxt='';
       endif;
     if($action==$addon::ACTION_COPY):
       $ueber='Kopieren';
       $zwtxt='';
       endif;
     $formular=$formular.'
<h4 align="center">'.$ueber.' eines '.$zwtxt.'Termins</h4>
<form method="post">';
     #     Fuellen der Formularfelder
     $formular=$formular.self::kal_eingabeformular();
     #     Ausgabe einer Fehlermeldung
     if(!empty($error)) $formular=$formular.'
<div class="'.$addon::CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="th_einf left"></th>
        <td class="td_einf">'.$error.'</td></tr>
</table>
</div>
';
     # --- Ausgabe der Radio-Buttons (Korrigieren / Abbruch) und Submit-Button
     $formular=$formular.'<br>'.self::kal_action($nextaction,$pid).'</form>
';
     else:
     $call_num=2;
     endif;
   #
   # --- naechste Aktion: zurueck zum Startmenue
   if(!$firstcall and empty($error)) $nextaction=$addon::ACTION_START;
   #
   return array($call_num,$nextaction,$error,$formular,$termin);
   }
public static function kal_eingeben() {
   #   Rueckgabe eines HTML-Formulars zur Eintragung, Korrigieren oder Kopieren
   #   eines Termins in die Datenbanktabelle inkl. Formularanfang und -ende
   #   (<form> ... </form>). Zusaetzlich wird eine Fehlermeldung zurueck gegeben,
   #   falls der Termin schon in der Datenbanktabelle enthalten ist oder der
   #   Termin aus anderen Gruenden nicht eingetragen werden konnte.
   #   Aufgerufen nur im Modul 'Termine verwalten'.
   #   erster Durchlauf ($_POST[$addon::CALL_NUM]=1):
   #      Zur Uebernahme der Daten eines Termins wird ein Formular angezeigt.
   #      Ggf. ist ein Datum des Termins vorgeschlagen, das aber ueberschrieben
   #      werden kann. Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren
   #      ersten Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[$addon::CALL_NUM]=2):
   #      Ein Submit fuehrt zum Eintragen des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[$addon::ACTION_NAME] = $addon::ACTION_INSERT / $addon::ACTION_START
   #      $_POST[$addon::PID_NAME]    = 0
   #      $_POST[$addon::CALL_NUM]    = Nummer des Durchlauf durch diese
   #                                    function (=1/2)
   #      $_POST[$addon::KAL_DATUM]   = vorgegebenes Datum (im Formular aenderbar)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $addon=self::this_addon;
   $action=$addon::ACTION_INSERT;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[$addon::ACTION_NAME])) $action=$_POST[$addon::ACTION_NAME];
   $pid=0;
   if(!empty($_POST[$addon::PID_NAME])) $pid=$_POST[$addon::PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[$addon::CALL_NUM])) $call_num=$_POST[$addon::CALL_NUM];
   #     ggf. vorgegebenes Datum auslesen
   $datum='';
   if(!empty($_POST[$addon::KAL_DATUM])) $datum=$_POST[$addon::KAL_DATUM];
   #
   # ---------- Aktion durchfuehren
   $pidneu=0;
   $formular='';
   $nextaction=$action;
   if($action!=$addon::ACTION_START):
     $error='';
     #
     # --- einzugebenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,$datum,$call_num);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $formular  =$arr[3];
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
           $formular=$formular.'
<div>'.$msg.'<br>&nbsp;</div>
'.kal_termine_menues::kal_menue(0,0);
           else:
           $error=$pidneu;
           $formular=$formular.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==$addon::ACTION_START):
     $call_num=2;
     $nextaction=$addon::ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[$addon::ACTION_NAME]=$nextaction;
   $_POST[$addon::PID_NAME]   =$pidneu;
   $_POST[$addon::CALL_NUM]   =$call_num;
   return $formular;
   }
public static function kal_korrigieren() {
   #   Rueckgabe eines HTML-Formulars zur Korrektur eines Termins in der
   #   Datenbanktabelle inkl. Formularanfang und -ende (<form> ... </form>).
   #   Zusaetzlich wird eine Fehlermeldung zurueck gegeben, falls der Termin
   #   nicht korrigiert werden konnte.
   #   Aufgerufen nur im Modul 'Termine verwalten'.
   #   erster Durchlauf ($_POST[$addon::CALL_NUM]=1):
   #      Zur Uebernahme der Daten des Termins wird ein Formular angezeigt.
   #      Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren ersten
   #      Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[$addon::CALL_NUM]=2):
   #      Ein Submit fuehrt zur Korrektur des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[$addon::ACTION_NAME] = $addon::ACTION_UPDATE / $addon::ACTION_START
   #      $_POST[$addon::PID_NAME]    = Id des Termins
   #      $_POST[$addon::CALL_NUM]    = Nummer des Durchlauf durch diese
   #                                    function (=1/2)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num)
   #      kal_termine_tabelle::kal_update_termin($pid,$termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $addon=self::this_addon;
   $action=$addon::ACTION_UPDATE;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[$addon::ACTION_NAME])) $action=$_POST[$addon::ACTION_NAME];
   $pid=0;
   if(!empty($_POST[$addon::PID_NAME])) $pid=$_POST[$addon::PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[$addon::CALL_NUM])) $call_num=$_POST[$addon::CALL_NUM];
   #
   # ---------- Aktion durchfuehren
   $formular='';
   $nextaction=$action;
   if($action!=$addon::ACTION_START):
     $error='';
     #
     # --- zu korrigierenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,'',$call_num);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $formular  =$arr[3];     
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
           $formular=$formular.'
<div>'.$msg.'<br>&nbsp;</div>
'.kal_termine_menues::kal_menue(0,0);
           else:
           $error='<span class="kal_fail">Der Termin konnte nicht korrigiert werden</span>';
           $formular=$formular.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==$addon::ACTION_START):
     $call_num=2;
     $nextaction=$addon::ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[$addon::ACTION_NAME]=$nextaction;
   $_POST[$addon::PID_NAME]   =$pid;
   $_POST[$addon::CALL_NUM]   =$call_num;
   return $formular;
   }
public static function kal_kopieren() {
   #   Rueckgabe eines HTML-Formulars zum Kopieren eines Termins in der
   #   Datenbanktabelle inkl. Formularanfang und -ende (<form> ... </form>).
   #   Zusaetzlich wird eine Fehlermeldung zurueck gegeben, falls der Termin
   #   nicht kopiert werden konnte.
   #   Aufgerufen nur im Modul 'Termine verwalten'.
   #   erster Durchlauf ($_POST[$addon::CALL_NUM]=1):
   #      Zur Uebernahme der Daten des Quell-Termins wird ein Formular angezeigt.
   #      Ein Submit fuehrt zu Fehlermeldungen (und einem weiteren ersten
   #      Durchlauf) oder zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[$addon::CALL_NUM]=2):
   #      Ein Submit fuehrt zur Kopie des Quell-Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[$addon::ACTION_NAME] = $addon::ACTION_COPY / $addon::ACTION_START
   #      $_POST[$addon::PID_NAME]    = Id des Quell-Termins
   #      $_POST[$addon::CALL_NUM]    = Nummer des Durchlauf durch diese
   #                                    function (=1/2)
   #      $_POST[$addon::KAL_DATUM]   = vorgegebenes Datum (im Formular aenderbar)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_prepare_action($action,$pid,$datum,$call_num)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$men)
   #
   $addon=self::this_addon;
   $action=$addon::ACTION_COPY;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[$addon::ACTION_NAME])) $action=$_POST[$addon::ACTION_NAME];
   $pid=0;
   if(!empty($_POST[$addon::PID_NAME]))  $pid=$_POST[$addon::PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[$addon::CALL_NUM])) $call_num=$_POST[$addon::CALL_NUM];
   #     ggf. vorgegebenes Datum auslesen
   $datum='';
   if(!empty($_POST[$addon::KAL_DATUM])) $datum=$_POST[$addon::KAL_DATUM];
   #
   # ---------- Aktion durchfuehren
   $pidneu=0;
   $formular='';
   $nextaction=$action;
   if($action!=$addon::ACTION_START):
     $error='';
     #
     # --- zu kopierenden Termin im Formular anzeigen/aendern
     if($call_num<=1):
       $arr=self::kal_prepare_action($action,$pid,$datum,$call_num);
       $call_num  =$arr[0];
       $nextaction=$arr[1];
       $error     =$arr[2];
       $formular  =$arr[3];
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
           $formular=$formular.'
<div>'.$msg.'<br>&nbsp;</div>';
           else:
           $error=$pidneu;
           $formular=$formular.'
<div>'.$error.'<br>&nbsp;</div>';
           endif;
         $formular=$formular.'
'.kal_termine_menues::kal_menue(0,0);
         endif;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==$addon::ACTION_START):
     $call_num=2;
     $nextaction=$addon::ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[$addon::ACTION_NAME]=$nextaction;
   $_POST[$addon::PID_NAME]   =$pidneu;
   $_POST[$addon::CALL_NUM]   =$call_num;
   return $formular;
   }
public static function kal_loeschen() {
   #   Rueckgabe eines HTML-Formulars zum Loeschen eines Termins in der
   #   Datenbanktabelle inkl. Formularanfang und -ende (<form> ... </form>).
   #   Zusaetzlich wird eine Fehlermeldung zurueck gegeben, falls der Termin
   #   nicht geloescht werden kann.
   #   Aufgerufen nur im Modul 'Termine verwalten'.
   #   erster Durchlauf ($_POST[$addon::CALL_NUM]=1):
   #      Die Daten des ausgewaehlten Termins werden angezeigt. Ein Submit fuehrt
   #      zum zweiten Durchlauf.
   #   zweiter Durchlauf ($_POST[$addon::CALL_NUM]=2):
   #      Ein Submit fuehrt zum Loeschen des Termins und zur Rueckkehr zum
   #      Startmenue (aktuelles Monatsmenue).
   #   Diese Daten werden mitgefuehrt:
   #      $_POST[$addon::ACTION_NAME] = $addon::ACTION_COPY / $addon::ACTION_START
   #      $_POST[$addon::PID_NAME]    = Id des Termins
   #      $_POST[$addon::CALL_NUM]    = Nummer des Durchlauf durch diese
   #                                    function (=1/2)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #   benutzte functions:
   #      self::kal_action($action,$pid)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_delete_termin($termin)
   #      kal_termine_menues::kal_menue($selkid,$mennr)
   #      kal_termine_menues::kal_terminblatt($termin,$realdate,$ruecklinks)
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keydat=$addon::TAB_KEY[2];   // datum
   #
   $action=$addon::ACTION_DELETE;
   #
   # --- Aktion, Termin-Id und Aufrufnummer als POST-Parameter uebernehmen
   if(!empty($_POST[$addon::ACTION_NAME])) $action=$_POST[$addon::ACTION_NAME];
   $pid=0;
   if(!empty($_POST[$addon::PID_NAME])) $pid=$_POST[$addon::PID_NAME];
   if(!empty($pid) and $pid<=0) $pid=0;
   $call_num=1;
   if(!empty($_POST[$addon::CALL_NUM])) $call_num=$_POST[$addon::CALL_NUM];
   #
   # ---------- Aktion durchfuehren
   $formular='';
   $nextaction=$action;
   if($action!=$addon::ACTION_START):
     #
     # --- Daten des zu loeschenden Termins aus der Datenbanktabelle holen
     $error='';
     $datum='';
     if($pid>0):
       $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
       if(count($termin)<=0):
         $error='<span class="kal_fail">Der Termin ('.$keypid.'='.$pid.') wurde nicht gefunden</span>';
         else:
         $datum=$termin[$keydat];
         endif;
       else:
       $error='<span class="kal_fail">Kein zu entfernender Termin angegeben</span>';
       $termin=array();
       endif;
     #
     # --- Terminausgabe zur Loeschungsbestaetigung
     if($call_num<=1 or !empty($error)):
       #     Ueberschrift
       $formular=$formular.'
<form method="post">
<h4 align="center">Soll dieser Termin wirklich in der Datenbanktabelle gelöscht werden?</h4>
<div class="'.$addon::CSS_EINFORM.'">
<table class="kal_table">';
       #     zu loeschender Termin
       $formular=$formular.'
    <tr><th class="left">Termin:</td>
        <td>'.kal_termine_menues::kal_terminblatt($termin,$datum,0).'
        </td></tr>';
       if(!empty($error)) $formular=$formular.'
    <tr><th class="left"></th>
        <td><br>'.$error.'</td></tr>';
       $formular=$formular.'
</table>
</div>
';
       #     Ausgabe der Radio-Buttons (Loeschen / Abbruch) und Submit-Button
       $_POST[$addon::CALL_NUM]=2;
       $formular=$formular.'<br>'.self::kal_action($action,$pid).'</form>
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
       if(!empty($msg)) $formular=$formular.'
<div>'.$msg.'<br>&nbsp;</div>';
       #
       # --- zurueck zum Startmenue
       $formular=$formular.'
'.kal_termine_menues::kal_menue(0,0);
       $nextaction=$addon::ACTION_START;
       endif;
     endif;
   #
   # ---------- Abbruch
   if($action==$addon::ACTION_START):
     $call_num=2;
     $nextaction=$addon::ACTION_START;
     endif;
   #
   # --- Ergebnisrueckgabe
   $_POST[$addon::ACTION_NAME]=$nextaction;
   $_POST[$addon::PID_NAME]   =$pid;
   $_POST[$addon::CALL_NUM]   =$call_num;
   return $formular;
   }
}
?>
