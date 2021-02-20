<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2021
*/
define ('KAL_MONAT'     , 'MONAT');
define ('KAL_KW'        , 'KW');
define ('KAL_JAHR'      , 'JAHR');
define ('KAL_DATUM'     , 'DATUM');
define ('KAL_DATUM2'    , 'DATUM2');
define ('KAL_ANZTAGE'   , 'ANZTAGE');
define ('KAL_MODUS'     , 'MODUS');
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
#   Monatsmenue
#         kal_monatsmenue($katid,$mon,$jahr)
#         kal_monatsmenue_modus($katid,$mon,$jahr,$datum1,$modus)
#   Monats-/Wochen-/Tagesblatt
#         kal_monatsblatt($katid,$mon,$jahr)
#         kal_wochenblatt($katid,$kw,$jahr)
#         kal_tagesblatt($katid,$datum)
#         kal_mowotablatt($katid,$mon,$kw,$jahr,$datum)
#         kal_termin_titel($termin)
#         kal_stundenleiste()
#         kal_terminpixel($termin)
#         kal_eval_start_ende($termin)
#   Termin-Auswahlmenue
#         kal_tages_such_menue($katid,$datum,$suchen,$vorher)
#         kal_wochen_such_menue($katid,$kw,$jahr,$suchen,$vorher)
#         kal_monats_such_menue($katid,$mon,$jahr,$suchen,$vorher)
#         kal_such_daten($datum,$anztage,$monat,$jahr)
#         kal_such_menue($katid,$datum,$datum2,$kid,$suchen,$vorher)
#         kal_such($katid,$param,$kid,$suchen,$vorher)
#   Menuewechsel
#         kal_menue($katid,$mennr)
#
public static function kal_define_menues() {
   #   Rueckgabe der moeglichen Startmenues in der Reihenfolge:
   #      [1]  Monatsmenue
   #      [2]  Zeitraummenue
   #      [3]  Monatsblatt
   #      [4]  Wochenblatt
   #      [5]  Tagesblatt
   #      [6]  Zeitraumfiltermenue
   #      [7]  Monatsfiltermenue
   #      [8]  Wochenfiltermenue
   #      [9]  Tagesfiltermenue
   #     [10]  Terminblatt
   #   Jedes Startmenue ist ein nummeriertes Array mit diesen Elementen:
   #           [1]  Bezeichnung des Menues
   #           [2]  Erlaeuterungstext fuer das Menue
   #
   $mome=array('name'=>'Monatsmenü', 'titel'=>'Monatsmenü inkl. Darstellung der '.
      'wesentlichen christlichen Feiertage. Alle Tage, an denen Termine eingetragen '.
      'sind, werden durch Schraffur gekennzeichnet und enthalten einen Link auf das '.
      'zugehörige Tagesblatt.');
   $mobl=array('name'=>'Monatsblatt', 'titel'=>'Monatsblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an den zugehörigen Tagen. '.
      'Jeder Termin enthält einen Link auf ein Blatt mit seinen Daten.');
   $wobl=array('name'=>'Wochenblatt', 'titel'=>'Wochenblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an den zugehörigen Tagen. '.
      'Jeder Termin enthält einen Link auf ein Blatt mit seinen Daten.');
   $tabl=array('name'=>'Tagesblatt', 'titel'=>'Tagesblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an diesem Tag. '.
      'Jeder Termin enthält einen Link auf ein Blatt mit seinen Daten.');
   $mfm=array('name'=>'Monatsfiltermenü', 'titel'=>'Liste der Termine eines Monats '.
      'mit Filterfunktionen zur Verkürzung der Liste.');
   $wfm=array('name'=>'Wochenfiltermenü', 'titel'=>'Liste der Termine einer Woche '.
      'mit Filterfunktionen zur Verkürzung der Liste.');
   $tfm=array('name'=>'Tagesfiltermenü', 'titel'=>'Liste der Termine eines Tages '.
      'mit Filterfunktionen zur Verkürzung der Liste.');
   $tebl=array('name'=>'Terminblatt', 'titel'=>'Tabellarische Darstellung der Daten '.
      'eines Termins.');
   $zme=array('name'=>'Zeitraummenü', 'titel'=>'Auswahl eines Zeitraums für die '.
      'Auflistung und Filterung von Terminen.');
   $zfm=array('name'=>'Zeitraumfiltermenü', 'titel'=>'Liste der Termine eines '.
      'Zeitraums mit Filterfunktionen zur Verkürzung der Liste.');
   $tli=array('name'=>'Terminliste', 'titel'=>'einfache Auflistung der Termine eines '.
      'Zeitabschnitts.');
   $menue=array(1=>$mome, 2=>$mobl, 3=>$wobl, 4=>$tabl, 5=>$mfm, 6=>$wfm, 7=>$tfm,
                8=>$tebl, 9=>$zme, 10=>$zfm, 11=>$tli);
   return $menue;
   }
public static function kal_link($par,$mennr,$linktext,$modus) {
   #   Rueckgabe einer Referenz als Formular
   #   $par            Link-Parameter-String in der Form 'par1=PAR1&par2=PAR2&...',
   #                   werden als hidden Parameter weiter gegeben
   #   $mennr          Nummer des Menues, auf das die Referenz verweisen soll,
   #                   wird als hidden Parameter weiter gegeben
   #   $linktext       anzuzeigender Linktext
   #   $modus          =1:    es wird ein Link zurueck gegeben
   #                   sonst: es wird statt des Links nur der Linktext zurueck gegeben
   #
   if(abs($modus)==0) return $linktext;
   #
   $action='';
   if(rex::isBackend()) $action=' action="'.$_SERVER['REQUEST_URI'].'"';
   $str='<form style="display:inline;" method="post" onsubmit=""'.$action.'>';
   $arr=explode('&',$par);
   for($i=0;$i<count($arr);$i=$i+1):
      $brr=explode('=',$arr[$i]);
      if(!empty($brr[1])) $str=$str.'
            <input type="hidden" name="'.$brr[0].'" value="'.$brr[1].'" />';
      endfor;
   $str=$str.'
            <input type="hidden" name="'.KAL_MENUE.'" value="'.$mennr.'" />
            <input type="hidden" name="REX_INPUT_VALUE[1]" value="'.ACTION_SEARCH.'" />
            <button type="submit" class="kal_transparent kal_linkbutton">'.$linktext.'</button>
            </form>';
   return $str;
   }
#
#----------------------------------------- Monatsmenue
public static function kal_monatsmenue($katid,$mon,$jahr) {
   return self::kal_monatsmenue_modus($katid,$mon,$jahr,1,1);
   }
