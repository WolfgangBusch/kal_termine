<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2021
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
#
public static function kal_manage_termine($value,$slice_id,$katid) {
   #   Rueckgabe des HTML-Codes zur Verwaltung der Termine einer bzw. aller
   #   Kategorien in der Datenbanktabelle (fuer einen entsprechenden Modul).
   #   Ueber verschiedene Formulare kann man einen neuen Termin eintragen oder
   #   einen vorhandenen Termin suchen, um ihn zu loeschen oder zu korrigieren.
   #   Die Aktionen werden gesteuert ueber die Redaxo-Variablen REX_VALUE[1],
   #   ..., REX_VALUE[20]. Am Ende werden alle Redaxo-Variablen geloescht.
   #   $value          nummeriertes Array der vorliegenden Redaxo-Variablen
   #                   $value[$i]=REX_VALUE[$i] ($i = 1, 2, ..., 20)
   #   $slice_id       Slice-Id des Artikel-blocks (REX_SLICE_ID)
   #   $katid          Id der Kategorie bzw. =0 fuer alle Kategorien
   #   benutzte functions:
   #      self::kal_find_termin($pidalt,$katid)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_formulare::kal_startauswahl()
   #      kal_termine_formulare::kal_eingeben($value,$action,$katid)
   #      kal_termine_formulare::kal_loeschen($pid,$action)
   #      kal_termine_formulare::kal_korrigieren($value,$pid,$action,$katid)
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
       echo kal_termine_formulare::kal_eingeben($val,$action,$katid);
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
       echo kal_termine_formulare::kal_korrigieren($val,$pid,$action,$katid);
       endif;
     endif;
   #
   # --- Loeschen aller Redaxo-Variablen
   if(!empty($slice_id)):
     $sql=rex_sql::factory();
     $upd='UPDATE rex_article_slice SET ';
     for($i=1;$i<=COL_ANZAHL;$i=$i+1) $upd=$upd.'value'.$i.'="", ';
     $upd=substr($upd,0,strlen($upd)-2);
     $upd=$upd.' WHERE id='.strval($slice_id);
     $sql->setQuery($upd);
     endif;
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
   #      kal_termine_formulare::kal_aktionsauswahl($pid)
   #      kal_termine_menues::kal_terminblatt($termin,$datum,$ruecklinks)
   #      kal_termine_menues::kal_menue($katid,$mennr)
   #
   # --- Ueberschrift
   $abbruch='Abbruch der Suche im Menü erst möglich nach Auswahl und Anzeige eines einzelnen Termins';
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
     # --- Ausgabe des gefundenen Termins mit Auswahl der Aktion 
     $termin=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     $datum='';
     if(!empty($_POST[KAL_DATUM]))$datum=$_POST[KAL_DATUM];
     return '<div class="'.CSS_EINFORM.'">
<table class="kal_table">
    <tr><th class="left"></th>
        <td><h4>'.$ueber.'</h4></td></tr>
    <tr><th class="left">gefundener Termin:</th>
        <td>'.kal_termine_menues::kal_terminblatt($termin,$datum,1).'        </td></tr>
</table><br/>
</div>'.
        kal_termine_formulare::kal_aktionsauswahl($pid);
     endif;
   }
