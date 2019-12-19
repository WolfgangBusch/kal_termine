<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Dezember 2019
 */
#
class kal_termine_module {
#
#----------------------------------------- Inhaltsuebersicht
#         kal_select_menue($name,$men)
#         kal_kalendermenue($menue,$kategorie)
#         kal_std_terminliste($von,$tage,$kategorie)
#         kal_find_termin($pid)
#
public static function kal_select_menue($name,$men) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kalendermenues.
   #   $name           Name des select-Formulars
   #   $men            Nummer des vorher gewaehlten Menues
   #   benutzte functions:
   #      kal_termine_config::kal_get_terminkategorien()
   #
   $menue=kal_termine_menues::kal_define_menues();
   $string='<select name="'.$name.'" class="kal_form_search">';
   for($i=1;$i<=count($menue);$i=$i+1):
      if($i==$men):
        $sel='selected="selected"';
        else:
        $sel='';
        endif;
      $string=$string.'
                <option value="'.$i.'" '.$sel.'>'.$menue[$i][1].'</option>';
      endfor;
   $string=$string.'
            </select>';
   return $string;
   }
public static function kal_manage_termine($value) {
   #   Rueckgabe des HTML-Codes zur Verwaltung der Termine in der Datenbanktabelle
   #   rex_kal_termine (fuer einen entsprechenden Modul). Ueber verschiedene
   #   Formulare kann man einen neuen Termin eintragen oder einen vorhandenen
   #   Termin suchen, um ihn zu loeschen oder zu korrigieren oder zu kopieren.
   #   Die Aktionen werden gesteuert ueber die Redaxo-Variablen REX_VALUE[1],
   #   REX_VALUE[2], ..., REX_VALUE[18].
   #   $value          nummeriertes Array der vorliegenden Redaxo-Variablen
   #                   $value[$i]=REX_VALUE[$i] ($i = 1, 2, ..., 18)
   #   benutzte functions:
   #      self::kal_find_termin($pid)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_formulare::kal_startauswahl()
   #      kal_termine_formulare::kal_eingeben($value,$action)
   #      kal_termine_formulare::kal_loeschen($pid,$action)
   #      kal_termine_formulare::kal_korrigieren($value,$pid,$action)
   #      kal_termine_formulare::kal_kopieren($pid,$action,$kop,$anz)
   #
   $val=$value;
   #
   $action='';
   $pid='';
   if(!empty($val[COL_ANZAHL])):
     $arr=explode(':',$val[COL_ANZAHL]);
     $action=$arr[0];
     if(count($arr)>1) $pid=$arr[1];
     endif;
   $ACT=$val[1];
   #
   # --- Start-/Eingabe-/Suchformular
   if(empty($pid) or (empty($action) and empty($ACT))):
     #  -  Startformular
     if($action==ACTION_START or (empty($action) and empty($ACT) and empty($pid)))
       echo kal_termine_formulare::kal_startauswahl();
     #  -  Eingabeformular
     if($action==ACTION_INSERT or $ACT==ACTION_INSERT):
       if($ACT==ACTION_INSERT) $val[1]='';
       echo kal_termine_formulare::kal_eingeben($val,$action);
       endif;
     #  -  Suchformular
     if(($pid>0 and empty($action) and empty($ACT)) or
        (empty($action) and $ACT==ACTION_SEARCH))
       echo self::kal_find_termin($pid);
   #
   # --- Loesch-/Korrektur-/Kopierformular
     ;else:
     #  -  Loeschformular
     if($action==ACTION_DELETE or $ACT==ACTION_DELETE)
       echo kal_termine_formulare::kal_loeschen($pid,$action);
     #  -  Korrekturformular
     if($action==ACTION_UPDATE or $ACT==ACTION_UPDATE):
       if($ACT==ACTION_UPDATE):
         $term=kal_termine_tabelle::kal_select_termin_by_pid($pid);
         $keys=array_keys($term);
         for($i=1;$i<count($term);$i=$i+1) $val[$i]=$term[$keys[$i]];
         endif;
       echo kal_termine_formulare::kal_korrigieren($val,$pid,$action);
       endif;
     #  -  Kopierformular
     if($action==ACTION_COPY or $ACT==ACTION_COPY):
       if($ACT==ACTION_COPY):
         $kop=0;
         $anz='';
         else:
         $kop=$val[1];
         $anz=$val[2];
         endif;
       echo kal_termine_formulare::kal_kopieren($pid,$action,$kop,$anz);
       endif;
     endif;
   }
public static function kal_kalendermenue($menue,$kategorie) {
   #   Rueckgabe des HTML-Codes des Auswahlmenues fuer ein Start-Kalendermenue
   #   (in einem entsprechenden Modul).
   #   kal_define_menues() liefert die verfuegbaren Kalendermenues.
   #   $menue          Nummer des ggf. voher schon gewaehlten Menues (Default: 1).
   #   $kategorie      Name der ggf. vorher schon gewaehlten Kategorie.
   #   Die Auswahl des Menues erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   benutzte functions:
   #      self::self::kal_select_menue($name,$men)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_formulare::kal_select_kategorie($name,$kategorie,$kont)
   #      kal_termine_menues::kal_define_menues()
   #   
   $str='<h4 align="center">Auswahl eines Start-Kalendermenüs</h4>
<div>Die entsprechenden Zeitabschnitte enthalten immer den heutigen Tag.</div><br/>';
   #
   # --- ggf. wird die Menue-Nummer korrigiert
   $menues=kal_termine_menues::kal_define_menues();
   $men=$menue;
   if($men<=0 or $men>count($menues)) $men=1;
   #
   # --- Auswahl des Kalendermenues
   $str=$str.'
<table class="kal_table">
    <tr><td class="kal_form_list_th">Kalendermenü:</td>
        <td class="kal_form_pad">
            '.self::kal_select_menue('REX_INPUT_VALUE[1]',$men).'<br/>
            '.$menues[$men][2].'</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $kat=kal_termine_config::kal_get_terminkategorien();
   $kat[0]='';
   $str=$str.'
    <tr><td colspan="3">&nbsp;</td></tr>
    <tr><td class="kal_form_list_th">Terminkategorie:</td>
        <td class="kal_form_pad">
            '.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[2]',$kategorie,'').'
            &nbsp; (keine Angabe: Termine aller Kategorien)<br/>
            Es werden nur die Termine der gewählten Kategorie ausgefiltert und angezeigt</td></tr>
