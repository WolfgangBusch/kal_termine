<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
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
#         kal_formular_radiobutton($action,$pid)
#         kal_formular_fuellen($value)
#         kal_formular_suchen($spar)
#         kal_formular_startauswahl()
#         kal_formular_eingeben($value)
#         kal_formular_loeschen($pid)
#         kal_formular_korrigieren($pid,$value)
#         kal_formular_kopieren($pid,$anz)
#   Terminliste
#         kal_terminliste($termin)
#         kal_terminliste_merge($termin)
#
static public function kal_proof_termin($termin) {
   #   Ueberpruefen der Felder eines Termin-Arrays auf
   #   - leere Pflichtfelder
   #   - Format und Zahlen der Datumsangabe
   #   - Format und Zahlen der Zeitangaben
   #   $termin         Termindaten in Form eines assoziativen Arrays
   #   Rueckgabe entsprechender Fehlermeldungen (rote Schrift)
   #   leere Ruckgabe, falls kein Fehler vorliegt
   #   benutzte functions
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
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
   $errdat1=$vor.'Datumsangabe \'<tt>'.$termin[datum].'</tt>\': hat kein Standardformat \'<tt>'.$cols[datum][2].'</tt>\'</span>';
   $arr=explode('.',$termin[datum]);
   if(count($arr)<>3) return $errdat1;
   #
   # --- Pruefen der Datumsangabe: Jahreszahl
   $jahr=$arr[2];
   $errdat2=$vor.'Datumsangabe \'<tt>'.$termin[datum].'</tt>\': Jahreszahl kein Integer</span>';
   if(intval($jahr)<=0 and $jahr!='00') return $errdat2;
   #
   # --- Pruefen der Datumsangabe: Monatszahl
   $monat=intval($arr[1]);
   $errdat3=$vor.'Datumsangabe \'<tt>'.$termin[datum].'</tt>\': Monatszahl nicht zwischen 1 und 12</span>';
   if($monat<1 or $monat>12) return $errdat3;
   #
   # --- Pruefen der Datumsangabe: Tageszahl
   $tag=intval($arr[0]);
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $mt=$mtage[$monat];
   $mon=kal_termine_kalender::kal_monate();
   $moname=$mon[$monat];
   $errdat4=$vor.'Datumsangabe \'<tt>'.$termin[datum].'</tt>\': Tageszahl im $moname nicht zwischen 1 und '.$mt.'</span>';
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
      $min=intval($arr[1]);
      $errtim3=$vor.'Zeitangabe \'<tt>'.$val.'</tt>\': Minutenzahl nicht zwischen 0 und 59</span>';
      if($min<0 or $min>59 or ($min==0 and $arr[1]!='' and $arr[1]!='0' and $arr[1]!='00'))
        return $errtim3;
      endfor;
   }
#
#----------------------------------------- Terminformulare
static public function kal_formular_radiobutton($action,$pid) {
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
   #   benutzte functions:
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #
   $block='';
   if(rex::isBackend())
     $block=' &nbsp; <span class="kal_form_block">(&laquo;Block &uuml;bernehmen&raquo;)</span>';
   $actionpid=$action.':'.$pid;
   #
   # --- Ausgabe der Radio-Buttons (Aktion / Abbruch)
   $actstr='ausw&auml;hlen';
   if($action==ACTION_SEARCH) $actstr='Termin suchen';
   if($action==ACTION_INSERT) $actstr='Termin eintragen';
   if($action==ACTION_DELETE) $actstr='Termin l&ouml;schen';
   if($action==ACTION_UPDATE) $actstr='Termin korrigieren';
   if($action==ACTION_COPY)   $actstr='Termin kopieren';
   if(empty($action) or $action==ACTION_SEARCH or $action==ACTION_INSERT or $pid>0):
     $check1='checked="checked"';
     $check2='';
     else:
     $check1='';
     $check2='checked="checked"';
     endif;
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
   $nzcols=count($cols);
   return '
    <tr><td class="kal_form_th">
            Aktion:</td>
        <td class="kal_form_padnowrap">
            <span class="kal_form_prom">'.$actstr.':</span>
            <input type="radio" name="REX_INPUT_VALUE['.$nzcols.']" value="'.$actionpid.'" '.$check1.' />
            &nbsp; / &nbsp; Abbruch:
            <input type="radio" name="REX_INPUT_VALUE['.$nzcols.']" value="start" '.$check2.' />
            '.$block.'</td></tr>';
   }
