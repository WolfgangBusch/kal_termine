<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2019
 */
define ('ACTION_START',  'start');
define ('ACTION_SEARCH', 'search');
define ('ACTION_INSERT', 'insert');
define ('ACTION_DELETE', 'delete');
define ('ACTION_UPDATE', 'update');
define ('ACTION_COPY',   'copy');
define ('MAXCOPY',       15);
#
class kal_termine_formulare {
#
#----------------------------------------- Inhaltsuebersicht
#         kal_proof_termin($termin)
#   Terminformulare
#         kal_radiobutton($action,$pid)
#         kal_fuellen($value)
#         kal_select_kategorie($name,$kategorie,$kont)
#         kal_startauswahl()
#         kal_eingeben($value,$action)
#         kal_aktionsauswahl($pid)
#         kal_loeschen($pid,$action)
#         kal_korrigieren($value,$pid,$action)
#         kal_kopieren($pid,$action,$kop,$anz)
#   Terminblatt
#         kal_terminblatt($termin,$headline)
#         kal_terminblatt_head($datum,$name)
#   Terminliste
#         kal_terminliste($termin)
#         kal_terminliste_merge($termin)
#
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
   # --- Pruefen der Pflichtfelder (die nicht leer sein duerfen)
   for($i=0;$i<count($cols);$i=$i+1):
      $key=$keys[$i];
      $name=$cols[$key][1];
      $pflicht=$cols[$key][3];
      if($pflicht!='nicht leer') continue;
      if(empty($termin[$key])):
        $errpflicht=$vor.'Das Terminfeld \''.$name.'\' muss \''.$pflicht.'\' sein</span>';
        return $errpflicht;
        endif;
      endfor;
   #
   # --- Pruefen der Datumsangabe: Standardformat
   $errdat1=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].'</tt>\': hat kein Standardformat \'<tt>'.$cols[COL_DATUM][2].'</tt>\'</span>';
   $arr=explode('.',$termin[COL_DATUM]);
   if(count($arr)<>3) return $errdat1;
   #
   # --- Pruefen der Datumsangabe: Jahreszahl
   $jahr=$arr[2];
   $errdat2=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].'</tt>\': Jahreszahl kein Integer</span>';
   if(intval($jahr)<=0 and $jahr!='00') return $errdat2;
   #
   # --- Pruefen der Datumsangabe: Monatszahl
   $monat=intval($arr[1]);
   $errdat3=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].'</tt>\': Monatszahl nicht zwischen 1 und 12</span>';
   if($monat<1 or $monat>12) return $errdat3;
   #
   # --- Pruefen der Datumsangabe: Tageszahl
   $tag=intval($arr[0]);
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $mt=$mtage[$monat];
   $mon=kal_termine_kalender::kal_monate();
   $moname=$mon[$monat];
   $errdat4=$vor.'Datumsangabe \'<tt>'.$termin[COL_DATUM].'</tt>\': Tageszahl im '.$moname.' nicht zwischen 1 und '.$mt.'</span>';
   if($tag<1 or $tag>$mt) return $errdat4;
   #
   # --- Schleife ueber die Zeitangaben
   for($i=0;$i<count($cols);$i=$i+1):
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
   }
#
#----------------------------------------- Terminformulare
public static function kal_radiobutton($action,$pid) {
   #   Rueckgabe des HTML-Codes einer 2-spaltigen Zeile einer HTML-Tabelle in einer der
   #   Funktionen kal_formular_aktion (aktion='eingeben' oder 'loeschen' oder 'korrigieren'
   #   oder 'kopieren'). Inhalt der beiden Spalten:
   #   - Bezeichnung der Aktion
   #   - Radiobutton-Paar (Durchfuehrung / Abbruch einer Aktion auf der Datenbanktabelle)
   #   $action         Kuerzel fuer die Aktion auf der DB-Tabelle (ACTION_SEARCH /
   #                   ACTION_INSERT / ACTION_DELETE / ACTION_UPDATE / ACTION_COPY)
   #   $pid            Id des betroffenen Termins
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Spaltennamen der Termintabelle)
   #
   $block='';
   if(rex::isBackend())
     $block=' &nbsp; <span class="kal_form_block">(&laquo;Block übernehmen&raquo;)</span>';
   $actionpid=$action.':'.$pid;
   #
   # --- Ausgabe der Radio-Buttons (Aktion / Abbruch)
   $actstr='auswählen';
   if($action==ACTION_SEARCH) $actstr='Termin suchen';
   if($action==ACTION_INSERT) $actstr='Termin eintragen';
   if($action==ACTION_DELETE) $actstr='Termin löschen';
   if($action==ACTION_UPDATE) $actstr='Termin korrigieren';
   if($action==ACTION_COPY)   $actstr='Termin kopieren';
   if(empty($action) or $action==ACTION_SEARCH or $action==ACTION_INSERT or $pid>0):
     $check1='checked="checked"';
     $check2='';
     else:
     $check1='';
     $check2='checked="checked"';
     endif;
   return '
    <tr><td class="kal_form_list_th">
            Aktion:</td>
        <td class="kal_form_pad kal_form_nowrap">
            <span class="kal_form_prom">'.$actstr.':</span>
            <input type="radio" name="REX_INPUT_VALUE['.COL_ANZAHL.']" value="'.$actionpid.'" '.$check1.' />
            &nbsp; / &nbsp; Abbruch:
            <input type="radio" name="REX_INPUT_VALUE['.COL_ANZAHL.']" value="'.ACTION_START.'" '.$check2.' />
            '.$block.'</td></tr>';
   }
