<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2024
 */
#
class kal_termine_menues {
#
#----------------------------------------- Inhaltsuebersicht
#   Basisfunktionen
#      kal_define_menues()
#      kal_link($par,$mennr,$linktext,$modus)
#      kal_blaettern_basis()
#      kal_blaettern_suche()
#      kal_blaettern_jahre($mon,$jahr,$kont)
#      kal_blaettern_monate($mon,$jahr,$kont)
#      kal_blaettern_wochen($kw,$jahr,$kont)
#      kal_blaettern_tage($datum,$kont)
#      kal_terminblatt($termin,$realdate,$ruecklinks)
#   Terminliste
#      kal_uhrzeit_string($termin)
#      kal_zusatzzeiten_string($termin)
#      kal_terminliste($termin,$datum_as_link)
#   Monatsmenue
#      kal_monatsmenue($kid,$katids,$mon,$jahr,$modus)
#   Monats-/Wochen-/Tagesblatt
#      kal_stundenleiste()
#      kal_eval_start_ende($termin)
#      kal_termin_poslen($termin)
#      kal_termin_titel($termin)
#      kal_terminfeld($termin,$class)
#      kal_mowotablatt($katids,$mon,$kw,$jahr,$datum)
#      kal_monatsblatt($katids,$mon,$jahr)
#      kal_wochenblatt($katids,$kw,$jahr)
#      kal_tagesblatt($katids,$datum)
#   Termin-Filtermenue
#      kal_such($katids,$jahr,$kid,$suchen,$vorher)
#   Menuewechsel
#      kal_menue($selkid,$mennr,$spieldaten)
#      kal_spielmenue()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Basisfunktionen
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
      'links'=>'Zugehörige Tagesblätter und Terminblätter, Vor- und Folgemonat, '.
      'Start-Monatsmenü, Filtermenü');
   $wobl=array('name'=>'Wochenblatt', 'titel'=>'Zeilenweise Darstellung der Termine '.
      'einer Woche, jeweils tageweise zusammengefasst. '.$halbgra.' '.$tooltip,
      'links'=>'Zugehörige Tagesblätter und Terminblätter, Vor- und Folgewoche, '.
      'Start-Monatsmenü, Filtermenü');
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
   #   $linktext       anzuzeigender Linktext, kann auch HTML-tags enthalten
   #   $modus          <=0: es wird statt des Links nur der Linktext zurueck gegeben
   #                        <0: der Linktext wird mittels Stylesheet formatiert
   #                   >0:  es wird ein Link zurueck gegeben
   #                        ==1: Linktext in Normalschrift
   #                        ==2: Linktext fett&groesser (class="kal_boldbig", fuer das Blaettern)
   #                        >=3: Linktext fett (class="kal_bold", fuer die Terminliste)
   #
   $ltxt=htmlspecialchars_decode($linktext);
   #
   if($modus==0) return $ltxt;
   if($modus<0)  return '<span class="kal_transparent kal_basecol kal_boldbig">'.$ltxt.'</span>';
   $addon=self::this_addon;
   #
   $str='    <form method="post">';
   $arr=explode('&',$par);
   for($i=0;$i<count($arr);$i=$i+1):
      $brr=explode('=',$arr[$i]);
      if(!empty($brr[1])) $str=$str.'
                <input type="hidden" name="'.$brr[0].'" value="'.$brr[1].'">';
      endfor;
   $big='';
   if($modus==2) $big=' kal_boldbig';
   if($modus>=3) $big=' kal_bold';
   $str=$str.'
                <input type="hidden" name="'.$addon::KAL_MENUE.'" value="'.$mennr.'">
                <button type="submit" class="kal_transparent kal_linkbutton'.$big.'">'.$ltxt.'</button>
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
   $addon=self::this_addon;
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   #
   $heute=kal_termine_kalender::kal_heute();
   $mon=substr($heute,3,2);
   $jahr=substr($heute,6);
   $linktext='<i class="fa fa-calendar"></i>';
   #
   $title=' title="'.$monate[intval($mon)].' '.$jahr.'"';
   $lp=$addon::KAL_MONAT.'='.$mon.'&'.$addon::KAL_JAHR.'='.$jahr;
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
   $addon=self::this_addon;
   $lnkmod=2;
   $menues=self::kal_define_menues();
   #
   $heute=kal_termine_kalender::kal_heute();
   $jahr=substr($heute,6);
   $linktext='<i class="fa fa-search"></i>';
   #
   $title=' title="Suche im Jahre '.$jahr.'"';
   $lp=$addon::KAL_JAHR.'='.$jahr;
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
   #                   POST-Parameter: $addon::KAL_MENUE=$men
   #                                   $addon::KAL_MONAT=$mon
   #                                   $addon::KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Monatsblatts des gewaehlten Monats)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_monate()
   #
   if(empty($jahr)) return;
   $addon=self::this_addon;
   #
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   $monneu=$mon;
   #
   # --- vorheriges Jahr
   if($kont<=-1):
     $jahrneu=$jahr-1;
     $linktext='<i class="fa fa-angle-double-left"></i>';
     endif;
   #
   # --- folgendes Jahr
   if($kont>=1):
     $jahrneu=$jahr+1;
     $linktext='<i class="fa fa-angle-double-right"></i>';
     endif;
   #
   # --- weitere Linkparameter
   $title=' title="'.$monate[intval($monneu)].' '.$jahrneu.'"';
   $lp=$addon::KAL_MONAT.'='.$monneu.'&'.$addon::KAL_JAHR.'='.$jahrneu;
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
   #                   POST-Parameter: $addon::KAL_MENUE=$men
   #                                   $addon::KAL_MONAT=$mon
   #                                   $addon::KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Monatsblatts des gewaehlten Monats)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_monate()
   #
   if(empty($mon) or empty($jahr)) return;
   $addon=self::this_addon;
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
     $linktext='<i class="fa fa-angle-left"></i>';
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
     $linktext='<i class="fa fa-angle-right"></i>';
     endif;
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1) $title=' title="'.$monate[intval($monneu)].' '.$jahrneu.'"';
   $lp=$addon::KAL_MONAT.'='.$monneu.'&'.$addon::KAL_JAHR.'='.$jahrneu;
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
   #                   POST-Parameter: $addon::KAL_MENUE=$men
   #                                   $addon::KAL_KW=$kw
   #                                   $addon::KAL_JAHR=$jahr
   #                   ($men = Menuenummer des Wochenblatts des gewaehlten Woche)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_montag_kw($montag)
   #
   if(empty($kw) or empty($jahr)) return;
   $addon=self::this_addon;
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
     $linktext='<i class="fa fa-angle-left"></i>';
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
     $linktext='<i class="fa fa-angle-right"></i>';
     endif;
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,6);
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1)
     $title=' title="Woche '.$kwneu.' ('.$jahrneu.'), '.substr($von,0,6).'-'.substr($bis,0,6).'"';
   $lp=$addon::KAL_KW.'='.$kwneu.'&'.$addon::KAL_JAHR.'='.$jahrneu;
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
   #                   POST-Parameter: $addon::KAL_MENUE=$men
   #                                   $addon::KAL_Datum=$datum
   #                   ($men = Menuenummer des Tagesblatts des gewaehlten Tages)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #
   if(empty($datum)) return;
   $addon=self::this_addon;
   #
   $lnkmod=2;
   $menues=self::kal_define_menues();
   $datumneu=$datum;
   #
   # --- vorheriger Tag
   if($kont==intval(-1)):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,-1);
     $linktext='<i class="fa fa-angle-left"></i>';
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
     $linktext='<i class="fa fa-angle-left"></i>';
     $lnkmod=2;
     endif;
   #
   # --- folgender Tag
   if($kont==1):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,1);
     $linktext='<i class="fa fa-angle-right"></i>';
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
   $lp=$addon::KAL_DATUM.'='.$datumneu;
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'gesblatt')>0) $men=$i;   // Tagesblatt
   return '<span'.$title.'>
            '.self::kal_link($lp,$men,$linktext,$lnkmod).'
            </span>';
   }