static public function kal_formular_fuellen($value) {
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
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_standard_termin_intern($value,$cols)
   #
   $kat=kal_termine_config::kal_get_terminkategorien();
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   #
   # --- Konstanten
   $stdwidth=450;
   $datwidth=80;
   $timwidth=60;
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
      $width=$stdwidth;
      if($type=='date') $width=$datwidth;
      if($type=='time') $width=$timwidth;
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
        <td style="white-space:nowrap;">
            '.$restr.' '.$spname.': &nbsp; </td>';
      if($key=='kategorie'):
        $zeile=$zeile.'
        <td align="right" class="kal_form_td450">
            <select name="REX_INPUT_VALUE['.$i.']">';
        for($k=1;$k<=count($kat);$k=$k+1):
           $kateg=utf8_encode($kat[$k]);
           if($value[$i]==$kateg):
             $sel=' selected="selected"';
             else:
             $sel='';
             endif;
           $zeile=$zeile.'
                <option'.$sel.'>'.$kateg.'</option>';
           endfor;
        $zeile=$zeile.'
            </select></td></tr>';
        else:
        $zeile=$zeile.'
        <td class="kal_form_td450">
            <input type="text" name="REX_INPUT_VALUE['.$i.']" value="'.$value[$i].'"
                   style="width:'.$width.'px;" />'.$form.'</td></tr>';
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
static public function kal_formular_suchen($spar) {
   #   Rueckgabe eines HTML-Formulars zur Suche nach einem Termin, gesteuert ueber
   #   diese 4 Parameter: Starttermin, Endtermin, Terminkategorie und Stichwort.
   #   Bei nicht korrekt ausgefuellten Feldern oder mehr als 1 gefundenem Termin
   #   wird das Formular durch eine Fehlermeldung ergaenzt.
   #   $spar           Array der Formularparameter-Werte:
   #                   [1]  Starttermin     (Parametername REX_INPUT_VALUE[1])
   #                   [2]  Endtermin       (Parametername REX_INPUT_VALUE[2])
   #                   [3]  Terminkategorie (Parametername REX_INPUT_VALUE[3])
   #                   [4]  Stichwort       (Parametername REX_INPUT_VALUE[4])
   #                   [5]  = ACTION_DELETE / ACTION_UPDATE / ACTION_COPY
   #                          Kuerzel fuer die Aktion, die nach Abschluss der Suche fuer
   #                          den Termin auf der Datenbanktabelle durchgefuehrt werden soll,
   #                          wird als 'hidden' Parameter weiter gereicht
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen)
   #      mit dem Schlusswert 'action:$pid' und
   #         'action' = Aktion gemaess $spar[5]), falls genau 1 Termin gefunden wurde
   #                  = 'ACTION_SEARCH, falls kein Termin oder mehr als 1 Termin gefunden wurde
   #   benutzte functions:
   #      self::kal_formular_radiobutton($action,$pid)
   #      self::kal_terminliste($termin)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #      kal_termine_tabelle::kal_select_termine($von,$bis,$kategorie,$stichwort)
   #
   $nextaction=$spar[5];
   #
   # --- Ueberschrift
   $string='
<div class="kal_form_prom">Suche eines Termins in der Datenbanktabelle zwischen \'Startdatum\' und \'Enddatum\'</div>
<table class="kal_table">';
   #
   # --- Tabelle
   $string=$string.'
    <tr valign="top">
        <td class="kal_form_th">
            Restriktionen:</td>
        <td class="kal_form_pad">
            <b>Restriktionen mit &nbsp * &nbsp; erfordern eine Eingabe</b></td></tr>';
   #
   # --- Standardisieren der Datumseingaben
   if(!empty($spar[1])) $spar[1]=kal_termine_kalender::kal_standard_datum($spar[1]);
   if(!empty($spar[2])) $spar[2]=kal_termine_kalender::kal_standard_datum($spar[2]);
   #
   # --- Auswahlmaske: Startdatum
   $string=$string.'
    <tr valign="top">
        <td><b>*</b> Startdatum:</td>
        <td class="kal_form_pad">
            <input name="REX_INPUT_VALUE[1]" type="text" value="'.$spar[1].'"
                   style="width:100px;" /> &nbsp; (<tt>tt.mm.yyyy</tt>)</td></tr>';
   #
   # --- Auswahlmaske: Enddatum
   $string=$string.'
    <tr valign="top">
        <td><b>*</b> Enddatum:</td>
        <td class="kal_form_pad">
            <input name="REX_INPUT_VALUE[2]" type="text" value="'.$spar[2].'"
                   style="width:100px;" /> &nbsp; (<tt>tt.mm.yyyy</tt>)</td></tr>';
   #
   # --- Auswahlmaske: Kategorien
   $kat=kal_termine_config::kal_get_terminkategorien();
   $string=$string.'
    <tr valign="top">
        <td>Kategorie:</td>
        <td class="kal_form_pad">
            <select name="REX_INPUT_VALUE[3]" class="kal_form_search">
                <option></option>';
   for($i=1;$i<=count($kat);$i=$i+1):
      $kateg=utf8_encode($kat[$i]);
      if($spar[3]==$kateg):
        $sel='selected="selected"';
        else:
        $sel='';
        endif;
      $string=$string.'
                <option '.$sel.'>'.$kateg.'</option>';
      endfor;
   $string=$string.'
            </select> &nbsp; &nbsp; &nbsp;
            (ggf. nur Termine einer bestimmten Kategorie)</td></tr>';
   #
   # --- Auswahlmaske: Stichwort
   $string=$string.'
    <tr valign="top">
        <td>Stichwort:</td>
        <td class="kal_form_pad">
            <input name="REX_INPUT_VALUE[4]" type="text" value="'.$spar[4].'"
                   class="kal_form_search" /> &nbsp; &nbsp; &nbsp;
            (ggf. nur Termine, die ein Stichwort enthalten)</td></tr>';
   #
   # --- hidden value (String zur Markierung der gewuenschten Aktion auf der DB-Tabelle)
   $string=$string.'
    <tr valign="top">
        <td> </td>
        <td><input type="hidden" name="REX_INPUT_VALUE[5]" value="'.$nextaction.'" /></td></tr>';
   #
   # --- Pruefung der Eingabedaten
   $error='';
   if(empty($spar[1])) $error='Bitte ein Startdatum angeben';
   if(empty($spar[2]) and !empty($spar[1])) $error='Bitte ein Enddatum angeben';
   if(!empty($spar[1]) and !empty($spar[2]) and
      kal_termine_tabelle::kal_datum_standard_mysql($spar[1])>
      kal_termine_tabelle::kal_datum_standard_mysql($spar[2]))
     $error='Das Enddatum muss NACH dem Startdatum liegen';
   #
   # --- zugehoerige Termine auslesen
   $pid=0;
   if(empty($error)):
     $termin=kal_termine_tabelle::kal_select_termine($spar[1],$spar[2],$spar[3],$spar[4]);
     if(count($termin)<=0):
       $str1='';
       $str2='            <span class="kal_form_fail">keine entsprechenden Termine gefunden, bitte Suche vergr&ouml;bern</span>';
       endif;
     if(count($termin)>1):
       $str1=count($termin).' Termine gefunden:';
       $str2='            <div class="kal_form_fail">bitte Suche verfeinern, bis genau 1 Termin vorliegt</div>';
       endif;
     if(count($termin)==1):
       $pid=$termin[1][pid];
       $str1='1 Termin gefunden:';
       $str2='';
       endif;
     endif;
   #
   if(!empty($error)):
   # --- entweder: Fehlermeldung ausgeben
     $string=$string.'
    <tr valign="top">
        <td> </td>
        <td class="kal_form_pad">
            <span class="kal_form_fail">'.$error.'</span></td></tr>';
     else:
   # --- oder: Gefunden-Zeile ausgeben, inkl. Terminliste
     $string=$string.'
    <tr valign="top">
        <td class="kal_form_th">
            '.$str1.'</td>
        <td class="kal_form_pad">
            '.$str2;
     if(count($termin)>0) $string=$string.self::kal_terminliste($termin);
     $string=$string.'
        </td></tr>';
     endif;
   #
   # --- Hinweis-Zeile (Termin gefunden / noch nicht gefunden)
   if($pid>0):
     $action=$nextaction;
     else:
     $action=ACTION_SEARCH;
     $pid='';
     endif;
   $string=$string.self::kal_formular_radiobutton($action,$pid);
   #
   $string=$string.'
</table>
';
   return $string;
   }