public static function kal_fuellen($value) {
   #   Rueckgabe eines HTML-Formular zum Eintragen oder Korrigieren eines Termins
   #   in der Datenbanktabelle in Form von 2-spaltigen Zeilen einer HTML-Tabelle.
   #   Tabellenanfang (ohne Newline) und -ende muessen im aufrufenden Programm definiert werden.
   #   Dabei werden die eingegebenen Datums- und Zeitangaben weitestgehend
   #   standardisiert, d.h. in die Formate 'tt.mm.yyyy' bzw. 'hh:mm' gebracht:
   #   - kuerzest moegliches Eingabeformat Datum: 't.m.yy'
   #   - kuerzest moegliches Eingabeformat Uhrzeit: 'h'
   #   $value          Array der Formularparameter-Werte
   #                   $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                   $i=1, 2, ..., $nzcols-1=count($cols)-1)
   #                   ($cols = Namen der Tabellenspalten, $keys=array_keys($cols))
   #   benutzte functions:
   #      self::kal_select_kategorie($name,$kategorie,$kont)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_standard_termin_intern($value,$cols)
   #
   $kat =kal_termine_config::kal_get_terminkategorien();
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardisierung der date- und time-Werte
   $value=kal_termine_tabelle::kal_standard_termin_intern($value,$cols);
   #
   # --- Hinweis auf Pflichtfelder
   $string='
    <tr valign="top">
        <td class="kal_form_th">
            Eingabefelder:</td>
        <td class="kal_form_td450">
            <b>Felder mit &nbsp * &nbsp; erfordern eine Eingabe (Pflichtfelder)</b></td></tr>';
   #
   # --- Schleife ueber die Formularzeilen
   for($i=1;$i<count($cols);$i=$i+1):
      #
      # --- Namen der Terminparameter
      $key=$keys[$i];
      $spname=$cols[$key][1];
      #
      # --- Formate der Eingabefelder
      $arr=explode(' ',$cols[$key][0]);
      $type=$arr[0];
      $arr=explode('(',$type);
      $type=$arr[0];
      $form=$cols[$key][2];
      if($form=='Kurztext' or $form=='Text') $form='';
      if(!empty($form)) $form=' &nbsp; (<tt>'.$form.'</tt>)';
      if($type=='time') $form=' &nbsp; Uhr'.$form;
      #
      # --- Restriktionen anzeigen
      $restr='';
      if(!empty($cols[$key][3])) $restr='<b>*</b>';
      #
      # --- Ausgabe einer Formularzeile
      $zeile='
    <tr valign="top">
        <td class="kal_form_nowrap">'.$restr.' '.$spname.': &nbsp; </td>';
      if($key=='kategorie'):
        $zeile=$zeile.'
        <td align="right" class="kal_form_td450">';
        $str=self::kal_select_kategorie('REX_INPUT_VALUE['.$i.']',$value[$i],1);
        $zeile=$zeile.$str.'</td></tr>';
        else:
        $class='kal_form_input_text';
        if($type=='date') $class='kal_form_input_date';
        if($type=='time') $class='kal_form_input_time';
        $zeile=$zeile.'
        <td class="kal_form_td450">
            <input type="text" name="REX_INPUT_VALUE['.$i.']" value="'.$value[$i].'"
                   class="'.$class.'" />'.$form.'</td></tr>';
        endif;
      $string=$string.$zeile;
      #
      # --- Ausgabe einer Zwischenzeile
      if($key=='kategorie')
        $string=$string.'
    <tr valign="top">
        <td colspan="2" class="kal_form_th">
            Falls mehr als eine Uhrzeit angegeben werden soll:</td></tr>';
      endfor;
   return $string;
   }
public static function kal_select_kategorie($name,$kategorie,$kont) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kategorien.
   #   $name           Name des select-Formulars
   #   $kategorie      Bezeichnung der schon gewaehlten Kategorie
   #   $kont           =1:    es ist genau eine Kategorie zu waehlen
   #                   sonst: man kann sich auch fuer keine spezielle Kategorie
   #                          entscheiden, die Wahl umfasst dann alle Kategorien
   #   benutzte functions:
   #      kal_termine_config::kal_get_terminkategorien()
   #
   $kat=kal_termine_config::kal_get_terminkategorien();
   $string='<select name="'.$name.'" class="kal_form_search">';
   if($kont!=1) $string=$string.'
                <option></option>';
   for($i=1;$i<=count($kat);$i=$i+1):
      $kateg=$kat[$i];
      if($kategorie==$kateg):
        $sel='selected="selected"';
        else:
        $sel='';
        endif;
      $string=$string.'
                <option '.$sel.'>'.$kateg.'</option>';
      endfor;
   $string=$string.'
            </select>';
   return $string;
   }