public static function kal_terminblatt($termin,$realdate,$ruecklinks) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins.
   #   Die Ueberschrift enthaelt die Ueberschrift des Termins (Termin-Name) sowie
   #   in der Regel zusaetzlich je einen Ruecklink auf das Kalendermenue des
   #   Monats und auf das aktuelle Tagesblatt.
   #   $termin         assoziatives Array des Termins
   #   $realdate       reales Datum, kann vom Spaltenwert von $termin abweichen,
   #                   wenn es sich um einen woechentlich wiederkehrenden Termin
   #                   oder um den Folgetermin eines mehrtägigen Termins handelt
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
   $addon=self::this_addon;
   $pid       =$termin[$addon::TAB_KEY[0]];
   $katid     =$termin[$addon::TAB_KEY[1]];
   $name      =$termin[$addon::TAB_KEY[2]];
   $datum     =$termin[$addon::TAB_KEY[3]];
   $beginn    =$termin[$addon::TAB_KEY[4]];
   $ende      =$termin[$addon::TAB_KEY[5]];
   $tage      =$termin[$addon::TAB_KEY[6]];
   $wochen    =$termin[$addon::TAB_KEY[7]];
   $monate    =$termin[$addon::TAB_KEY[8]];
   $ausrichter=$termin[$addon::TAB_KEY[9]];
   $ort       =$termin[$addon::TAB_KEY[10]];
   $link      =$termin[$addon::TAB_KEY[11]];
   $komm      =$termin[$addon::TAB_KEY[12]];
   $zeit2     =$termin[$addon::TAB_KEY[13]];
   $text2     =$termin[$addon::TAB_KEY[14]];
   $zeit3     =$termin[$addon::TAB_KEY[15]];
   $text3     =$termin[$addon::TAB_KEY[16]];
   $zeit4     =$termin[$addon::TAB_KEY[17]];
   $text4     =$termin[$addon::TAB_KEY[18]];
   $zeit5     =$termin[$addon::TAB_KEY[19]];
   $text5     =$termin[$addon::TAB_KEY[20]];
   #
   # --- ggf. Ruecklinks
   $dat=$realdate;
   if(empty($dat)) $dat=kal_termine_kalender::kal_heute();
   $rdat=kal_termine_kalender::kal_standard_datum($dat);
   $arr=explode('.',$dat);
   $dat=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   if($ruecklinks<=0):
     $ueber=array('','',$dat);
     else:
     $datx=kal_termine_kalender::kal_standard_datum($realdate);
     $mon=substr($datx,3,2);
     $monate=kal_termine_kalender::kal_monate();
     $monat=$monate[intval($mon)];
     $jahr=substr($datx,6);
     $ueber=array(self::kal_blaettern_basis(),self::kal_blaettern_tage($dat,3),$dat);
     endif;
   #
   # --- Ueberschrift-Zeile
   $seite='