static public function kal_formular_startauswahl() {
   #   Rueckgabe des HTML-Formulars zur Auswahl einer Aktion fuer einen Termin
   #   auf der Datenbanktabelle (Eintragen / Korrigieren / Loeschen / Kopieren).
   #   Die Auswahl der Aktion erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Auswahl erfolgt ueber die Redaxo-Variable
   #   REX_INPUT_VALUE[count($cols)] ($cols = Array der Terminspaltennamen).
   #   benutzte functions:
   #      self::kal_formular_radiobutton($action,$pid)
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #
   # --- Ausgabe der Radio-Buttons
   $string='
<table class="kal_table">
    <tr valign="top">
        <td colspan="2" class="kal_form_prom">
            Verwaltung der Termine in der Datenbanktabelle</td></tr>
    <tr valign="top">
        <td class="kal_form_th">
            gew&uuml;nschte Aktion:</td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_INSERT.'" '.$chkein.'
                   onfocus="this.blur();" />
            &nbsp; Eintragen eines neuen Termins</td></tr>
    <tr valign="top">
        <td class="kal_form_th"> </td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_DELETE.'" '.$chkloe.'
                   onfocus="this.blur();" />
            &nbsp; L&ouml;schen eines vorhandenen Termins</td></tr>
    <tr valign="top">
            <td class="kal_form_th"> </td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_UPDATE.'" '.$chkkor.'
                   onfocus="this.blur();" />
            &nbsp; Korrigieren eines vorhandenen Termins</td></tr>
    <tr valign="top">
        <td class="kal_form_th"> </td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="'.ACTION_COPY.'" '.$chkcop.'
                   onfocus="this.blur();" />
            &nbsp; Kopieren eines vorhandenen Termins</td></tr>';
   #
   # --- Hinweis auf die Durchfuehrung
   $string=$string.self::kal_formular_radiobutton('','').'
</table>
';
   return $string;
   }
