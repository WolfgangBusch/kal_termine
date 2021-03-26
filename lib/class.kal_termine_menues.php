<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2021
*/
define ('KAL_MONAT'     , 'MONAT');
define ('KAL_KW'        , 'KW');
define ('KAL_JAHR'      , 'JAHR');
define ('KAL_DATUM'     , 'DATUM');
define ('KAL_KATEGORIE' , 'KATEGORIE');
define ('KAL_SUCHEN'    , 'SUCHEN');
define ('KAL_VORHER'    , 'VORHER');
define ('KAL_MENUE'     , 'MENUE');
define ('KAL_PID'       , 'PID');
#
class kal_termine_menues {
#
#----------------------------------------- Inhaltsuebersicht
#         kal_define_menues()
#         kal_link($par,$mennr,$linktext,$modus)
#         kal_blaettern_basis()
#         kal_blaettern_suche()
#         kal_blaettern_jahre($mon,$jahr,$kont)
#         kal_blaettern_monate($mon,$jahr,$kont)
#         kal_blaettern_wochen($kw,$jahr,$kont)
#         kal_blaettern_tage($datum,$kont)
#         kal_terminblatt($termin,$datum,$ruecklinks)
#   Monatsmenue
#         kal_monatsmenue($katid,$mon,$jahr,$modus)
#   Monats-/Wochen-/Tagesblatt
#         kal_monatsblatt($katid,$mon,$jahr)
#         kal_wochenblatt($katid,$kw,$jahr)
#         kal_tagesblatt($katid,$datum)
#         kal_mowotablatt($katid,$mon,$kw,$jahr,$datum)
#         kal_stundenleiste()
#         kal_terminfeld($termin,$class)
#         kal_termin_poslen($termin)
#         kal_eval_start_ende($termin)
#         kal_termin_titel($termin)
#   Termin-Filtermenue
#         kal_such($katid,$jahr,$kid,$suchen,$vorher)
#   Menuewechsel
#         kal_menue($katid,$mennr)
#
public static function kal_define_menues() {
   #   Rueckgabe der moeglichen Startmenues in der Reihenfolge:
   #                  [1]  Monatsmenue
   #                  [2]  Monatsblatt
   #                  [3]  Wochenblatt
   #                  [4]  Tagesblatt
   #                  [5]  Terminblatt
   #                  [6]  Filtermenue
   #                  [7]  Terminliste
   #   Jedes Startmenue ist ein nummeriertes Array mit diesen Elementen:
   #                  [1]  Bezeichnung des Menues
   #                  [2]  Erlaeuterungstext fuer das Menue
   #
   $tooltip='Alle wesentlichen christlichen Feiertage werden beim Überfahren mit der '.
      'Maus als tooltip angezeigt.';
   $halbgra='An Desktop-Displays werden die Termine innerhalb des Tages gemäß '.
      'ihrem Uhrzeitbereich horizontal angeordnet (halbgrafische Darstellung).';
   $mome=array('name'=>'Monatsmenü',
      'titel'=>'Kompakte Darstellung der Tage eines Monats. Tage, an denen Termine '.
      'eingetragen sind, werden durch Schraffur gekennzeichnet. '.$tooltip,
      'links'=>'Zugehörige Tages- und Wochenblätter, Vor- und Folgemonat, '.
      'Vor- und Folgejahr, Filtermenü');
   $mobl=array('name'=>'Monatsblatt', 'titel'=>'Zeilenweise Darstellung der Termine '.
      'eines Monats, jeweils tageweise zusammengefasst. '.$halbgra.' '.$tooltip,
      'links'=>'Zugehörige Terminblätter, Vor- und Folgemonat, Start-Monatsmenü, '.
      'Filtermenü');
   $wobl=array('name'=>'Wochenblatt', 'titel'=>'Zeilenweise Darstellung der Termine '.
      'einer Woche, jeweils tageweise zusammengefasst. '.$halbgra.' '.$tooltip,
      'links'=>'Zugehörige Terminblätter, Vor- und Folgewoche, Start-Monatsmenü, '.
      'Filtermenü');
   $tabl=array('name'=>'Tagesblatt', 'titel'=>'Zeilenweise Darstellung der Termine '.
      'eines Tages. '.$halbgra.' '.$tooltip,
      'links'=>'Zugehörige Terminblätter, Vor- und Folgetag, Start-Monatsmenü, '.
      'Filtermenü');
   $tebl=array('name'=>'Terminblatt', 'titel'=>'Tabellarische Darstellung der Daten '.
      'eines Termins.',
      'links'=>'zugehöriges Tagesblatt, Start-Monatsmenü');
   $fil=array('name'=>'Filtermenü', 'titel'=>'Liste der Termine eines '.
      'Kalenderjahres mit Filterfunktionen zur Verkürzung der Liste.',
      'links'=>'Anderes Kalenderjahr, Start-Monatsmenü');
   $tli=array('name'=>'Terminliste', 'titel'=>'Einfache Auflistung der Termine eines '.
      'Zeitabschnitts.',
      'links'=>'-');
   $menue=array(1=>$mome, 2=>$mobl, 3=>$wobl, 4=>$tabl, 5=>$tebl, 6=>$fil, 7=>$tli);
   return $menue;
   }
public static function kal_link($par,$mennr,$linktext,$modus) {
   #   Rueckgabe einer Referenz als Formular
   #   $par            Link-Parameter-String in der Form 'par1=PAR1&par2=PAR2&...',
   #                   werden als hidden Parameter weiter gegeben
   #   $mennr          Nummer des Menues, auf das die Referenz verweisen soll,
   #                   wird als hidden Parameter weiter gegeben
   #   $linktext       anzuzeigender Linktext
   #   $modus          <=0: es wird statt des Links nur der Linktext zurueck gegeben
   #                        <0: der Linktext wird mittels Stylesheet formatiert
   #                   >0:  es wird ein Link zurueck gegeben
   #                        =1: Linktext in Normalschrift
   #                        >1: Linktext fett und groesser (class="kal_boldbig")
   #
   if($modus==0) return $linktext;
   if($modus<0)  return '<span class="kal_transparent kal_basecol kal_boldbig">'.$linktext.'</span>';
   #
   $action='';
   if(rex::isBackend()) $action=' action="'.$_SERVER['REQUEST_URI'].'"';
   $str='    <form style="display:inline;" method="post" onsubmit=""'.$action.'>';
   $arr=explode('&',$par);
   for($i=0;$i<count($arr);$i=$i+1):
      $brr=explode('=',$arr[$i]);
      if(!empty($brr[1])) $str=$str.'
                <input type="hidden" name="'.$brr[0].'" value="'.$brr[1].'" />';
      endfor;
   $big='';
   if($modus>1) $big=' kal_boldbig';
   $str=$str.'
                <input type="hidden" name="'.KAL_MENUE.'" value="'.$mennr.'" />
                <input type="hidden" name="REX_INPUT_VALUE[1]" value="'.ACTION_SEARCH.'" />
                <button type="submit" class="kal_transparent kal_linkbutton'.$big.'">'.$linktext.'</button>
                </form>';
   return $str;
   }
public static function kal_blaettern_basis() {
   #   Zurueck blaettern auf das Basismenue (Monatsmenu mit heutigem Tag).
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   #
   $heute=kal_termine_kalender::kal_heute();
   $mon=substr($heute,3,2);
   $jahr=substr($heute,6);
   $linktext='&wedgeq;';
   #
   $title=' title="'.$monate[intval($mon)].' '.$jahr.'"';
   $lp=KAL_MONAT.'='.$mon.'&'.KAL_JAHR.'='.$jahr;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0) $men=$i;   // Monatsmenue
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_blaettern_suche() {
   #   Zurueck blaettern auf das Basis-Suchmenue (bezogen auf das aktuelle Jahr).
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_heute()
   #
   # --- kein Link auf das Suchmenue bei der Terminverwaltung
   if(rex::isBackend() and
     strpos($_SERVER['REQUEST_URI'],'page='.PACKAGE.'/example')<=0) return;
   #
   $lnkmod=2;
   $menues=self::kal_define_menues();
   #
   $heute=kal_termine_kalender::kal_heute();
   $jahr=substr($heute,6);
   $linktext='<span class="kal_rotate">&#9740;</span>';
   #
   $title=' title="Suche im Jahre '.$jahr.'"';
   $lp=KAL_JAHR.'='.$jahr;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'iltermenü')>0) $men=$i;  // Filtermenue
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_blaettern_jahre($mon,$jahr,$kont) {
   #   Das aktuelle Menue um ein Jahr weiterblaettern (Monatsmenue).
   #   $mon            Nummer des Monats
   #   $jahr           Jahreszahl
   #   $kont           =-1: Blaettern zum vorherigen Jahr (Monatsmenue: gleicher Monat)
   #                        Linktext: «
   #                   = 1: Blaettern zum folgenden  Jahr (Monatsmenue: gleicher Monat)
   #                        Linktext: »
   #                   POST-Parameter: KAL_MENUE=$men, KAL_MONAT=$mon, KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Monatsblatts des gewaehlten Monats)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_monate()
   #
   if(empty($jahr)) return;
   #
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   $monneu=$mon;
   #
   # --- vorheriges Jahr
   if($kont<=-1):
     $jahrneu=$jahr-1;
     $linktext='&laquo;';
     endif;
   #
   # --- folgendes Jahr
   if($kont>=1):
     $jahrneu=$jahr+1;
     $linktext='&raquo;';
     endif;
   #
   # --- weitere Linkparameter
   $title=' title="'.$monate[intval($monneu)].' '.$jahrneu.'"';
   $lp=KAL_MONAT.'='.$monneu.'&'.KAL_JAHR.'='.$jahrneu;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0) $men=$i;   // Monatsmenue
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_blaettern_monate($mon,$jahr,$kont) {
   #   Das aktuelle Menue um einen Monat weiterblaettern.
   #   $mon            Nummer des Monats
   #   $jahr           Jahreszahl
   #   $kont           =-1: Blaettern zum vorherigen Monat (im Monatsblatt)
   #                        Linktext: <
   #                   =-2: Blaettern zum vorherigen Monat (im Monatsmenue)
   #                        Linktext: <
   #                   = 0: Ueberschrift ueber das aktuelle Monatsblatt
   #                   = 1: Blaettern zum folgenden Monat  (im Monatsblatt)
   #                        Linktext: >
   #                   = 2: Blaettern zum folgenden Monat  (im Monatsmenue)
   #                        Linktext: >
   #                   = 3: Blaettern zum aktuellen Monatsblatt (im Monatsmenue)
   #                        Linktext: Monatsname $jahr
   #                   POST-Parameter: KAL_MENUE=$men, KAL_MONAT=$mon, KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Monatsblatts des gewaehlten Monats)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_monate()
   #
   if(empty($mon) or empty($jahr)) return;
   #
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   $jahrneu=$jahr;
   #
   # --- vorheriger Monat (Monatsblatt)
   if($kont==-1 or $kont==-2):
     $monneu=intval($mon)-1;
     if($monneu<=0):
       $monneu=$monneu+12;
       $jahrneu=$jahr-1;
       endif;
     $linktext='&lt;';
     endif;
   #
   # --- aktueller Monat Monatsmenue/Monatsblatt
   if($kont==0 or $kont==3):
     $monneu=$mon;
     $jahrneu=$jahr;
     $linktext=$monate[intval($monneu)].'&nbsp;'.$jahr;
     if($kont==0) $lnkmod=-1;
     endif;
   #
   # --- folgender Monat (Monatsblatt)
   if($kont==1 or $kont==2):
     $monneu=intval($mon)+1;
     if($monneu>12):
       $monneu=$monneu-12;
       $jahrneu=$jahr+1;
       endif;
     $linktext='&gt;';
     endif;
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1) $title=' title="'.$monate[intval($monneu)].' '.$jahrneu.'"';
   $lp=KAL_MONAT.'='.$monneu.'&'.KAL_JAHR.'='.$jahrneu;
   $men='';
   if(abs($kont)==2):
      for($i=1;$i<=count($menues);$i=$i+1)
         if(strpos($menues[$i]['name'],'natsmenü')>0)  $men=$i;   // Monatsmenue
      endif;
   if(abs($kont)==1 or $kont==3):
      for($i=1;$i<=count($menues);$i=$i+1)
         if(strpos($menues[$i]['name'],'natsblatt')>0) $men=$i;   // Monatsblatt
      endif;
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_blaettern_wochen($kw,$jahr,$kont) {
   #   Das aktuelle Wochenblatt um eine Woche weiterblaettern.
   #   $kw             Nummer der Kalenderwoche
   #   $jahr           Jahreszahl
   #   $kont           =-1: Blaettern zur vorherigen Woche (im Wochenblatt)
   #                        Linktext: <
   #                   = 0: Ueberschrift ueber das aktuelle Wochenblatt
   #                   = 1: Blaettern zur folgenden  Woche (im Wochenblatt)
   #                        Linktext: >
   #                   = 2: Blaettern zum aktuellen Wochenblatt (im Monatsmenue)
   #                   POST-Parameter: KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Wochenblatts des gewaehlten Woche)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_montag_kw($montag)
   #
   if(empty($kw) or empty($jahr)) return;
   #
   $lnkmod=2;
   $menues=self::kal_define_menues();
   $jahrneu=$jahr;
   $montag=kal_termine_kalender::kal_kw_montag($kw,$jahr);
   #
   # --- vorherige Woche (Zahl), ggf. berechnetes Jahr
   if($kont==-1):
     $kwneu=intval($kw)-1;
     $von=kal_termine_kalender::kal_datum_vor_nach($montag,-7);
     if($kwneu<=0):
       $kwneu=kal_termine_kalender::kal_montag_kw($von);
       $jahrneu=$jahrneu-1;
       endif;
     $linktext='&lt;';
     endif;
   #
   # --- aktuelle Woche
   if($kont==0 or $kont==2):
     $kwneu=$kw;
     $jahrneu=$jahr;
     if($kont==0):
       $linktext='Woche '.$kwneu.' ('.$jahrneu.')';
       $lnkmod=-1;
       else:
       $linktext=$kwneu.'&nbsp;';
       $lnkmod=1;
       endif;
     $von=$montag;
     endif;
   #
   # --- folgende Woche (Zahl), ggf. berechnetes Jahr
   if($kont==1):
     $kwneu=intval($kw)+1;
     $von=kal_termine_kalender::kal_datum_vor_nach($montag,7);
     if($kwneu>52):
       $tag=substr($von,0,2);
       if($tag<14 and $tag>7) return '**';  // 53. Woche existiert gar nicht
       $kwneu=kal_termine_kalender::kal_montag_kw($von);
       if($kwneu<$kw) $jahrneu=$jahrneu+1;
       endif;
     $linktext='&gt;';
     endif;
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,6);
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1)
     $title=' title="Woche '.$kwneu.' ('.$jahrneu.'), '.substr($von,0,6).'-'.substr($bis,0,6).'"';
   $lp=KAL_KW.'='.$kwneu.'&'.KAL_JAHR.'='.$jahrneu;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'chenblatt')>0) $men=$i;   // Wochenblatt
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_blaettern_tage($datum,$kont) {
   #   Das aktuelle Tagesblatt um einen Tag weiterblaettern.
   #   $datum          Datum des aktuellen Tagesblatts
   #   $kont           =-1: Blaettern zum vorherigen Tag (im Tagesblatt)
   #                        Linktext: <
   #                   = 0: Ueberschrift des aktuellen Tages (im Tagesblatt)
   #                   = 1: Blaettern zum folgenden Tag (im Tagesblatt)
   #                        Linktext: >
   #                   = 2: Blaettern zum aktuellen Tag (im Monatsmenue)
   #                        Linktext: $datum
   #                   = 3: Blaettern zum aktuellen Tag (im Terminblatt)
   #                        Linktext: <
   #                   POST-Parameter: KAL_MENUE=$men, KAL_Datum=$datum
   #                   ($men = Menuenummer des Tagesblatts des gewaehlten Tages)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #
   if(empty($datum)) return;
   #
   $lnkmod=2;
   $menues=self::kal_define_menues();
   $datumneu=$datum;
   #
   # --- vorheriger Tag
   if($kont==intval(-1)):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,-1);
     $linktext='&lt;';
     endif;
   #
   # --- aktueller Tag
   if($kont==0):   // Tagesblatt
     $linktext=$datumneu;
     $lnkmod=-1;
     endif;
   if($kont==2):   // Monatsmenue
     $linktext=intval(substr($datum,0,2));
     $lnkmod=1;
     endif;
   if($kont==3):   // Terminblatt
     $linktext='&lt;';
     $lnkmod=2;
     endif;
   #
   # --- folgender Tag
   if($kont==1):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,1);
     $linktext='&gt;';
     endif;
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)==1 or $kont==3) $title=' title="'.$datumneu.'"';
   if($kont==2):
     $feiertag=kal_termine_kalender::kal_datum_feiertag($datumneu);
     if(empty($feiertag)):
       $title=' title="'.$datumneu.'"';
       else:
       $title=' title="'.$datumneu.' ('.$feiertag.')"';
       endif;
     endif;     
   $lp=KAL_DATUM.'='.$datumneu;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'gesblatt')>0) $men=$i;   // Tagesblatt
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }

public static function kal_terminblatt($termin,$datum,$ruecklinks) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins.
   #   Die Ueberschrift enthaelt die Ueberschrift des Termins (Termin-Name) sowie
   #   in der Regel zusaetzlich je einen Ruecklink auf das Kalendermenue des
   #   Monats und auf das aktuelle Tagesblatt.
   #   $termin         assoziatives Array des Termins
   #   $datum          Datum, kann von $termin[COL_DATUM] abweichen, wenn es
   #                   sich um einen woechentlich wiederkehrenden Termin oder
   #                   um den Folgetermin eines mehrtägigen Termins handelt
   #   $ruecklinks     >0:  Ruecklinks werden angezeigt
   #                   <=0: Ruecklinks werden nicht angezeigt (nur im Falle des
   #                        Loeschens eines Termins
   #   benutzte functions:
   #      self::kal_blaettern_basis()
   #      self::kal_blaettern_tage($datum,$kont)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_tabelle::kal_select_termin_by_pid($pid)
   #      kal_termine_tabelle::kal_kategorie_name($katid)
   #
   # --- ggf. Ruecklinks
   $dat=$datum;
   if(empty($dat)) $dat=kal_termine_kalender::kal_heute();
   $arr=explode('.',$dat);
   $dat=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   if($ruecklinks<=0):
     $ueber=array('','',$dat);
     else:
     $datx=kal_termine_kalender::kal_standard_datum($dat);
     $mon=substr($datx,3,2);
     $monate=kal_termine_kalender::kal_monate();
     $monat=$monate[intval($mon)];
     $jahr=substr($datx,6);
     $ueber=array(self::kal_blaettern_basis(),self::kal_blaettern_tage($dat,3),$dat);
     endif;
   #
   # --- Ueberschrift-Zeile
   $seite='