<div class="'.$addon::CSS_TERMBLATT.'">
<table class="kal_table kal_box">
    <tr><td class="kopf">
            <table class="kal_transparent"><tr>
                <td>'.$ueber[0].'</td>
                <td>&nbsp;</td>
                <td>'.$ueber[1].'</td>
            </tr></table></td>
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
   # --- Datum, Enddatum, Veranstaltungsname
   $arr=explode('.',$datum);
   $strdat=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   if($tage>1):
     $datend=kal_termine_kalender::kal_datum_vor_nach($datum,$tage-1);
     else:
     $datend=$datum;
     endif;
   $arr=explode('.',$datend);
   $strdatend=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   $seite=$seite.'
            '.$name.'</td></tr>';
   $wt=kal_termine_kalender::kal_wotag($datum);
   $wte=kal_termine_kalender::kal_wotag($datend);
   #
   # --- Startzeit, Endzeit, woechentl. Wiederh., mehrt. Termine
   $str=$wt.', '.$strdat;
   if($datend==$datum):
     if(!empty($termin[$beginn]))
       $str=$str.', &nbsp; '.$termin[$beginn];
     if(!empty($ende)) $str=$str.' - '.$ende;
     if(!empty($str)) $str=$str.' Uhr';
     ;else:
     if(!empty($termin[$beginn]))
       $str=$str.', &nbsp; '.$termin[$beginn].' Uhr &nbsp;';
     $str=$str.' &nbsp; - &nbsp; '.$wte.', '.$strdatend;
     if(!empty($ende)) $str=$str.', &nbsp; '.$ende.' Uhr';
     endif;
   $warnung='';
   #     Wiederholungstermine
   if($wochen>0):
     $ter=kal_termine_tabelle::kal_select_termin_by_pid($pid);
     $warnung='<b>wöchentlich</b>, über '.$wochen.' Wochen, ab '.$strdat;
     if(!empty($ueber[1])) $str=$str.'<br>
            ('.$warnung.')';
     endif;
   #     Folgetermine
   if($tage>1):
     $warnung='<b>mehrtägig</b>, '.$tage.' Tage, ab '.$strdat;
     if(!empty($ueber[1])) $str=$str.'<br>
            ('.$warnung.')';
     endif;
   $seite=$seite.'
    <tr><th>Termin:</th>
        <td class="'.$addon::CSS_COLS.'6">
            '.$str;
   #
   # --- eventuelle bis zu 4 Zusatzzeiten
   $z='';
   if(!empty($zeit2))
     $z=$z.'<br>
            '.$zeit2.'&nbsp;Uhr: &nbsp; '.$text2;
   if(!empty($zeit3))
     $z=$z.'<br>
            '.$zeit3.'&nbsp;Uhr: &nbsp; '.$text3;
   if(!empty($zeit4))
     $z=$z.'<br>
            '.$zeit4.'&nbsp;Uhr: &nbsp; '.$text4;
   if(!empty($zeit5))
     $z=$z.'<br>
            '.$zeit5.'&nbsp;Uhr: &nbsp; '.$text5;
   if(!empty($z)) $seite=$seite.$z;
   $seite=$seite.'</td></tr>';
   #
   # --- reales Datum nicht das Startdatum des Termins
   if($tage>1 or $wochen>0):
   $seite=$seite.'
    <tr><th>Datum:</th>
        <td class="'.$addon::CSS_COLS.'6">
            '.$dat.'</td></tr>';
     endif;
   #
   # --- Ausrichter
   if(!empty($ausrichter))
     $seite=$seite.'
    <tr valign="top">
        <th>Ausrichter:</th>
        <td class="td '.$addon::CSS_COLS.'6">
            '.$ausrichter.'</td></tr>';
   #
   # --- Veranstaltungsort
   if(!empty($ort))
     $seite=$seite.'
    <tr valign="top">
        <th>Ort:</th>
        <td class="'.$addon::CSS_COLS.'6">
            '.$ort.'</td></tr>';
   #
   # --- Link
   if(!empty($link)):
     $linktext=substr($link,0,50);
     if(strlen($link)>50) $linktext=$linktext.' . . . ';
     if(!rex::isBackend())
       $linktext='<a href="'.$link.'" target="_blank">'.$linktext.'</a>';
     $seite=$seite.'
    <tr valign="top">
        <th>Link:</th>
        <td class="'.$addon::CSS_COLS.'6">
            '.$linktext.'</td></tr>';
     endif;
   #
   # --- Hinweise
   if(!empty($komm))
     $seite=$seite.'
    <tr valign="top">
        <th>Hinweise:</th>
        <td class="'.$addon::CSS_COLS.'6">
            '.$komm.'</td></tr>';
   #
   # --- Kategorie
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
        <th class="kal_fail">Vorsicht:</th>
        <td class="kal_fail">'.$warnung.'</td></tr>';
   #
   $seite=$seite.'
</table>
</div>
';
   return $seite;
   }
#
#----------------------------------------- Terminliste
public static function kal_uhrzeit_string($termin) {
   #   Rueckgabe eines Strings, in dem die Zeitangaben aufbereitet sind,
   #   zu einem gegebenen Termin. Der String endet mit: ': '.
   #   $termin         assoziatives Array des Termins
   #   benutzte functions:
   #      kal_termine_kalender::kal_datum_vor_nach($datsta,$anztage)
   #
   $addon=self::this_addon;
   $datsta=$termin[$addon::TAB_KEY[3]];   // datum
   $beginn=$termin[$addon::TAB_KEY[4]];   // beginn
   $ende  =$termin[$addon::TAB_KEY[5]];   // ende
   $tage  =$termin[$addon::TAB_KEY[6]];   // tage
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
   #   Rueckgabe eines Strings im HTML-Format, in dem die zusaetzlichen
   #   Zeitangaben zu einem gegebenen Termin zeilenweise dargestellt werden.
   #   $termin         assoziatives Array des Termins
   #
   $addon=self::this_addon;
   $zeit2=$termin[$addon::TAB_KEY[13]];
   $text2=$termin[$addon::TAB_KEY[14]];
   $zeit3=$termin[$addon::TAB_KEY[15]];
   $text3=$termin[$addon::TAB_KEY[16]];
   $zeit4=$termin[$addon::TAB_KEY[17]];
   $text4=$termin[$addon::TAB_KEY[18]];
   $zeit5=$termin[$addon::TAB_KEY[19]];
   $text5=$termin[$addon::TAB_KEY[20]];
   $zusatz='';
   if(!empty($zeit2)):
     $zeit=$zeit2;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text2;
     endif;
   if(!empty($zeit3)):
     $zeit=$zeit3;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text3;
     endif;
   if(!empty($zeit4)):
     $zeit=$zeit4;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text4;
     endif;
   if(!empty($zeit5)):
     $zeit=$zeit5;
     if(substr($zeit,0,1)=='0') $zeit=substr($zeit,1);
     $zusatz=$zusatz.'<br>
            '.$zeit.' Uhr: '.$text5;
     endif;
   return $zusatz;
   }
public static function kal_terminliste($termin,$datum_as_link=FALSE) {
   #   Rueckgabe einer Liste von Terminen in Form eines HTML-Codes.
   #   Falls ein Link vorgegeben ist, wird der Terminname mit diesem Link
   #   unterlegt. Leere Parameter sowie die Terminkategorie werden nicht
   #   mit ausgegeben. Ggf. sind die Terminkategorien farblich markiert.
   #   $termin         Array der auszugebenden Termine (Nummerierung ab 1)
   #   $datum_as_link  ==TRUE:  Die Datumsangabe in der linken Tabellenspalte
   #                            der Liste wird als Link auf den ersten Tag
   #                            des Termins ausgegeben.
   #                   ==FALSE: Die Datumsangabe wird als Text ausgegeben.
   #   benutzte functions:
   #      self::kal_uhrzeit_string($termin)
   #      self::kal_zusatzzeiten_string($termin)
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      $addon::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$anztage)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_tabelle::kal_get_spielkategorien()
   #
   if(count($termin)<=0) return;
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];
   $keynam=$addon::TAB_KEY[2];
   $keydat=$addon::TAB_KEY[3];
   $keytag=$addon::TAB_KEY[6];
   $keyaus=$addon::TAB_KEY[9];
   $keyort=$addon::TAB_KEY[10];
   $keylnk=$addon::TAB_KEY[11];
   $keykom=$addon::TAB_KEY[12];
   #
   $kat_id=$termin[1][$keykat];   // kat_id
   if($kat_id<$addon::SPIEL_KATID):
     $kateg=$addon::kal_get_terminkategorien();
     else:
     $kateg=kal_termine_tabelle::kal_get_spielkategorien();
     endif;
   $kat=array();
   for($i=0;$i<count($kateg);$i=$i+1):
      $k=$i+1;
      $kat[$k]=$kateg[$i]['id'];
      if($kat[$k]>$addon::SPIEL_KATID) $kat[$k]=$kat[$k]-$addon::SPIEL_KATID;
      endfor;
   $anzkat=count($kat);
   #
   if($datum_as_link):   // ggf. Datumsangabe als Link auf das Tagesblatt
     $menues=self::kal_define_menues();
     for($i=1;$i<=count($menues);$i=$i+1)
        if(strpos($menues[$i]['name'],'gesblatt')>0) $men=$i;   // Tagesblatt
     endif;
   #
   # --- Formular
   $string='