public static function kal_monatsmenue_modus($katid,$mon,$jahr,$datum1,$modus) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalendermenues fuer einen Monat.
   #   Der Monat wird bestimmt durch ein vorgegebenes Datum. Ist kein solches Datum
   #   vorgegeben, wird der aktuelle Monat angenommen.
   #   Die Tage, an denen Termine anstehen, werden durch Schraffur markiert.
   #   Die Ausgabe des Kalendermenues erfolgt im aktuellen Browserfenster.
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
   #   $datum1         Datum, genutzt zur Markierung eines gewaehlten Tages ($modus<0)
   #   $modus          >0:  das Menue erhaelt Links auf alle Tage des Monats
   #                        sowie auf Vorjahr, Vormonat, Folgemonat, Folgejahr;
   #                        es enthaelt auch einen Link auf das Monatsfiltermenue;
   #                        Tage, an denen Termine anstehen, werden schraffiert
   #                   <0:  wie bei >0, aber es fehlen:
   #                        der Link auf das Monatsfiltermenue und die Schraffuren
   #                   =0:  das Menue enthaelt keine Links und keine Schraffuren
   #                        (es werden keine Termine ausgelesen)
   #   ----- Links und POST-Parameter im Falle $modus>0:
   #   Jeder Tag des Monats ist als Button-Link abgelegt mit der Nummer des Tages
   #   als Linktext und mit den POST-Parametern:
   #   KAL_MENUE=$men, KAL_DATUM=$datum
   #      $datum:    10-Zeichen-Datums-String des gewaehlten Tages; falls $datum ein
   #                 Feiertag ist, enthaelt der Titel des Links dessen Bezeichnung
   #      $men       Menuenummer des Tagesblatts des gewaehlten Tages
   #   Jede Kalenderwoche ist als Button-Link abgelegt mit der Nummer der Woche
   #   als Linktext und mit den POST-Parametern:
   #   KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr
   #      $kw:       Nummer der gewaehlten Kalenderwoche
   #      $jahr:     4-stellige Jahreszahl des Jahres, zu dem die Kalenderwoche
   #                 gehoert (Jahresanfang/-ende!)
   #      $men       Menuenummer des Wochenblatts der gewaehlten Kalenderwoche
   #   Jeder Vor-/Folgemonat, jedes Vor-/Folgejahr ist als Button-Link abgelegt
   #   mit den POST-Parametern:
   #   KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #      «          Linktext Vorjahr
   #      <          Linktext Vormonat
   #      >          Linktext Folgemonat
   #      »          Linktext Folgejahr
   #      $monat     Nummer des gewaehlten Monats
   #      $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      $men       Menuenummer des Monatsblatts des gewaehlten Monats
   #   Die Ueberschrift des Monatsmenues ist unterlegt mit einem Button-Link
   #   auf das zugehoerige Monatsfiltermenue und mit den POST-Parametern:
   #   KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #      $monat     Nummer des aktuellen Monats
   #      $jahr      4-stellige Jahreszahl des aktuellen Jahres
   #      $men       Menuenummer des Monatsfiltermenues des aktuellen Monats
   #   ----- Links und POST-Parameter im Falle $modus<0:
   #   wie im Falle $modus>0, aber: Die Ueberschrift des Monatsmenues und die
   #   Kalenderwochen sind nicht mit Links unterlegt, und vorhandenen Links
   #   enthalten den zusaetzlichen POST-Paramter KAL_MODUS=-1.
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_standard_datum($datum)
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
   # --- Standardformat fuer den gewaehlten Tag ($modus<0)
   $seltag=kal_termine_kalender::kal_standard_datum($datum1);
   #
   # --- Uebergabeparameter
   $param='';
   if($modus<0) $param='&'.KAL_MODUS.'='.$modus;
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;   // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0) $menmob=$i;   // Monatsblatt
      if(strpos($menues[$i]['name'],'chenblatt')>0) $menwob=$i;   // Wochenblatt
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;   // Tagesblatt
      if(strpos($menues[$i]['name'],'raummenü')>0)  $menzrm=$i;   // Zeitraummenue
      endfor;
   #
   # --- heutiges Datum
   $heute=kal_termine_kalender::kal_heute();
   #
   # --- Ueberpruefung  der Werte von $mon und $jahr
   $mmm=intval($mon);
   $jjj=intval($jahr);
   if($mmm<=0 or $mmm>12 or $jjj<=0):
   #     $mon und $jahr werden aus dem aktuellen Datum entnommen
     $strmon=substr($heute,3,2);
     $strjahr=substr($heute,6);
     else:
     #     Formatierung der Jahreszahl
     $strmon=$mon;
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
    <tr><td align="right" class="kal_txt2">KW&nbsp;</td>';
   # --- Wochentage (2-Zeichen-Kuerzel)
   for($i=1;$i<=count($wt);$i=$i+1)
      $kopf=$kopf.'
        <td align="center" class="kal_txt1">'.$wt[$i].'</td>';
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
        $kwjahr=intval($strjahr);
        if($kw<=1 and $strmon>=12): $kwjahr=$kwjahr+1; endif;
        else:
        $datum=33-intval($tagfirst);
        $vorjahr=intval($strjahr)-1;
        $datum=$datum.'.12.'.$vorjahr;
        $kw=kal_termine_kalender::kal_montag_kw($datum);
        $kwjahr=$vorjahr;
        if($tagfirst<=4): $kwjahr=$strjahr; endif;
        endif;
      #
      # --- Link auf die Kalenderwoche
      $mod=$modus;
      if($mod<0) $mod=0;
      $title='Kalenderwoche '.$kw.' ('.$strjahr.')';
      $lp=KAL_KW.'='.$kw.'&'.KAL_JAHR.'='.$strjahr;
      $zeil=$zeil.'
        <td align="right" class="kal_txt2" title="'.$title.'">
            '.self::kal_link($lp.$param,$menwob,$kw.'&nbsp;',$mod).'
            </td>';
      #
      # --- Kalenderzellen
      for($k=1;$k<=count($wt);$k=$k+1):
         $tag=intval($tag)+1;
         if($tag<1 or $tag>$anztage):
           #
           # --- Tag vor oder nach dem Monat
           $strjahrvn=$strjahr;
           if($tag<1):
             #  -  Tag vor dem Monat
             $strmonvn=intval($mon)-1;
             if($strmonvn<1):
               $strmonvn=12;
               $strjahrvn=intval($strjahr)-1;
               endif;
             if(strlen($strmonvn)<2) $strmonvn='0'.$strmonvn;
             $tagevn=kal_termine_kalender::kal_monatstage($strjahrvn);
             $tagvn=$tagevn[intval($strmonvn)]+$tag;
             else:
             #  -  Tag nach dem Monat
             $strmonvn=intval($mon)+1;
             if($strmonvn>12):
               $strmonvn=1;
               $strjahrvn=intval($strjahr)+1;
               endif;
             if(strlen($strmonvn)<2) $strmonvn='0'.$strmonvn;
             $tagvn=intval($tag)-$anztage;
             endif;
           $datumvn=$tagvn;
           if(strlen($datumvn)<2) $datumvn='0'.$datumvn;
           $datumvn=$datumvn.'.'.$strmonvn.'.'.$strjahrvn;
           $temp='
        <td class="kal_tag kal_vntag" title="'.$datumvn.'">'.$tagvn.'</td>';
           else:
           #
           # --- Tag im Monat
           $datum=$tag;
           $schraff='';
           if($daterm[$tag]>0 and $modus>0) $schraff='id="hatch"';
           if(strlen($datum)<2) $datum='0'.$datum;
           $datum=$datum.'.'.$strmon.'.'.$strjahr;
           $feiertag=kal_termine_kalender::kal_datum_feiertag($datum);
           #  -  Link auf den Tag
           if($k<=6 and empty($feiertag)):
             $class='kal_tag kal_wotag';
             else:
             $class='kal_tag kal_sotag';
             endif;
           if($datum==$heute) $class='kal_tag kal_hetag';
           $title=$datum;
           if(!empty($feiertag)) $title=$title.' ('.$feiertag.')';
           $lp=KAL_DATUM.'='.$datum;
           if($modus>=0):
             $temp='
        <td '.$schraff.' class="'.$class.'" title="'.$title.'">
            '.self::kal_link($lp.$param,$mentab,$tag,$modus).'
            </td>';
             else:
             $schr='';
             if($datum==$seltag) $schr='id="hatch"';
             $temp='
        <td '.$schr.' class="'.$class.'" title="'.$title.'">
            '.self::kal_link($lp.$param,$menmom,$tag,$modus).'
            </td>';
             endif;
           endif;
         $zeil=$zeil.$temp;
         endfor;
      $zeil=$zeil.'</tr>';
      $zeilen=$zeilen.$zeil;
      if($tag>=$anztage) break;
      endfor;
   #
   # --- Monats- und Jahresschalter
   $ueber='
    <tr><td colspan="8" align="center">
            <table cellpadding="0" cellspacing="0" class="kal_table">';
   #  -  vorheriges Jahr
   $vjahr=intval($strjahr)-1;
   $vmon=intval($strmon);
   #  -  vorheriger Monat
   $vorjahr=$strjahr;
   $vormon=intval($strmon)-1;
   if($vormon<=0):
     $vormon=$vormon+12;
     $vorjahr=intval($vorjahr)-1;
     endif;
   $vormonat=$monate[$vormon];
   #
   # --- Link auf das Vorjahr und den Vormonat
   $lp=KAL_MONAT.'='.$vmon.'&'.KAL_JAHR.'='.$vjahr;
   $ueber=$ueber.'
                <tr><td class="kal_txtb1" title="'.$monat.' '.$vjahr.'">
                        '.self::kal_link($lp.$param,$menmom,'&laquo;&nbsp;',$modus).'
                    </td>';
   $lp=KAL_MONAT.'='.$vormon.'&'.KAL_JAHR.'='.$vorjahr;
   $ueber=$ueber.'
                    <td class="kal_txtb1" title="'.$vormonat.' '.$vorjahr.'">
                        '.self::kal_link($lp.$param,$menmom,'<small>&lt;</small>',$modus).'
                    </td>';
   #
   # ---  aktueller Monat (Ueberschrift, Link auf das Monatsblatt)
   if($modus>0 and (!rex::isBackend() or strpos($_SERVER['REQUEST_URI'],'?page='.PACKAGE)>0)):
     $mentit=$menues[$menmob]['name'];
     $lp=KAL_MONAT.'='.intval($strmon).'&'.KAL_JAHR.'='.$strjahr;
     $ueber=$ueber.'
                    <td align="center" class="kal_txt_titel" title="'.$mentit.' '.$monat.' '.$strjahr.'">
                        '.self::kal_link($lp.$param,$menmob,$monat.'&nbsp;'.$strjahr,$modus).'
                    </td>';
     else:
     $ueber=$ueber.'
                    <td align="center" class="kal_txt_titel">
                        '.$monat.'&nbsp;'.$strjahr.'</td>';
     endif;
   #  -  naechster Monat
   $nachjahr=$strjahr;
   $nachmon=intval($strmon)+1;
   if($nachmon>12):
     $nachmon=$nachmon-12;
     $nachjahr=intval($nachjahr)+1;
     endif;
   $nachmonat=$monate[$nachmon];
   #  -  naechstes Jahr
   $njahr=intval($strjahr)+1;
   $nmon=$strmon;
   #
   # --- Ueberschrift
   if(abs($modus)>0):
     #     Link auf den Folgemonat und das Folgejahr
     $lp=KAL_MONAT.'='.$nachmon.'&'.KAL_JAHR.'='.$nachjahr;
     $ueber=$ueber.'
                    <td class="kal_txtb1" title="'.$nachmonat.' '.$nachjahr.'">
                        '.self::kal_link($lp.$param,$menmom,'<small>&gt;</small>',$modus).'
                    </td>';
     $lp=KAL_MONAT.'='.intval($nmon).'&'.KAL_JAHR.'='.$njahr;
     $ueber=$ueber.'
                    <td class="kal_txtb1" title="'.$monat.' '.$njahr.'">
                        '.self::kal_link($lp.$param,$menmom,'&nbsp;&raquo;',$modus).'
                    </td></tr>
            </table></td></tr>';
     else:
     $ueber='
    <tr><td colspan="8" align="center" class="kal_txt_titel">'.$monat.'&nbsp;'.$strjahr.'</td></tr>';
     endif;
   #
   # --- Zusammenstellung Ausgabezeilen
   $str='