public static function kal_startauswahl() {
   #   Rueckgabe des HTML-Formulars zur Entscheidung, ob ein neuer Termin
   #   eingegeben werden soll oder ob ein vorhandener Termin bearbeitet
   #   (geloescht bzw. korrigiert bzw. kopiert) werden soll.
   #   Die Auswahlentscheidung erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen).
   #   benutzte functions:
   #      self::kal_radiobutton($action,$pid)
   #
   # --- Ausgabe der Radio-Buttons zur Auswahl
   $string='
<h4 align="center">Verwaltung der Termine in der Datenbanktabelle</h4>
<table class="kal_table">
    <tr valign="top">
        <td class="kal_form_list_th">
            gewünschte Aktion:</td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_INSERT.'"
                   onfocus="this.blur();" />
            &nbsp; Eintragen eines neuen Termins</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th"> </td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_SEARCH.'"
                   onfocus="this.blur();" />
            &nbsp; Suchen eines vorhandenen Termins<br/>
            &nbsp; &nbsp; &nbsp;
            &nbsp; (um ihn zu löschen oder zu korrigieren oder zu kopieren)</td></tr>';
   #
   # --- Hinweis auf die Durchfuehrung
   $string=$string.self::kal_radiobutton('','').'
</table>
';
   return $string;
   }
public static function kal_aktionsauswahl($pid) {
   #   Rueckgabe des HTML-Formulars zur Auswahl einer Aktion fuer einen Termin
   #   auf der Datenbanktabelle (Korrigieren / Loeschen / Kopieren).
   #   $pid            Id des Termins
   #   Die Auswahl der Aktion erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen).
   #   benutzte functions:
   #      self::kal_radiobutton($action,$pid)
   #
   # --- Ausgabe der Radio-Buttons
   $string='
<table class="kal_table">
    <tr valign="top">
        <td class="kal_form_list_th">
            Dieser Termin soll:</td>
        <td class="kal_form_pad"> &nbsp;
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_DELETE.'"
                   onfocus="this.blur();" />
            &nbsp; gelöscht werden</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th"> </td>
        <td class="kal_form_pad"> &nbsp;
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_UPDATE.'"
                   onfocus="this.blur();" />
            &nbsp; korrigiert werden</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th"> </td>
        <td class="kal_form_pad"> &nbsp;
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_COPY.'"
                   onfocus="this.blur();" />
            &nbsp; kopiert werden</td></tr>';
   #
   # --- Hinweis auf die Durchfuehrung
   $string=$string.self::kal_radiobutton('',$pid).'
</table>
';
   return $string;
   }
public static function kal_eingeben($value,$action) {
   #   Rueckgabe eines HTML-Formulars zur Eintragung eines Termins in die Datenbanktabelle.
   #   Die eingegebenen Termindaten werden formal ueberprueft.
   #   $value          Array der Formularparameter-Werte
   #                      $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                         ($i=1, ..., count($cols)-1, $cols = Array der Terminspaltennamen)
   #                      $value[count($cols)] = '' / ACTION_INSERT:'
   #                         vor / nach Eintragung in die Formularfelder)
   #   $action         =ACTION_INSERT: der Termin wird in die Tabelle eingetragen
   #                   leer: es werden nur die zu einzutragenen Termindaten angezeigt
   #                         und nach Bestaetigung der Eintragung gefragt
   #                   Die Uebergabe der Werte erfolgt ueber die Werte der
   #                   Redaxo-Variablen REX_INPUT_VALUE[$i], REX_INPUT_VALUE[count($cols)]
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Eintragung oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)]
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls
   #      - Pflichtfeldern nicht ausgefuellt sind oder
   #      - Datums- oder Zeitangaben formal falsch sind oder
   #      - der Termin schon in der Datenbanktabelle enthalten ist oder
   #      - der Termin aus anderen Gruenden nicht eingetragen werden konnte
   #   benutzte functions:
   #      self::kal_fuellen($value)
   #      self::kal_proof_termin($termin)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #      kal_termine_config::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_exist_termin($termin)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nzcols=count($cols);
   #
   # --- Uebertragen der Eingabedaten in ein Termin-Array
   $termin[$keys[0]]=0;
   for($i=1;$i<$nzcols;$i=$i+1) $termin[$keys[$i]]=$value[$i];
   #
   # --- formale Ueberpruefung der Termindaten
   $error=self::kal_proof_termin($termin);
   #
   # --- Eintragen des neuen Termins in die Datenbank (erst nach Auftrag zum Eintragen)
   $msg='';
   $error='';
   if($action==ACTION_INSERT):
     $pidneu=kal_termine_tabelle::kal_insert_termin($termin);
     if($pidneu>0):
       $msg='<span class="kal_form_msg">Der Termin wurde in die Datenbank eingetragen</span>';
       else:
       $error='<span class="kal_form_fail">Der Termin konnte nicht eingetragen werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error)):
     #
     #     Ueberschrift
     $string='
<h4 align="center">Eintragen eines einzelnen Termins in die Datenbanktabelle</h4>
<table class="kal_table">';
     #
     #     Fuellen der Formularfelder
     $string=$string.self::kal_fuellen($value);
     #
     #     Ausgabe der Radio-Buttons (Eintragen / Abbruch)
     if(!empty($error)) $string=$string.'
    <tr><td> </td>
        <td class="kal_form_pad">'.
            $error.'</td></tr>';
     if(!empty($msg)) $string=$string.'
    <tr><td> </td>
        <td class="kal_form_pad">'.
            $msg.'</td></tr>';
     $string=$string.self::kal_radiobutton(ACTION_INSERT,'').'