<table class="kal_table termlist_table">';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      $kat_id=$term[$keykat];
      if($kat_id>$addon::SPIEL_KATID) $kat_id=$kat_id-$addon::SPIEL_KATID;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      #
      # --- Startdatum aufbereiten
      $datum=$term[$keydat];
      $datsta=$datum;
      $arr=explode('.',$datsta);
      if(substr($arr[0],0,1)=='0') $arr[0]=substr($arr[0],1);
      if(substr($arr[1],0,1)=='0') $arr[1]=substr($arr[1],1);
      $dat1=$arr[0].'.'.$arr[1].'.';
      $jahr1=$arr[2];
      $wot1=kal_termine_kalender::kal_wotag($datsta);
      #
      # --- Enddatum aufbereiten
      $dat2=$jahr1;
      $tage=$term[$keytag];
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
      if($datum_as_link):   // ggf. Datumsangabe als Link auf das Tagesblatt
        $datstr=htmlspecialchars('<span class="termdate_'.$kat_id.'">'.$datstr.'</span>');
        $datstr=self::kal_link($addon::KAL_DATUM.'='.$datsta,$men,$datstr,3);
        endif;
      $zeile='
    <tr><th class="termlist_th termdate_'.$kat_id.'">
            '.$datstr.'</th>
        <td class="termlist_td termbord_'.$kat_id.'">';
      $str='';
      #
      # --- Uhrzeiten aufbereiten
      $uhrz=self::kal_uhrzeit_string($term);
      $str=$str.'
            '.$uhrz;
      #
      # --- Veranstaltungsbezeichnung (ggf. als Link)
      $veran=$term[$keynam];
      $link =$term[$keylnk];
      if(empty($link)):
        $veran='<span class="termtitle_'.$kat_id.'">'.$veran.'</span>';
        else:
        $tar='';
        if(substr($link,0,4)=='http' and strpos($link,'://')>0)
          $tar=' target="_blank"';
          $veran='<a class="termtitle_'.$kat_id.'" href="'.$link.'"'.$tar.'>'.$veran.'</a>';
        endif;
      $str=$str.$veran;
      #
      # --- Ort
      $ort=$term[$keyort];
      if(!empty($ort)):
        $str=$str.'
            <span class="termlist_ort">'.$ort.'</span>';
        endif;
      #
      # --- Ausrichter
      $ausrichter=$term[$keyaus];
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
      $hinw=$term[$keykom];
      if(!empty($hinw)):
        $str=$str.'
            <span class="termlist_komm">'.$hinw.'</span>';
        endif;
      #
      $zeile=$zeile.$str.'</td></tr>';
   #
      $string=$string.$zeile;
      endfor;
   $string=$string.'
</table>';
   return $string;
   }
#
#----------------------------------------- Monatsmenue
public static function kal_monatsmenue($kid,$katids,$mon,$jahr,$modus=1) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalendermenues fuer einen Monat
   #   eines Jahres. Falls der Monat oder das Jahr nicht angegeben sind, wird der
   #   aktuelle Monat angenommen. Die Tage, an denen Termine anstehen, werden durch
   #   Schraffur markiert. Die Ausgabe des Kalendermenues erfolgt im aktuellen
   #   Browserfenster.
   #   $kid            Beruecksichtigung der Termine aller erlaubten Kategorien oder
   #                   nur der Termine ener einzelnen Kategorie
   #                   =0 oder =$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $kid
   #   $katids         Array der fuer den Redakteur erlaubten Kategorie-Ids
   #                   (Nummerierung ab 1, Spieldaten: alle Kategorien)
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
   #      self::kal_define_menues()
   #      self::kal_blaettern_jahre($mon,$jahr,$kont)
   #      self::kal_blaettern_monate($mon,$jahr,kont)
   #      self::kal_blaettern_wochen($kw,$jahr,$kont)
   #      self::kal_blaettern_suche()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_kalender::kal_wotag_nr($wt)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_montag_kw($datum)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katids,$kontif)
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   #
   $menues=self::kal_define_menues();
   for($j=1;$j<=count($menues);$j=$j+1)
      if(strpos($menues[$j]['name'],'gesblatt')>0) $men=$j;   // Menue-Nr. Tagesblatt
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
   if($modus>0):
     if($kid>0 and $kid!=$addon::SPIEL_KATID):
       $kids=array(1=>$kid);
       else:
       $kids=$katids;
       endif;
     $termine=kal_termine_tabelle::kal_get_termine($dat1,$dat2,$kids,1);
     endif;
   #
   # --- Tage markieren, an denen ein Termin vorhanden ist (Schraffur)
   for($k=1;$k<=count($termine);$k=$k+1):
      $datum=$termine[$k][$keydat];
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
        <td class="rechts '.$addon::CSS_COLS.'9"'.$title.'>'.$tagvn.'</td>';
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
             $class='rechts '.$addon::CSS_COLS.'6';
             else:
             $class='rechts '.$addon::CSS_COLS.'5';
             endif;
           if($datum==$heute) $class='rechts '.$addon::CSS_COLS.'8';
           $link=intval(substr($datum,0,2));
           if($modus>0 and !empty($datum))
             $link=self::kal_link($addon::KAL_DATUM.'='.$datum,$men,$tag,1);
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
<div class="'.$addon::CSS_MONMENUE.'">
<table class="kal_table kal_box">'.
$string.$kopf.$zeilen.'
</table>
</div>';
   return $str;
   }