<table class="kal_border">'.$ueber.$kopf.$zeilen.'
</table>
';
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
   #                   falls =0/=SPIEL_KATID Termine aller Kategorien (Datenbank-/Spieldaten)
   #   $mon            Nummer des Monats eines Jahres im Format 'mm' oder 'm'
   #   $kw             Nummer der Woche (<=53) im Format 'ww' oder 'w'
   #                   falls die 53. Kalenderwoche nicht existiert, wird die
   #                   erste Kalenderwoche des Folgejahres angenommen
   #   $jahr           Kalenderjahr im Format 'yyyy' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   $datum          Datum im Format 'tt.mm.yyyy'' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #      ----- JEDES Zeitabschnittsblatt enthaelt einen Button-Link
   #         auf das zugehoerige Monatsmenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #            Linktext   «
   #            $men       Menuenummer des Monatsmenues
   #            $monat     Nummer des gewaehlten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #         auf alle Terminblaetter der angezeigten Termine mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_PID=$pid
   #            Linktext   Bezeichnung des Termins
   #            $men       Menuenummer des Terminblatts
   #            $datum     Datum des gewaehlten Tages
   #            $pid       Nummer des Termins in der Datenbanktabelle
   #      ----- Das Monatsblatt enthaelt ferner diesen Button-Link
   #         auf das zugehoerige Monatsfiltermenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr, KAL_SUCHEN=, KAL_VOHER=
   #            Linktext   Ueberschrift des Monatsblatts
   #            $men       Menuenummer des Monatsfiltermenues
   #            $monat     Nummer des gewaehlten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      ----- Das Wochenblatt enthaelt ferner diese Button-Links
   #         auf das zugehoerige Monatsblatt mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #            Linktext   <
   #            $men       Menuenummer des Monatsblatts
   #            $monat     Nummer des zugehoerigen Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #         auf das zugehoerige Wochenfiltermenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr, KAL_SUCHEN=, KAL_VORHER=
   #            Linktext   Ueberschrift des Wochenblatts
   #            $men       Menuenummer des Wochenfiltermenues
   #            $kw        Nummer der gewaehlten Kalenderwoche
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem die Woche gehoert
   #      ----- Das Tagesblatt enthaelt ferner diese Button-Links
   #         auf das zugehoerige Wochenblatt mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr
   #            Linktext   <
   #            $men       Menuenummer des Wochenblatts
   #            $kw        Nummer der zugehoerigen Kalenderwoche
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem die Woche gehoert
   #         auf das zugehoerige Tagesfiltermenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_DATUM=$datum, KAL_SUCHEN=, KAL_VORHER=
   #            Linktext   Ueberschrift des Tagesblatts
   #            $men       Menuenummer des Tagesfiltermenues
   #            $datum     Datum des gewaehlten Tages
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      self::kal_termin_titel($termin)
   #      self::kal_terminpixel($termin)
   #      self::kal_stundenleiste()
   #      kal_termine_config::kal_define_stundenleiste()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_feiertage($jahr)
   #      kal_termine_kalender::kal_monat_kw($datum)
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;   // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0) $menmob=$i;   // Monatsblatt
      if(strpos($menues[$i]['name'],'chenblatt')>0) $menwob=$i;   // Wochenblatt
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;   // Tagesblatt
      if(strpos($menues[$i]['name'],'natsfilter')>0) $menmfi=$i;   // Monatsfiltermenue
      if(strpos($menues[$i]['name'],'chenfilter')>0) $menwfi=$i;   // Wochenfiltermenue
      if(strpos($menues[$i]['name'],'gesfilter')>0)  $mentfi=$i;   // Tagesfiltermenue
      if(strpos($menues[$i]['name'],'erminblatt')>0)  $menteb=$i;   // Terminblatt
      endfor;
   #
   # --- mehrfach verwendete Daten
   $heute=kal_termine_kalender::kal_heute();
   $monate=kal_termine_kalender::kal_monate();   // alle Monatsnamen
   #
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
   #     --- Kalendermonat
   if(!empty($mon) and !empty($jahr)):
     $mtage=kal_termine_kalender::kal_monatstage($strjahr);
     $end=$mtage[intval($mon)];
     for($i=1;$i<=$end;$i=$i+1):
        $tag=$i;
        if(strlen($tag)<2) $tag='0'.$tag;
        $dat[$i]=$tag.'.'.$strmon.'.'.$strjahr;
        endfor;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[$end],$katid,1);
     endif;
   #     --- Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     #     Montag (Starttag) der Woche als Datum
     $dat[1]=kal_termine_kalender::kal_kw_montag($kw,$strjahr);
     #     restliche Tage der Woche als Datum
     for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[7],$katid,1);
     endif;
   #     --- Einzeldatum
   if(!empty($stdatum)):
     $dat[1]=$stdatum;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[1],$katid,1);
     endif;
   #
   # --- alle Feiertage
   $ft=kal_termine_kalender::kal_feiertage($strjahr);
   $feiertag=array();
   for($i=1;$i<=count($dat);$i=$i+1):
      $feiertag[$i]='';
      for($k=1;$k<=count($ft);$k=$k+1):
         if(substr($dat[$i],0,6)==substr($ft[$k][COL_DATUM],0,6)):
           $feiertag[$i]=$ft[$k][COL_NAME];
           break;
           endif;
         endfor;
      endfor;
   #
   # --- Ruecklinks, Ueberschrift inkl. Link auf das Terminmenue (Monat/Woche/Datum)
   $mentit='';
   $menrr=$menues[$menmom]['name'];  // Monatsmenue
   #     --- Kalendermonat
   if(!empty($mon) and !empty($jahr)):
     $menr='';
     #     Ruecklink auf den Startmonat
     $von=$dat[1];
     $bis=$dat[$mtage[intval($mon)]];
     $backmon=intval(substr($von,3,2));
     $backjahr=substr($von,6);
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink1='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,$menmom,'&laquo;',1);
     #     Ruecklink auf den Kalendermonat entfaellt hier
     $title2='';
     $ruecklink2='';
     #     Link auf die Terminliste (Monatsfiltermenue)
     $ueber='Termine im '.$monate[intval($mon)].' '.$strjahr;
     if(!rex::isBackend() or strpos($_SERVER['REQUEST_URI'],'?page='.PACKAGE)>0):
       $ttr=KAL_MONAT.'='.$strmon.'&'.KAL_JAHR.'='.$strjahr.'&'.KAL_KATEGORIE.'='.$katid.'&'.KAL_SUCHEN.'=&'.KAL_VORHER.'=';
       $ueber=self::kal_link($ttr,$menmfi,$ueber,1);
       $mentit=$menues[$menmfi]['titel'];  // Monatsfiltermenue
       endif;
     endif;
   #     --- Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     $menr=$menues[$menmob]['name'];  // Monatsblatt
     #     Ruecklink auf den Startmonat
     $mmm=kal_termine_kalender::kal_monat_kw($dat[1]);
     $backmon=$mmm['monat'];
     $backjahr=$mmm['jahr'];
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ttr=KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr;
     $ruecklink1='
            '.self::kal_link($ttr,$menmom,'&laquo;',1);
     #     Ruecklink auf den Kalendermonat
     $title2=$menr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink2='
            '.self::kal_link($ttr,$menmob,'<small>&lt;</small>',1);
     #     Link auf die Terminliste (Wochenfiltermenue)
     $ueber='Termine in der Kalenderwoche '.$strkw.' ('.$strjahr.')';
     if(!rex::isBackend() or strpos($_SERVER['REQUEST_URI'],'?page='.PACKAGE)>0):
       $ttr=KAL_KW.'='.$strkw.'&'.KAL_JAHR.'='.$strjahr.'&'.KAL_KATEGORIE.'='.$katid.'&'.KAL_SUCHEN.'=&'.KAL_VORHER.'=';
       $ueber=self::kal_link($ttr,$menwfi,$ueber,1);
       $mentit='title="'.$menues[$menwfi]['titel'].'"';  // Wochenfiltermenue
       endif;
     endif;
   #     --- Einzeltag
   if(!empty($stdatum)):
     $menr=$menues[$menwob]['name'];  // Wochenblatt
      #     Ruecklink auf den Startmonat
     $mmm=kal_termine_kalender::kal_monat_kw($stdatum);
     $backmon=$mmm['monat'];
     $backjahr=$mmm['jahr'];
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink1='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,$menmom,'&laquo;',1);
     #     Ruecklink auf die Kalenderwoche
     $strkw=kal_termine_kalender::kal_kw($stdatum);
     $title2=$menr.' Kalenderwoche '.$strkw.' ('.$backjahr.')';
     $ruecklink2='
            '.self::kal_link(KAL_KW.'='.$strkw.'&'.KAL_JAHR.'='.$backjahr,$menwob,'<small>&lt;</small>',1);
     #     Link auf die Terminliste (Tagesfiltermenue)
     $ueber='Termine am '.$stdatum;
     if(!empty($feiertag[1])) $ueber=$ueber.' &nbsp; ('.$feiertag[1].')';
     if(!rex::isBackend() or strpos($_SERVER['REQUEST_URI'],'?page='.PACKAGE)>0):
       $ttr=KAL_DATUM.'='.$stdatum.'&'.KAL_KATEGORIE.'='.$katid.'&'.KAL_SUCHEN.'=&'.KAL_VORHER.'=';
       $ueber=self::kal_link($ttr,$mentfi,$ueber,1);
       $mentit='title="'.$menues[$mentfi]['titel'].'"';  // Tagesfiltermenue
       endif;
     endif;
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
   for($i=1;$i<=count($dat);$i=$i+1):
      for($k=1;$k<=count($termin[$i]);$k=$k+1):
         #  -  Pixel-Breite des Termins
         $pixel=self::kal_terminpixel($termin[$i][$k]);
         $str='';
         if($pixel['vor']>0 and $pixel['dauer']>0):
           # --- div-Container (Streifen) mit zugemessener Position/Laenge und Termin-Link
           $title=self::kal_termin_titel($termin[$i][$k]);
           $param=KAL_DATUM.'='.$termin[$i][$k][COL_DATUM].'&'.KAL_PID.'='.$termin[$i][$k][COL_PID];
           $str=$str.'
            <div style="margin-left:'.$pixel['vor'].'px; width:'.$pixel['dauer'].'px;" title="'.$title.'" class="kal_termintag">
            '.self::kal_link($param,$menteb,$termin[$i][$k][COL_NAME],1);
           else:
           $str=$str.'
            <div>&nbsp;';
           endif;
         $str=$str.'</div>';
         $content[$i][$k]=$str;
         endfor;
      endfor;
   #
   # --- Ausgabe Ueberschriftszeile, Stundenleiste
   $string='