static public function kal_formular_eingeben($value) {
   #   Rueckgabe eines HTML-Formulars zur Eintragung eines Termins in die Datenbanktabelle.
   #   Die eingegebenen Termindaten werden formal ueberprueft.
   #   $value          Array der Formularparameter-Werte
   #                      $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                         ($i=1, ..., count($cols)-1, $cols = Array der Terminspaltennamen)
   #                      $value[count($cols)] = '' / 'insert:'
   #                         vor / nach Eintragung in die Formularfelder)
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
   #      self::kal_formular_fuellen($value)
   #      self::kal_proof_termin($termin)
   #      self::kal_formular_radiobutton($action,$pid)
   #      self::kal_formular_startauswahl()
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_exist_termin($termin)
   #      kal_termine_tabelle::kal_insert_termin($termin)
   #
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nzcols=count($cols);
   $arr=explode(':',$value[$nzcols]);
   $action=$arr[0];
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
   if(empty($error) and $action==ACTION_INSERT):
     $pid=kal_termine_tabelle::kal_insert_termin($termin);
     if($pid>0):
       $msg='<span class="kal_form_msg">Der Termin wurde in die Datenbank eingetragen</span>';
       else:
       $error='<span class="kal_form_fail">Der Termin konnte nicht eingetragen werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if($pid<=0):
     #
     #     Ueberschrift
     $string='
<div class="kal_form_prom">Eintragen eines einzelnen Termins in die Datenbanktabelle</div>
<table class="kal_table">';
     #
     #     Fuellen der Formularfelder
     $string=$string.self::kal_formular_fuellen($value);
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
     $string=$string.self::kal_formular_radiobutton(ACTION_INSERT,$pid).'
</table>
';
   #
   # --- Zurueck zum Startmenue
     else:
     $string=$msg.self::kal_formular_startauswahl();
     endif;
   return $string;
   }
