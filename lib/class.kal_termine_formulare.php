<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2021
*/
define ('ACTION_START',  'START');
define ('ACTION_SEARCH', 'SEARCH');
define ('ACTION_INSERT', 'INSERT');
define ('ACTION_DELETE', 'DELETE');
define ('ACTION_UPDATE', 'UPDATE');
#
class kal_termine_formulare {
#
#----------------------------------------- Inhaltsuebersicht
#   Terminformulare
#         kal_terminblatt($termin,$datum)
#         kal_proof_termin($termin)
#         kal_select_kategorie($name,$katid,$kid,$kont)
#         kal_radiobutton($action,$pid)
#         kal_startauswahl()
#         kal_aktionsauswahl($pid)
#         kal_eingabeformular($value,$katid)
#         kal_eingeben($value,$action,$katid)
#         kal_loeschen($pid,$action)
#         kal_korrigieren($value,$pid,$action,$katid)
#   Terminliste
#         kal_terminliste($termin)
#         kal_uhrzeit_string($termin)
#         kal_zusatzzeiten_string($termin)
#
#----------------------------------------- Terminformulare
public static function kal_terminblatt($termin,$datum) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins.
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
public static function kal_select_kategorie($name,$katid,$kid,$kont) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kategorien
   #   $name           Name des select-Formulars
   #   $katid          Id der vorgegebenen Kategorie (Datenbankdaten / Spieldaten)
   #                   =0/SPIEL_KATID, falls alle Kategorien zugelassen sind
   #   $kid            Id der ggf. schon ausgewaehlten Kategorie
   #   $kont           =1:    es muss genau eine Kategorie ausgewaehlt werden
   #                   sonst: es kann auch 'alle Kategorien' ausgewaehlt werden
   #   benutzte functions:
   #      kal_termine_config::kal_get_terminkategorien()
   #
   $alle=TRUE;
   if($kont==1) $alle=FALSE;
   #
   if($katid>=SPIEL_KATID):
     $kate=kal_termine_tabelle::kal_get_spielkategorien();
     else:
     $kate=kal_termine_config::kal_get_terminkategorien();
     endif;
   if(rex::isBackend() and
     strpos($_SERVER['REQUEST_URI'],'page='.PACKAGE.'/example')<=0):
     $sclass='';
     $oclass='';
     else:
     $sclass=' class="kal_col5"';
     $oclass=' class="kal_col6"';
     endif;
   $string='
            <select name="'.$name.'"'.$sclass.'>';
   if($katid==0 or $katid==SPIEL_KATID):
     #     alle Kategorien (gewaehlt: $kid)
     $val0=0;
     if($katid==SPIEL_KATID) $val0=SPIEL_KATID;
     if($alle):
       $cs=$oclass;
       if($kid<=0) $cs=$sclass.' selected="selected"';       
       $string=$string.'
                <option value="'.$val0.'"'.$cs.'></option>';
       endif;
     if(!$alle and $kid<=0) $kid=1;
     for($i=0;$i<count($kate);$i=$i+1):
        if($kate[$i]['id']==$kid):
          $sel=$sclass.' selected="selected"';
          else:
          $sel=$oclass;
          endif;
        $option='
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
        $string=$string.$option;
        endfor;
     else:
     #     genau eine Kategorie (immer: $katid)
     for($i=0;$i<count($kate);$i=$i+1)
        if($kate[$i]['id']==$katid):
          $sel=$oclass.' selected="selected"';
          $string=$string.'
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
          break;
          endif;
     endif;
   $string=$string.'
            </select>';
   return $string;
   }