</table>';
   return $str;
   }
public static function kal_std_terminliste($ab,$tage,$kategorie) {
   #   Rueckgabe des HTML-Codes zur Konfigurierung einer Standard-Ausgabeliste
   #   von Terminen (fuer einen entsprechenden Modul).
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine
   #                   (falls leer, wird jeweils das heutige Datum eingesetzt).
   #   $tage           ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen.
   #   $kategorie      Name der ggf. vorher schon gewaehlten Kategorie.
   #   Die Auswahl des Startdatums erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Anzahl Tage erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[3].
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_formulare::kal_select_kategorie($name,$kategorie,$kont)
   #
   $von=$ab;
   if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
   #
   # --- Ueberschrift
   $strtage='';
   if($tage>0) $strtage='für die nächsten <u>'.$tage.' Tage</u>';
   $str='<h4 align="center">Liste aller Termine einer Kategorie über einen Zeitraum<br/>&nbsp;</h4>
   <table class="kal_table">';
   #
   # --- Datum, ab wann die Termine ausgegeben werden sollen
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th">ab:</td>
        <td class="kal_form_pad">
            <input type="text" name="REX_INPUT_VALUE[1]" value="'.$von.'" class="kal_form_input_date" /></td>
        <td class="kal_form_pad">
            <tt>tt.mm.jjjj</tt> (festes Datum) oder<br/>
            keine Angabe (jeweils der heutige Tag)</td></tr>';
   #
   # --- Zeitraum in Anzahl Tage
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th">Zeitraum:</td>
        <td class="kal_form_pad">
            <input type="text" name="REX_INPUT_VALUE[2]" value="'.$tage.'" class="kal_install_number" /></td>
        <td class="kal_form_pad">
            Anzahl Tage nach dem Startdatum</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th">Kategorie:</td>
        <td class="kal_form_pad">
            '.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[3]',$kategorie,'').' &nbsp; </td>
        <td class="kal_form_pad">
            keine Angabe: Termine aller Kategorien</td></tr>
</table>';
   return $str;
   }
public static function kal_find_termin($pidalt) {
   #   Suche eines Termins, beginnend mit dem Monatsmenue des aktuellen Monats.
   #   Ueber dieses oder ueber ein Tages-/Wochen-/Monatsblatt wird der Termin
   #   ausgewaehlt. Solange die Suche andauert, wird nur der HTML-Code fuer die
   #   entsprechenden Menues zurueck gegeben.
   #   Ist der Termin gefunden, werden dessen Termindaten ausgegeben und die
   #   Auswahl der geplanten Aktion angezeigt.
   #   $pidalt         Id eines vorher schon bestimmten Termins;
   #                   wird benutzt, falls zunaechst keine Auswahl einer
   #                   Aktion fuer den Termin erfolgt ist
   #   Die Auswahl der Aktion erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Aktion (bzw. deren Abbruch) erfolgt ueber die
   #      Redaxo-Variable REX_INPUT_VALUE[count($cols)]
   #      ($cols = Array der Terminspaltennamen) mit dem Schlusswert
   #      'action:$pid' ($pid = Id des gefundenen Termins)
   #   benutzte functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_formulare::kal_formular_radiobutton($action,$pid)
   #      kal_termin_formulare::kal_terminblatt($termin,$headline)
   #      kal_termine_menues::kal_menue($kategorie,$termtyp,$mennr)
   #
   # --- Ueberschrift
   $abbruch='Abbruch der Suche erst nach erfolgter Auswahl eines Termins möglich';
   $ueber='Auswahlmenü zum Suchen eines Termins';
   #
   # --- Einlesen der Termin-Id
   $pid='';
   if(!empty($_POST[KAL_PID])) $pid=$_POST[KAL_PID];
   if(empty($pid)) $pid=$pidalt;
   if($pid<=0):
     #
     # --- Oeffnen des Terminmenues
     $men='';
     if(!empty($_POST[KAL_MENUE])) $men=$_POST[KAL_MENUE];
     if(empty($men)) $men=1;
     return '<div align="center">
<h4 title="'.$abbruch.'">'.$ueber.'</h4>
<p><i>('.$abbruch.')</i></p>'.
kal_termine_menues::kal_menue('','',$men).'
</div>';
     else:
     #
     # --- Ausgabe des des gefundenen Termins mit Auswahl der Aktion 
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     return '<table class="kal_table">
    <tr><td></td>
        <td class="kal_form_pad">
            <h4>'.$ueber.'</h4></td></tr>
    <tr valign="top">
        <td class="kal_form_list_th">
            gefundener Termin:</td>
        <td class="kal_form_pad">
'.      kal_termine_formulare::kal_terminblatt($termin,'kal_termine_formulare::kal_terminblatt_head').'</td></tr>
</table><br/>'.
        kal_termine_formulare::kal_aktionsauswahl($pid);
     endif;
   }
}
?>