static public function kal_formular_loeschen($pid) {
   #   Rueckgabe eines HTML-Formulars zum Loeschen eines Termins in der Datenbanktabelle.
   #   $pid            Id des zu loeschenden Termins
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Loeschen oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)] ($cols: Array der Tabellenspalten)
   #   Es wird eine Fehlermeldung zurueck gegeben (in rot), falls der Termin nicht
   #   geloescht werden konnte
   #   benutzte functions:
   #      self::kal_terminliste($termin)
   #      self::kal_formular_radiobutton($action,$pid)
   #      self::kal_formular_startauswahl()
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_delete_termin($termin)
   #
   # --- Ermittlung der zu $pid gehoerigen Termindaten (falls vorhanden)
   if($pid>0):
     $termin[1]=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     $error='';
     if(count($termin[1])<=0):
       $error='<span class="kal_form_fail">Der Termin (pid='.$pid.') wurde nicht gefunden</span>';
       $pid=0;
       endif;
     else:
     $error='<span class="kal_form_fail">Kein zu l&ouml;schender Termin angegeben</span>';
     $pid=0;
     endif;
   #
   # --- Loeschen des Termins in der Datenbanktabelle
   $msg='';
   $error2='';
   if($pid>0 and empty($error)):
     $ret=kal_termine_tabelle::kal_delete_termin($pid);
     if(empty($ret)):
       $msg='<span class="kal_form_msg">Der Termin wurde in der Datenbank gel&ouml;scht</span>';
       else:
       $error2='<span class="kal_form_fail">Der Termin (pid='.$pid.') konnte nicht gel&ouml;scht werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if($pid<=0 or !empty($error) or !empty($error2)):
   #  -  Ueberschrift
     $string='
<div class="kal_form_prom">L&ouml;schen eines einzelnen Termins in der Datenbanktabelle</div>
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
        <td class="kal_form_th">
            Termin:</td>
        <td class="kal_form_pad">'.self::kal_terminliste($termin).'
        </td></tr>';
       endif;
   #  -  Termin konnte nicht geloescht werden
     if(!empty($error2))
       $string=$string.'
    <tr valign="top">
        <td class="kal_form_th"> </td>
        <td class="kal_form_pad">
            '.$error2.'</td></tr>';
   #  -  Radio-Buttons (Loeschen / Abbruch)
     $string=$string.self::kal_formular_radiobutton(ACTION_DELETE,$pid).'
</table>';
   #
   # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>';
     $string=$string.self::kal_formular_startauswahl();
     endif;
   return $string;
   }