<div class="'.CSS_TERMBLATT.'">
<table class="kal_table kal_box">
    <tr><td class="kopf">
            '.$ueber[0].'&nbsp;&nbsp;'.$ueber[1].'</td>
        <td class="kopf">';
   #
   # --- return, falls kein Termin angegeben ist
   if(count($termin)<=2):
     $seite=$seite.'
            ... kein Termin vorhanden/angegeben ...</td></tr>  
    <tr><th>Termin:</th>
        <td></td></tr>
</table>
</div>';
     return $seite;
     endif;
   #
   # --- Termindaten
   $name=$termin[COL_NAME];
   $datum=$termin[COL_DATUM];
   $pid=$termin[COL_PID];
   $dauer=$termin[COL_TAGE];
   $arr=explode('.',$datum);
   $strdat=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   if($dauer>1):
     $datend=kal_termine_kalender::kal_datum_vor_nach($datum,$dauer-1);
     else:
     $datend=$datum;
     endif;
   $arr=explode('.',$datend);
   $strdatend=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   $seite=$seite.'
            '.$name.'</td></tr>';
   #
   # --- Datum, Startzeit, Enddatum, Endzeit, woechentliche Wiederholungen, Folgetermine
   $wt=kal_termine_kalender::kal_wotag($datum);
   $wte=kal_termine_kalender::kal_wotag($datend);
   $str=$wt.', '.$strdat;
   if($datend==$datum):
     if(!empty($termin[COL_BEGINN])) $str=$str.', &nbsp; '.$termin[COL_BEGINN];
     if(!empty($termin[COL_ENDE])) $str=$str.' - '.$termin[COL_ENDE];
     if(!empty($str)) $str=$str.' Uhr';
     ;else:
     if(!empty($termin[COL_BEGINN]))
       $str=$str.', &nbsp; '.$termin[COL_BEGINN].' Uhr &nbsp;';
     $str=$str.' &nbsp; - &nbsp; '.$wte.', '.$strdatend;
     if(!empty($termin[COL_ENDE])) $str=$str.', &nbsp; '.$termin[COL_ENDE].' Uhr';
     endif;
   $warnung='';
   #     Wiederholungstermine
   if($termin[COL_WOCHEN]>0):
     $dat1=$ueber[2];
     $wta=kal_termine_kalender::kal_wotag($dat1);
     $warnung='<b>wöchentlich</b>, '.$termin[COL_WOCHEN].' Wochen, ab '.$strdat;
     $str=$wta.', '.$dat1;
     if(!empty($ueber[1])):
       $ter=kal_termine_tabelle::kal_select_termin_by_pid($pid);
       $dat=$ter[COL_DATUM];
       $arr=explode('.',$dat);
       $dat=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
       $str=$str.'<br/>
            ('.$warnung.')';
       endif;
     endif;
   #     Folgetermine
   if($dauer>1):
     $dat=$ueber[2];
     $wta=kal_termine_kalender::kal_wotag($dat);
     $warnung='<b>mehrtägig</b>, '.$dauer.' Tage, ab '.$strdat;
     $str=$wta.', '.$dat;
     if(!empty($ueber[1])) $str=$str.'<br/>
            ('.$warnung.')';
     endif;
   $seite=$seite.'
    <tr><th>Termin:</th>
        <td class="kal_col6">
            '.$str;
   #
   # --- eventuelle bis zu 4 Zusatzzeiten
   $z='';
   if(!empty($termin[COL_ZEIT2]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT2].'&nbsp;Uhr: &nbsp; '.$termin[COL_TEXT2];
   if(!empty($termin[COL_ZEIT3]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT3].'&nbsp;Uhr: &nbsp; '.$termin[COL_TEXT3];
   if(!empty($termin[COL_ZEIT4]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT4].'&nbsp;Uhr: &nbsp; '.$termin[COL_TEXT4];
   if(!empty($termin[COL_ZEIT5]))
     $z=$z.'<br/>
            '.$termin[COL_ZEIT5].'&nbsp;Uhr: &nbsp; '.$termin[COL_TEXT5];
   if(!empty($z)) $seite=$seite.$z;
   $seite=$seite.'</td></tr>';
   #
   # --- Ausrichter
   $ausrichter=$termin[COL_AUSRICHTER];
   if(!empty($ausrichter))
     $seite=$seite.'
    <tr valign="top">
        <th>Ausrichter:</th>
        <td class="kal_col6">
            '.$ausrichter.'</td></tr>';
   #
   # --- Veranstaltungsort
   $ort=$termin[COL_ORT];
   if(!empty($ort))
     $seite=$seite.'
    <tr valign="top">
        <th>Ort:</th>
        <td class="kal_col6">
            '.$ort.'</td></tr>';
   #
   # --- Link
   $link=$termin[COL_LINK];
   if(!empty($link)):
     $tg='_blank';
     if(!empty($_GET['page'])) $tg='_self';
     $seite=$seite.'
    <tr valign="top">
        <th>Link:</th>
        <td class="kal_col6">
            <a href="'.$link.'" target="'.$tg.'">'.substr($link,0,50).' . . .</td></tr>';
     endif;
   #
   # --- Hinweise
   $komm=$termin[COL_KOMM];
   if(!empty($komm))
     $seite=$seite.'
    <tr valign="top">
        <th>Hinweise:</th>
        <td class="kal_col6">
            '.$komm.'</td></tr>';
   #
   # --- Kategorie
   $katid=$termin[COL_KATID];
   $kategorie=kal_termine_tabelle::kal_kategorie_name($katid);
   $seite=$seite.'
    <tr valign="top">
        <th>Kategorie:</th>
        <td>'.$kategorie.'</td></tr>';
   #
   # --- Warnung wegen Wiederholungs-/Folgetermin im Falle der Loeschung
   if($ruecklinks<=0 and !empty($warnung))
     $seite=$seite.'
    <tr valign="top">
        <th><span class="kal_fail">Vorsicht:</span></th>
        <td><span class="kal_fail">'.$warnung.'</span></td></tr>';
   #
   $seite=$seite.'
</table>
</div>
';
   return $seite;
   }
#
#----------------------------------------- Monatsmenue
public static function kal_monatsmenue($katid,$mon,$jahr,$modus=1) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalendermenues fuer einen Monat
   #   eines Jahres. Falls der Monat oder das Jahr nicht angegeben sind, wird der
   #   aktuelle Monat angenommen. Die Tage, an denen Termine anstehen, werden durch
   #   Schraffur markiert. Die Ausgabe des Kalendermenues erfolgt im aktuellen
   #   Browserfenster.
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   $mon            Nummer des Monats eines Jahres
   #                   im Format 'mm' oder 'm'
   #                   falls intval($mon)<=0 oder >12 ist, werden $mon
   #                   und $jahr aus dem aktuellen Datum entnommen
   #   $jahr           Kalenderjahr im Format 'yyyy'
   #                   falls intval($jahr)<=0 ist, werden $mon und
   #                   $jahr aus dem aktuellen Datum entnommen
   #                   falls intval($jahr)<=99 ist, wird $jahr durch $jahr+2000 ersetzt
   #   $modus          >0: das Menue erhaelt Links auf alle zugehoerigen Tagesblaetter
   #                       und Wochenblaetter, auf das Monatsblatt sowie auf Vorjahr,
   #                       Vormonat, Folgemonat, Folgejahr;
   #                       Tage, an denen Termine anstehen, werden schraffiert
   #                   =0: das Menue enthaelt keine Links und keine Schraffuren und
   #                       es werden keine Termine ausgelesen
   #   benutzte functions:
   #      self::kal_blaettern_jahre($mon,$jahr,$kont)
   #      self::kal_blaettern_monate($mon,$jahr,kont)
   #      self::kal_blaettern_wochen($kw,$jahr,$kont)
   #      self::kal_blaettern_tage($datum,$kont)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_kalender::kal_wotag_nr($wt)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_montag_kw($datum)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #
   # --- heutiges Datum
   $strmon=$mon;
   $strjahr=$jahr;
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Ueberpruefung der Werte von $mon und $jahr
   $mmm=intval($strmon);
   $jjj=intval($strjahr);
   if($mmm<=0 or $mmm>12 or $jjj<=0):
     #     $mon und $jahr werden aus dem aktuellen Datum entnommen
     $strmon=substr($heute,3,2);
     $strjahr=substr($heute,6);
     else:
     #     Formatierung der Jahreszahl
     if(strlen($strmon)<2) $strmon='0'.$strmon;
     $strjahr=$jahr;
     if($jjj<100) $jjj=$jjj+2000;
     $strjahr=$jjj;
     endif;
   #
   # --- Monatsbezeichnung, Anzahl Tage im Monat
   $monate=kal_termine_kalender::kal_monate();
   $monat =$monate[intval($strmon)];
   $tage=kal_termine_kalender::kal_monatstage($strjahr);
   $anztage=$tage[intval($strmon)];
   #
   # --- Tage markieren (Schraffur)
   $daterm=array();
   for($i=1;$i<=$anztage;$i=$i+1) $daterm[$i]=0;
   #
   # --- Termine in diesem Monat auslesen
   $dat1='01.'.$strmon.'.'.$strjahr;
   $dat2=$anztage.'.'.$strmon.'.'.$strjahr;
   $termine=array();
   if($modus>0)
     $termine=kal_termine_tabelle::kal_get_termine($dat1,$dat2,$katid,1);
   #
   # --- Tage markieren, an denen ein Termin vorhanden ist (Schraffur)
   for($k=1;$k<=count($termine);$k=$k+1):
      $datum=$termine[$k][COL_DATUM];
      $i=intval(substr($datum,0,2));
      $daterm[$i]=1;
      endfor;
   #
   # --- Wochentag (Kuerzel) des 1. Tages im Monat: $wtfirst
   $wtfirst=kal_termine_kalender::kal_wotag('01.'.$strmon.'.'.$strjahr);
   #     Nummer des Wochentages des 1. Tages im Monat: $tagfirst
   $tagfirst=kal_termine_kalender::kal_wotag_nr($wtfirst);
   #
   # --- Kalenderzellen-Ueberschriften
   $wt=kal_termine_kalender::kal_wochentage();
   # --- Kalenderwoche (KW)
   $kopf='
    <tr><td class="kalenderwoche">KW&nbsp;</td>';
   # --- Wochentage (2-Zeichen-Kuerzel)
   for($i=1;$i<=count($wt);$i=$i+1)
      $kopf=$kopf.'
        <td class="wot">'.$wt[$i].'</td>';
   $kopf=$kopf.'</tr>';
   #
   # --- Kalenderzeilen
   $tag=1-intval($tagfirst);
   $zeilen='';
   for($i=1;$i<=6;$i=$i+1):
      $zeil='
    <tr>';
      #
      # --- Wochennummer
      if($tag>=0 or $strmon>=2):
        $datum=$tag+1;
        if(strlen($datum)<2) $datum='0'.$datum;
        $datum=$datum.'.'.$strmon.'.'.$strjahr;
        $kw=kal_termine_kalender::kal_montag_kw($datum);
        else:
        $datum=33-intval($tagfirst);
        $datum=$datum.'.12.'.intval($strjahr-1);
        $kw=kal_termine_kalender::kal_montag_kw($datum);
        endif;
      #
      # --- Link auf die Kalenderwoche
      $link=$kw.'&nbsp;';
      if($modus>0) $link=self::kal_blaettern_wochen($kw,$strjahr,2);
      $zeil=$zeil.'
        <td class="kalenderwoche">
            '.$link.'
            </td>';
      #
      # --- Kalenderzellen
      for($k=1;$k<=count($wt);$k=$k+1):
         $tag=intval($tag)+1;
         if($tag<1 or $tag>$anztage):
           #
           # --- Tag vor oder nach dem Monat (ohne Link)
           $jahrvn=$strjahr;
           if($tag<1):
             #     Tag vor dem Monat
             $monvn=intval($strmon)-1;
             if($monvn<1):
               $monvn=12;
               $jahrvn=intval($strjahr)-1;
               endif;
             if(strlen($monvn)<2) $monvn='0'.$monvn;
             $tagevn=kal_termine_kalender::kal_monatstage($jahrvn);
             $tagvn=$tagevn[intval($monvn)]+$tag;
             else:
             #     Tag nach dem Monat
             $monvn=intval($strmon)+1;
             if($monvn>12):
               $monvn=1;
               $jahrvn=intval($strjahr)+1;
               endif;
             if(strlen($monvn)<2) $monvn='0'.$monvn;
             $tagvn=intval($tag)-$anztage;
             endif;
           $datumvn=$tagvn;
           if(strlen($datumvn)<2) $datumvn='0'.$datumvn;
           $datumvn=$datumvn.'.'.$monvn.'.'.$jahrvn;
           $title='';
           if($modus>0) $title=' title="'.$datumvn.'"';
           $temp='
        <td class="rechts kal_col9"'.$title.'>'.$tagvn.'</td>';
           else:
           #
           # --- Tag im Monat (mit Link)
           $datum=$tag;
           $schraff='';
           if($daterm[$tag]>0 and $modus>0) $schraff='id="hatch"';
           if(strlen($datum)<2) $datum='0'.$datum;
           $datum=$datum.'.'.$strmon.'.'.$strjahr;
           #     Link auf den Tag
           if($k<=6 and empty(kal_termine_kalender::kal_datum_feiertag($datum))):
             $class='rechts kal_col6';
             else:
             $class='rechts kal_col5';
             endif;
           if($datum==$heute) $class='rechts kal_col8';
           $link=intval(substr($datum,0,2));
           if($modus>0) $link=self::kal_blaettern_tage($datum,2);
           $temp='
        <td '.$schraff.' class="'.$class.'">
            '.$link.'
            </td>';
           endif;
         $zeil=$zeil.$temp;
         endfor;
      $zeil=$zeil.'</tr>';
      $zeilen=$zeilen.$zeil;
      if($tag>=$anztage) break;
      endfor;
   #
   # --- Monats- und Jahresschalter
   if($modus>0):
     $ruecklink1='
            '.self::kal_blaettern_jahre($strmon,$strjahr,-1);
     $ruecklink2='
            '.self::kal_blaettern_monate($strmon,$strjahr,-2);
     $ueber='
            '.self::kal_blaettern_monate($strmon,$strjahr,3);
     $ruecklink3='
            '.self::kal_blaettern_monate($strmon,$strjahr,2);
     $ruecklink4='
            '.self::kal_blaettern_jahre($strmon,$strjahr,1);
     $ruecklink5='
            '.self::kal_blaettern_suche();
     $string='
    <tr><th colspan="8">';
     #     Vorjahr / Vormonat / Monatsblatt / Folgemonat / Folgejahr / Suche
     $string=$string.'
            <table class="kal_table kal_100pro"><tr>
                <td class="padl kal_basecol">'.$ruecklink1.'</td>
                <td class="padl kal_basecol">'.$ruecklink2.'</td>
                <td class="left"></td>
                <th class="width center kal_basecol">'.$ueber.'</th>
                <td class="right"></td>
                <td class="padr kal_basecol">'.$ruecklink3.'</td>        
                <td class="padr kal_basecol">'.$ruecklink4.'</td>
                <td class="padr kal_basecol">'.$ruecklink5.'</td>
            </tr></table>';
    #     nur die Ueberschrift ($modus=0)
     else:
     $string='
        <th colspan="8" class="kal_boldbig">'.$monat.'&nbsp;'.$strjahr;
     endif;
   $string=$string.'
        </th></tr>';
   #
   # --- Zusammenstellung Ausgabezeilen
   $str='
<div class="'.CSS_MONMENUE.'">
<table class="kal_table kal_box">'.
$string.$kopf.$zeilen.'
</table>
</div>';
   return $str;
   }
#
#----------------------------------------- Monats-/Wochen-/Tagesblatt
public static function kal_monatsblatt($katid,$mon,$jahr) {
   if(empty($mon) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $mon=substr($heute,3,2);
     $jahr=substr($heute,6);
     endif;
   return self::kal_mowotablatt($katid,$mon,'',$jahr,'');
   }
public static function kal_wochenblatt($katid,$kw,$jahr) {
   if(empty($kw) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     endif;
   return self::kal_mowotablatt($katid,'',$kw,$jahr,'');
   }
public static function kal_tagesblatt($katid,$datum) {
   if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
   return self::kal_mowotablatt($katid,'','','',$datum);
   }
public static function kal_mowotablatt($katid,$mon,$kw,$jahr,$datum) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalenderblatts fuer entweder
   #   - einen Kalendermonat (nicht leer: $mon, $jahr, leer: $kw, $datum) oder
   #   - eine Kalenderwoche  (nicht leer: $kw, $jahr,  leer: $mon, $datum ) oder
   #   - einen einzelnen Tag (nicht leer: $datum,      leer: $mon, $kw, $jahr)
   #   Falls alle 4 Datumsparameter leer sind, wird der heutige
   #   Tag als einzelner Tag angenommen
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/SPIEL_KATID: Termine aller Kategorien
   #   $mon            Nummer des Monats eines Jahres im Format 'mm' oder 'm'
   #   $kw             Nummer der Woche (<=53) im Format 'ww' oder 'w'
   #                   falls die 53. Kalenderwoche nicht existiert, wird die
   #                   erste Kalenderwoche des Folgejahres angenommen
   #   $jahr           Kalenderjahr im Format 'yyyy' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   $datum          Datum im Format 'tt.mm.yyyy'' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   benutzte functions:
   #      self::kal_terminfeld($termin,$class)
   #      self::kal_stundenleiste()
   #      kal_termine_config::kal_define_stundenleiste()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #
   # --- mehrfach verwendete Daten
   $heute=kal_termine_kalender::kal_heute();
   $strmon =$mon;
   $strkw  =$kw;
   $strjahr=$jahr;
   $stdatum=$datum;
   if(!empty($stdatum)) $stdatum=kal_termine_kalender::kal_standard_datum($stdatum);
   #
   # --- falls ein Einzeltermin gemeint ist, werden Monat und Jahr ermittelt
   if(empty($mon) and empty($kw)):
     $strmon =substr($stdatum,3,2);
     $strjahr=substr($stdatum,6);
     endif;
   #
   # --- Formatierungen
   if(intval($strmon)>0 and strlen($strmon)==1) $strmon='0'.$strmon;
   if(strlen($strjahr)==2) $strjahr='20'.$strjahr;
   $strjahr=substr($strjahr,0,4);
   #
   # --- Definition des Tageszeilen-Formats
   $daten=kal_termine_config::kal_define_stundenleiste();
   $colspan=$daten[2]-$daten[1];  // Anzahl der Tabellen-(Stunden-)Spalten
   #
   # --- Datums-Array $dat[] und Auslesen der Termine  (Monat/Woche/Datum)
   $dat=array();
   #     Kalendermonat
   if(!empty($mon) and !empty($jahr)):
     $mtage=kal_termine_kalender::kal_monatstage($strjahr);
     $end=$mtage[intval($strmon)];
     for($i=1;$i<=$end;$i=$i+1):
        $tag=$i;
        if(strlen($tag)<2) $tag='0'.$tag;
        $dat[$i]=$tag.'.'.$strmon.'.'.$strjahr;
        endfor;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[$end],$katid,1);
     endif;
   #     Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     #     Montag (Starttag) der Woche als Datum
     $dat[1]=kal_termine_kalender::kal_kw_montag($kw,$strjahr);
     #     restliche Tage der Woche als Datum
     for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[7],$katid,1);
     endif;
   #     Einzeldatum
   if(!empty($datum)):
     $dat[1]=$stdatum;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[1],$katid,1);
     endif;
   #
   # --- Ruecklinks und Ueberschriften
   #     Ruecklink auf den Startmonat
   $ruecklink1='
            '.self::kal_blaettern_basis();
   #     Monatsblatt, Ruecklinks und Ueberschrift
   if(!empty($mon) and !empty($jahr)):
     $ruecklink2='
            '.self::kal_blaettern_monate($strmon,$jahr,-1);
     $ruecklink3='
            '.self::kal_blaettern_monate($strmon,$jahr, 1);
     $ueber=self::kal_blaettern_monate($strmon,$jahr,0);
     endif;
   #     Wochenblatt, Ruecklinks und Ueberschrift
   if(!empty($kw) and !empty($jahr)):
     $ruecklink2='
            '.self::kal_blaettern_wochen($kw,$jahr,-1);
     $ruecklink3='
            '.self::kal_blaettern_wochen($kw,$jahr, 1);
     $ueber='
            '.self::kal_blaettern_wochen($kw,$jahr,0);
     endif;
   #     Tagesblatt, Ruecklinks und Ueberschrift
   if(!empty($datum)):
     $ruecklink2='
            '.self::kal_blaettern_tage($stdatum,-1);
     $ruecklink3='
            '.self::kal_blaettern_tage($stdatum, 1);
     $ueber=self::kal_blaettern_tage($stdatum,0);
     endif;
   #     Ruecklink auf das Suchmenue
   $ruecklink4='
            '.self::kal_blaettern_suche();
   #
   # --- Zuordnen der Termine in tagesbezogene Arrays:
   #     $termin[$i][$k] ($k=1, 2, ...) Array der Termine pro Tag $i
   $termin=array();
   for($i=1;$i<=count($dat);$i=$i+1):
      $m=0;
      $termin[$i][1][COL_DATUM]=$dat[$i];
      for($k=1;$k<=count($term);$k=$k+1):
         if($term[$k][COL_DATUM]==$dat[$i]):
           $m=$m+1;
           $termin[$i][$m]=$term[$k];
           endif;
         endfor;
      endfor;
   #
   # --- Zusammenstellen der Tageszeilen
   #     $content[$i][$k] ($k=1, 2, ...) Array der Zeileninhalte (Termine) pro Tag $i
   $content=array();
   for($i=1;$i<=count($dat);$i=$i+1)
      for($k=1;$k<=count($termin[$i]);$k=$k+1)
         $content[$i][$k]=self::kal_terminfeld($termin[$i][$k],'streifen'.$i.$k);
   #
   # --- Ausgabe Ueberschriftszeile, Stundenleiste
   $colspan1=$colspan+1;
   $string='