public static function kal_radiobutton($action,$pid) {
   #   Rueckgabe des HTML-Codes einer 2-spaltigen Tabelle in einer der Funktionen
   #   kal_formular_aktion (aktion='eingeben' oder 'loeschen' oder 'korrigieren').
   #   Die erste Spalte der Tabelle hat eine feste Breite, passend zu eingabeformular.
   #   Inhalt der beiden Spalten:
   #   - Bezeichnung der Aktion
   #   - Radiobutton-Paar (Durchfuehrung / Abbruch einer Aktion auf der Datenbanktabelle)
   #   $action         Kuerzel fuer die Aktion auf der DB-Tabelle
   #                   (ACTION_SEARCH / ACTION_INSERT / ACTION_DELETE / ACTION_UPDATE)
   #   $pid            Id des betroffenen Termins
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Spaltennamen der Termintabelle)
   #
   $actionpid=$action.':'.$pid;
   #
   # --- Ausgabe der Radio-Buttons (Aktion / Abbruch)
   $block=' &nbsp; &nbsp; <span class="kal_block_uebernehmen">( Ausführung: &laquo; Block übernehmen &raquo; )</span>';
   $actstr='auswählen';
   if($action==ACTION_SEARCH) $actstr='Termin suchen';
   if($action==ACTION_INSERT) $actstr='Termin eintragen';
   if($action==ACTION_DELETE) $actstr='Termin löschen';
   if($action==ACTION_UPDATE) $actstr='Termin korrigieren';
   if(empty($action) or $action==ACTION_SEARCH or $action==ACTION_INSERT or $pid>0):
     $check1='checked="checked"';
     $check2='';
     else:
     $check1='';
     $check2='checked="checked"';
     endif;
   return '<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left">Aktion:</th>
        <td><span class="action">'.$actstr.':</span>
            <input type="radio" name="REX_INPUT_VALUE['.COL_ANZAHL.']" value="'.$actionpid.'" '.$check1.' />
            &nbsp; / &nbsp; Abbruch:
            <input type="radio" name="REX_INPUT_VALUE['.COL_ANZAHL.']" value="'.ACTION_START.'" '.$check2.' />
            '.$block.'</td></tr>
</table>
</div>
';
   }
public static function kal_startauswahl() {
   #   Rueckgabe des HTML-Formulars zur Entscheidung, ob ein neuer Termin
   #   eingegeben werden soll oder ob ein vorhandener Termin geloescht oder
   #   korrigiert werden soll. Das Formular ist eingebettet in eine 2-spaltige
   #   Tabelle, deren erste Spalte eine feste Breite hat, passend zum eingabeformular.
   #   Die Auswahlentscheidung erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen).
   #   benutzte functions:
   #      self::kal_radiobutton($action,$pid)
   #
   # --- Ausgabe der Radio-Buttons zur Auswahl
   $string='
<h4 align="center">Verwaltung der Termine in der Datenbanktabelle</h4>
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left">gewünschte Aktion:</th>
        <td><input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_INSERT.'"
                   onfocus="this.blur();" />
            &nbsp; Eintragen eines neuen Termins</td></tr>
    <tr><td class="left"></td>
        <td><input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_SEARCH.'"
                   onfocus="this.blur();" />
            &nbsp; Suchen eines vorhandenen Termins<br/>
            &nbsp; &nbsp; &nbsp;
            &nbsp; (um ihn zu löschen oder zu korrigieren)</td></tr>
</table>
</div>
';
   #
   # --- Hinweis auf die Durchfuehrung
   $string=$string.self::kal_radiobutton('','');
   return $string;
   }
public static function kal_aktionsauswahl($pid) {
   #   Rueckgabe des HTML-Formulars zur Auswahl einer Aktion fuer einen Termin auf
   #   der Datenbanktabelle (Korrigieren / Loeschen). Das Formular ist eingebettet
   #   in eine 2-spaltige Tabelle, deren erste Spalte eine feste Breite hat,
   #   passend zum eingabeformular.
   #   $pid            Id des Termins
   #   Die Auswahl der Aktion erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen).
   #   benutzte functions:
   #      self::kal_radiobutton($action,$pid)
   #
   # --- Ausgabe der Radio-Buttons
   $string='
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left">Dieser Termin soll:</th>
        <td><input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_DELETE.'"
                   onfocus="this.blur();" />
            &nbsp; gelöscht werden</td></tr>
    <tr><th class="left"></th>
        <td><input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_UPDATE.'"
                   onfocus="this.blur();" />
            &nbsp; korrigiert werden</td></tr>
</table>
</div>
';
   #
   # --- Hinweis auf die Durchfuehrung
   $string=$string.self::kal_radiobutton('',$pid);
   return $string;
   }