<table class="kal_border">
    <tr><td align="center" class="kal_txtb1" title="'.$title1.'">'.$ruecklink1.'</td>
        <td align="center" class="kal_txtb1" title="'.$title2.'">'.$ruecklink2.'</td>
        <td colspan="'.$colspan.'" class="kal_txt_titel" '.$mentit.'>
            '.$ueber.'</td>
        <td> </td></tr>
'.self::kal_stundenleiste();
   #
   # --- Ausgabe Tageszeilen
   $wtage=kal_termine_kalender::kal_wochentage();
   if(!empty($mon)) $mwoch=0;
   for($i=1;$i<=count($dat);$i=$i+1):
      $wt=kal_termine_kalender::kal_wotag($dat[$i]);
      $shdat=substr($dat[$i],0,6);
      $cont='';
      for($k=1;$k<=count($termin[$i]);$k=$k+1) $cont=$cont.$content[$i][$k];
      $title='title="'.$shdat.'"';
      if(!empty($feiertag[$i]) or $wt=='So'):
        if(!empty($feiertag[$i])) $title='title="'.$feiertag[$i].', '.$shdat.'"';
        $col='kal_strtag kal_somtag';
        else:
        $col='kal_strtag kal_womtag';
        endif;
      if($dat[$i]==$heute) $col='kal_strtag kal_hemtag';
      $string=$string.'
    <tr valign="top">
        <td class="kal_txt2" '.$title.'>'.$wt.',</td>
        <td align="right" class="kal_txtb2" '.$title.'>'.$shdat.'</td>
        <td colspan="'.$colspan.'" class="'.$col.'">'.$cont.'</td>
        <td> </td></tr>';
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
</table>';
   return $string;
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
     $title='ganztägig: &nbsp; ';
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
public static function kal_stundenleiste() {
   #   Rueckgabe einer Stundenleiste in Form des Codes einer HTML-Tabelle
   #   in der folgenden Form:
   #          9:00       11:00       13:00       15:00
   #     |     |     |     |     |     |     |     |     |
   #   Uhrzeiten im 2-Stunden-Abstand
   #   Striche im 1-Stunden-Abstand (CSS-Stil: border-left)
   #   benutzte functions:
   #      kal_termine_config::kal_define_stundenleiste()
   #
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   $sizeuhr=$enduhr-$stauhr;
   #
   # --- Uhrzeiten-Leiste
   $stunden='    <tr><td colspan="2"> </td>';
   for($k=$stauhr+1;$k<$enduhr;$k=$k+2):
      $stunden=$stunden.'
        <td colspan="2" class="kal_std2">'.$k.':00</td>';
      endfor;
   if(2*intval($sizeuhr/2)<$sizeuhr)
     $stunden=$stunden.'
        <td class="kal_std1">'.$enduhr.':</td>';
   $stunden=$stunden.'
        <td> </td></tr>';
   #
   # --- Stundenstrich-Leiste
   $stdlineal='
    <tr><td colspan="2"> </td>';
   for($k=$stauhr;$k<$enduhr;$k=$k+1):
      if($k<$enduhr-1):
        $stdlineal=$stdlineal.'
        <td class="kal_pix kal_pixn">&nbsp;</td>';
        else:
        $stdlineal=$stdlineal.'
        <td class="kal_pix kal_pixr">&nbsp;</td>';
        endif;
      endfor;
   $stdlineal=$stdlineal.'
        <td> </td></tr>';
   #
   return $stunden.$stdlineal;
   }