#
#----------------------------------------- Monats-/Wochen-/Tagesblatt
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
   #      $addon::kal_define_stundenleiste()
   #
   $addon=self::this_addon;
   $daten=$addon::kal_define_stundenleiste();
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
public static function kal_eval_start_ende($termin) {
   #   Rueckgabe der Startuhrzeit und der Enduhrzeit eines Termins. Falls
   #   Start-/Enduhrzeit nicht definiert sind, werden sie sinnvoll ergaenzt.
   #   Rueckgabe als assoziatives Arrays (Start-/Enduhrzeit)
   #   - Falls der Terminbeginn leer ist:
   #                   der Beginn wird aus Zusatzzeiten 2 bis 5 abgeleitet
   #   - Falls der Terminbeginn und die Zusatzzeiten 2 bis 5 leer sind:
   #                   es wird 'ganztaegig' angenommen und Anfang/Ende als
   #                   Startuhrzeit/Enduhrzeit berechnet
   #   - Falls das Terminende leer ist:
   #                   das Ende wird aus den die Zusatzzeiten 2 bis 5 abgeleitet
   #   $termin         Daten eines Termins (Array)
   #   benutzte functions:
   #      $addon::kal_define_stundenleiste()
   #
   $addon=self::this_addon;
   $keynam=$addon::TAB_KEY[2];   // name
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   $keyze2=$addon::TAB_KEY[13];  // zeit2
   $keyze3=$addon::TAB_KEY[15];  // zeit3
   $keyze4=$addon::TAB_KEY[17];  // zeit4
   $keyze5=$addon::TAB_KEY[19];  // zeit5
   $daten =$addon::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   #
   # --- Startzeit bei fehlendem Beginn eines Termins
   #     falls eine der Zusatzzeiten nicht leer ist, ersetze Beginn
   #     durch die kleinste der nicht-leeren Zusatzzeiten ($minz);
   #     falls auch $minz leer ist, ersetze Startzeit durch Startuhrzeit
   $beginn=$termin[$keybeg];
   $zeitmax=max($termin[$keyze2],$termin[$keyze3],
                $termin[$keyze4],$termin[$keyze5]);
   if(empty($beginn) and !empty($zeitmax)):
     $begm=$zeitmax;
     if(!empty($termin[$keyze2]))
       $begm=$termin[$keyze2];
     if(!empty($termin[$keyze3]) and $termin[$keyze3]<$begm)
       $begm=$termin[$keyze3];
     if(!empty($termin[$keyze4]) and $termin[$keyze4]<$begm)
       $begm=$termin[$keyze4];
     if(!empty($termin[$keyze5]) and $termin[$keyze5]<$begm)
       $begm=$termin[$keyze5];
     $beginn=$begm;
     endif;
   if(empty($beginn)) $beginn=$stauhr;
   $arr=explode(':',$beginn);
   if(empty($arr[1])) $arr[1]='00';
   $beginn=intval($arr[0]).':'.$arr[1];
   #
   # --- Endzeit bei fehlendem Ende eines Termins
   #     falls $maxz=max(Zusatzzeiten) nicht leer ist, ersetze Endzeit durch
   #     $maxz+eps (eps = erste Zeitdifferenz zwischen 1. und 2. Zusatzzeit
   #     oder, falls diese 0 ist, alternativ 30 Min.);
   #     falls auch $maxz leer ist, ersetze Endzeit durch Enduhrzeit
   $ende=$termin[$keyend];
   if(empty($ende) and !empty($zeitmax)):
     if(!empty($termin[$keyze2]) and !empty($termin[$keyze3])):
       $arr=explode(':',$termin[$keyze2]);
       $brr=explode(':',$termin[$keyze3]);
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
   return array($keybeg=>$beginn, $keyend=>$ende);
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
   #      $addon::kal_define_stundenleiste()
   #
   $addon=self::this_addon;
   $keynam=$addon::TAB_KEY[2];   // name
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   if(empty($termin[$keynam])) return array('vor'=>0, 'dauer'=>0);
   #
   # --- Bemassung der Stundenleiste
   $daten=$addon::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $stunden=$daten[2]-$stauhr;
   $eps=0.4;
   #
   # --- ggf Start-/Enduhrzeit sinnvoll ergaenzen
   $sez=self::kal_eval_start_ende($termin);
   $beginn=$sez[$keybeg];
   $ende  =$sez[$keyend];
   #
   # --- Laenge vor dem Termin in Prozent der Gesamtlaenge
   $nziff=10;   // mit so vielen Ziffern wird gerechnet
   $arr=explode(':',$beginn);
   $voruhr=substr(floatval($arr[0]+$arr[1]/60),0,$nziff);
   $vor=$voruhr-$stauhr;
   $prozvor=substr(floatval($vor*100/$stunden),0,$nziff);
   $dps=0;
   if($prozvor<$eps):
     $prozvor=$eps;   // (*) kleiner Innenabstand vorne
     $dps=$eps;
     endif;
   #
   # --- Laenge des Termins in Prozent der Gesamtlaenge
   $arr=explode(':',$ende);
   $nachuhr=substr(floatval($arr[0]+$arr[1]/60),0,$nziff);
   $dau=$nachuhr-$voruhr;
   $prozdau=substr(floatval($dau*100/$stunden),0,$nziff);
   if($dps>0) $prozdau=$prozdau-$dps;   // (*) bei der Dauer abgezogen
   $prozdau=$prozdau-$eps;       // leichte Verkuerzung der Laenge
   if($prozvor+$prozdau>100-3*$eps)
     $prozdau=$prozdau-3*$eps;   // kleiner Innenabstand hinten
   #
   # --- Dezimalkomma ggf. durch Dezimalpunkt ersetzen
   $prozvor=str_replace(',','.',$prozvor);
   $prozdau=str_replace(',','.',$prozdau);
   #
   return array('vor'=>$prozvor, 'dauer'=>$prozdau);
   }
