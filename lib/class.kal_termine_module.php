<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Januar 2021
*/
#
class kal_termine_module {
#
#----------------------------------------- Inhaltsuebersicht
#      kal_manage_termine($value,$slice_id,$katid)
#          kal_find_termin($pidalt,$katid)
#      kal_terminmenue_in($men,$ab,$anztage,$katid)
#          kal_terminmenue_select($name,$mennr)
#      kal_terminmenue_out($men,$ab,$anztage,$katid)
#      ########### obsolet ab Version 3.1:  #########
#      #  kal_kalendermenue($menue,$katid)          #
#      #      kal_select_menue($name,$mennr)        #
#      #  kal_out_kalendermenue($menue,$katid)      #
#      #  kal_std_terminliste($ab,$anztage,$katid)  #
#      #  kal_mod_terminliste($ab,$anztage,$katid)  #
#      ##############################################
#
public static function kal_manage_termine($value,$slice_id,$katid=0) {
   #   Rueckgabe des HTML-Codes zur Verwaltung der Termine einer bzw. aller
   #   Kategorien in der Datenbanktabelle (fuer einen entsprechenden Modul).
   #   Ueber verschiedene Formulare kann man einen neuen Termin eintragen oder
   #   einen vorhandenen Termin suchen, um ihn zu loeschen oder zu korrigieren.
   #   Die Aktionen werden gesteuert ueber die Redaxo-Variablen REX_VALUE[1],
   #   ..., REX_VALUE[20]. Am Ende werden alle Redaxco-Variablen geloescht.
   #   $value          nummeriertes Array der vorliegenden Redaxo-Variablen
   #                   $value[$i]=REX_VALUE[$i] ($i = 1, 2, ..., 20)
   #   $slice_id       Slice-Id des Artikel-blocks (REX_SLICE_ID)
   #   $katid          Id der Kategorie bzw. =0 fuer alle Kategorien
   #   benutzte functions:
   #      self::kal_find_termin($pidalt,$katid)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_formulare::kal_startauswahl()
   #      kal_termine_formulare::kal_eingeben($value,$action)
   #      kal_termine_formulare::kal_loeschen($pid,$action)
   #      kal_termine_formulare::kal_korrigieren($value,$pid,$action)
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
       echo self::kal_find_termin($katid,$pid);
   #
   # --- Loesch-/Korrekturformular
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
     endif;
   #
   # --- Loeschen aller Redaxo-Variablen
   $sql=rex_sql::factory();
   $upd='UPDATE rex_article_slice SET ';
   for($i=1;$i<=COL_ANZAHL;$i=$i+1) $upd=$upd.'value'.$i.'="", ';
   $upd=substr($upd,0,strlen($upd)-2);
   $upd=$upd.' WHERE id='.strval($slice_id);
   $sql->setQuery($upd);
   }
public static function kal_find_termin($pidalt,$katid=0) {
   #   Suche eines Termins in einer bzw. in allen Kategorien, beginnend mit dem
   #   Monatsmenue des aktuellen Monats. Ueber dieses oder ueber ein Tages-/Wochen-/
   #   Monatsblatt wird der Termin ausgewaehlt. Solange die Suche andauert, wird
   #   nur der HTML-Code fuer die entsprechenden Menues zurueck gegeben.
   #   Ist der Termin gefunden, werden dessen Termindaten ausgegeben und die
   #   Auswahl der geplanten Aktion angezeigt.
   #   $pidalt         Id eines vorher schon bestimmten Termins;
   #                   wird benutzt, falls zunaechst keine Auswahl einer
   #                   Aktion fuer den Termin erfolgt ist
   #   $katid          Id der Kategorie bzw. =0 fuer alle Kategorien
   #   Die Auswahl der Aktion erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Durchfuehrung der Aktion (bzw. deren Abbruch) erfolgt ueber die
   #      Redaxo-Variable REX_INPUT_VALUE[count($cols)]
   #      ($cols = Array der Terminspaltennamen) mit dem Schlusswert
   #      'action:$pid' ($pid = Id des gefundenen Termins)
   #   benutzte functions:
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_formulare::kal_terminblatt($termin)
   #      kal_termine_formulare::kal_aktionsauswahl($pid)
   #      kal_termine_menues::kal_menue($katid,$mennr)
   #
   # --- Ueberschrift
   $abbruch='Abbruch der Suche erst nach erfolgter Auswahl und Anzeige eines einzelnen Termins möglich';
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
kal_termine_menues::kal_menue($katid,$men).'
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
'.      kal_termine_formulare::kal_terminblatt($termin).'</td></tr>
</table><br/>'.
        kal_termine_formulare::kal_aktionsauswahl($pid);
     endif;
   }
public static function kal_terminmenue_in($men,$ab,$anztage,$katid) {
   #   Rueckgabe des HTML-Codes zur Konfigurierung einer Standard-Ausgabeliste
   #   von Terminen (fuer einen entsprechenden Modul).
   #   $men            ggf. vorher schon gewaehlte Menuenummer
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine
   #                   (falls leer, wird jeweils das heutige Datum eingesetzt).
   #   $anztage        ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen.
   #   $katid          Id der ggf. vorher schon gewaehlten Kategorie.
   #   Die Auswahl des Startdatums erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Anzahl Tage erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[3].
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_formulare::kal_select_kategorie($name,$kategorie,$kont)
   #      kal_termine_menues::kal_define_menues()
   #
   $von=$ab;
   if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
   $menues=kal_termine_menues::kal_define_menues();
   $ro='';
   $butxt='';
   $abtxt='(Default, nicht änderbar)';
   $zrtxt=$abtxt;
   if(empty($men)) $men=1;
   #
   # --- Zeitdaten ergaenzen
   #     Monatsmenue / Monatsblatt
   if(strpos($menues[$men]['name'],'natsmenü')>0 or
      strpos($menues[$men]['name'],'natsblatt')>0  ):
     #     Defaultdaten
     $heute=kal_termine_kalender::kal_heute();
     $von='01'.substr($heute,2);
     $jahr=substr($heute,6);
     $mon=intval(substr($heute,3,2));
     $anztage=kal_termine_kalender::kal_monatstage($jahr)[$mon];
     $ro=' readonly';
     endif;
   #     Wochenblatt
   if(strpos($menues[$men]['name'],'chenblatt')>0):
     #     Defaultdaten
     $heute=kal_termine_kalender::kal_heute();
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     $von=kal_termine_kalender::kal_kw_montag($kw,$jahr);
     $anztage=7;
     $ro=' readonly';
     endif;
   #     Tagesblatt
   if(strpos($menues[$men]['name'],'agesblatt')>0):
     #     Defaultdaten
     $von=kal_termine_kalender::kal_heute();
     $anztage=1;
     $ro=' readonly';
     endif;
   #     Zeitraumfiltermenue / Terminliste
   if(strpos($menues[$men]['name'],'raumfilter')>0 or
      strpos($menues[$men]['name'],'minliste')>0     ):
     #     Defaultdaten
     $anztage=365;
     $butxt='<br/>
            Mit <span class="kal_form_block">(&laquo;Block speichern&raquo;)</span> wird ein individueller Zeitraum übernommen.';
     $abtxt='Eingabe im Format <tt>tt.mm.jjjj</tt> (Default: der heutige Tag)';
     $zrtxt='Eingabe der Anzahl Tage (inkl. Startdatum, Default: '.$anztage.')';
     endif;
   #
   # --- Ueberschrift
   $strtage='';
   $str='<h4 align="center">Kalendermenü zur Auswahl von Terminen</h4>
   <table class="kal_table">';
   #
   # --- Auswahl des Kalendermenues
   if($men<=0):
     $titel='';
     else:
     $titel=$menues[$men]['titel'];
     endif;
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th"><br/>Menü:</td>
        <td class="kal_form_pad" width="50"><br/>
'.self::kal_terminmenue_select('REX_INPUT_VALUE[1]',$men).'</td>
        <td class="kal_form_pad"><br/>
            (der Default-Zeitraum enthält jeweils den heutigen Tag)</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th"></td>
        <td class="kal_form_pad" colspan="2">
            '.$titel.'
            <div class="kal_config_small"><br/>
            Mit <span class="kal_form_block">(&laquo;Block übernehmen&raquo;)</span>
            wird die Charakterisierung des Menüs angepasst und der zugehörige
            Default-Zeitraum sowie die Kategorie übernommen.'.$butxt.'</div></td></tr>';
   #
   # --- Datum, ab wann die Termine ausgegeben werden sollen
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th"><br/>ab:</td>
        <td class="kal_form_pad"><br/>
            <input type="text" name="REX_INPUT_VALUE[2]" value="'.$von.'" class="kal_form_input_date"'.$ro.' /></td>
        <td class="kal_form_pad"><br/>
            '.$abtxt.'</tt></td></tr>';
   #
   # --- Zeitraum in Anzahl Tage
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th"><br/>Zeitraum:</td>
        <td class="kal_form_pad"><br/>
            <input type="text" name="REX_INPUT_VALUE[3]" value="'.$anztage.'" class="kal_install_number"'.$ro.' /></td>
        <td class="kal_form_pad"><br/>
            '.$zrtxt.'</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th"><br/>Kategorie:</td>
        <td class="kal_form_pad"><br/>
'.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[4]',$katid,'').' &nbsp; </td>
        <td class="kal_form_pad"><br/>
            keine Angabe: Termine aller Kategorien</td></tr>';
   #
   $str=$str.'
</table>';
   return $str;
   }