<div class="'.CSS_MWTBLATT.'">
<table class="kal_table kal_box kal_100pro">
    <tr><th colspan="'.$colspan1.'">
            <table class="kal_table kal_100pro"><tr>
                <td class="pad0">'.$ruecklink1.'</td>
                <td class="pad0">'.$ruecklink2.'</td>
                <td class="left"></td>
                <td class="kal_100pro center">'.$ueber.'</td>
                <td class="right"></td>
                <td class="pad0">'.$ruecklink3.'</td>        
                <td class="pad0">'.$ruecklink4.'</td>
            </tr></table>
            </th></tr>
'.self::kal_stundenleiste();
   #
   # --- Ausgabe Tageszeilen
   $wtage=kal_termine_kalender::kal_wochentage();
   if(!empty($mon)) $mwoch=0;
   for($i=1;$i<=count($dat);$i=$i+1):
      $wt=kal_termine_kalender::kal_wotag($dat[$i]);
      $feiertag=kal_termine_kalender::kal_datum_feiertag($dat[$i]);
      $shdat=substr($dat[$i],0,6);
      if(substr($shdat,0,1)=='0') $shdat=substr($shdat,1);
      $pos=strpos($shdat,'.')+1;
      if(substr($shdat,$pos,1)=='0') $shdat=substr($shdat,0,$pos).substr($shdat,$pos+1);
      $cont='';
      for($k=1;$k<=count($termin[$i]);$k=$k+1):
         if($k>=2) $cont=$cont.'
            <hr/>';
         $cont=$cont.$content[$i][$k];
         endfor;
      $title='title="'.$shdat.'"';
      if(!empty($feiertag) or $wt=='So'):
        if(!empty($feiertag)) $title='title="'.$feiertag.', '.$dat[$i].'"';
        $class='tag kal_col5';
        else:
        $class='tag kal_col6';
        endif;
      if($dat[$i]==$heute) $class='tag kal_col8';
      $string=$string.'
    <tr><td class="pad1" '.$title.'>'.$wt.', '.$shdat.'</td>
        <td colspan="'.$colspan.'" class="'.$class.'">'.$cont.'</td></tr>';
      #     im Monatsmenue nach jedem Sonntag eine Stundenleiste einfuegen
      if(!empty($mon)):
        $mwoch=$mwoch+1;
        if($wt=='So' and $i<count($dat)):
          $mwoch=0;
          $string=$string.self::kal_stundenleiste();
          endif;
        endif;
      endfor;
   $string=$string.'