</table>
';
   #
   # --- Zurueck zum Startmenue
     else:
     $string=$msg.self::kal_startauswahl();
     endif;
   return $string;
   }
public static function kal_loeschen($pid,$action) {
   #   Rueckgabe eines HTML-Formulars zum Loeschen eines Termins in der Datenbanktabelle.
   #   $pid            Id des zu loeschenden Termins
   #   $action         leer: es wird nur der zu loeschende Termin angezeigt
   #                         und nach Bestaetigung der Loeschung gefragt
   #                   =ACTION_DELETE: die Loeschung wird durchgefuehrt
   #                   =ACTION_START:  Rueckkehr zum Startmenue ohne Loeschung
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Loeschen oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)] ($cols: Array der Tabellenspalten)
   #   Es wird eine Fehlermeldung zurueck gegeben (in rot), falls der Termin nicht
   #   geloescht werden konnte
   #   benutzte functions:
   #      self::kal_terminblatt($termin,$headline)
   #      self::kal_terminblatt_head($datum,$name)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_delete_termin($termin)
   #
   # --- Ermittlung der zu $pid gehoerigen Termindaten (falls vorhanden)
   $error='';
   if($pid>0):
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0)
       $error='<span class="kal_form_fail">Der Termin ('.COL_PID.'='.$pid.') wurde nicht gefunden</span>';
     endif;
   if($pid<=0 and $action!=ACTION_START)
     $error='<span class="kal_form_fail">Kein zu löschender Termin angegeben</span>';
   #
   # --- Loeschen des Termins in der Datenbanktabelle
   $msg='';
   $error2='';
   if($action==ACTION_DELETE and $pid>0 and empty($error)):
     $ret=kal_termine_tabelle::kal_delete_termin($pid);
     if(empty($ret)):
       $msg='<span class="kal_form_msg">Der Termin wurde in der Datenbank gelöscht</span>';
       else:
       $error2='<span class="kal_form_fail">Der Termin ('.COL_PID.'='.$pid.') konnte nicht gelöscht werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error) or !empty($error2)):
   #  -  Ueberschrift
     $string='
<h4 align="center">Löschen eines einzelnen Termins in der Datenbanktabelle</h4>
<table class="kal_table">';
   #  -  zu loeschender Termin
     if(!empty($error)):
       $string=$string.'
    <tr valign="top">
        <td> </td>
        <td class="kal_form_pad">
            '.$error.'</td></tr>';
       else:
       $string=$string.'
    <tr valign="top">
        <td class="kal_form_list_th">
            Termin:</td>
        <td class="kal_form_pad">'.self::kal_terminblatt($termin,'self::kal_terminblatt_head').'
        </td></tr>';
       endif;
   #  -  Termin konnte nicht geloescht werden
     if(!empty($error2))
       $string=$string.'
    <tr valign="top">
        <td class="kal_form_list_th"> </td>
        <td class="kal_form_pad">
            '.$error2.'</td></tr>';
   #  -  Radio-Buttons (Loeschen / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_DELETE,$pid).'
</table>';
   #
   # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>';
     $string=$string.self::kal_startauswahl();
     endif;
   return $string;
   }
public static function kal_korrigieren($value,$pid,$action) {
   #   Rueckgabe eines HTML-Formulars zur Korrektur eines Termins in der Datenbanktabelle
   #   $value          Array zur Aufnahme der Daten des zu korrigierenden Termins:
   #                      $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                      ($keys=array_keys($cols), $i=1, ..., count($cols)-1)
   #                      ($cols = Array der Namen der Tabellenspalten)
   #                      Die Uebergabe der Werte erfolgt ueber die Werte der
   #                      Redaxo-Variablen REX_INPUT_VALUE[$i]
   #                   Mit diesen Daten werden die aktuellen Termindaten ueberschrieben.
   #   $pid            Id des zu korrigierenden Termins
   #   $action         leer: es werden nur die zu korrigierenden Termindaten angezeigt
   #                         und nach Bestaetigung der Korrektur gefragt
   #                   =ACTION_UPDATE: die Korektur wird durchgefuehrt
   #                   =ACTION_START:  Rueckkehr zum Startmenue ohne Korrektur
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Korrigieren oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)]
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls der Termin nicht
   #   korrigiert werden konnte
   #   benutzte functions:
   #      self::kal_fuellen($value)
   #      self::kal_proof_termin($termin)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #      kal_termine_config::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_update_termin($pid,$termin)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nzcols=count($cols);
   #
   # --- Termindaten aus der Datenbanktabelle holen (falls vorhanden)
   $error='';
   if($pid>0):
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0)
       $error='<span class="kal_form_fail">Der Termin wurde nicht gefunden</span>';
     endif;
   if($pid<=0 and $action!=ACTION_START)
       $error='<span class="kal_form_fail">Kein zu korrigierender Termin angegeben</span>';
   #
   # --- Termindaten aus dem Formularfeld uebernehmen
   if($pid>0 and empty($error)):
     for($i=1;$i<$nzcols;$i=$i+1)
        $termin[$keys[$i]]=$value[$i];
     #  -  formale Ueberpruefung der Termindaten
     $error=self::kal_proof_termin($termin);
     endif;
   #
   # --- Korrigieren des Termins
   $msg='';
   $error2='';
   if($action==ACTION_UPDATE and $pid>0 and empty($error)):
     $ret=kal_termine_tabelle::kal_update_termin($pid,$termin);
     if(empty($ret)):
       $msg='<span class="kal_form_msg">Der Termin wurde in der Datenbanktabelle korrigiert</span>';
       else:
       $error2='<span class="kal_form_fail">Der Termin konnte nicht korrigiert werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error) or !empty($error2)):
     #  -  Ueberschrift
     $string='