public static function kal_terminmenue_select($name,$mennr) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kalendermenues:
   #      Monatsmenue, Monatsblatt, Wochenblatt, Tagesblatt, Zeitraumfiltermenue
   #   $name           Name des select-Formulars
   #   $mennr          Nummer des vorher gewaehlten Menues (Default: 1)
   #   benutzte functions:
   #      kal_termine_menues::kal_define_menues()
   #
   $menues=kal_termine_menues::kal_define_menues();
   $men=$mennr;
   if($men<=1) $men=1;
   $string='<select name="'.$name.'" class="kal_form_search">';
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')<=0 and
         strpos($menues[$i]['name'],'atsblatt')<=0 and
         strpos($menues[$i]['name'],'henblatt')<=0 and
         strpos($menues[$i]['name'],'gesblatt')<=0 and
         strpos($menues[$i]['name'],'umfilter')<=0 and
         strpos($menues[$i]['name'],'minliste')<=0    ) continue;
      if($i==$men):
        $sel='selected="selected"';
        else:
        $sel='';
        endif;
      $string=$string.'
    <option value="'.$i.'" '.$sel.'>'.$menues[$i]['name'].'</option>';
      endfor;
   $string=$string.'
</select>';
   return $string;
   }
public static function kal_terminmenue_out($men,$ab,$anztage,$katid) {
   #   Rueckgabe des HTML-Codes zur Ausgabe einer Standard-Terminliste
   #   (fuer einen entsprechenden Modul).
   #   $men            Nummer des gewaehlten Menues
   #   $ab             gewaehltes Datum fuer den ersten Tag der auszugebenden Termine
   #                   (falls leer, wird das heutige Datum eingesetzt)
   #   $anztage        gewaehlte Anzahl von Tagen fuer den Zeitraum, fuer die Termine
   #                   ausgegeben werden sollen
   #   $katid          Id der gewaehlten Kategorie
   #   Die Auswahl des Startdatums erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Anzahl Tage erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[3].
   #   benutzte functions:
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$katif)
   #      kal_termine_tabelle::kal_kategorie_name($katid)
   #      kal_termine_formulare::kal_terminliste($termin)
   #      kal_termine_menues::kal_define_menues()
   #      kal_termine_menues::kal_menue($katid,$mennr)
   #
   $von=$ab;
   if(empty($von)) $von=kal_termine_kalender::kal_heute();
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,intval($anztage-1));
   $term=kal_termine_tabelle::kal_get_termine($von,$bis,$katid,0);
   $menues=kal_termine_menues::kal_define_menues();
   if(empty($men)) $men=1;
   if(!rex::isBackend()):
     #
     # --- Frontend
     if(strpos($menues[$men]['name'],'minliste')>0):
       $str=kal_termine_formulare::kal_terminliste($term);
       else:
       $str=kal_termine_menues::kal_menue($katid,$men);
       endif;
     else:
     #
     # --- Backend
     $stb='<u>'.$menues[$men]['name'].':</u> &nbsp; '.$von.' - '.$bis;
     $stc='alle Kategorien';
     if($katid>0):
       $kateg=kal_termine_tabelle::kal_kategorie_name($katid);
       $stc='Kategorie "'.$kateg.'"';
       endif;
     $std=count($term).' Termine';
     $str='