public static function kal_termin_titel($termin) {
   #   Rueckgabe des div-Container-Titels eines Termins im Tages-/Wochen-/Monatsblatt.
   #   Er enthaelt die Termin-Parameter Name, Beginn, Ende, Ort, Ausrichter
   #   $termin         Daten eines Termins (assoziatives Array)
   #   benutzte functions:
   #      self::kal_uhrzeit_string($termin)
   #
   $addon=self::this_addon;
   $keynam=$addon::TAB_KEY[2];   // name
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   $keyaus=$addon::TAB_KEY[9];   // ausrichter
   $keyort=$addon::TAB_KEY[10];  // ort
   $keyze2=$addon::TAB_KEY[13];  // zeit2
   $keyze3=$addon::TAB_KEY[15];  // zeit3
   $keyze4=$addon::TAB_KEY[17];  // zeit4
   $keyze5=$addon::TAB_KEY[19];  // zeit5
   #
   # --- Uhrzeiten-String
   if(empty($termin[$keybeg]) and empty($termin[$keyend]) and
      empty($termin[$keyze2]) and empty($termin[$keyze3]) and
      empty($termin[$keyze4]) and empty($termin[$keyze5])    ):
     $title='';
     else:
     $title=self::kal_uhrzeit_string($termin);
     endif;
   #
   # --- Name und Daten
   if(!empty($termin[$keynam])) $title=$title.$termin[$keynam];
   $ort=$termin[$keyort];
   if(!empty($ort)) $title=$title.' ('.$ort.')';
   $ausrichter=$termin[$keyaus];
   if(!empty($ausrichter)) $title=$title.', Ausrichter: '.$ausrichter;
   return $title;
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
   #      self::kal_uhrzeit_string($termin)
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'erminblatt')>0) $men=$i;   // Terminblatt
   $proz=self::kal_termin_poslen($termin);
   $cont='';
   if($proz['vor']>0 or $proz['dauer']>0):
     # --- div-Container (Streifen) mit zugemessener Position/Laenge und Termin-Link
     $title=self::kal_termin_titel($termin);
     $zeiten=self::kal_uhrzeit_string($termin);
     if(!empty($zeiten)):
       $pos=strpos($zeiten,' &nbsp; ');
       $zeiten='<div class="zeitenzeile">'.substr($zeiten,0,$pos).'</div>';
       endif;
     $linktext=$zeiten.$termin[$keynam];
     $param=$addon::KAL_DATUM.'='.$termin[$keydat].'&'.$addon::KAL_PID.'='.$termin[$keypid];
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
            <div class="leertermin">&nbsp;</div>';
     endif;
   return $cont;
   }