<h4 align="center">Korrigieren eines einzelnen Termins in der Datenbanktabelle</h4>
<table class="kal_table">';
     #  -  Formular
     $string=$string.self::kal_fuellen($value);
     #  -  Fehlermeldungen
     if(!empty($error2)):
       if(empty($error)):
         $error=$error2;
         else:
         $error=$error.'<br/>'.$error2;
         endif;
       endif;
     if(!empty($error))  $string=$string.'
    <tr valign="top">
        <td class="kal_form_list_th"> </td>
        <td class="kal_form_pad">
            '.$error.'</td></tr>';
     #  -  Radio-Button (Korrigieren / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_UPDATE,$pid).'
</table>';
     #
     # --- zurueck zum Startmenue
     else:
     $string='<div>'.$msg.'</div>';
     $string=$string.self::kal_startauswahl();
     endif;
   return $string;
   }
public static function kal_kopieren($pid,$action,$kop,$anz) {
   #   Rueckgabe eines HTML-Formulars zur Kopie eines Termins in der Datenbanktabelle,
   #   entweder auf den Folgetag (Doppeltermin) oder woechentlich (bis zu MAXCOPY Kopien)
   #   $pid            Id des zu kopierenden Termins
   #   $action         leer: es werden nur die zu korrigierenden Termindaten angezeigt
   #                         und nach Bestaetigung der Korrektur gefragt
   #                   =ACTION_COPY:  die Kopien werden durchgefuehrt
   #                   =ACTION_START: Rueckkehr zum Startmenue ohne Kopien
   #   $kop            Art der Kopie
   #                   =0: Art der Kopie muss noch ausgewaehlt werden
   #                   =1: Kopie auf den Folgetag
   #                   =2: woechentliche Kopie auf den gleichen Wochentag
   #                       wie der zu kopierende Termin
   #                   Die Uebergabe des Wertes erfolgt ueber den Wert der
   #                   Redaxo-Variablen REX_INPUT_VALUE[1]
   #   $anz            Anzahl der zu kopierenden Termine (1 <= $anz <= MAXCOPY)
   #                   (nur fuer den Fall der woechentlichen Kopie)
   #                   Die Uebergabe des Wertes erfolgt ueber den Wert der
   #                   Redaxo-Variablen REX_INPUT_VALUE[2]
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Korrigieren oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)] ($cols: Array der Tabellenspalten)
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls der Termin nicht
   #   kopiert werden konnte
   #   benutzte functions:
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_terminblatt($termin,$headline)
   #      self::kal_terminblatt_head($datum,$name)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_wochentag($datum)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_copy_termin($pid,$datumneu)
   #
   #
   # --- Formatierung der Eingabeparameter
   $anzkop=$anz;
   if($anzkop>MAXCOPY) $anzkop=MAXCOPY;
   #
   # --- Ermittlung der zu $pid gehoerigen Termindaten (falls vorhanden)
   $error='';
   if($pid>0):
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0)
       $error='<span class="kal_form_fail">Der zu kopierende Termin wurde nicht gefunden</span>';
     $datum=$termin[COL_DATUM];
     endif;
   if($pid<=0 and $action!=ACTION_START)
     $error='<span class="kal_form_fail">Kein zu kopierender Termin angegeben</span>';
   #
   # --- zusaetzliche Termintage $datneu[$i] (falls eine Kopie vorgenommen werden kann)
   if(empty($error) and $action!=ACTION_START):
     #  -  Datum moeglicher Kopierziele ($datum1 / $datkop[1], $datkop[2], ...)
     $datum1=kal_termine_kalender::kal_datum_vor_nach($datum,1);
     for($i=1;$i<=MAXCOPY;$i=$i+1)
        $datkop[$i]=kal_termine_kalender::kal_datum_vor_nach($datum,$i*7);
     #  -  Wochentag der Kopierziele
     $wotag1=kal_termine_kalender::kal_wochentag($datum1);
     $wotag =kal_termine_kalender::kal_wochentag($datum);
     #  -  Datums-Array aller Kopierziele
     if($kop==1) $datneu[1]=$datum1;
     if($kop==2) for($i=1;$i<=$anzkop;$i=$i+1) $datneu[$i]=$datkop[$i];
     endif;
   #
   # --- Kopieren des Termins (erst nach Festlegung der Kopieart)
   $msg='';
   $warning='';
   if($action==ACTION_COPY and $kop>0 and empty($error)):
     $ziel='';
     for($i=1;$i<=count($datneu);$i=$i+1):
        $ziel=$ziel.', '.$datneu[$i];
        $erg=kal_termine_tabelle::kal_copy_termin($pid,$datneu[$i]);
        $warn='';
        if(intval($erg)<0):
          $ter=kal_termine_tabelle::kal_select_termin_by_pid(abs(intval($erg)));
          $warn='<span class="kal_form_fail">Die Terminkopie am <tt>'.$ter[COL_DATUM].
             '</tt> ist schon vorhanden</span>';
          endif;
        if(strlen($erg)>15) $warn=$erg;
        if(!empty($warn)) $warning=$warning.'<br/>'.$warn;
        endfor;
     if(!empty($ziel)) $ziel='<tt>'.substr($ziel,2).'</tt>';
     $msg='<span class="kal_form_msg">Der Termin wurde kopiert auf den &nbsp; '.$ziel.'</span>';
     if(!empty($warning)) $msg=substr($warning,5);
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or $kop<=0 or !empty($error)):
     #  -  Ueberschrift
     $string='