<div><span class="kal_form_msg">'.$stb.' &nbsp; ('.$stc.'): &nbsp; '.$std.'</span> &nbsp; '.
        '<small>(Ausgaben nur im Frontend)</small></div>';
     endif;
   return $str;
   }
############################## alle folgenden functions ab Version 3.1 obsolet
public static function kal_kalendermenue($menue,$katid) {
   #   Rueckgabe des HTML-Codes des Auswahlmenues fuer ein Start-Kalendermenue
   #   (in einem entsprechenden Modul).
   #   kal_define_menues() liefert die verfuegbaren Kalendermenues.
   #   $menue          Nummer des ggf. voher schon gewaehlten Menues (Default: 1).
   #   $katid          Id der ggf. vorher schon gewaehlten Kategorie.
   #   Die Auswahl des Menues erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   benutzte functions:
   #      self::self::kal_select_menue($name,$men)
   #      kal_termine_formulare::kal_select_kategorie($name,$katid,$kont)
   #      kal_termine_menues::kal_define_menues()
   #   
   $str='<h4 align="center">Auswahl eines Start-Kalendermenüs</h4>';
   #
   # --- ggf. wird die Menue-Nummer korrigiert
   $menues=kal_termine_menues::kal_define_menues();
   $men=$menue;
   if($men<=0 or $men>count($menues)) $men=1;
   #
   # --- Auswahl des Kalendermenues
   $str=$str.'
<table class="kal_table">
    <tr valign="top">
        <td class="kal_form_list_th">Kalendermenü:</td>
        <td class="kal_form_pad" width="100">
'.self::kal_select_menue('REX_INPUT_VALUE[1]',$men).'</td>
        <td class="kal_form_pad">
            (alle Zeitabschnitte enthalten den heutigen Tag)</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th"></td>
        <td class="kal_form_pad" colspan="2">
            '.$menues[$men][2].'</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $str=$str.'
    <tr valign="top">
        <td colspan="3">&nbsp;</td></tr>
    <tr valign="top">
        <td class="kal_form_list_th">Terminkategorie:</td>
        <td class="kal_form_pad" width="100">
'.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[2]',$katid,'').'</td>
        <td class="kal_form_pad">
            (keine Angabe: Termine aller Kategorien)</td></tr>
    <tr valign="top">
        <td class="kal_form_pad"></td>
        <td class="kal_form_pad" colspan="2">
            Es werden nur die Termine der gewählten Kategorie ausgefiltert und angezeigt</td></tr>