public static function kal_terminpixel($termin) {
   #   Rueckgabe von Pixel-Anzahlen, die eine halbgrafische Darstellung eines
   #   vorgegebenen Termins ermoeglichen, in Form eines assoziativen Arrays:
   #                   $pixel['vor']     Anzahl Pixel vor Beginn des Termins
   #                   $pixel['dauer']   Anzahl Pixel der Dauer des Termins
   #   Falls der Termin leer ist: $pixel['vor'] = $pixel['dauer'] = 0
   #   $termin         Daten eines Termins (Array)
   #   benutzte functions:
   #      self::kal_eval_start_ende($termin)
   #      kal_termine_config::kal_define_stundenleiste()
   #
   if(empty($termin[COL_NAME])) return array('vor'=>0, 'dauer'=>0);
   #
   # --- Bemassung der Stundenleiste
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $stdsiz=$daten[4];
   #  -  Anzahl Pixel pro Stunde/td-Zelle um 5 vergroessern:
   #        2 Pixel (padding-left:1px; padding-right:1px;)
   #        1 Pixel (border-width:1px)
   #        2 Pixel (border-spacing:2px)
   $stdsiz=$stdsiz+5;
   #
   # --- ggf Start-/Enduhrzeit sinnvoll ergaenzt
   $sez=self::kal_eval_start_ende($termin);
   $beginn=$sez[COL_BEGINN];
   $ende  =$sez[COL_ENDE];
   #
   # --- Laenge vor dem Termin in Anzahl Pixel
   $arr=explode(':',$beginn);
   $voruhr=intval($arr[0])+intval($arr[1])/60;
   $vor=$voruhr-$stauhr;
   $vor=intval($vor*$stdsiz);
   $vor=$vor-2;     // Feinkorrektur
   #
   # --- Laenge des Termins in Anzahl Pixel
   $arr=explode(':',$ende);
   $nachuhr=intval($arr[0])+intval($arr[1])/60;
   $dau=$nachuhr-$voruhr;
   $dau=intval($dau*$stdsiz);
   $dau=$dau+1;     // Feinkorrektur
   #
   return array('vor'=>$vor, 'dauer'=>$dau);
   }
public static function kal_eval_start_ende($termin) {
   #   Rueckgabe der Startuhrzeit und der Enduhrzeit eines Termins. Falls
   #   Start-/Enduhrzeit nicht definiert sind, werden sie sinnvoll ergaenzt.
   #   Rueckgabe als assoziatives Arrays (Start-/Enduhrzeit = [COL_BEGINN]/[COL_ENDE])
   #   - Falls der Terminbeginn leer ist:
   #                   der Beginn wird aus COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 abgeleitet
   #   - Falls der Terminbeginn und COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 leer sind:
   #                   es wird 'ganztaegig' angenommen und Anfang/Ende als
   #                   Startuhrzeit+eps/Enduhrzeit-eps berechnet (eps: siehe unten)
   #   - Falls das Terminende leer ist:
   #                   das Ende wird aus COL_ZEIT2/COL_ZEIT3/COL_ZEIT4/COL_ZEIT5 abgeleitet
   #   $termin         Daten eines Termins (Array)
   #   benutzte functions:
   #      kal_termine_config::kal_define_stundenleiste()
   #
   $eps=30;    // Minuten
   #
   $daten=kal_termine_config::kal_define_stundenleiste();
   $stauhr=$daten[1];
   $enduhr=$daten[2];
   #
   # --- Startzeit bei fehlendem Beginn eines Termins
   #     falls minz=min(COL_ZEIT2,COL_ZEIT3,COL_ZEIT4,COL_ZEIT5) nicht leer ist, 
   #     ersetze Beginn durch minz; falls auch minz leer ist, ersetze Startzeit
   #     durch $eps Minuten nach Startuhrzeit
   $beginn=$termin[COL_BEGINN];
   $begm=min($termin[COL_ZEIT2],$termin[COL_ZEIT3],$termin[COL_ZEIT4],$termin[COL_ZEIT5]);
   if(empty($beginn) and !empty($begm)) $beginn=$begm;
   if(empty($beginn)) $beginn=$stauhr.':'.$eps;
   #
   # --- Endzeit bei fehlendem Ende eines Termins
   #     falls maxz=max(COL_ZEIT2,COL_ZEIT3,COL_ZEIT4,COL_ZEIT5) nicht leer ist,
   #     ersetze Endzeit durch maxz + $eps; falls auch maxz leer ist, ersetze Endzeit
   #     durch $eps Minuten vor Enduhrzeit
   $ende=$termin[COL_ENDE];
   $endm=max($termin[COL_ZEIT2],$termin[COL_ZEIT3],$termin[COL_ZEIT4],$termin[COL_ZEIT5]);
   if(empty($ende) and !empty($endm)):
     $arr=explode(':',$endm);
     $std=$arr[0];
     $min=intval($arr[1]+$eps);
     if($min>59):
       $std=intval($std+1);
       $min=intval($min-60);
       endif;
     if(strlen($std)<=1) $std=' '.$std;
     if(strlen($min)<=1) $min='0'.$min;
     $ende=$std.':'.$min;
     endif;
   if(empty($ende) or intval($ende)>intval($enduhr)):
     $min=intval(60-$eps);
     if(strlen($min)<=1) $min='0'.$min;
     $anduhr=intval($enduhr-1);
     $ende=$anduhr.':'.$min;
     endif;
   #
   return array(COL_BEGINN=>$beginn, COL_ENDE=>$ende);
   }
#
#----------------------------------------- Termin-Auswahlmenue
public static function kal_tages_such_menue($katid,$datum,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Auswahl bzw. Suche von Terminen
   #   eines Tages in Form eines Formulars
   #   $katid          (vergl. unten)
   #   $datum          Datum im Format 'tt.mm.yyyy''
   #   $kid            (vergl. unten)
   #   $suchen         (vergl. unten)
   #   $vorher         (vergl. unten)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_such($katid,$param,$kid,$suchen,$vorher)
   #      kal_termine_kalender::kal_heute()
   #
   if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $men=$i;   // Tagesblatt
      if(strpos($menues[$i]['name'],'gesfilter')>0) $menue=$i; // Tagesfiltermenue
      endfor;
   $param[0]=$datum;
   $param[1]=$datum;
   $param[2]='name="'.KAL_DATUM.'" value="'.$datum.'"';
   $param[3]='';
   $param[4]=substr($datum,6);
   $param[5]=substr($datum,3,2);
   $param[6]=KAL_DATUM.'='.$datum;
   $param[7]=$men;
   $param[8]=$menue;
   $param[9]=$datum;
   return self::kal_such($katid,$param,$kid,$suchen,$vorher);
   }