public static function kal_mowotablatt($kid,$katids,$mon,$kw,$jahr,$datum) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalenderblatts fuer entweder
   #   - einen Kalendermonat (nicht leer: $mon, $jahr, leer: $kw, $datum) oder
   #   - eine Kalenderwoche  (nicht leer: $kw, $jahr,  leer: $mon, $datum ) oder
   #   - einen einzelnen Tag (nicht leer: $datum,      leer: $mon, $kw, $jahr)
   #   Falls alle 4 Datumsparameter leer sind, wird der heutige Tag als
   #   einzelner Tag angenommen.
   #   $kid            Beruecksichtigung der Termine aller erlaubten Kategorien oder
   #                   nur der Termine ener einzelnen Kategorie
   #                   =0 oder =$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $kid
   #   $katids         Array der fuer den Redakteur erlaubten Kategorie-Ids
   #                   (Nummerierung ab 1, Spieldaten: alle Kategorien)
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
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      self::kal_blaettern_basis()
   #      self::kal_blaettern_monate($strmon,$jahr,$kont)
   #      self::kal_blaettern_wochen($kw,$jahr,$kont)
   #      self::kal_blaettern_tage($datum,$kont)
   #      self::kal_blaettern_suche()
   #      $addon::kal_define_stundenleiste()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katids,$kontif)
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   #
   # --- mehrfach verwendete Daten
   $heute=kal_termine_kalender::kal_heute();
   $strmon =$mon;
   $strkw  =$kw;
   $strjahr=$jahr;
   $stdatum=$datum;
   if(!empty($stdatum)) $stdatum=kal_termine_kalender::kal_standard_datum($stdatum);
   $menues=self::kal_define_menues();
   for($j=1;$j<=count($menues);$j=$j+1)
      if(strpos($menues[$j]['name'],'gesblatt')>0) $men=$j;   // Menue-Nr. Tagesblatt
   #
   # --- Kategorien
   if($kid>0 and $kid!=$addon::SPIEL_KATID):
     $kids=array(1=>$kid);
     else:
     $kids=$katids;
     endif;
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
   $daten=$addon::kal_define_stundenleiste();
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
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[$end],$kids,1);
     endif;
   #     Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     #     Montag (Starttag) der Woche als Datum
     $dat[1]=kal_termine_kalender::kal_kw_montag($kw,$strjahr);
     #     restliche Tage der Woche als Datum
     for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[7],$kids,1);
     endif;
   #     Einzeldatum
   if(!empty($datum)):
     $dat[1]=$stdatum;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[1],$kids,1);
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
      $termin[$i][1][$keydat]=$dat[$i];
      for($k=1;$k<=count($term);$k=$k+1):
         if($term[$k][$keydat]==$dat[$i]):
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
<div class="'.$addon::CSS_MWTBLATT.'">
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
            </th></tr>'.self::kal_stundenleiste();
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
            <hr>';
         $cont=$cont.$content[$i][$k];
         endfor;
      $title='title="'.$shdat.'"';
      if(!empty($feiertag) or $wt=='So'):
        if(!empty($feiertag)) $title='title="'.$feiertag.', '.$dat[$i].'"';
        $class='tag '.$addon::CSS_COLS.'5';
        else:
        $class='tag '.$addon::CSS_COLS.'6';
        endif;
      if($dat[$i]==$heute) $class='tag '.$addon::CSS_COLS.'8';
      $tgtext=$wt.', '.$shdat;
      #     Monats-/Wochenblatt: Link auf den Tag
      if(!empty($jahr)) $tgtext=self::kal_link($addon::KAL_DATUM.'='.$dat[$i],$men,$tgtext,1);
      $string=$string.'
    <tr><td class="pad1" '.$title.'>'.$tgtext.'</td>
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
public static function kal_monatsblatt($kid,$katids,$mon,$jahr) {
   if(empty($mon) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $mon=substr($heute,3,2);
     $jahr=substr($heute,6);
     endif;
   return self::kal_mowotablatt($kid,$katids,$mon,'',$jahr,'');
   }
public static function kal_wochenblatt($kid,$katids,$kw,$jahr) {
   if(empty($kw) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     endif;
   return self::kal_mowotablatt($kid,$katids,'',$kw,$jahr,'');
   }
public static function kal_tagesblatt($kid,$katids,$datum) {
   if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
   return self::kal_mowotablatt($kid,$katids,'','','',$datum);
   }
#
#----------------------------------------- Termin-Filtermenue
public static function kal_such($kid,$katids,$jahr,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Filterung der Termine eines
   #   Kalenderjahres (maximal 4 Jahre vor und 2 Jahre nach dem aktuellen Jahr,
   #   wie unten festgelegt (*)). Die Termine gehören zu einer oder mehreren
   #   Kategorien. Die Filterbedingungen sind diese: 
   #   - Beschraenkung auf eine Kategorie, wenn mehrere Kategorien vorgegeben sind
   #   - Beschraenkung auf Termine, die einen Suchbegriff enthalten
   #   - Beschraenkung auf die Termine ab dem heutigen Datum
   #   Die Bedingungen werden ueber die Parameter $kid, $suchen, $vorher definiert
   #   und gelten zugleich.
   #   $kid            Beruecksichtigung der Termine aller erlaubten Kategorien oder
   #                   nur der Termine ener einzelnen Kategorie
   #                   =0 oder =$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $kid
   #   $katids         Array der fuer den Redakteur erlaubten Kategorie-Ids
   #                   (Nummerierung ab 1, Spieldaten: alle Kategorien)
   #   $jahr           Kalenderjahr, auf das sich die Suche bezieht
   #                   falls leer, wird das aktuelle Jahr angenommen
   #   $suchen         im Menue eingegebener Suchbegriff (unabhaengig von
   #                   Gross-/Kleinschreibung), der Begriff wird in diesen
   #                   Termin-Parametern gesucht: Name, Ausrichter, Ort,
   #                   Kommentar, Text2, Text3, Text4, Text5
   #   $vorher         Wert des im Menue markierten Radiobuttons
   #                   ='vorher': auch abgelaufene Termine
   #                   sonst:     nur zukuenftige Termine (ab dem heutigen Tag)
   #   Das Auswahlformular liefert diese Parameter samt Werten:
   #      $addon::KAL_KATEGORIE  gemaess $kid
   #      $addon::KAL_SUCHEN     gemaess $suchen
   #      $addon::KAL_VORHER     gemaess $vorher
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_blaettern_basis()
   #      self::kal_terminliste($termin,$datum_as_link)
   #      $addon::kal_select_kategorie($name,$kid,$katids,$all)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katids,$kontif)
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // pid
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $keyaus=$addon::TAB_KEY[9];   // ausrichter
   $keyort=$addon::TAB_KEY[10];  // ort
   $keykom=$addon::TAB_KEY[12];  // komm
   $keytx2=$addon::TAB_KEY[14];  // text2
   $keytx3=$addon::TAB_KEY[16];  // text3
   $keytx4=$addon::TAB_KEY[18];  // text4
   $keytx5=$addon::TAB_KEY[20];  // text5
   #
   # --- (*) Festlegung der betrachteten Kalenderjahre
   #     Anzahl der Jahre vor/nach dem aktuellen Jahr: $jvor/$jnach
   $jvor =4;
   $jnach=2;
   #
   # --- ggf. das aktuelle Jahre nehmen
   $JAHR=$jahr;
   $heute=kal_termine_kalender::kal_heute();
   $jahrdef=substr($heute,6);
   if(empty($JAHR)) $JAHR=$jahrdef;
   #
   # --- Auslesen aller Termine inkl./exkl. abgelaufene Termine
   if(!empty($vorher)):
     $von='01.01.'.$JAHR;
     else:
     $von=$heute;
     if($JAHR>$jahrdef) $von='01.01.'.$JAHR;
     endif;
   $bis='31.12.'.$JAHR;
   $termin=kal_termine_tabelle::kal_get_termine($von,$bis,$katids,0);
   $nztermin=count($termin);
   #
   # --- Ausgaben
   $monate=kal_termine_kalender::kal_monate();
   $string='
<div class="'.$addon::CSS_SUCH.'">
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
        <td class="td '.$addon::CSS_COLS.'6">
            <select name="'.$addon::KAL_JAHR.'" class="'.$addon::CSS_COLS.'5">';
   #     Jahresauswahl definieren
   $jvpn=$jvor+$jnach;
   for($i=0;$i<=$jvpn;$i=$i+1) $ja[$i+1]=$jahrdef-$jvor+$i;
   for($i=1;$i<=count($ja);$i=$i+1)
      if($ja[$i]==$JAHR):
        $string=$string.'
                <option value="'.$ja[$i].'" class="'.$addon::CSS_COLS.'5" selected="selected">'.$ja[$i].'</option>';
        else:
        $string=$string.'
                <option value="'.$ja[$i].'" class="'.$addon::CSS_COLS.'6">'.$ja[$i].'</option>';
        endif;
   $string=$string.'
            </select></th></tr>';
   #
   # --- Auswahlmaske: Kategorien
   $selmen=$addon::kal_select_kategorie($addon::KAL_KATEGORIE,$kid,$katids,TRUE);
   $string=$string.'
    <tr><th class="th">Kategorie:</th>
        <td class="td '.$addon::CSS_COLS.'6">'.$selmen;
   $string=$string.'</td></tr>';
   #
   # --- Auswahlmaske: Stichwort
   $string=$string.'
    <tr><th class="th">Stichwort:</th>
        <td class="td '.$addon::CSS_COLS.'6">
            <input name="'.$addon::KAL_SUCHEN.'" type="text" value="'.$suchen.'" class="stichwort '.$addon::CSS_COLS.'5"></td></tr>';
   #
   # --- Auswahlmaske: nur aktuelle Termine beruecksichtigen
   if($vorher=='vorher'):
     $chk1='';
     $chk2=' checked';     
     else:
     $chk1=' checked';   
     $chk2='';     
     endif;
   $string=$string.'
    <tr valign="top">
        <th class="th">Aktualität:</th>
        <td class="td '.$addon::CSS_COLS.'6">
            <input type="radio" name="'.$addon::KAL_VORHER.'" value=""'.$chk1.'>
            <span class="small">nur zukünftige</span><br>
            <input type="radio" name="'.$addon::KAL_VORHER.'" value="vorher"'.$chk2.'>
            <span class="small">auch abgelaufene</span></td></tr>';
   #
   # --- Menue-Nr. als hidden-Parameter und Submit-Button
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'iltermenü')>0) $men=$i;  // Filtermenue
   $string=$string.'
    <tr><th class="th"></th>
        <td><input type="hidden" name="'.$addon::KAL_MENUE.'" value="'.$men.'">
            <button type="submit" class="'.$addon::CSS_COLS.'5 kal_linkbutton filter_button">
            <b>Filtern der Termine</b></button>
            </form></td></tr>
</table>';
   #
   # --- Herausfiltern der Termine, die zur gewaehlten Kategorie gehoeren ($term)
   $term=array();
   if($kid>0 and $kid!=$addon::SPIEL_KATID):
     $m=0;
     for($i=1;$i<=$nztermin;$i=$i+1):
        if($termin[$i][$keykat]==$kid):
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
        if(!empty(stristr($termin[$i][$keynam],$suchen)) or
           !empty(stristr($termin[$i][$keyaus],$suchen)) or
           !empty(stristr($termin[$i][$keyort],$suchen)) or
           !empty(stristr($termin[$i][$keykom],$suchen)) or
           !empty(stristr($termin[$i][$keytx2],$suchen)) or
           !empty(stristr($termin[$i][$keytx3],$suchen)) or
           !empty(stristr($termin[$i][$keytx4],$suchen)) or
           !empty(stristr($termin[$i][$keytx5],$suchen))   ):
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
<div class="left anzahl"><br>';
   if($nztermin<=0):
     $string=$string.'+++++ keine entsprechenden Termine gefunden';
     else:
     $ter='Termine';
     if($nztermin==1) $ter='Termin';
     $string=$string.'<u>'.$nztermin.' '.$ter.' gefunden:</u><br>
    <div class="liste">'.
        self::kal_terminliste($termin,TRUE).'
    </div>';
     endif;
   $string=$string.'
</div>
</div>';
   return $string;
   }