static public function kal_formular_korrigieren($pid,$value) {
   #   Rueckgabe eines HTML-Formulars zur Korrektur eines Termins in der Datenbanktabelle
   #   $pid            Id des zu korrigierenden Termins
   #   $value          Array zur Aufnahme der Daten des zu korrigierenden Termins
   #                   falls $value[1] (und damit das gesamte Array) nicht leer ist:
   #                      das Array die eingelesenen korrigierten Terminparameter:
   #                      $value[$i] = Terminparameter mit dem Schluessel $keys[$i]
   #                      ($keys=array_keys($cols), $i=1, ..., count($cols)-1)
   #                      ($cols = Array der Namen der Tabellenspalten)
   #                      Die Uebergabe der Werte erfolgt ueber die Werte der
   #                      Redaxo-Variablen REX_INPUT_VALUE[$i]
   #                   falls $value[1] (und damit das gesamte Array) leer ist:
   #                      die Daten des zu korrigierenden Termins werden
   #                      in das Array eingelesen
   #   #################################################################
   #   im Hauptprogramm sollte darauf geachtet werden, dass die Funktion
   #   NUR EINMAL AUFGERUFEN wird (entweder im Frontend oder im Backend)
   #   #################################################################
   #      Die Auswahl Korrigieren oder Abbruch erfolgt ueber den Wert der Redaxo-Variablen
   #      REX_INPUT_VALUE[count($cols)]
   #   Es wird eine Fehlermeldung ausgegeben (in rot), falls der Termin nicht
   #   korrigiert werden konnte
   #   benutzte functions:
   #      self::kal_formular_fuellen($value)
   #      self::kal_proof_termin($termin)
   #      self::kal_formular_radiobutton($action,$pid)
   #      self::kal_formular_startauswahl()
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_update_termin($pid,$termin)
   #
   $cols=kal_termine_tabelle::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $nzcols=count($cols);
   #
   # --- Termindaten aus der Datenbanktabelle holen (falls vorhanden)
   if($pid>0):
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     $error='';
     if(count($termin)<=0):
       $error='<span class="kal_form_fail">Der Termin wurde nicht gefunden</span>';
       $pid=0;
       endif;
     else:
     $error='<span class="kal_form_fail">Kein zu korrigierender Termin angegeben</span>';
     $pid=0;
     endif;
   #
   # --- Termindaten aus der Datenbanktabelle ($val)
   if($pid>0 and empty($error)):
     for($i=1;$i<$nzcols;$i=$i+1):
        $key=$keys[$i];
        if($key!='pid') $val[$i]=$termin[$key];
        endfor;
     endif;
   #
   # --- neue Termindaten aus dem Formularfeld ($val:=$value)
   if($pid>0 and empty($error)):
     for($i=1;$i<$nzcols;$i=$i+1):
        if(!empty($value[$i])):
          $val[$i]=$value[$i];
          $key=$keys[$i];
          if($key!='pid') $termin[$key]=$val[$i];
          endif;
        endfor;
     #  -  formale Ueberpruefung der Termindaten
     $error=self::kal_proof_termin($termin);
     endif;
   #
   # --- Korrigieren des Termins
   $msg='';
   $error2='';
   if($pid>0 and empty($error) and !empty($value[1])):
     $ret=kal_termine_tabelle::kal_update_termin($pid,$termin);
     if(empty($ret)):
       $msg='<span class="kal_form_msg">Der Termin wurde in der Datenbanktabelle korrigiert</span>';
       else:
       $error2='<span class="kal_form_fail">Der Termin konnte nicht korrigiert werden</span>';
       endif;
     endif;
   #
   # --- Formularausgabe
   if($pid<=0 or !empty($error) or !empty($error2) or empty($value[1])):
     #  -  Ueberschrift
     $string='
<div class="kal_form_prom">Korrigieren eines einzelnen Termins in der Datenbanktabelle</div>
<table class="kal_table">';
     #  -  Formular
     if(empty($error) or !empty($value[1])):
       $string=$string.self::kal_formular_fuellen($val);
       endif;
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
        <td class="kal_form_th"> </td>
        <td class="kal_form_pad">
            '.$error.'</td></tr>';
     #  -  Radio-Button (Korrigieren / Abbruch)
     $string=$string.self::kal_formular_radiobutton(ACTION_UPDATE,$pid).'
</table>';
     #
     # --- zurueck zum Startmenue
     else:
     $string='<div>'.$msg.'</div>';
     $string=$string.self::kal_formular_startauswahl();
     endif;
   return $string;
   }
static public function kal_formular_kopieren($pid,$kop,$anz) {
   #   Rueckgabe eines HTML-Formulars zur Kopie eines Termins in der Datenbanktabelle,
   #   entweder auf den Folgetag (Doppeltermin) oder woechentlich (bis zu MAXCOPY Kopien)
   #   $pid            Id des zu kopierenden Termins
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
   #      self::kal_formular_radiobutton($action,$pid)
   #      self::kal_terminliste($termin)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_wochentag($datum)
   #      kal_termine_tabelle::kal_define_tabellenspalten()
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_copy_termin($pid,$datumneu)
   #
   #
   # --- Formatierung der Eingabeparameter
   $anzkop=$anz;
   if($anzkop>MAXCOPY) $anzkop=MAXCOPY;
   #
   # --- Ermittlung der zu $pid gehoerigen Termindaten (falls vorhanden)
   if($pid>0):
     $termin[1]=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     $error='';
     if(count($termin[1])<=0):
       $error='<span class="kal_form_fail">Der zu kopierende Termin wurde nicht gefunden</span>';
       $pid=0;
       endif;
     $datum=$termin[1][datum];
     else:
     $error='<span class="kal_form_fail">Kein zu kopierender Termin angegeben</span>';
     endif;
   #
   # --- zusaetzliche Termintage $datneu[$i] (falls eine Kopie vorgenommen werden kann)
   if(empty($error)):
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
   if($pid>0 and $kop>0 and empty($error)):
     $ziel='';
     for($i=1;$i<=count($datneu);$i=$i+1):
        $ziel=$ziel.', '.$datneu[$i];
        $erg=kal_termine_tabelle::kal_copy_termin($pid,$datneu[$i]);
        $warn='';
        if(intval($erg)<0):
          $ter=kal_termine_tabelle::kal_select_termin_by_pid(abs(intval($erg)));
          $warn='<span class="kal_form_fail">Die Terminkopie am <tt>'.$ter[datum].
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
   if($pid<=0 or $kop<=0 or !empty($error)):
     #  -  Ueberschrift
     $string='
<div class="kal_form_prom">Kopieren eines Termins auf den Folgetag oder mehrfach auf denselben Wochentag</div>
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
        <td class="kal_form_th">
            Termin:</td>
        <td class="kal_form_pad">'.self::kal_terminliste($termin).'
        </td></tr>';
       endif;
     if(empty($error)):
       #  -  Auswahl der Kopierart (Folgetag / Folgewochentage)
       $aktstr1='auf den Folgetag ('.$wotag1.', '.$datum1.')';
       $aktstr2='auf jeden folgenden '.$wotag.' bis einschlie&szlig;lich &nbsp;';
       $aktstr2=$aktstr2.'
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
        <td class="kal_form_th">
            zu kopieren:</td>
        <td class="kal_form_pad">
            <input type="radio" name="REX_INPUT_VALUE[1]" value="1" '.$chk1.' />
            &nbsp; '.$aktstr1.'<br/>
            <input type="radio" name="REX_INPUT_VALUE[1]" value="2" '.$chk2.' />
            &nbsp; '.$aktstr2.'</td></tr>';
       endif;
     #  -  Radio-Buttons (Kopieren / Abbruch)
     $string=$string.self::kal_formular_radiobutton(ACTION_COPY,$pid).'
</table>';
     #
     # --- zurueck zum Startmenue
     else:
     $string='
<div>'.$msg.'</div>';
     $string=$string.self::kal_formular_startauswahl();
     endif;
   return $string;
   }