</table>
</div>';
   return $string;
   }
public static function kal_stundenleiste() {
   #   Rueckgabe einer Stundenleiste in Form des Codes zweier Zeilen einer
   #   HTML-Tabelle in der folgenden Form:
   #          9:00       11:00       13:00       15:00
   #     |     |     |     |     |     |     |     |     |
   #   1. Zeile: Uhrzeiten im 2-Stunden-Abstand
   #   2. Zeile: Striche im 1-Stunden-Abstand (CSS-Stil: border-left)
   #   Jede Uhrzeit und jeder Strich ist Inhalt bzw. Rand einer einzelnen Zelle.
   #   Beide Zeilen enthalten eine zusaetzliche leere erste Zelle.
   #   benutzte functions:
   #      kal_termine_config::kal_define_stundenleiste()
   #
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   $sizeuhr=$enduhr-$stauhr;
   #
   # --- Uhrzeiten-Leiste
   $stunden='
    <tr class="vis_leiste">
        <td></td>';
   for($k=$stauhr+1;$k<$enduhr;$k=$k+2)
      $stunden=$stunden.'
        <td colspan="2" class="width2 center">'.$k.':00</td>';
   if(2*intval($sizeuhr/2)<$sizeuhr)
     $stunden=$stunden.'
        <td class="width1 right">'.$enduhr.':</td>';
   $stunden=$stunden.'</tr>';
   #
   # --- Stundenstrich-Leiste
   $stdlineal='
    <tr class="vis_leiste">
        <td></td>';
   for($k=$stauhr;$k<$enduhr;$k=$k+1)
      $stdlineal=$stdlineal.'
        <td class="width1 lineal">&nbsp;</td>';
   $stdlineal=$stdlineal.'</tr>';
   #
   return $stunden.$stdlineal;
   }