<h4 align="center">Kopieren eines Termins auf den Folgetag oder mehrfach auf denselben Wochentag</h4>
<table class="kal_table">';
     #  -  zu kopierender Termin
     if(!empty($error)):
       $string=$string.'
    <tr valign="top">
        <td> </td>
        <td class="kal_form_pad">
            '.$error.'</td></tr>';
       else:
       $string=$string.'
    <tr valign="top">
        <td class="kal_form_list_th">
            Termin:</td>
        <td class="kal_form_pad">'.self::kal_terminblatt($termin,'self::kal_terminblatt_head').'
        </td></tr>';
       endif;
     if(empty($error)):
       #  -  Auswahl der Kopierart (Folgetag / Folgewochentage)
       $aktstr1='auf den Folgetag ('.$wotag1.', '.$datum1.')';
       $aktstr2='auf jeden folgenden '.$wotag.' bis einschließlich &nbsp;	   
            <select name="REX_INPUT_VALUE[2]">';
       for($i=1;$i<=MAXCOPY;$i=$i+1):
          if($i==$anzkop):
            $sel='selected="selected"';
            else:
            $sel='';
            endif;
          $aktstr2=$aktstr2.'
                <option value="'.$i.'" '.$sel.'>'.$datkop[$i].'</option>';
          endfor;
       $aktstr2=$aktstr2.'
            </select>';			
       if($kop<=1):
         $chk1='checked';
         $chk2='';
         else:
         $chk1='';
         $chk2='checked';
         endif;
       $string=$string.'
    <tr valign="top">
        <td class="kal_form_list_th">
            zu kopieren:</td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="1" '.$chk1.' />
            &nbsp; '.$aktstr1.'<br/>
            <input type="radio" name="REX_INPUT_VALUE[1]" value="2" '.$chk2.' />
            &nbsp; '.$aktstr2.'</td></tr>';
       endif;
     #  -  Radio-Buttons (Kopieren / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_COPY,$pid).'
</table>';
     #
     # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>';
     $string=$string.self::kal_startauswahl();
     endif;
   return $string;
   }
#
#----------------------------------------- Terminblatt
public static function kal_terminblatt($termin,$headline) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins
   #   $termin         assoziatives Array des Termins
   #   $headline       function, die den HTML-Code der Ueberschrift-Zeile zurueck gibt.
   #                   Die Zeile kann enthalten
   #                   - die Ueberschrift des Termins (Termin-Name)
   #                   - einen Link auf das Kalendermenue des Monats
   #                   - einen Link auf das aktuelle Tagesblatt
   #                   oder nur die Ueberschrift des Termins (Termin-Name)
   #   benutzte functions:
   #      kal_termine_kalender::kal_wotag($datum)
   #      $headline($datum,$name)
   #         d.h. alternativ:
   #         self::kal_terminblatt_head($datum,$name)
   #         kal_termine_menues::kal_terminblatt_head($datum,$name)
   #
   if(count($termin)>2):
     $datum=$termin[COL_DATUM];
     $name=$termin[COL_NAME];
     $pid=$termin[COL_PID];
     else:
     $datum='';
     $name='<small>... kein Termin vorhanden/angegeben ...</small>';
     $pid=0;
     endif;
   #
   $seite='<table class="kal_border">
    <tr valign="top">';
   #
   # --- Ueberschrift-Zeile
   $seite=$seite.call_user_func($headline,$datum,$name);
   #
   # --- return, falls kein Termin angegeben ist
   if(count($termin)<=2):
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Termin:</td>
        <td class="kal_termv kal_termval"></td></tr>