#----------------------------------------- Menuewechsel
public static function kal_menue($selkid,$mennr,$spieldaten=FALSE) {
   #   Rueckgabe des HTML-Codes zur Anzeige des gewaehlten Menues.
   #   $selkid         Beruecksichtigung der Termine aller erlaubten Kategorien oder
   #                   nur der Termine ener einzelnen Kategorie
   #                   =0 oder =$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $selkid
   #   $mennr          Nummer des ersten anzuzeigenden Menues (Startmenue),
   #                   wird nur benutzt, wenn der Wert von $_POST[$addon::KAL_MENUE]
   #                   (noch) leer ist.
   #                   >0: wird als Startmenue-Nummer benutzt,
   #                       falls die Zahl einem der Menues entspricht
   #                   =0/leer/falsch: als Startmenue wird das Monatsmenue genommen
   #   $spieldaten     =TRUE:  das Menue wird mit Spielterminen gestartet
   #                   sonst:  das Menue wird mit echten Terminen gestartet
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_monatsmenue($kid,katids,$mon,$jahr,$modus)
   #      self::kal_monatsblatt($kid,$katids,$monat,$jahr)
   #      self::kal_wochenblatt($kid,$katids,$kw,$jahr)
   #      self::kal_tagesblatt($kid,$katids,$datum)
   #      self::kal_terminblatt($termin,$realdate,$ruecklinks)
   #      self::kal_such($kid,$katids,$jahr,$suchen,$vorher)
   #      $addon::kal_allowed_terminkategorien()
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katids,$kontif)
   #      kal_termine_tabelle::kal_get_spielkategorien()
   #
   $addon=self::this_addon;
   #
   # --- Spieldaten oder Datenbankdaten
   $katids=array();
   if($spieldaten):
     $kats=kal_termine_tabelle::kal_get_spielkategorien();
     for($i=0;$i<count($kats);$i=$i+1) $katids[$i+1]=$kats[$i]['id'];
     else:
     #     einzelne Kategorie / alle Kategorien
     $kids=$addon::kal_allowed_terminkategorien();
     if($selkid>0):
       $katids[1]=$kids[$selkid];
       else:
       $katids=$kids;
       endif;
     endif;
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
   $suchen ='';
   $vorher ='';
   $men    =0;
   $pid    =0;
   $kid    =$selkid;
   if(!empty($_POST[$addon::KAL_MONAT]))    $monat =$_POST[$addon::KAL_MONAT];
   if(!empty($_POST[$addon::KAL_KW]))       $kw    =$_POST[$addon::KAL_KW];
   if(!empty($_POST[$addon::KAL_JAHR]))     $jahr  =$_POST[$addon::KAL_JAHR];
   if(!empty($_POST[$addon::KAL_DATUM]))    $datum =$_POST[$addon::KAL_DATUM];
   if(!empty($_POST[$addon::KAL_SUCHEN]))   $suchen=$_POST[$addon::KAL_SUCHEN];
   if(!empty($_POST[$addon::KAL_VORHER]))   $vorher=$_POST[$addon::KAL_VORHER];
   if(isset($_POST[$addon::KAL_MENUE]))     $men   =$_POST[$addon::KAL_MENUE];
   if(isset($_POST[$addon::KAL_PID]))       $pid   =$_POST[$addon::KAL_PID];
   if(isset($_POST[$addon::KAL_KATEGORIE])) $kid   =$_POST[$addon::KAL_KATEGORIE];
   #
   # --- Startmenue
   if($men<=0 and $mennr>0) $men=$mennr;
   if($men<=0 or $men>count($menues)) $men=$menmom;
   #
   # --- Monatsmenue
   if($men==$menmom)
     return self::kal_monatsmenue($kid,$katids,$monat,$jahr,1);
   #
   # --- Monatsblatt
   if($men==$menmob)
     return self::kal_monatsblatt($kid,$katids,$monat,$jahr);
   #
   # --- Wochenblatt
   if($men==$menwob)
     return self::kal_wochenblatt($kid,$katids,$kw,$jahr);
   #
   # --- Tagesblatt
   if($men==$mentab)
     return self::kal_tagesblatt($kid,$katids,$datum);
   #
   # --- Terminblatt
   if($men==$menteb):
     $termin=array();
     $term=kal_termine_tabelle::kal_get_termine($datum,$datum,$katids,0);
     for($i=1;$i<=count($term);$i=$i+1)
        if($term[$i][$addon::TAB_KEY[0]]==$pid) $termin=$term[$i];
     return self::kal_terminblatt($termin,$datum,1);
     endif;
   #
   # --- Filtermenue
   if($men==$menfil)
     return self::kal_such($kid,$katids,$jahr,$suchen,$vorher);
   }
public static function kal_spielmenue() {
   #   Anzeige des Monatsmenues der Spieldaten.
   #   Aufgerufen nur in pages/example.php
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_menue($selkid,$mennr,$spieldaten)
   #
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;   // Monats-Menuenummer
   $addon=self::this_addon;
   return self::kal_menue($addon::SPIEL_KATID,$menmom,TRUE);
   }
}
?>