public static function kal_terminfeld($termin,$class) {
   #   Rueckgabe des vollstaendigen div-Containers eines Termins im
   #   Tages-/Wochen-/Monatsblatt.
   #   $termin         Daten eines Termins (assoziatives Array)
   #   $class          temporaer erzeugte CSS-Klasse
   #   benutzte functions:
   #      self::kal_termin_poslen($termin)
   #      self::kal_termin_titel($termin)
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      self::kal_define_menues()
   #      kal_termine_formulare::kal_uhrzeit_string($termin)
   #
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'erminblatt')>0) $men=$i;   // Terminblatt
   $proz=self::kal_termin_poslen($termin);
   $cont='';
   if($proz['vor']>0 or $proz['dauer']>0):
     # --- div-Container (Streifen) mit zugemessener Position/Laenge und Termin-Link
     $title=self::kal_termin_titel($termin);
     $zeiten=kal_termine_formulare::kal_uhrzeit_string($termin);
     if(!empty($zeiten)):
       $pos=strpos($zeiten,' &nbsp; ');
       $zeiten='<div class="zeitenzeile">'.substr($zeiten,0,$pos).'</div>';
       endif;
     $linktext=$zeiten.$termin[COL_NAME];
     $param=KAL_DATUM.'='.$termin[COL_DATUM].'&'.KAL_PID.'='.$termin[COL_PID];
     $cont='
            <style>
               @media screen and (min-width:35em) {
                 .'.$class.' { margin-left:'.$proz['vor'].'%; width:'.$proz['dauer'].'%; }
                 }
            </style>';
     $cont=$cont.'
            <div class="termin '.$class.'" title="'.$title.'">
            '.self::kal_link($param,$men,$linktext,1).'
            </div>';
     else:
     $cont=$cont.'
            <div>&nbsp;</div>';
     endif;
   return $cont;
   }
public static function kal_termin_poslen($termin) {
   #   Rueckgabe von Massangaben, die eine halbgrafische Darstellung von Lage und Dauer
   #   eines vorgegebenen Termins innerhalb eines Tages ermoeglichen. Bestimmt werden
   #   der Zeitraum vor Beginn des Termins sowie dessen Dauer. Die Daten sind jeweils
   #   prozentuale Anteile der Gesamtlaenge einer Tabellenzelle, in die die Darstellung
   #   eingebettet ist. Die Gesamtlaenge selbst ist relativ (100%).
   #   $termin         Daten eines Termins (Array)
   #   Rueckgabe in Form eines assoziativen Arrays:
   #      $proz['vor']     % der Gesamtlaenge VOR BEGINN des Termins
   #      $proz['dauer']   % der Gesamtlaenge DAUER des Termins
   #      $proz['vor'] = $proz['dauer'] = 0  bei leerem Termin
   #   benutzte functions:
   #      self::kal_eval_start_ende($termin)
   #      kal_termine_config::kal_define_stundenleiste()
   #
   if(empty($termin[COL_NAME])) return array('vor'=>0, 'dauer'=>0);
   #
   # --- Bemassung der Stundenleiste
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $stunden=$daten[2]-$stauhr;
   $eps=0.4;
   $mue=0.5*$eps;
   #
   # --- ggf Start-/Enduhrzeit sinnvoll ergaenzen
   $sez=self::kal_eval_start_ende($termin);
   $beginn=$sez[COL_BEGINN];
   $ende  =$sez[COL_ENDE];
   #
   # --- Laenge vor dem Termin in Prozent der Gesamtlaenge
   $nziff=10;   // mit so vielen Ziffern wird gerechnet
   $arr=explode(':',$beginn);
   $voruhr=substr(strval($arr[0]+$arr[1]/60),0,$nziff);
   $vor=$voruhr-$stauhr;
   $prozvor=substr(strval($vor*100/$stunden),0,$nziff);
   if($prozvor<$eps) $prozvor=$eps;  // kleiner Abstand vorne
   #
   # --- Laenge des Termins in Prozent der Gesamtlaenge
   $arr=explode(':',$ende);
   $nachuhr=substr(strval($arr[0]+$arr[1]/60),0,$nziff);
   $dau=$nachuhr-$voruhr;
   $prozdau=substr(strval($dau*100/$stunden),0,$nziff);
   $summe=$prozvor+$prozdau;
   $hps=100-$eps;
   if($summe>$hps) $prozdau=$hps-$prozvor-0.5*$eps;  // kleiner Abstand hinten
   #
   # --- Dezimalkomma ggf. durch Dezimalpunkt ersetzen
   $prozvor=str_replace(',','.',$prozvor);
   $prozdau=str_replace(',','.',$prozdau);
   #
   return array('vor'=>$prozvor, 'dauer'=>$prozdau);
   }