public static function kal_wochen_such_menue($katid,$kw,$jahr,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Auswahl bzw. Suche von Terminen
   #   einer Woche in Form eines Formulars
   #   $katid          (vergl. unten)
   #   $kw             Nummer der Woche
   #   $jahr           Kalenderjahr
   #   $kid            (vergl. unten)
   #   $suchen         (vergl. unten)
   #   $vorher         (vergl. unten)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_such($katid,$param,$kid,$suchen,$vorher)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_monat_kw($datum)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   if(empty($kw) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     endif;
   $von=kal_termine_kalender::kal_kw_montag($kw,$jahr);
   $mmm=kal_termine_kalender::kal_monat_kw($von);
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'chenblatt')>0)  $men=$i;   // Wochenblatt
      if(strpos($menues[$i]['name'],'chenfilter')>0) $menue=$i; // Wochenfiltermenue
      endfor;
   $param[0]=$von;
   $param[1]=kal_termine_kalender::kal_datum_vor_nach($von,6);
   $param[2]='name="'.KAL_KW.'" value="'.$kw.'"';
   $param[3]='name="'.KAL_JAHR.'" value="'.$jahr.'"';
   $param[4]=$mmm['jahr'];
   $param[5]=$mmm['monat'];
   $param[6]=KAL_KW.'='.$kw.'&'.KAL_JAHR.'='.$jahr;
   $param[7]=$men;
   $param[8]=$menue;
   $param[9]='Kalenderwoche '.$kw.' ('.$jahr.')';
   return self::kal_such($katid,$param,$kid,$suchen,$vorher);
   }
public static function kal_monats_such_menue($katid,$mon,$jahr,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Auswahl bzw. Suche von Terminen
   #   eines Monats in Form eines Formulars
   #   $katid          (vergl. unten)
   #   $mon            Nummer des Monats eines Jahres
   #   $jahr           Kalenderjahr
   #   $kid            (vergl. unten)
   #   $suchen         (vergl. unten)
   #   $vorher         (vergl. unten)
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_such($katid,$param,$kid,$suchen,$vorher)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #
   if(empty($mon) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $mon=intval(substr($heute,3,2));
     $jahr=intval(substr($heute,6));
     endif;
   if(strlen($mon)<2) $mon='0'.$mon;
   $mtage=kal_termine_kalender::kal_monatstage($jahr);
   $mo=intval($mon);
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsblatt')>0)  $men=$i;   // Monatsblatt
      if(strpos($menues[$i]['name'],'natsfilter')>0) $menue=$i; // Monatsfiltermenue
      endfor;
   $param[0]='01.'.$mon.'.'.$jahr;
   $param[1]=$mtage[$mo].'.'.$mon.'.'.$jahr;
   $param[2]='name="'.KAL_MONAT.'" value="'.$mo.'"';
   $param[3]='name="'.KAL_JAHR.'" value="'.$jahr.'"';
   $param[4]=$jahr;
   $param[5]=$mo;
   $param[6]=KAL_MONAT.'='.$mo.'&'.KAL_JAHR.'='.$jahr;
   $param[7]=$men;
   $param[8]=$menue;
   $param[9]=$monate[$mo].' '.$jahr;
   return self::kal_such($katid,$param,$kid,$suchen,$vorher);
   }
public static function kal_such_daten($datum,$anztage,$monat,$jahr) {
   #   Rueckgabe eines HTML-Codes zur Auswahl eines Zeitraums fuer eine
   #   Terminsuche (Startdatum und Dauer). Das Datum wird ueber Monatsmenues
   #   bestimmt. Das Auswahlformular liefert Startdatum und Anzahl Tage als
   #   Wert der Parameter KAL_DATUM2 bzw. KAL_ANZTAGE.
   #   $datum          vorgeschlagenes Startdatum
   #   $anztage        vorgeschlagene Dauer (in Tagen)
   #   $monat          Monatsnummer des vorgeschlagenen Start-/Enddatums
   #   $jahr           Kalenderjahr des vorgeschlagenen Start-/Enddatums
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_monatsmenue_modus($katid,$monat,$jahr,$tag,$modus)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'raumfilter')>0)  $men=$i; // Zeitraumfiltermenue
   #
   # --- Bestimmung der abgeleiteten Datumsparameter
   $dat=$datum;
   if(empty($dat)) $dat=kal_termine_kalender::kal_heute();
   if(!empty($monat) and !empty($jahr)):
     $mon=$monat;
     if(strlen($mon)<2) $mon='0'.$mon;
     $jah=$jahr;
     $dat='01.'.$mon.'.'.$jah;
     else:
     $dat=kal_termine_kalender::kal_standard_datum($dat);
     $mon=substr($dat,3,2);
     $jah=substr($dat,6);
     endif;
   #
   # --- Dauer: Default 365 Tage
   $defanzt=365;
   $anzt=$anztage;
   if($anzt<=0) $anzt=$defanzt;
   #
   # --- Ausgabe des Formulars
   $tit='Auswahl bestätigen';
   return '
<table class="kal_border">
    <tr valign="top">
        <td class="kal_config_indent" colspan="3" align="center">
            <h4>Suche von Terminen in einem definierten Zeitraum</h4></td></tr>
    <tr valign="top">
        <td class="kal_config_indent">
            <u>Beginn des Zeitraums:</u><br/>
            Auswahl schraffiert<br/>
            (Default: heutiger Tag)</td>
        <td class="kal_config_indent">'.
        self::kal_monatsmenue_modus(0,$mon,$jah,$dat,-$anzt).'
        </td>
        <td class="kal_config_indent"></td></tr>
    <tr valign="top">
        <td class="kal_config_indent"><br/>
            <u>Dauer des Zeitraums:</u></td>
        <td class="kal_config_indent"><br/>
            <form style="display:inline;" method="post" onsubmit="">
            <input type="text" class="kal_form_input_int" name="'.KAL_ANZTAGE.'" value="'.$anzt.'" />
             &nbsp; Tage &nbsp; (Default: '.$defanzt.' Tage)</td>
        <td class="kal_config_indent"></td></tr>
    <tr valign="top">
        <td></td>
        <td class="kal_config_indent" title="'.$tit.'"><br/>
            <input type="hidden" name="'.KAL_DATUM2.'" value="'.$dat.'" />
            <input type="hidden" name="'.KAL_MENUE.'"  value="'.$men.'"  />
            <button type="submit" class="kal_form kal_submit kal_linkbutton">'.$tit.'</button>
            </form><br/>&nbsp;</td>
        <td class="kal_config_indent"></td></tr>
</table>';
   }
public static function kal_such_menue($katid,$datum1,$anztage,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Auswahl und Filterung von Terminen
   #   eines Zeitraums und einer Kategorie bzw. aller Kategorien in Form eines Formulars
   #   $katid          (vergl. unten) Id der vorgegebenen Kategorie(n)
   #   $datum1         Startdatum des Zeitraums
   #   $anztage        Anzahl der Tage des Zeitraums
   #   $kid            (vergl. unten) Id der Filter-Kategorie
   #   $suchen         (vergl. unten) Filter-Suchbegriff
   #   $vorher         (vergl. unten) Filter-Checkbox-Wert
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_such($katid,$param,$kid,$suchen,$vorher)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #
   $dat1=$datum1;
   if(empty($dat1)) $dat1=kal_termine_kalender::kal_heute();
   $anzt=$anztage;
   if($anzt<=0) $anzt=365;
   $dat2=kal_termine_kalender::kal_datum_vor_nach($dat1,intval($anzt-1));
   $jahr=substr($dat1,6);
   $mon=substr($dat1,3,2);
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'raummenü')>0)   $men=$i;   // Zeitraummenue
      if(strpos($menues[$i]['name'],'raumfilter')>0) $menue=$i; // Zeitraumfiltermenue
      endfor;
   $param[0]=$dat1;
   $param[1]=$dat2;
   $param[2]='name="'.KAL_DATUM2.'" value="'.$dat1.'"';
   $param[3]='name="'.KAL_ANZTAGE.'" value="'.$anzt.'"';
   $param[4]=$jahr;
   $param[5]=intval($mon);
   $param[6]=KAL_MONAT.'='.intval($mon).'&'.KAL_JAHR.'='.$jahr;
   $param[7]=$men;
   $param[8]=$menue;
   $param[9]=$dat1.' - '.$dat2;
   return self::kal_such($katid,$param,$kid,$suchen,$vorher);
   }