</table>';
     return $seite;
     endif;
   #
   # --- Datum, Start- und Endzeit
   $wt=kal_termine_kalender::kal_wotag($datum);
   $str='';
   if(!empty($termin[COL_BEGINN])) $str=$str.$termin[COL_BEGINN];
   if(!empty($termin[COL_ENDE])) $str=$str.' - '.$termin[COL_ENDE];
   if(!empty($str)) $str='<br/>
            '.$str.' Uhr';
   $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Termin:</td>
        <td class="kal_termv kal_termval">
            '.$wt.', '.$datum.$str;
   #
   # --- eventuelle bis zu 4 Zusatzzeiten
   $z='';
   if(!empty($termin[COL_ZEIT2]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT2].' Uhr: &nbsp; '.$termin[COL_TEXT2];
   if(!empty($termin[COL_ZEIT3]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT3].' Uhr: &nbsp; '.$termin[COL_TEXT3];
   if(!empty($termin[COL_ZEIT4]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT4].' Uhr: &nbsp; '.$termin[COL_TEXT4];
   if(!empty($termin[COL_ZEIT5]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT5].' Uhr: &nbsp; '.$termin[COL_TEXT5];
   if(!empty($z)) $seite=$seite.$z;
   $seite=$seite.'</td></tr>';
   #
   # --- Ausrichter
   $ausrichter=$termin[COL_AUSRICHTER];
   if(!empty($ausrichter))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Ausrichter:</td>
        <td class="kal_termv kal_termval">
            '.$ausrichter.'</td></tr>';
   #
   # --- Veranstaltungsort
   $ort=$termin[COL_ORT];
   if(!empty($ort))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Ort:</td>
        <td class="kal_termv kal_termval">
            '.$ort.'</td></tr>';
   #
   # --- Link
   $link=$termin[COL_LINK];
   if(!empty($link)):
     $tg='_blank';
     if(!empty($_GET['page'])) $tg='_self';
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Link:</td>
        <td class="kal_termv kal_termval">
            <a href="'.$link.'" target="'.$tg.'">'.substr($link,0,50).' . . .</td></tr>';
     endif;
   #
   # --- Hinweise
   $komm=$termin[COL_KOMM];
   if(!empty($komm))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Hinweise:&nbsp;&nbsp;&nbsp;</td>
        <td class="kal_termv">
            '.$komm.'</td></tr>';
   #
   # --- Kategorie
   $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Kategorie:</td>
        <td class="kal_termv">
            '.$termin[COL_KATEGORIE].'</td></tr>';
   $seite=$seite.'
</table>';
   return $seite;
   }
public static function kal_terminblatt_head($datum,$name) {
   #   Rueckgabe des HTML-Codes fuer die Ueberschrift-Zeile eines Terminblatts
   #   als 4-spaltige Tabellenzeile, die sich in die Tabelle des Terminblatts
   #   einfuegt.
   #   $datum          Datum des Termins (wird hier nicht benutzt)
   #   $name           Name des Termins (Spalte COL_NAME)
   #   Format der zurueck gegebenen Ueberschrift-Zeile:
   #   1., 2., 3. Spalte: leer
   #   4. Spalte: Ueberschrift-Text (= Termin-Name)
   #
   return '
        <td class="kal_txtb1"></td>
        <td class="kal_txtb1"></td>
        <td width="50"></td>
        <td class="kal_txt_titel">
            '.$name.'</td></tr>';
   }
#
#----------------------------------------- Terminliste
public static function kal_terminliste($termin) {
   #   Rueckgabe einer Liste von Terminen in Form eines HTML-Codes
   #   $termin         Array der auszugebenden Termine (Indizierung ab 1)
   #   Termine haben diese Parameter:
   #      $termin[COL_NAME] (als Link, falls [link] nicht leer)
   #             [COL_DATUM]
   #             . . .
   #      NICHT mit ausgegeben:
   #             [COL_KATEGORIE]
   #   benutzte functions:
   #      self::kal_terminliste_merge($termin)
   #
   # --- Doppeltermine zusammenfassen
   $termin=self::kal_terminliste_merge($termin);
   $string='';
   #
   # --- Formular
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      #
      # --- Datum
      $arr=explode('.',$term[COL_DATUM]);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $brr=explode('/',$term[COL_DATUM]);
      if(count($brr)>1):
        # --- Doppeltermin
        $arr=explode('.',$brr[1]);
        if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
        if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
        $dat2='/'.$arr[0].'.'.$arr[1].'.'.$arr[2];
        else:
        # --- Einzeltermin
        $dat2=$arr[2];
        endif;
      $datum=$dat1.$dat2;
      $zeile='
    <tr><td class="kal_form_list_th">'.$datum.':</td>
        <td class="kal_form_pad">';
      $str='';
      #
      # --- Uhrzeit
      $uhrz='';
      $beginn=$term[COL_BEGINN];
      if(substr($beginn,0,1)=='0') $beginn=substr($beginn,1);
      if(!empty($beginn)) $uhrz=$beginn;
      $ende=$term[COL_ENDE];
      if(substr($ende,0,1)=='0') $ende=substr($ende,1);
      if(!empty($ende)) $uhrz=$uhrz.' - '.$ende;
      if(!empty($uhrz)) $uhrz=$uhrz.' Uhr: &nbsp; ';
      $str=$str.$uhrz;
      #
      # --- Veranstaltungsbezeichnung (ggf. als Link)
      $veran=$term[COL_NAME];
      #  -  Link
      $link=$term[COL_LINK];
      if(!empty($link)) $veran='<a href="'.$link.'" target="_blank">'.$veran.'</a>';
      $str=$str.'
            '.$veran;
      #
      # --- Ort
      $ort=$term[COL_ORT];
      if(!empty($ort)):
        $ort='('.$ort.')';
        $str=$str.'
            '.$ort;
        endif;
      #
      # --- Ausrichter
      $ausrichter=$term[COL_AUSRICHTER];
      if(!empty($ausrichter)):
        $ausrichter='Ausrichter: '.$ausrichter;
        $str=$str.',
            '.$ausrichter;
        endif;
      #
      # --- Zusatzzeiten
      $zusatz='';
      if(!empty($term[COL_ZEIT2])):
        $zeit=$term[COL_ZEIT2];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[COL_TEXT2];
        endif;
      if(!empty($term[COL_ZEIT3])):
        $zeit=$term[COL_ZEIT3];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[COL_TEXT3];
        endif;
      if(!empty($term[COL_ZEIT4])):
        $zeit=$term[COL_ZEIT4];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[COL_TEXT4];
        endif;
      if(!empty($term[COL_ZEIT5])):
        $zeit=$term[COL_ZEIT5];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[COL_TEXT5];
        endif;
      if(!empty($zusatz)) $str=$str.$zusatz;
      #
      # --- Hinweise zur Veranstaltung
      $hinw=$term[COL_KOMM];
      if(!empty($hinw))
        $str=$str.'<br/>
          '.$hinw;
      #
      $zeile=$zeile.$str.'</td></tr>';
   #
   # ---
      $string=$string.$zeile;
      endfor;
   return '
<div align="left">
<table class="kal_table">'.$string.'
</table>
</div>
';
   }
public static function kal_terminliste_merge($termin) {
   #   Zusammenfuehren von Doppelterminen in einer Terminliste.
   #   Doppeltermine bestehen aus einen Paar von Terminen mit aufeinander
   #   folgendem Datum und identischen Parametern (ausser [COL_PID]).
   #   Rueckgabe der entsprechend komprimierten Terminliste.
   #   $termin         Array der Termine (Indizierung ab 1)
   #                   Rueckgabe des entsprechend verkuerzten Termin-Arrays
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #
   if(count($termin)<=0) return;
   #
   # --- [$ter] Sortierung nach 'name' vorbereiten ('name' vor COL_PID kleben)
   for($i=1;$i<=count($termin);$i=$i+1):
      $ter[$i]=$termin[$i];
      $dat=$ter[$i][COL_NAME];
      $datum=$ter[$i][COL_DATUM];
      $ter[$i][COL_PID]=$dat.':'.$ter[$i][COL_PID];
      endfor;
   #
   # --- [$term] Sortierung nach 'name' durchfuehren
   sort($ter);
   #     umspeichern und COL_PID wieder wegnehmen
   for($i=0;$i<count($ter);$i=$i+1) $term[$i+1]=$ter[$i];
   unset($ter);
   #
   # --- [$date] (max. 2) identische Termine mit unterschiedlichem Datum finden
   $streichen='streichen';
   $termalt=$term[1];
   $date[1][COL_DATUM]='';
   $keys=array_keys($termalt);
   for($i=2;$i<=count($term);$i=$i+1):
     $termneu=$term[$i];
      $date[$i][COL_DATUM]='';
      $gleich=TRUE;
      for($k=1;$k<=count($termneu);$k=$k+1):
         $key=$keys[$k-1];
         if($key==COL_PID or $key=='datum') continue;
         if($termneu[$key]==$termalt[$key]) continue;
         $gleich=FALSE;
         break;
         endfor;
      if($gleich and $termneu[COL_DATUM]==kal_termine_kalender::kal_datum_vor_nach($termalt[COL_DATUM],1)):
      # --- auch bei gleichen Daten: nur streichen bei aufeinander folgendem Datum
        $arr=explode('.',$termalt[COL_DATUM]);
        $tag1=$arr[0];
        $mon1=$arr[1];
        $arr=explode('.',$termneu[COL_DATUM]);
        $tag2=$arr[0];
        $mon2=$arr[1];
        $date[$i][COL_DATUM]=$tag1.'.'.$mon1.'./'.$tag2.'.'.$mon2.'.'.$arr[2];
        $date[$i][COL_NAME]=$termneu[COL_NAME];
        $date[$i-1][COL_NAME]=$streichen;
        endif;
      $termalt=$termneu;
      endfor;
   #
   # --- [$term] identische Termine mit unterschiedlichem Datum zusammenfassen
   $m=0;
   for($i=1;$i<=count($term);$i=$i+1):
      if(!empty($date[$i][COL_NAME])) if($date[$i][COL_NAME]==$streichen) continue;
      $m=$m+1;
      $ter[$m]=$term[$i];
      if(!empty($date[$i][COL_DATUM])) $ter[$m][COL_DATUM]=$date[$i][COL_DATUM];
      endfor;
   unset($term);
   for($i=1;$i<=count($ter);$i=$i+1) $term[$i]=$ter[$i];
   unset($ter);
   #
   # --- [$term] statt 'name' jetzt Datum in MySQL-Format vor COL_PID kleben
   for($i=1;$i<=count($term);$i=$i+1):
      $datumdopp=$term[$i][COL_DATUM];
      if(strpos($datumdopp,'/')>0):
        $arr=explode('/',$datumdopp);
        $datumdopp=$arr[1];
        endif;
      $datum=kal_termine_tabelle::kal_datum_standard_mysql($datumdopp);
      $arr=explode(':',$term[$i][COL_PID]);
      $term[$i][COL_PID]=$datum.':'.$arr[1];
      endfor;
   #
   # --- [$ter] Sortierung nach Datum durchfuehren
   sort($term);
   #     umspeichern
   for($i=0;$i<count($term);$i=$i+1) $ter[$i+1]=$term[$i];
   unset($term);
   #
   # --- vor COL_PID geklebtes Datum wieder entfernen
   for($i=1;$i<=count($ter);$i=$i+1):
      $arr=explode(':',$ter[$i][COL_PID]);
      $ter[$i][COL_PID]=$arr[1];
      endfor;
   #
   # --- Rueckgabe des bereinigten Termin-Arrays
   return $ter;
   }
}
?>