public static function kal_eval_start_ende($termin) {
   #   Rueckgabe der Startuhrzeit und der Enduhrzeit eines Termins. Falls
   #   Start-/Enduhrzeit nicht definiert sind, werden sie sinnvoll ergaenzt.
   #   Rueckgabe als assoziatives Arrays (Start-/Enduhrzeit = [COL_BEGINN]/[COL_ENDE])
   #   - Falls der Terminbeginn leer ist:
   #                   der Beginn wird aus COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 abgeleitet
   #   - Falls der Terminbeginn und COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 leer sind:
   #                   es wird 'ganztaegig' angenommen und Anfang/Ende als
   #                   Startuhrzeit/Enduhrzeit berechnet
   #   - Falls das Terminende leer ist:
   #                   das Ende wird aus COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 abgeleitet
   #   $termin         Daten eines Termins (Array)
   #   benutzte functions:
   #      kal_termine_config::kal_define_stundenleiste()
   #
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   #
   # --- Startzeit bei fehlendem Beginn eines Termins
   #     falls eines von COL_ZEIT2,...,COL_ZEIT5 nicht leer ist, ersetze Beginn
   #     durch das kleinste der nicht-leeren COL_ZEIT2,...,COL_ZEIT5 ($minz);
   #     falls auch $minz leer ist, ersetze Startzeit durch Startuhrzeit
   $beginn=$termin[COL_BEGINN];
   $zeitmax=max($termin[COL_ZEIT2],$termin[COL_ZEIT3],$termin[COL_ZEIT4],$termin[COL_ZEIT5]);
   if(empty($beginn) and !empty($zeitmax)):
     $begm=$zeitmax;
     if(!empty($termin[COL_ZEIT2])) $begm=$termin[COL_ZEIT2];
     if(!empty($termin[COL_ZEIT3]) and $termin[COL_ZEIT3]<$begm) $begm=$termin[COL_ZEIT3];
     if(!empty($termin[COL_ZEIT4]) and $termin[COL_ZEIT4]<$begm) $begm=$termin[COL_ZEIT4];
     if(!empty($termin[COL_ZEIT5]) and $termin[COL_ZEIT5]<$begm) $begm=$termin[COL_ZEIT5];
     $beginn=$begm;
     endif;
   if(empty($beginn)) $beginn=$stauhr;
   $arr=explode(':',$beginn);
   if(empty($arr[1])) $arr[1]='00';
   $beginn=intval($arr[0]).':'.$arr[1];
   #
   # --- Endzeit bei fehlendem Ende eines Termins
   #     falls $maxz=max(COL_ZEIT2,COL_ZEIT3,COL_ZEIT4,COL_ZEIT5) nicht leer ist,
   #     ersetze Endzeit durch $maxz+eps (eps = erste Zeitdifferenz zwischen
   #     COL_ZEIT2 und COL_ZEIT3 oder, falls diese 0 ist, alternativ 30 Min.);
   #     falls auch $maxz leer ist, ersetze Endzeit durch Enduhrzeit
   $ende=$termin[COL_ENDE];
   if(empty($ende) and !empty($zeitmax)):
     if(!empty($termin[COL_ZEIT2]) and !empty($termin[COL_ZEIT3])):
       $arr=explode(':',$termin[COL_ZEIT2]);
       $brr=explode(':',$termin[COL_ZEIT3]);
       $crr=explode(':',$zeitmax);
       $std=$crr[0]+($brr[0]-$arr[0]);
       $min=$crr[1]+($brr[1]-$arr[1]);
       if($min<0) $std=$std-1;
       $ende=$std.':'.abs($min);
       endif;
     endif;
   if(empty($ende)) $ende=$enduhr;
   $arr=explode(':',$ende);
   if(empty($arr[1])) $arr[1]='00';
   $ende=intval($arr[0]).':'.$arr[1];
   #
   return array(COL_BEGINN=>$beginn, COL_ENDE=>$ende);
   }
public static function kal_termin_titel($termin) {
   #   Rueckgabe des div-Container-Titels eines Termins im Tages-/Wochen-/Monatsblatt.
   #   Er enthaelt die Termin-Parameter COL_BEGINN, COL_ENDE, COL_NAME, COL_ORT, COL_AUSRICHTER
   #   $termin         Daten eines Termins (assoziatives Array)
   #   benutzte functions:
   #      kal_termine_formulare::kal_uhrzeit_string($termin)
   #
   # --- Uhrzeiten-String
   if(empty($termin[COL_BEGINN]) and empty($termin[COL_ENDE])  and
      empty($termin[COL_ZEIT2])  and empty($termin[COL_ZEIT3]) and
      empty($termin[COL_ZEIT4])  and empty($termin[COL_ZEIT5])    ):
     $title='';
     else:
     $title=kal_termine_formulare::kal_uhrzeit_string($termin);
     endif;
   #
   # --- Name und Daten
   if(!empty($termin[COL_NAME]))
     $title=$title.$termin[COL_NAME];
   $ort=$termin[COL_ORT];
   if(!empty($ort)) $title=$title.' ('.$ort.')';
   $ausrichter=$termin[COL_AUSRICHTER];
   if(!empty($ausrichter)) $title=$title.', Ausrichter: '.$ausrichter;
   return $title;
   }
#
#----------------------------------------- Termin-Filtermenue
public static function kal_such($katid,$jahr,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Filterung der Termine eines
   #   Kalenderjahres (maximal 4 Jahre vor und 2 Jahre nach dem aktuellen Jahr,
   #   wie unten festgelegt (*)). Die Termine gehören zu einer vorgegebenen
   #   Kategorie (bzw. zu allen Kategorien). Die Filterbedingungen sind diese: 
   #   - Beschraenkung auf eine Kategorie, wenn alle Kategorien vorgegeben sind
   #   - Beschraenkung auf Termine, die einen Suchbegriff enthalten
   #   - Beschraenkung auf die Termine ab dem heutigen Datum
   #   Die Bedingungen werden ueber die Parameter $kid, $suchen, $vorher definiert
   #   und gelten zugleich.
   #   $katid          vorgegebene Kategorie-Id bzw.
   #                   =0/SPIEL_KATID (alle Kategorien, Datenbank-/Spieldaten)
   #   $jahr           Kalenderjahr, auf das sich die Suche bezieht
   #                   falls leer, wird das aktuelle Jahr angenommen
   #   $kid            Id der im Menue ausgewaehlten Kategorie bzw.
   #                   =$katid, falls $katid>0 bzw. $katid>SPIEL_KATID
   #   $suchen         im Menue eingegebener Suchbegriff (unabhaengig von
   #                   Gross-/Kleinschreibung), der Begriff wird in diesen
   #                   Termin-Parametern gesucht:
   #                      [COL_NAME], [COL_KOMM], [COL_AUSRICHTER], [COL_ORT]
   #                      [COL_TEXT2], [COL_TEXT3], [COL_TEXT4], [COL_TEXT5]
   #   $vorher         Wert der im Menue markierten Checkbox
   #                   ='on':  nur zukuenftige Termine ab dem heutigen Tag
   #                   ='':    auch abgelaufene Termine
   #   Das Auswahlformular liefert diese Parameter samt Werten:
   #      KAL_KATEGORIE  gemaess $kid
   #      KAL_SUCHEN     gemaess $suchen
   #      KAL_VORHER     gemaess $vorher
   #   benutzte functions:
   #      self::kal_define_menues()
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #      kal_termine_tabelle::kal_get_spielkategorien()
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #      kal_termine_formulare::kal_select_kategorie($name,$katid,$kid,$kont)
   #      kal_termine_formulare::kal_terminliste($termin)
   #
   # --- (*) Festlegung der betrachteten Kalenderjahre
   #     Anzahl der Jahre vor/nach dem aktuellen Jahr: $jvor/$jnach
   $jvor =4;
   $jnach=2;
   #
   # --- ggf. das aktuelle Jahre nehmen
   $heute=kal_termine_kalender::kal_heute();
   $jahrdef=substr($heute,6);
   if(empty($jahr)) $jahr=$jahrdef;
   #
   # --- Auslesen der Termine
   $von='01.01.'.$jahr;
   $bis='31.12.'.$jahr;
   $termin=kal_termine_tabelle::kal_get_termine($von,$bis,$katid,0);
   $nztermin=count($termin);
   #
   # --- Ausgaben
   $monate=kal_termine_kalender::kal_monate();
   $string='
<div class="'.CSS_SUCH.'">
<table class="kal_table kal_box">';
   #
   # --- Ruecklink
   $string=$string.'
    <tr><td class="td kopf">
            '.self::kal_blaettern_basis().'</td>';
   #
   # --- Ueberschrift
   $string=$string.'
        <td class="td kopf">
            Termine des Jahres
            <form method="post"></td></tr>';
   #
   # --- Auswahlmaske: Jahr
   $string=$string.'
    <tr><th class="th">Jahr:</th>
        <td class="td kal_col6">
            <select name="'.KAL_JAHR.'" class="kal_col5">';
   #     Jahresauswahl definieren
   $jvpn=$jvor+$jnach;
   for($i=0;$i<=$jvpn;$i=$i+1) $ja[$i+1]=$jahrdef-$jvor+$i;
   for($i=1;$i<=count($ja);$i=$i+1)
      if($ja[$i]==$jahr):
        $string=$string.'
                <option value="'.$ja[$i].'" class="kal_col5" selected="selected">'.$ja[$i].'</option>';
        else:
        $string=$string.'
                <option value="'.$ja[$i].'" class="kal_col6">'.$ja[$i].'</option>';
        endif;
   $string=$string.'
            </select></th></tr>';
   #
   # --- Auswahlmaske: Kategorien
   $selmen=kal_termine_formulare::kal_select_kategorie(KAL_KATEGORIE,$katid,$kid,0);
   $string=$string.'
    <tr><th class="th">Kategorie:</th>
        <td class="td kal_col6">'.$selmen;
   $string=$string.'</td></tr>';
   #
   # --- Auswahlmaske: Stichwort
   $string=$string.'
    <tr><th class="th">Stichwort:</th>
        <td class="td kal_col6">
            <input name="'.KAL_SUCHEN.'" type="text" value="'.$suchen.'" class="kal_col5" /></td></tr>';
   #
   # --- Auswahlmaske: nur aktuelle Termine beruecksichtigen
   if($vorher=='1' or strtolower($vorher)=='on'):
     $chk='checked="checked"';
     else:
     $chk='';
     endif;
   $string=$string.'
    <tr><th class="th">künftige:</th>
        <td class="td kal_col6">
            <input type="checkbox" name="'.KAL_VORHER.'" '.$chk.' />
            (abgelaufene Termine ausblenden)</td></tr>';
   #
   # --- Menue-Nr. als hidden-Parameter und Submit-Button
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'iltermenü')>0) $men=$i;  // Filtermenue
   $string=$string.'
    <tr><th class="th"></th>
        <td class="td kal_col6">
            <input type="hidden" name="'.KAL_MENUE.'" value="'.$men.'" />
            <button type="submit" class="kal_col4 kal_linkbutton right">
            <b>Filtern der Termine</b></button></td></tr>