public static function kal_eingabeformular($value,$katid) {
   #   Rueckgabe eines HTML-Formular zum Eintragen oder Korrigieren eines Termins
   #   in der Datenbanktabelle in Form einer 2-spaltigen Tabelle. Die erste Spalte
   #   der Tabelle hat eine feste Breite, passend zu startauswahl und radiobutton.
   #   Dabei werden die eingegebenen Datums- und Zeitangaben weitestgehend
   #   standardisiert, d.h. in die Formate 'tt.mm.yyyy' bzw. 'hh:mm' gebracht:
   #   - kuerzest moegliches Eingabeformat Datum: 't.m.yy'
   #   - kuerzest moegliches Eingabeformat Uhrzeit: 'h'
   #   $value          Array der Formularparameter-Werte
   #                   $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                   $i=1, 2, ..., $nzcols-1=count($cols)-1)
   #                   ($cols = Namen der Tabellenspalten, $keys=array_keys($cols))
   #   $katid          Id der zugelassenen Kategorie (=0: alle Kategorien zugelassen)
   #   benutzte functions:
   #      self::kal_select_kategorie($name,$katid,$kid,$kont)
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_standard_termin_intern($value,$cols)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Standardisierung der date- und time-Werte
   $value=kal_termine_tabelle::kal_standard_termin_intern($value,$cols);
   #
   # --- Hinweis auf Pflichtfelder
   $string='
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr valign="top">
        <th class="left">Eingabefelder:</th>
        <th>Felder mit &nbsp * &nbsp; erfordern eine nicht leere Eingabe (Pflichtfelder)</th></tr>';
   #
   # --- Schleife ueber die Formularzeilen
   for($i=1;$i<count($cols);$i=$i+1):
      #
      # --- Namen der Terminparameter
      $key=$keys[$i];
      $spname=$cols[$key][1];
      if($key==COL_KATID) $spname='Kategorie';
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
      if($key==COL_TAGE   and empty($value[$i])) $value[$i]=1;
      if($key==COL_KATID  and empty($value[$i])) $value[$i]=1;
      if($key==COL_WOCHEN and empty($value[$i])) $value[$i]=0;
      #
      # --- Ausgabe einer Formularzeile
      $zeile='
    <tr valign="top">
        <td class="left">'.$pflicht.' '.$spname.': &nbsp; </td>';
      if($key==COL_KATID):
        $zeile=$zeile.'
        <td class="right">
'.self::kal_select_kategorie('REX_INPUT_VALUE['.$i.']',$katid,$value[$i],1).'</td></tr>';
        else:
        $class='text';
        if($type=='date') $class='date right';
        if($type=='time') $class='time right';
        if(substr($type,0,3)=='int') $class='int right';
        $zeile=$zeile.'
        <td><input name="REX_INPUT_VALUE['.$i.']" value="'.$value[$i].'" class="'.$class.'" />'.$form.'</td></tr>';
        endif;
      $string=$string.$zeile;
      #
      # --- Ausgabe einer Zwischenzeile
      if($key=='kat_id')
        $string=$string.'
    <tr valign="top">
        <th colspan="2" class="left">
            Falls mehr als eine Uhrzeit angegeben werden soll:</th></tr>';
      endfor;
   $string=$string.'
</table>
</div>
';
   return $string;
   }
public static function kal_eingeben($value,$action,$katid) {
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
   #   $katid          Id der zugelassenen Kategorie (=0: alle Kategorien zugelassen)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #      Die Auswahl Eintragung oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)]
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls
   #      - Pflichtfeldern nicht ausgefuellt sind oder
   #      - Datums- oder Zeitangaben formal falsch sind oder
   #      - der Termin schon in der Datenbanktabelle enthalten ist oder
   #      - der Termin aus anderen Gruenden nicht eingetragen werden konnte
   #   benutzte functions:
   #      self::kal_proof_termin($termin)
   #      self::kal_eingabeformular($value,$katid)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #      kal_termine_config::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #
   $cols=kal_termine_config::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nzcols=count($cols);
   #
   # --- Uebertragen der Eingabedaten in ein Termin-Array
   $termin=array();
   $termin[$keys[0]]=0;
   for($i=1;$i<$nzcols;$i=$i+1) $termin[$keys[$i]]=$value[$i];
   #
   # --- formale Ueberpruefung der Termindaten
   $err0='';
   $err0=self::kal_proof_termin($termin);
   #
   # --- Eintragen des neuen Termins in die Datenbank (erst nach Auftrag zum Eintragen)
   $msg='';
   $error='';
   if($action==ACTION_INSERT and empty($err0)):
     $pidneu=kal_termine_tabelle::kal_insert_termin($termin);
     if($pidneu>0):
       $msg='<span class="kal_msg">Der Termin wurde in die Datenbank eingetragen</span>';
       else:
       $error=$pidneu;
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error) or !empty($err0)):
     #     Ueberschrift
     $string='