#
#----------------------------------------- Terminliste
static public function kal_terminliste($termin) {
   #   Rueckgabe einer Liste von Terminen in Form eines HTML-Codes
   #   $termin         Array der auszugebenden Termine (Indizierung ab 1)
   #   Termine haben diese Parameter:
   #      $termin[name] (als Link, falls [link] nicht leer)
   #             [datum]
   #             . . .
   #      NICHT mit ausgegeben:
   #             [kategorie]
   #   benutzte functins:
   #      self::kal_terminliste_merge($termin)
   #
   # --- Doppeltermine zusammenfassen
   $termin=self::kal_terminliste_merge($termin);
   #
   # --- Formular
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      #
      # --- Datum
      $arr=explode('.',$term[datum]);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $brr=explode('/',$term[datum]);
      $dat2=$brr[1];
      if(!empty($dat2)):
        # --- Doppeltermin
        $arr=explode('.',$dat2);
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
      $beginn=$term[beginn];
      if(substr($beginn,0,1)=='0') $beginn=substr($beginn,1);
      if(!empty($beginn)) $uhrz=$beginn;
      $ende=$term[ende];
      if(substr($ende,0,1)=='0') $ende=substr($ende,1);
      if(!empty($ende)) $uhrz=$uhrz.' - '.$ende;
      if(!empty($uhrz)) $uhrz=$uhrz.' Uhr: &nbsp; ';
      $str=$str.$uhrz;
      #
      # --- Veranstaltungsbezeichnung (ggf. als Link)
      $veran=$term[name];
      #  -  Link
      $link=$term[link];
      if(!empty($link)) $veran='<a href="'.$link.'" target="_blank">'.$veran.'</a>';
      $str=$str.'
            '.$veran;
      #
      # --- Ort
      $ort=$term[ort];
      if(!empty($ort)):
        $ort='('.$ort.')';
        $str=$str.'
            '.$ort;
        endif;
      #
      # --- Ausrichter
      $ausrichter=$term[ausrichter];
      if(!empty($ausrichter)):
        $ausrichter='Ausrichter: '.$ausrichter;
        $str=$str.',
            '.$ausrichter;
        endif;
      #
      # --- Zusatzzeiten
      $zusatz='';
      if(!empty($term[zeit2])):
        $zeit=$term[zeit2];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[text2];
        endif;
      if(!empty($term[zeit3])):
        $zeit=$term[zeit3];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[text3];
        endif;
      if(!empty($term[zeit4])):
        $zeit=$term[zeit4];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[text4];
        endif;
      if(!empty($term[zeit5])):
        $zeit=$term[zeit5];
        if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
        $zusatz=$zusatz.'<br/>
            '.$zeit.' Uhr: &nbsp; '.$term[text5];
        endif;
      if(!empty($zusatz)) $str=$str.$zusatz;
      #
      # --- Hinweise zur Veranstaltung
      $hinw=$term[komm];
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
static public function kal_terminliste_merge($termin) {
   #   Zusammenfuehren von Doppelterminen in einer Terminliste.
   #   Doppeltermine bestehen aus einen Paar von Terminen mit aufeinander
   #   folgendem Datum und identischen Parametern (ausser [pid]).
   #   Rueckgabe der entsprechend komprimierten Terminliste.
   #   $termin         Array der Termine (Indizierung ab 1)
   #                   Rueckgabe des entsprechend verkuerzten Termin-Arrays
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #
   # --- [$ter] Sortierung nach 'name' vorbereiten ('name' vor 'pid' kleben)
   $key='name';
   for($i=1;$i<=count($termin);$i=$i+1):
      $ter[$i]=$termin[$i];
      $dat=$ter[$i][$key];
      $datum=$ter[$i][datum];
      $ter[$i][pid]=$dat.':'.$ter[$i][pid];
      endfor;
   #
   # --- [$term] Sortierung nach 'name' durchfuehren
   sort($ter);
   #     umspeichern und pid wieder
   for($i=0;$i<count($ter);$i=$i+1) $term[$i+1]=$ter[$i];
   unset($ter);
   #
   # --- [$date] (max. 2) identische Termine mit unterschiedlichem Datum finden
   $streichen='streichen';
   $termalt=$term[1];
   $date[1][datum]='';
   $keys=array_keys($termalt);
   for($i=2;$i<=count($term);$i=$i+1):
     $termneu=$term[$i];
      $date[$i][datum]='';
      $gleich=TRUE;
      for($k=1;$k<=count($termneu);$k=$k+1):
         $key=$keys[$k-1];
         if($key=='pid' or $key=='datum') continue;
         if($termneu[$key]==$termalt[$key]) continue;
         $gleich=FALSE;
         break;
         endfor;
      if($gleich and $termneu[datum]==kal_termine_kalender::kal_datum_vor_nach($termalt[datum],1)):
      # --- auch bei gleichen Daten: nur streichen bei aufeinander folgendem Datum
        $arr=explode('.',$termalt[datum]);
        $tag1=$arr[0];
        $mon1=$arr[1];
        $arr=explode('.',$termneu[datum]);
        $tag2=$arr[0];
        $mon2=$arr[1];
        $date[$i][datum]=$tag1.'.'.$mon1.'./'.$tag2.'.'.$mon2.'.'.$arr[2];
        $date[$i][name]=$termneu[name];
        $date[$i-1][name]=$streichen;
        endif;
      $termalt=$termneu;
      endfor;
   #
   # --- [$term] identische Termine mit unterschiedlichem Datum zusammenfassen
   $m=0;
   for($i=1;$i<=count($term);$i=$i+1):
      if($date[$i][name]==$streichen) continue;
      $m=$m+1;
      $ter[$m]=$term[$i];
      if(!empty($date[$i][datum])) $ter[$m][datum]=$date[$i][datum];
      endfor;
   unset($term);
   for($i=1;$i<=count($ter);$i=$i+1) $term[$i]=$ter[$i];
   unset($ter);
   #
   # --- [$term] statt 'name' jetzt Datum in MySQL-Format vor 'pid' kleben
   for($i=1;$i<=count($term);$i=$i+1):
      $datumdopp=$term[$i][datum];
      if(strpos($datumdopp,'/')>0):
        $arr=explode('/',$datumdopp);
        $datumdopp=$arr[1];
        endif;
      $datum=kal_termine_tabelle::kal_datum_standard_mysql($datumdopp);
      $arr=explode(':',$term[$i][pid]);
      $term[$i][pid]=$datum.':'.$arr[1];
      endfor;
   #
   # --- [$ter] Sortierung nach Datum durchfuehren
   sort($term);
   #     umspeichern
   for($i=0;$i<count($term);$i=$i+1) $ter[$i+1]=$term[$i];
   unset($term);
   #
   # --- vor 'pid' geklebtes Datum wieder entfernen
   for($i=1;$i<=count($ter);$i=$i+1):
      $arr=explode(':',$ter[$i][pid]);
      $ter[$i][pid]=$arr[1];
      endfor;
   #
   # --- Rueckgabe des bereinigten Termin-Arrays
   return $ter;
   }
}
?>