public static function kal_such($katid,$param,$kid,$suchen,$vorher) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Filterung gegebener Termine
   #   in Form eines Formulars. Die Termine ...
   #   ... liegen in einem vorgegebenen Zeitraum und
   #   ... gehören zu einer vorgegebenen Kategorie (bzw. zu allen Kategorien).
   #   Der vorgegebene Zeitraum ist beliebig, umfasst im Spezialfall aber auch
   #   einen Tag, eine Woche oder einen Monat und wird durch die Parameter $katid
   #   und $param definiert. Die Filterbedingungen sind diese: Alle Termine ...
   #   ... gehoeren zu einer bestimmten Kategorie bzw. zu allen Kategorien und
   #   ... enthalten einen Suchbegriff oder keinen und
   #   ... liegen in der Zukunft (inkl. heutiger Tag) oder auch vorher
   #   Die Bedingungen werden ueber die Parameter $kid, $suchen, $vorher definiert
   #   und gelten zugleich.
   #   $katid          vorgegebene Kategorie-Id bzw. =0/SPIEL_KATID (alle Kategorien,
   #                   Datenbank-/Spieldaten)
   #   $param          nummeriertes Array von Parametern:
   #              [0]  Startdatum des vorgegebenen Zeitraums
   #              [1]  Enddatum des vorgegebenen Zeitraums
   #              [2]  erster hidden-Parameter
   #              [3]  zweiter hidden-Parameter
   #              [4]  Jahr des Monats fuer den ersten Ruecklink auf das Monatsmenue
   #              [5]  Monat fuer den ersten Ruecklink auf das Monatsmenue
   #              [6]  Parameter fuer den zweiten Ruecklink auf das Zeitraummenue
   #              [7]  Menuenummer fuer das Zeitraummenue
   #              [8]  Menuenummer fuer das Zeitraumfiltermenue
   #              [9]  Ueberschrift fuer das Auswahlmenue
   #   $kid            Id der im Menue ausgewaehlten Kategorie
   #                   (=$katid, falls $katid>0 bzw. $katid>SPIEL_KATID) 
   #   $suchen         im Menue eingegebener Suchbegriff (unabhaengig von
   #                   Gross-/Kleinschreibung), der Begriff wird in diesen
   #                   Termin-Parametern gesucht:
   #                      [COL_NAME], [COL_KOMM], [COL_AUSRICHTER], [COL_ORT]
   #                      [COL_TEXT2], [COL_TEXT3], [COL_TEXT4], [COL_TEXT5]
   #   $vorher         Wert der im Menue markierten Checkbox
   #                   ='':    nur Termine ab dem heutigen Tag
   #                   ='on':  auch abgelaufene Termine
   #   Das Auswahlformular liefert diese Parameter samt Werten:
   #      KAL_KATEGORIE  gemaess $kid
   #      KAL_SUCHEN     gemaess $suchen
   #      KAL_VORHER     gemaess $vorher
   #   und die 'hidden' Parameter samt Werten:
   #      KAL_DATUM              (bei Tagesterminen)
   #      KAL_KW/KAL_JAHR        (bei Wochenterminen)
   #      KAL_MONAT/KAL_JAHR     (bei Monatsterminen)
   #      KAL_DATUM2/KAL_ANZTAGE (bei Zeitraumterminen)
   #   Jedes Tages-/Wochen-/Monatsfiltermenue enthaelt diese beiden Button-Links:
   #      1) auf das zugehoerige Monatsmenue mit den POST-Parametern:
   #      KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #         Linktext  «
   #         $men      Menuenummer des Monatsmenues
   #         $monat    Nummer des gewaehlten Monats
   #         $jahr     4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      2) auf das zugehoerige Tages-, Wochen-, Monatsblatt der angezeigten
   #      Termine mit den POST-Parametern:
   #      KAL_MENUE=$men, KAL_DATUM=$datum                 (Tagesblatt)  bzw.
   #      KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr       (Wochenblatt) bzw.
   #      KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr (Monatsblatt)
   #         Linktext  <
   #         $men      Menuenummer des Tages-/Wochen-/Monatsblatts
   #         $datum    Datum des angezeigten Tages
   #         $kw       Nummer der angezeigten Woche
   #         $monat    Nummer des angezeigten Monats
   #         $jahr     4-stellige Jahreszahl des Jahres, zur Woche bzw. zum Monat
   #   Jedes Zeitraumfiltermenue enthaelt diese beiden Button-Links:
   #      1) auf das zugehoerige Monatsmenue mit den POST-Parametern:
   #      KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #         Linktext  «
   #         $men      Menuenummer des Monatsmenues
   #         $monat    Nummer des gewaehlten Monats
   #         $jahr     4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      2) auf das Zeitraummenue mit den POST-Parametern:
   #      KAL_MENUE=$men, KAL_DARUM2=$datum2, KAL_ANZTAGE=$anztage
   #         Linktext  <
   #         $men      Menuenummer des Zeitraummenues
   #         $datum2   Startdatum des Zeitraums
   #         $anztage  Anzahl der Tage des Zeitraums
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_datumsdifferenz($von,$bis)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #      kal_termine_tabelle::kal_get_spielkategorien()
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #      kal_termine_formulare::kal_terminliste($termin)
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;   // Monatsmenue
      if(strpos($menues[$i]['name'],'raummenü')>0) $menzrm=$i;   // Zeitraummenue
      endfor;
   #
   # --- Parameter
   $von       =$param[0];
   $bis       =$param[1];
   $hidden1   =$param[2];
   $hidden2   =$param[3];
   $startjahr =$param[4];
   $startmonat=$param[5];
   $par2      =$param[6];
   $men       =$param[7];
   $menue     =$param[8];
   $ueber     =$param[9];
   $anztage=kal_termine_kalender::kal_datumsdifferenz($von,$bis)+1;
   $linkpar=KAL_DATUM2.'='.$von.'&'.KAL_ANZTAGE.'='.$anztage;
   #
   # --- Auslesen der Termine
   $termin=kal_termine_tabelle::kal_get_termine($von,$bis,$katid,0);
   $nztermin=count($termin);
   #
   # --- alle Terminkategoriebezeichnungen
   if($katid>=SPIEL_KATID):
     $kate=kal_termine_tabelle::kal_get_spielkategorien();
     else:
     $kate=kal_termine_config::kal_get_terminkategorien();
     endif;
   #
   # --- Ausgaben
   $monate=kal_termine_kalender::kal_monate();
   $string='<table class="kal_border">';
   #
   # --- Ruecklinks
   $string=$string.'
    <tr valign="top">
        <td rowspan="2" class="kal_search kal_search_th">';
   $startit=$menues[$menmom]['name'].' '.$monate[intval($startmonat)].' '.$startjahr;
   if($men==$menzrm):
     #     zum Zeitraummenue
     $string=$string.'
            <table class="kal_transparent"><tr>
            <td title="'.$startit.'">
            '.self::kal_link(KAL_MONAT.'='.$startmonat.'&'.KAL_JAHR.'='.$startjahr,$menmom,'&laquo;&nbsp;&nbsp;',1).'</td>
            <td title="Festlegung eines anderen Zeitraums">
            '.self::kal_link($linkpar,$menzrm,'&nbsp;<small>&lt;</small>',1).'</td>
            </tr></table>
            </td>';
     else:
     #     zum Monatsmenue und zum Tages-/Wochen-/Monatsblatt
     $backtit=$menues[$men]['name'].' '.$ueber;
     $string=$string.'
            <table class="kal_transparent"><tr>
            <td title="'.$startit.'">
            '.self::kal_link(KAL_MONAT.'='.$startmonat.'&'.KAL_JAHR.'='.$startjahr,$menmom,'&laquo;&nbsp;&nbsp;',1).'</td>
            <td title="'.$backtit.'">
            '.self::kal_link($par2,$men,'&nbsp;<small>&lt;</small>',1).'</td>
            </tr></table>
            </td>';
     endif;
   #
   # --- Ueberschrift, Formularanfang, hidden-Parameter
   $hid='
            <input type="hidden" '.$hidden1.' />';
   if(!empty($hidden2)) $hid=$hid.'
            <input type="hidden" '.$hidden2.' />';
   $linkueber='
            <span title="Festlegung eines anderen Zeitraums">
            '.self::kal_link($linkpar,$menzrm,$ueber,1).'</span';
   $string=$string.'
        <td colspan="4" class="kal_search kal_search_td">
            <b>Anzeige und Filterung der Termine: &nbsp; '.$linkueber.'</b>
            <form method="post">'.$hid.'            
            <input type="hidden" name="'.KAL_MENUE.'" value="'.$menue.'" />
            </td></tr>';
   #
   # --- Auswahlmaske: Kategorien
   $string=$string.'
    <tr><td class="kal_search kal_search_td">Kategorie:<br/>
            <select name="'.KAL_KATEGORIE.'" class="kal_option kal_select">';
   if($katid==0 or $katid==SPIEL_KATID):
     #     alle Kategorien (gewaehlt: $kid)
     $val0=0;
     if($katid==SPIEL_KATID) $val0=SPIEL_KATID;
     $string=$string.'
                <option value="'.$val0.'" class="kal_option kal_select" selected="selected"></option>';
     for($i=0;$i<count($kate);$i=$i+1):
        if($kate[$i]['id']==$kid):
          $sel='class="kal_option kal_select" selected="selected"';
          else:
          $sel='class="kal_option"';
          endif;
        $option='
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
        $string=$string.$option;
        endfor;
     else:
     #     genau eine Kategorie (immer: $katid)
     for($i=0;$i<count($kate);$i=$i+1)
        if($kate[$i]['id']==$katid):
          $sel='class="kal_option kal_select" selected="selected"';
          $string=$string.'
                <option value="'.$kate[$i]['id'].'" '.$sel.'>'.$kate[$i]['name'].'</option>';
          break;
          endif;
     endif;
   $string=$string.'
            </select></td>';
   #
   # --- Auswahlmaske: Stichwort
   $string=$string.'
        <td class="kal_search kal_search_td">enthaltene Zeichenfolge:<br/>
            <input name="'.KAL_SUCHEN.'" type="text" value="'.$suchen.'" class="kal_form kal_input" /></td>';
   #
   # --- Auswahlmaske: auch abgelaufene Termine beruecksichtigen
   if($vorher=='1' or strtolower($vorher)=='on'):
     $chk='checked="checked"';
     else:
     $chk='';
     endif;
   $string=$string.'
        <td class="kal_search kal_search_td">abgelaufene Termine:<br/>
            auch anzeigen &nbsp; <input type="checkbox" name="'.KAL_VORHER.'" '.$chk.' /></td>';
   #
   # --- Auswahlmaske: Submit-Button
   $tit='Termine suchen';
   $string=$string.'
        <td class="kal_search kal_search_td" title="'.$tit.'"><br/>
            <button type="submit" class="kal_form kal_submit kal_linkbutton">'.$tit.'</button>
            </form></td></tr>
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
   if(empty($vorher)):
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
<div align="left"><br/>';
   if($nztermin<=0):
     $string=$string.'+++++ keine entsprechenden Termine gefunden</div>';
     else:
     $ter='Termine';
     if($nztermin==1) $ter='Termin';
     $string=$string.'<u>'.$nztermin.' '.$ter.' gefunden:</u></div>'.
        kal_termine_formulare::kal_terminliste($termin);
     endif;
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
   #      self::kal_monatsmenue($katid,$mon,$jahr)
   #      self::kal_monatsblatt($katid,$monat,$jahr)
   #      self::kal_wochenblatt($katid,$kw,$jahr)
   #      self::kal_tagesblatt($katid,$datum)
   #      self::kal_monats_such_menue($katid,$mon,$jahr,$suchen,$vorher)
   #      self::kal_wochen_such_menue($katid,$kw,$jahr,$suchen,$vorher)
   #      self::kal_tages_such_menue($katid,$datum,$suchen,$vorher)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_tabelle::kal_get_termine($von,$bis,$katid,$kontif)
   #      kal_termine_formulare::kal_terminblatt($termin,$datum,$ruecklinks)
   #
   # --- Menuenummern
   $menues=self::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0) $menmom=$i;    // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0) $menmob=$i;   // Monatsblatt
      if(strpos($menues[$i]['name'],'chenblatt')>0) $menwob=$i;   // Wochenblatt
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;   // Tagesblatt
      if(strpos($menues[$i]['name'],'natsfilter')>0) $menmfi=$i;  // Monatsfiltermenue
      if(strpos($menues[$i]['name'],'chenfilter')>0) $menwfi=$i;  // Wochenfiltermenue
      if(strpos($menues[$i]['name'],'gesfilter')>0)  $mentfi=$i;  // Tagesfiltermenue
      if(strpos($menues[$i]['name'],'raumfilter')>0) $menzfi=$i;  // Zeitraumfiltermenue
      if(strpos($menues[$i]['name'],'erminblatt')>0)  $menteb=$i; // Terminblatt
      if(strpos($menues[$i]['name'],'raummenü')>0)   $menzrm=$i;  // Zeitraummenue
      endfor;
   #
   # --- POST-Parameter auslesen
   $monat  ='';
   $kw     ='';
   $jahr   ='';
   $datum  ='';
   $datum2 ='';
   $anztage='';
   $modus  ='';
   $kid    ='';
   $suchen ='';
   $vorher ='';
   $men    ='';
   $pid    ='';
   if(!empty($_POST[KAL_MONAT]))     $monat  =$_POST[KAL_MONAT];
   if(!empty($_POST[KAL_KW]))        $kw     =$_POST[KAL_KW];
   if(!empty($_POST[KAL_JAHR]))      $jahr   =$_POST[KAL_JAHR];
   if(!empty($_POST[KAL_DATUM]))     $datum  =$_POST[KAL_DATUM];
   if(!empty($_POST[KAL_DATUM2]))    $datum2 =$_POST[KAL_DATUM2];
   if(!empty($_POST[KAL_ANZTAGE]))   $anztage=$_POST[KAL_ANZTAGE];
   if(!empty($_POST[KAL_MODUS]))     $modus  =$_POST[KAL_MODUS];
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
   if($men==$menmom and empty($modus))
     return self::kal_monatsmenue($kid,$monat,$jahr);
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
   # --- Monatsfiltermenue
   if($men==$menmfi)
     return self::kal_monats_such_menue($katid,$monat,$jahr,$kid,$suchen,$vorher);
   #
   # --- Wochenfiltermenue
   if($men==$menwfi)
     return self::kal_wochen_such_menue($katid,$kw,$jahr,$kid,$suchen,$vorher);
   #
   # --- Tagesfiltermenue
   if($men==$mentfi)
     return self::kal_tages_such_menue($katid,$datum,$kid,$suchen,$vorher);
   #
   # --- Terminblatt
   if($men==$menteb):
     $termin=array();
     $term=kal_termine_tabelle::kal_get_termine($datum,$datum,$kid,0);
     for($i=1;$i<=count($term);$i=$i+1)
        if($term[$i][COL_PID]==$pid) $termin=$term[$i];
     return kal_termine_formulare::kal_terminblatt($termin,$datum,1);
     endif;
   #
   # --- Zeitraummenue
   if($men==$menzrm or ($men==$menmom and $modus<0)):
     if(!empty($datum2)) $datum=$datum2;
     if($modus<0 and $anztage<=0) $anztage=-$modus;
     return self::kal_such_daten($datum,$anztage,$monat,$jahr);
     endif;
   #
   # --- Zeitraumfiltermenue
   if($men==$menzfi)
     return self::kal_such_menue($katid,$datum2,$anztage,$kid,$suchen,$vorher);
   }
}
?>