<h4 align="center">Eintragen eines Termins in die Datenbanktabelle</h4>';
     #     Fuellen der Formularfelder
     $string=$string.self::kal_eingabeformular($value,$katid);
     #     Ausgabe von Meldungen
     if(!empty($error) or !empty($err0) or !empty($msg)) $string=$string.'
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left"></th>';     
     if(!empty($err0)) $string=$string.'
        <td>'.$err0.'</td></tr>';
     if(!empty($error)) $string=$string.'
        <td>'.$error.'</td></tr>';
     if(!empty($msg)) $string=$string.'
        <td>'.$msg.'</td></tr>';
     if(!empty($error) or !empty($err0) or !empty($msg)) $string=$string.'
</table>
</div>
';
     #     Ausgabe der Radio-Buttons (Eintragen / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_INSERT,'');
   #
   # --- Zurueck zum Startmenue
     else:
     $string='';
     if(!empty($msg)) $string='
<div>'.$msg.'</div>
';
     $string=$string.'
'.self::kal_startauswahl();
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
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #      Die Auswahl Loeschen oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)] ($cols: Array der Tabellenspalten)
   #   Es wird eine Fehlermeldung zurueck gegeben (in rot), falls der Termin nicht
   #   geloescht werden konnte
   #   benutzte functions:
   #      self::kal_terminblatt($termin,$datum)
   #      self::kal_show_termin($termin,$ueber)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_delete_termin($termin)
   #
   # --- Ermittlung der zu $pid gehoerigen Termindaten (falls vorhanden)
   $error='';
   if($pid>0):
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     if(count($termin)<=0)
       $error='<span class="kal_fail">Der Termin ('.COL_PID.'='.$pid.') wurde nicht gefunden</span>';
     endif;
   if($pid<=0 and $action!=ACTION_START)
     $error='<span class="kal_fail">Kein zu löschender Termin angegeben</span>';
   #
   # --- Loeschen des Termins in der Datenbanktabelle
   $msg='';
   $error2='';
   if($action==ACTION_DELETE and $pid>0 and empty($error)):
     $ret=kal_termine_tabelle::kal_delete_termin($pid);
     if(empty($ret)):
       $msg='<span class="kal_msg">Der Termin wurde in der Datenbank gelöscht</span>';
       else:
       $error2=$ret;
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error) or !empty($error2)):
   #     Ueberschrift
     $string='
<h4 align="center">Löschen eines Termins in der Datenbanktabelle</h4>
<div class="'.CSS_EINFORM.'">
<table class="kal_table">';
   #     zu loeschender Termin
     if(!empty($error)):
       $string=$string.'
    <tr><th class="left"></th>
        <td>'.$error.'</td></tr>';
       else:
       $string=$string.'
    <tr><th class="left">Termin:</td>
        <td>'.self::kal_terminblatt($termin,$termin[COL_DATUM]).'
        </td></tr>';
       endif;
   #     Termin konnte nicht geloescht werden
     if(!empty($error2))
       $string=$string.'
    <tr><th class="left"></th>
        <td>'.$error2.'</td></tr>';
     $string=$string.'
</table>
</div>
';
   #     Radio-Buttons (Loeschen / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_DELETE,$pid);
   #
   # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>
'.self::kal_startauswahl();
     endif;
   return $string;
   }
public static function kal_korrigieren($value,$pid,$action,$katid) {
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
   #   $katid          Id der zugelassenen Kategorie (=0: alle Kategorien zugelassen)
   #   =================================================================
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   =================================================================
   #      Die Auswahl Korrigieren oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)]
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls der Termin nicht
   #   korrigiert werden konnte
   #   benutzte functions:
   #      self::kal_proof_termin($termin)
   #      self::kal_eingabeformular($value,$katid)
   #      self::kal_radiobutton($action,$pid)
   #      self::kal_startauswahl()
   #      kal_termine_config::kal_define_tabellenspalten()
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
       $error='<span class="kal_fail">Der Termin ('.COL_PID.'='.$pid.') wurde nicht gefunden</span>';
     endif;
   if($pid<=0 and $action!=ACTION_START)
       $error='<span class="kal_fail">Kein zu korrigierender Termin angegeben</span>';
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
       $msg='<span class="kal_msg">Der Termin wurde in der Datenbanktabelle korrigiert</span>';
       else:
       $error2='<span class="kal_fail">Der Termin konnte nicht korrigiert werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if(empty($action) or !empty($error) or !empty($error2)):
     #     Ueberschrift
     $string='
<h4 align="center">Korrigieren eines Termins in der Datenbanktabelle</h4>';
     #     Formular
     $string=$string.self::kal_eingabeformular($value,$katid);
     #     Fehlermeldungen
     if(!empty($error2)):
       if(empty($error)):
         $error=$error2;
         else:
         $error=$error.'<br/>'.$error2;
         endif;
       endif;
     if(!empty($error))  $string=$string.'
<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left"></th>
        <td>'.$error.'</td></tr>
</table>
</div>
';
     #     Radio-Button (Korrigieren / Abbruch)
     $string=$string.self::kal_radiobutton(ACTION_UPDATE,$pid);
     #
     # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>
'.self::kal_startauswahl();
     endif;
   return $string;
   }