public static function kal_terminmenue_in($men,$ab,$anztage,$kid,$katid) {
   #   Rueckgabe des HTML-Codes zur Konfigurierung einer Standard-Ausgabeliste
   #   von Terminen (fuer einen entsprechenden Modul).
   #   $men            ggf. vorher schon gewaehlte Menuenummer
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine
   #                   (falls leer, wird jeweils das heutige Datum eingesetzt).
   #   $anztage        ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen.
   #   $kid            Id der ggf. vorher schon gewaehlten Kategorie
   #                   (=0: es wurde 'alle Kategorien' gewaehlt)
   #   $katid          Id der zugelassenen Kategorie (=0: alle Kategorien zugelassen)
   #   Die Auswahl des Startdatums erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[1].
   #   Die Auswahl der Anzahl Tage erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[2].
   #   Die Auswahl der Kategorie erfolgt ueber die Redaxo-Variable REX_INPUT_VALUE[3].
   #   benutzte functions:
   #      self::kal_terminmenue_select($name,$mennr)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_formulare::kal_select_kategorie($name,$katid,$kid,$kont)
   #      kal_termine_menues::kal_define_menues()
   #
   $von=$ab;
   if(!empty($von)) $von=kal_termine_kalender::kal_standard_datum($von);
   $menues=kal_termine_menues::kal_define_menues();
   $ro='';
   $abtxt='(Default, nicht änderbar)';
   $zrtxt=$abtxt;
   if(empty($men)) $men=1;
   $block='<span class="kal_block_uebernehmen">&laquo; Block übernehmen &raquo;</span>';
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Schaltjahr?
   $jahr=substr($heute,7);
   $nztage=365;
   $jahr4=intval($jahr/4);
   if(4*$jahr4>=$jahr) $nztage=366;
   #
   # --- Zeitdaten ergaenzen
   #     Monatsmenue / Monatsblatt
   if(strpos($menues[$men]['name'],'natsmenü')>0 or
      strpos($menues[$men]['name'],'natsblatt')>0  ):
     #     Defaultdaten
     $von='01'.substr($heute,2);
     $jahr=substr($heute,6);
     $mon=intval(substr($heute,3,2));
     $defanz=kal_termine_kalender::kal_monatstage($jahr)[$mon];
     $anz=$defanz;
     $ro=' readonly';
     endif;
   #     Wochenblatt
   if(strpos($menues[$men]['name'],'chenblatt')>0):
     #     Defaultdaten
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     $von=kal_termine_kalender::kal_kw_montag($kw,$jahr);
     $defanz=7;
     $anz=$defanz;
     $ro=' readonly';
     endif;
   #     Tagesblatt
   if(strpos($menues[$men]['name'],'agesblatt')>0):
     #     Defaultdaten
     $von=$heute;
     $defanz=1;
     $anz=$defanz;
     $ro=' readonly';
     endif;
   #     Filtermenue
   if(strpos($menues[$men]['name'],'iltermenü')>0):
     #     Defaultdaten
     $von='1.1.'.substr($heute,6);
     $defanz=$nztage;
     $anz=$defanz;
     endif;
   #     Terminliste
   if(strpos($menues[$men]['name'],'minliste')>0):
     #     Defaultdaten
     $von='';
     if(!empty($ab)):
       $arr=explode('.',$ab);
       $von=intval($arr[0]).'.'.intval($arr[1]).'.'.intval($arr[2]);
       endif;
     $defanz=$nztage;
     $anz=$anztage;
     $abtxt='Eingabe im Format <tt>tt.mm.jjjj</tt> (Default: der heutige Tag)';
     $zrtxt='Eingabe der Anzahl Tage (inkl. Startdatum, Default: '.$defanz.')';
     endif;
   #
   # --- Ueberschrift
   $strtage='';
   $str='<h4 align="center">Kalendermenü zur Auswahl von Terminen</h4>
   <div class="'.CSS_EINFORM.'">
   <table class="kal_table">';
   #
   # --- Auswahl des Kalendermenues
   if($men<=0):
     $titel='';
     $links='';
     else:
     $titel=$menues[$men]['titel'];
     $links='Links zu anderen Menüs: &nbsp; '.$menues[$men]['links'];
     endif;
   $str=$str.'
    <tr><th class="left2">Kalendermenü:</th>
        <td class="left2">
'.self::kal_terminmenue_select('REX_INPUT_VALUE[1]',$men).'</td>
        <td class="pad"><b>'.$titel.'</b>
            Der Default-Zeitraum enthält jeweils den heutigen Tag.<br/>
        '.$links.'</td></tr>
    <tr><th colspan="2"></th>
        <td class="pad">Anpassung der Menü-Charakterisierung: &nbsp;'.$block.'</td></tr>';
   #
   # --- Datum, ab wann die Termine ausgegeben werden sollen
   if(!empty($von)):
     $arr=explode('.',$von);
     $von=intval($arr[0]).'.'.intval($arr[1]).'.'.intval($arr[2]);
     endif;
   $str=$str.'
    <tr><th class="left2">Termine ab:</th>
        <td><input type="text" name="REX_INPUT_VALUE[2]" value="'.$von.'" class="date right"'.$ro.' /></td>
        <td class="pad">'.$abtxt.'</td></tr>';
   #
   # --- Zeitraum in Anzahl Tage
   $str=$str.'
    <tr><th class="left2">Anzahl Tage:</th>
        <td><input type="text" name="REX_INPUT_VALUE[3]" value="'.$anz.'" class="int right"'.$ro.' /></td>
        <td class="pad">'.$zrtxt.'</td></tr>';
   #
   # --- Auswahl der Terminkategorie
   $str=$str.'
    <tr><th class="left2">Kategorie:</th>
        <td>'.kal_termine_formulare::kal_select_kategorie('REX_INPUT_VALUE[4]',$katid,$kid,0).'</td>
        <td class="pad">keine Angabe: Termine aller Kategorien</td></tr>
    <tr><td colspan="2" class="right">Speicherung der Daten:</td>
        <td class="pad">'.$block.'</td></tr>
</table>
</div>';
   #
   return $str;
   }
public static function kal_terminmenue_select($name,$mennr) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kalendermenues:
   #      Monatsmenue, Monatsblatt, Wochenblatt, Tagesblatt, Filtermenue
   #   $name           Name des select-Formulars
   #   $mennr          Nummer des vorher gewaehlten Menues (Default: 1)
   #   benutzte functions:
   #      kal_termine_menues::kal_define_menues()
   #
   $menues=kal_termine_menues::kal_define_menues();
   $men=$mennr;
   if($men<=1) $men=1;
   $string='<select name="'.$name.'">';
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')<=0 and
         strpos($menues[$i]['name'],'atsblatt')<=0 and
         strpos($menues[$i]['name'],'henblatt')<=0 and
         strpos($menues[$i]['name'],'gesblatt')<=0 and
         strpos($menues[$i]['name'],'ltermenü')<=0 and
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
     # --- Frontend (Terminliste || Filtermenue / alle anderen Menues)
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
<div><span class="kal_msg">'.$stb.' &nbsp; ('.$stc.'): &nbsp; '.$std.'</span> &nbsp; '.
        '<small>(Ausgaben nur im Frontend)</small></div>';
     endif;
   return $str;
   }
}
?>