</table>';
   return $str;
   }
public static function kal_select_menue($name,$mennr) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kalendermenues.
   #   $name           Name des select-Formulars
   #   $mennr          Nummer des vorher gewaehlten Menues (Default: 1)
   #   benutzte functions:
   #      kal_termine_menues::kal_define_menues()
   #
   $menue=kal_termine_menues::kal_define_menues();
   $men=$mennr;
   if($men<=1) $men=1;
   $string='<select name="'.$name.'" class="kal_form_search">';
   for($i=1;$i<=count($menue);$i=$i+1):
      if($i==$men):
        $sel='selected="selected"';
        else:
        $sel='';
        endif;
      $string=$string.'
    <option value="'.$i.'" '.$sel.'>'.$menue[$i]['name'].'</option>';
      endfor;
   $string=$string.'
</select>';
   return $string;
   }
public static function kal_out_kalendermenue($menue,$katid) {
   #   Rueckgabe des HTML-Codes fuer die Ausgabe eines Start-Kalendermenues
   #   (in einem entsprechenden Modul).
   #   kal_define_menues() liefert die verfuegbaren Kalendermenues.
   #   $menue          Nummer des ggf. voher schon gewaehlten Menues (Default: 1).
   #   $katid          Id der ggf. vorher schon gewaehlten Kategorie.
   #   Die Auswahl des Menues erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   benutzte functions:
   #      kal_termine_tabelle::kal_kategorie_name($katid)
   #      kal_termine_menues::kal_define_menues()
   #      kal_termine_menues::kal_menue($katid,$mennr)
   #
   $mennr=$menue;
   if(empty($mennr)) $mennr=1;
   if(rex::isBackend()):
     $str='<u>aller</u> Kategorien';
     if($katid>0) $str='der Kategorie <u>'.kal_termine_tabelle::kal_kategorie_name($katid).'</u>';
     $menues=kal_termine_menues::kal_define_menues();
     $str='
<div><span class="kal_form_msg"><u>'.$menues[$mennr]['name'].':</u> Termine '.$str.'</div>';
     else:
     $str=kal_termine_menues::kal_menue($katid,$mennr);
     endif;
   return $str;
   }