#
#----------------------------------------- Terminliste
public static function kal_terminliste($termin) {
   #   Rueckgabe einer Liste von Terminen in Form eines HTML-Codes.
   #   Falls ein Link vorgegeben ist, wird der Terminname $termin[$i][COL_NAME]
   #   mit diesem Link unterlegt.
   #   Leere Parameter sowie die Terminkategorie werden nicht mit ausgegeben.
   #   $termin         Array der auszugebenden Termine (Indizierung ab 1)
   #   benutzte functions:
   #      self::kal_uhrzeit_string($termin)
   #      self::kal_zusatzzeiten_string($termin)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #
   # --- Formular
   $string='
<div class="'.CSS_TERMLIST.'">
<table class="kal_table">';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      #
      # --- Startdatum aufbereiten
      $datsta=$term[COL_DATUM];
      $arr=explode('.',$datsta);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $jahr1=$arr[2];
      #
      # --- Enddatum aufbereiten
      $dat2=$jahr1;
      $tage=$term[COL_TAGE];
      if($tage>1):
        $datend=kal_termine_kalender::kal_datum_vor_nach($datsta,$tage-1);
        $arr=explode('.',$datend);
        if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
        if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
        $trenn='/';
        if($tage>2) $trenn='-';
        $dat2=$trenn.$arr[0].'.'.$arr[1].'.'.$arr[2];
        if($arr[2]>$jahr1) $dat1=$dat1.$jahr1;
        endif;
      #
      # --- Datumsangabe
      $datum=$dat1.$dat2;
      $zeile='
    <tr><th>'.$datum.':</th>
        <td>';
      #
      $str='';
      #
      # --- Uhrzeiten aufbereiten
      $uhrz=self::kal_uhrzeit_string($term);
      $str=$str.$uhrz;
      #
      # --- Veranstaltungsbezeichnung (ggf. als Link)
      $veran=$term[COL_NAME];
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
      # --- Zusatzzeiten aufbereiten
      $zusatz=self::kal_zusatzzeiten_string($term);
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
   $string=$string.'
</table>
</div>';
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
         $uhrz=$uhrz.' - '.$ende.' Uhr: &nbsp; ';
         else:
         $uhrz=$uhrz.' Uhr: &nbsp; ';
         endif;
       else:
       if(!empty($ende)) $uhrz='Ende: '.$ende.' Uhr: &nbsp; ';
       endif;
     else:
     #
     # --- bei Terminen ueber mehrere Tage
     $datend=kal_termine_kalender::kal_datum_vor_nach($datsta,$tage-1);
     if(!empty($beginn)) $uhrz='Beginn '.$beginn.' Uhr';
     if(!empty($ende)):
       if(!empty($uhrz)) $uhrz=$uhrz.' ('.substr($datsta,0,6).'), ';
       $uhrz=$uhrz.'Ende '.$ende.' Uhr ('.substr($datend,0,6).'): &nbsp; ';
       else:
       if(!empty($uhrz)) $uhrz=$uhrz.' ('.substr($datsta,0,6).'): &nbsp; ';
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
     $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$termin[COL_TEXT2];
     endif;
   if(!empty($termin[COL_ZEIT3])):
     $zeit=$termin[COL_ZEIT3];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$termin[COL_TEXT3];
     endif;
   if(!empty($termin[COL_ZEIT4])):
     $zeit=$termin[COL_ZEIT4];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$termin[COL_TEXT4];
     endif;
   if(!empty($termin[COL_ZEIT5])):
     $zeit=$termin[COL_ZEIT5];
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$termin[COL_TEXT5];
     endif;
   return $zusatz;
   }
}
?>
