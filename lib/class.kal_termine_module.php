<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version September 2021
*/
#
class kal_termine_module {
#
#----------------------------------------- Inhaltsuebersicht
#      kal_manage_termine()
#      kal_terminmenue_in($men,$ab,$anztage,$katid)
#          kal_terminmenue_select($name,$mennr)
#      kal_terminmenue_out($men,$ab,$anztage,$katid)
#
public static function kal_manage_termine() {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Verwaltung der Termine einer
   #   Auswahl an Kategorien in der Datenbanktabelle (fuer einen entsprechenden
   #   Modul). Die Auswahl haengt von den entsprechenden Permissions des aktuell
   #   im Backend eingeloggten Redakteurs ab.
   #   Ueber verschiedene Formulare kann man einen neuen Termin eintragen oder
   #   einen vorhandenen Termin suchen, um ihn zu loeschen, zu korrigieren
   #   oder zu kopieren.
   #   Die Aktionen werden gesteuert ueber die Werte der Parameter ACTION_NAME
   #   und PID_NAME.
   #   benutzte functions:
   #      kal_termine_formulare::kal_action($action,$pid)
   #      kal_termine_formulare::kal_eingeben()
   #      kal_termine_formulare::kal_korrigieren()
   #      kal_termine_formulare::kal_loeschen()
   #      kal_termine_formulare::kal_kopieren()
   #      kal_termine_menues::kal_define_menues()
   #      kal_termine_menues::kal_menue($selkid,$mennr)
   #
   #                     +--------------------+
   #                     | kal_manage_termine |
   #                     +----------+---------+
   #                                |
   #                         +------+-----+
   #                         |  kal_menue |
   #                         +------+-----+
   #                                |
   #          +-- Tagesblatt -------+-- Terminblatt --+
   #          |                                       |
   #          |                   +-------------------+------------------+
   #          |                   |                   |                  |
   #  +-------+-------+  +--------+--------+  +-------+-------+  +-------+--------+
   #  | $action=''    |  | $action=UPDATE  |  | $action=COPY  |  | $action=DELETE |
   #  | $pid=0        |  | $pid>0          |  | $pid>0        |  | $pid>0         |
   #  +---------------+  +-----------------+  +---------------+  +----------------+
   #  |  kal_eingeben |  | kal_korrigieren |  |  kal_kopieren |  |  kal_loeschen  |
   #  +---------------+  +-----------------+  +---------------+  +----------------+
   #  | $action=''    |  | $action=UPDATE  |  | $action=COPY  |  | $action=DELETE |
   #  |         START |  |         START   |  |         START |  |         START  |
   #  | $pid=$pidneu  |  | $pid=$pid       |  | $pid=$pidneu  |  | $pid=$pid      |
   #  +---------------+  +-----------------+  +---------------+  +----------------+
   #
   # --- Menue sowie Aktion und Termin-Id als POST-Parameter uebernehmen
   $men=0;
   if(!empty($_POST[KAL_MENUE])) $men=$_POST[KAL_MENUE];
   $action='';
   if(!empty($_POST[ACTION_NAME])) $action=$_POST[ACTION_NAME];
   $pid='';
   if(empty($action)):
     if(!empty($_POST[KAL_PID])) $pid=$_POST[KAL_PID];
     ;else:
     if(!empty($_POST[PID_NAME])) $pid=$_POST[PID_NAME];
     endif;
   #
   # --- Tagesblatt oder Terminblatt erreicht? ($tagesblatt/$terminblatt==TRUE)
   $menues=kal_termine_menues::kal_define_menues();
   $tagesblatt =FALSE;
   $terminblatt=FALSE;
   $datum='';
   if($men>0):
     if(strpos($menues[$men]['name'],'agesblatt')>0):
       $tagesblatt=TRUE;
       $datum=$_POST[KAL_DATUM];
       endif;
     if(strpos($menues[$men]['name'],'rminblatt')>0) $terminblatt=TRUE;
     endif;
   #
   # --- Monatsmenue-Nummer
   $menmom=0;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;    // Monatsmenue
   #
   # --- Ueberschrift + Erlaeuterung
   $str='
<div align="center">
<h3>Termine verwalten</h3>
<p align="left">Die <b>Eingabe</b> eines Termins erfolgt im zugehörigen <b>Tagesblatt</b>.<br/>
<b>Korrektur</b>, <b>Löschung</b> oder <b>Kopie</b> eines Termins sind im zugehörigen <b>Terminblatt</b> möglich.</p>
<div>&nbsp;</div>';
   #
   if(empty($action) or $action==ACTION_START or $action==ACTION_SELECT):
     #
     # --- Termin/Datum suchen (Startformular)
     $str=$str.kal_termine_menues::kal_menue(0,$menmom).'
<form method="post">';
     $act=$action;
     if($tagesblatt)  $act=ACTION_INSERT;
     if($terminblatt) $act= ACTION_SELECT;
     $str=$str.'<br/>'.kal_termine_formulare::kal_action($act,$pid);
     $str=$str.'</form>';
     #
     else:
     #
     # --- neuen Termin eingeben
     if($action==ACTION_INSERT and $pid<=0):
       $str=$str.kal_termine_formulare::kal_eingeben();
       endif;
     #
     # --- Termin korrigieren
     if($action==ACTION_UPDATE and $pid>0):
       $str=$str.kal_termine_formulare::kal_korrigieren();
       endif;
     #
     # --- Termin loeschen
     if($action==ACTION_DELETE and $pid>0):
       $str=$str.kal_termine_formulare::kal_loeschen();
       endif;
     #
     # --- Termin kopieren
     if($action==ACTION_COPY and $pid>0):
       $str=$str.kal_termine_formulare::kal_kopieren();
       endif;
     endif;
   return $str.'
</div>';
   }
public static function kal_terminmenue_in($men,$ab,$anztage,$kid) {
   #   Rueckgabe des HTML-Codes zur Konfigurierung einer Standard-Ausgabeliste
   #   von Terminen (fuer einen entsprechenden Modul).
   #   $men            ggf. vorher schon gewaehlte Menuenummer
   #                   (Auswahl ueber REX_VALUE[1])
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine (leer: heutiges Datum eingesetzt)
   #                   (Auswahl ueber REX_VALUE[2])
   #   $anztage        ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen
   #                   (Auswahl ueber REX_VALUE[3])
   #   $kid            ggf. vorher schon gewaehlte Terminkategorie-Id
   #                   =0: alle erlaubten Kategorien
   #                   (Auswahl ueber REX_VALUE[4])
   #   benutzte functions:
   #      self::kal_terminmenue_select($name,$mennr)
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_config::kal_allowed_terminkategorien()
   #      kal_termine_formulare::kal_select_kategorie($name,$kid,$katids,$all)
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
     $von=$heute;
     if(!empty($ab)):
       $arr=explode('.',$ab);
       $von=intval($arr[0]).'.'.intval($arr[1]).'.'.intval($arr[2]);
       endif;
     $defanz=$nztage;
     $anz=$anztage;
     if($anz<=0) $anz=$defanz;
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
     $links='Links zu anderen Menüs: <i>'.$menues[$men]['links'].'</i>';
     endif;
   $str=$str.'
    <tr><th class="left2">Kalendermenü:</th>
        <td class="left2">
'.self::kal_terminmenue_select('REX_INPUT_VALUE[1]',$men).'</td>
        <td class="pad"><b>'.$titel.'</b>
            Der Default-Zeitraum enthält jeweils den heutigen Tag.<br/>
        '.$links.'</td></tr>
    <tr><th colspan="2"></th>
        <td class="pad">Anpassung der Menü-Charakterisierung und des Zeitraums: &nbsp;'.$block.'</td></tr>';
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
   # --- Beschraenkung auf eine Kategorie
   $katids=kal_termine_config::kal_allowed_terminkategorien();
   $name='REX_INPUT_VALUE[4]';
   $str=$str.'
    <tr><th class="left2">Kategorien:</th>
        <td>'.kal_termine_formulare::kal_select_kategorie($name,$kid,$katids,TRUE).'</td>
        <td class="pad">Es können die Termine aller erlaubten Terminkategorien ausgewählt werden
            oder aber nur die Termine aus einer dieser Kategorien.</td></tr>';
   #
   $str=$str.'
</table>
</div>';
   #
   return $str;
   }
public static function kal_terminmenue_select($name,$mennr) {
   #   Rueckgabe des HTML-Codes fuer die Auswahl der moeglichen Kalendermenues:
   #   Monatsmenue, Monatsblatt, Wochenblatt, Tagesblatt, Filtermenue, Terminliste
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
public static function kal_terminmenue_out($mennr,$ab,$anztage,$kid) {
   #   Rueckgabe des HTML-Codes zur Ausgabe einer Standard-Terminliste
   #   (fuer einen entsprechenden Modul).
   #   $mennr          ggf. vorher schon gewaehlte Menuenummer
   #                   (Auswahl ueber REX_VALUE[1])
   #   $ab             ggf. vorher schon gewaehltes Datum fuer den ersten Tag der
   #                   auszugebenden Termine (leer: heutiges Datum eingesetzt)
   #                   (Auswahl ueber REX_VALUE[2])
   #   $anztage        ggf. vorher schon gewaehlte Anzahl von Tagen fuer den
   #                   Zeitraum, fuer die Termine ausgegeben werden sollen
   #                   (Auswahl ueber REX_VALUE[3])
   #   $kid            ggf. vorher schon gewaehlte Terminkategorie-Id
   #                   =0: alle erlaubten Kategorien
   #                   (Auswahl ueber REX_VALUE[4])
   #   benutzte functions:
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_config::kal_allowed_terminkategorien()
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katids,$katif)
   #      kal_termine_tabelle::kal_kategorie_name($katid)
   #      kal_termine_formulare::kal_terminliste($termin)
   #      kal_termine_menues::kal_define_menues()
   #      kal_termine_menues::kal_menue($selkid,$mennr)
   #
   $menues=kal_termine_menues::kal_define_menues();
   $menmom=0;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;    // Monatsmenue
   #
   # --- Parameterueberprefung
   $men=$mennr;
   if(empty($men)) $men=$menmom;
   $von=kal_termine_kalender::kal_standard_datum($ab);
   if(empty($von)) $von=kal_termine_kalender::kal_heute();
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,intval($anztage-1));
   #     alle oder genau eine Kategorie
   $katids=kal_termine_config::kal_allowed_terminkategorien();
   if($kid<=0):
     $kids=$katids;
     else:
     $kids=array(1=>$kid);
     endif;
   #
   # --- alle Termine auslesen 
   $term=kal_termine_tabelle::kal_get_termine($von,$bis,$kids,0);
   $nzt=count($term);
   #
   # --- Frontend
   if(!rex::isBackend()):
     if(strpos($menues[$men]['name'],'minliste')>0):
       #    Terminliste
       $str=kal_termine_formulare::kal_terminliste($term);
       else:
       #    Menues ausser Terminliste
       $str=kal_termine_menues::kal_menue($kid,$men);
       endif;
     endif;
   #
   # --- Backend: es wird nur eine Zusammenfassung angezeigt
   if(rex::isBackend()):
     $kateg='alle';
     if($kid>0) $kateg=kal_termine_tabelle::kal_kategorie_name($kid);
     $str='
<div><span class="kal_msg"><u>'.$menues[$men]['name'].':</u> &nbsp; '.$von.' - '.$bis.
        ' &nbsp; ('.$nzt.' Termine, &nbsp; Kategorien: '.$kateg.')</span>'.
        ' &nbsp; <small>(Ausgaben nur im Frontend)</small></div>';
     endif;
   return $str;
   }
}
?>