</table>';
   #
   # --- Herausfiltern der Termine, die zur gewaehlten Kategorie gehoeren ($term)
   $term=array();
   if(($kid>0 and $kid<SPIEL_KATID) or $kid>SPIEL_KATID):
     $m=0;
     for($i=1;$i<=$nztermin;$i=$i+1):
        if($termin[$i][COL_KATID]==$kid):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     $termin=$term;
     $nztermin=count($termin);
     endif;
   #
   # --- alle Termine heraussuchen, die das Stichwort enthalten ($term)
   $term=array();
   if(!empty($suchen)):
     $m=0;
     for($i=1;$i<=$nztermin;$i=$i+1):
        if(!empty(stristr($termin[$i][COL_NAME],$suchen)) or
           !empty(stristr($termin[$i][COL_AUSRICHTER],$suchen)) or
           !empty(stristr($termin[$i][COL_ORT],$suchen)) or
           !empty(stristr($termin[$i][COL_KOMM],$suchen)) or
           !empty(stristr($termin[$i][COL_TEXT2],$suchen)) or
           !empty(stristr($termin[$i][COL_TEXT3],$suchen)) or
           !empty(stristr($termin[$i][COL_TEXT4],$suchen)) or
           !empty(stristr($termin[$i][COL_TEXT5],$suchen))):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     $termin=$term;
     $nztermin=count($termin);
     endif;
   #
   # --- alle Termine heraussuchen, die nach dem heutigen Tage liegen ($term)
   $term=array();
   if(!empty($vorher)):
     $heutesql=kal_termine_tabelle::kal_datum_standard_mysql(kal_termine_kalender::kal_heute());
     $m=0;
     for($i=1;$i<=$nztermin;$i=$i+1):
        if(kal_termine_tabelle::kal_datum_standard_mysql($termin[$i][COL_DATUM])>=$heutesql):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     $termin=$term;
     $nztermin=count($termin);
     endif;
   #
   # --- Ausgabe der gesuchten Termine
   $string=$string.'
<div class="left anzahl"><br/>';
   if($nztermin<=0):
     $string=$string.'+++++ keine entsprechenden Termine gefunden';
     else:
     $ter='Termine';
     if($nztermin==1) $ter='Termin';
     $string=$string.'<u>'.$nztermin.' '.$ter.' gefunden:</u><br/>
    <div class="liste">'.
        kal_termine_formulare::kal_terminliste($termin).'
    </div>';
     endif;
   $string=$string.'
</div>
</div>';
   return $string;
   }
#----------------------------------------- Menuewechsel
public static function kal_menue($katid,$mennr) {
   #   Rueckgabe des HTML-Codes zur Anzeige des gewaehlten Startmenues
   #   $katid          nur Termine mit dieser Kategorie-Id
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   $mennr          vorgegebene Nummer des Startmenues
   #                   falls leer/0: Wert des entspr. POST-Parameters
   #                   =1, falls auch der POST-Parameter leer ist
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_monatsmenue($katid,$mon,$jahr,$modus)
   #      self::kal_monatsblatt($katid,$monat,$jahr)
   #      self::kal_wochenblatt($katid,$kw,$jahr)
   #      self::kal_tagesblatt($katid,$datum)
   #      self::kal_monats_such_menue($katid,$mon,$jahr,$suchen,$vorher)
   #      self::kal_terminblatt($termin,$datum,$ruecklinks)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;    // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0) $menmob=$i;   // Monatsblatt
      if(strpos($menues[$i]['name'],'chenblatt')>0) $menwob=$i;   // Wochenblatt
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;   // Tagesblatt
      if(strpos($menues[$i]['name'],'iltermenü')>0) $menfil=$i;   // Filtermenue
      if(strpos($menues[$i]['name'],'erminblatt')>0) $menteb=$i;  // Terminblatt
      endfor;
   #
   # --- POST-Parameter auslesen
   $monat  ='';
   $kw     ='';
   $jahr   ='';
   $datum  ='';
   $kid    ='';
   $suchen ='';
   $vorher ='';
   $men    ='';
   $pid    ='';
   if(!empty($_POST[KAL_MONAT]))     $monat  =$_POST[KAL_MONAT];
   if(!empty($_POST[KAL_KW]))        $kw     =$_POST[KAL_KW];
   if(!empty($_POST[KAL_JAHR]))      $jahr   =$_POST[KAL_JAHR];
   if(!empty($_POST[KAL_DATUM]))     $datum  =$_POST[KAL_DATUM];
   if(!empty($_POST[KAL_KATEGORIE])) $kid    =$_POST[KAL_KATEGORIE];
   if(!empty($_POST[KAL_SUCHEN]))    $suchen =$_POST[KAL_SUCHEN];
   if(!empty($_POST[KAL_VORHER]))    $vorher =$_POST[KAL_VORHER];
   if(!empty($_POST[KAL_MENUE]))     $men    =$_POST[KAL_MENUE];
   if(!empty($_POST[KAL_PID]))       $pid    =$_POST[KAL_PID];
   #
   # --- ggf. Standard-Startmenue
   if(empty($men) and $mennr>0) $men=$mennr;
   if(empty($men)) $men=$menmom;
   #
   # --- Einschraenkung auf eine Kategorie ggf. beibehalten
   if($kid<=0 and $katid>0) $kid=$katid;
   #
   # --- Monatsmenue
   if($men==$menmom)
     return self::kal_monatsmenue($kid,$monat,$jahr,1);
   #
   # --- Monatsblatt
   if($men==$menmob)
     return self::kal_monatsblatt($kid,$monat,$jahr);
   #
   # --- Wochenblatt
   if($men==$menwob)
     return self::kal_wochenblatt($kid,$kw,$jahr);
   #
   # --- Tagesblatt
   if($men==$mentab)
     return self::kal_tagesblatt($kid,$datum);
   #
   # --- Terminblatt
   if($men==$menteb):
     $termin=array();
     $term=kal_termine_tabelle::kal_get_termine($datum,$datum,$kid,0);
     for($i=1;$i<=count($term);$i=$i+1)
        if($term[$i][COL_PID]==$pid) $termin=$term[$i];
     return self::kal_terminblatt($termin,$datum,1);
     endif;
   #
   # --- Filtermenue
   if($men==$menfil)
     return self::kal_such($katid,$jahr,$kid,$suchen,$vorher);
   }
}
?>