public static function kal_std_terminliste($ab,$anztage,$katid) {
   #   Rueckgabe des HTML-Codes zur Konfigurierung einer Standard-Ausgabeliste
   #   von Terminen (fuer einen entsprechenden Modul).
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine
   #                   (falls leer, wird jeweils das heutige Datum eingesetzt).
   #   $anztage        ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen.
   #   $katid          Id der ggf. vorher schon gewaehlten Kategorie.
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
   if($anztage>0) $strtage='für die nächsten <u>'.$anztage.' Tage</u>';
   $str='<h4 align="center">Liste aller Termine einer Kategorie bzw. aller Kategorien über einen Zeitraum</h4>
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
            keine Angabe (der heutige Tag)</td></tr>';
   #
   # --- Zeitraum in Anzahl Tage
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th">Zeitraum:</td>
        <td class="kal_form_pad">
            <input type="text" name="REX_INPUT_VALUE[2]" value="'.$anztage.'" class="kal_install_number" /></td>
        <td class="kal_form_pad">
            Anzahl Tage (inkl. Startdatum)</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $str=$str.'
    <tr valign="top">
        <td class="kal_form_list_th">Kategorie:</td>
        <td class="kal_form_pad">
'.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[3]',$katid,'').' &nbsp; </td>
        <td class="kal_form_pad">
            keine Angabe: Termine aller Kategorien</td></tr>
</table>';
   return $str;
   }
public static function kal_mod_terminliste($ab,$anztage,$katid) {
   #   Rueckgabe des HTML-Codes zur Ausgabe einer Standard-Terminliste
   #   (fuer einen entsprechenden Modul).
   #   $ab              gewaehltes Datum fuer den ersten Tag der auszugebenden Termine
   #                   (falls leer, wird das heutige Datum eingesetzt)
   #   $anztage        gewaehlte Anzahl von Tagen fuer den Zeitraum, fuer die Termine
   #                   ausgegeben werden sollen
   #   $katid          Id der gewaehlten Kategorie
   #   Die Auswahl des Startdatums erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Anzahl Tage erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[3].
   #   benutzte functions:
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$katif)
   #      kal_termine_tabelle::kal_kategorie_name($katid)
   #      kal_termine_formulare::kal_terminliste($termin)
   #
   $von=$ab;
   if(empty($von)) $von=kal_termine_kalender::kal_heute();
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,intval($anztage-1));
   $term=kal_termine_tabelle::kal_get_termine($von,$bis,$katid,0);
   if(!rex::isBackend()):
     $str=kal_termine_formulare::kal_terminliste($term);
     else:
     $sta=$von.' - '.$bis;
     $stb='alle Kategorien';
     if($katid>0):
       $kateg=kal_termine_tabelle::kal_kategorie_name($katid);
       $stb='Kategorie "'.$kateg.'"';
       endif;
     $stc=count($term).' Termine';
     $str='
<div><span class="kal_form_msg">'.$sta.' &nbsp; ('.$stb.'): &nbsp; '.$stc.'</span> &nbsp; '.
        '<small>(Terminliste nur im Frontend)</small></div>';
     endif;
   return $str;
   }
}
?>
