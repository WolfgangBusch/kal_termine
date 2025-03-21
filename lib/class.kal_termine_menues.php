<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
#
class kal_termine_menues {
#
#----------------------------------------- Methoden
#   Basismethoden
#      kal_blaettern_basismonat($datum)
#      kal_blaettern_uebersicht($modus)
#      kal_blaettern_jahre($mon,$jahr,$kont)
#      kal_blaettern_monate($mon,$jahr,$kont)
#      kal_blaettern_wochen($kw,$jahr,$kont)
#      kal_blaettern_tage($datum,$kont)
#      kal_terminblatt($termin)
#      kal_neuer_termin($kid,$datum)
#   Monatsmenue
#      kal_monatsmenue($kid,$mon,$jahr,$modus)
#   Monats-/Wochen-/Tagesblatt
#      kal_stundenleiste()
#      kal_eval_start_ende($termin)
#      kal_termin_poslen($termin)
#      kal_termin_titel($termin)
#      kal_terminfeld($termin,$class)
#      kal_mowotablatt($kid,$mon,$kw,$jahr,$datum)
#      kal_monatsblatt($kid,$mon,$jahr)
#      kal_wochenblatt($kid,$kw,$jahr)
#      kal_tagesblatt($kid,$datum)
#   Terminuebersicht
#      kal_termin_uebersicht_intern($termin)
#      kal_termin_uebersicht($selkid)
#   Menuewechsel
#      kal_menue($selkid,$mennr)
#      kal_spielmenue()
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Basismethoden
public static function kal_blaettern_basismonat($datum) {
   #   Zurueck blaettern auf den Monat (Monatsmenue), der ein vorgegebenes
   #   Datum enthaelt.
   #   $datum          vorgegebenes Datum im Format tt.mm.jjjj
   #                      POST-Parameter: $addon::KAL_MENUE
   #                                      $addon::KAL_MONAT
   #                                      $addon::KAL_JAHR
   #
   $addon=self::this_addon;
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
   #
   $mon=substr($datum,3,2);
   $jahr=substr($datum,6);
   $linktext='<span class="kal_icon">'.$addon::AWE_MONAT.'</span>';
   #
   $title=$monate[intval($mon)].' '.$jahr;
   $par=array($addon::KAL_MONAT=>$mon, $addon::KAL_JAHR=>$jahr, $addon::KAL_MENUE=>$menmom);
   return $addon::kal_link($par,$linktext,$title,$lnkmod);
   }
public static function kal_blaettern_uebersicht($modus) {
   #   Zurueck blaettern auf die Terminuebersicht (Linktext mit modus=2).
   #   $modus          Parameter (int) zur Konstruktion des Links und zur
   #                   Darstellung des Linktextes (verl. kal_link)
   #                      POST-Parameter: $addon::KAL_MENUE
   #
   $addon=self::this_addon;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'erminüber')>0)  $menueb=$i;  // Terminuebersicht
   $heute=kal_termine_kalender::kal_heute();
   #
   $lnkmod=$modus;
   $linktext='<span class="kal_icon">'.$addon::AWE_UEBERSICHT.'</span>';
   $par=array($addon::KAL_ANKER=>$heute, $addon::KAL_MENUE=>$menueb);
   return $addon::kal_link($par,$linktext,'Terminübersicht',$lnkmod);
   }
public static function kal_blaettern_jahre($mon,$jahr,$kont) {
   #   Das aktuelle Menue um ein Jahr weiterblaettern (Monatsmenue).
   #   $mon            Nummer des Monats
   #   $jahr           Jahreszahl
   #   $kont           fuer das Monatsmenue, Parameter:  kal_link  hier
   #                      =-2: 12 Mon. Blaettern zum Vorjahr
   #                           Linktext: AWE_VORJAHR     modus=4   $modus=2
   #                      = 2: 12 Mon. Blaettern zum Folgejahr
   #                           Linktext: AWE_NACHJAHR    modus=4   $modus=2
   #                      =-1: 12 Mon. Blaettern zum Vorjahr
   #                           Linktext: AWE_VORJAHR     modus=4   $modus=1
   #                      = 1: 12 Mon. Blaettern zum Folgejahr
   #                           Linktext: AWE_NACHJAHR    modus=4   $modus=1
   #                      POST-Parameter: $addon::KAL_MENUE
   #                                      $addon::KAL_MONAT
   #                                      $addon::KAL_JAHR
   #
   if(empty($jahr)) return;
   $addon=self::this_addon;
   #
   $lnkmod=4;
   $monate=kal_termine_kalender::kal_monate();
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
   $monneu=$mon;
   #
   # --- vorheriges Jahr
   if($kont<=-1):
     $jahrneu=$jahr-1;
     $linktext='<span class="kal_icon">'.$addon::AWE_VORJAHR.'</span>';
     endif;
   #
   # --- folgendes Jahr
   if($kont>=1):
     $jahrneu=$jahr+1;
     $linktext='<span class="kal_icon">'.$addon::AWE_NACHJAHR.'</span>';
     endif;
   #
   # --- weitere Linkparameter
   $title=$monate[intval($monneu)].' '.$jahrneu;
   if(abs($kont)<=1):
     $par=array($addon::KAL_MONAT=>$monneu, $addon::KAL_MODUS=>1, $addon::KAL_JAHR=>$jahrneu, $addon::KAL_MENUE=>$menmom);
     else:
     $par=array($addon::KAL_MONAT=>$monneu, $addon::KAL_JAHR=>$jahrneu, $addon::KAL_MENUE=>$menmom);
     endif;
   return $addon::kal_link($par,$linktext,$title,$lnkmod);
   }
public static function kal_blaettern_monate($mon,$jahr,$kont) {
   #   Das aktuelle Menue um einen Monat weiterblaettern.
   #   $mon            Nummer des Monats
   #   $jahr           Jahreszahl
   #   $kont           fuer das Monatsmenue ($modus=2):
   #                      =-2: Blaettern zum Vormonat
   #                           Linktext: AWE_VORHER              modus=4
   #                      = 4: Blaettern zum aktuellen Monatsblatt
   #                           Linktext: Monatsname $jahr        modus=5
   #                      = 2: Blaettern zum Folgemonat
   #                           Linktext: AWE_NACHHER             modus=4
   #                   fuer das Monatsmenue ($modus=1):
   #                      =-1: Blaettern zum Vormonat
   #                           Linktext: AWE_VORHER              modus=4
   #                      = 1: Blaettern zum Folgemonat
   #                           Linktext: AWE_NACHHER             modus=4
   #                   fuer das Monatsblatt:
   #                      =-3: Blaettern zum vorherigen Monat
   #                           Linktext: AWE_VORHER              modus=2
   #                      = 0: Ueberschrift ueber dem Monat
   #                           Linktext: (Ueberschrift)          modus=-1 (kein Link)
   #                      = 3: Blaettern zum folgenden Monat
   #                           Linktext: AWE_NACHHER             modus=2
   #                      POST-Parameter: $addon::KAL_MENUE
   #                                      $addon::KAL_MONAT
   #                                      $addon::KAL_JAHR
   #
   if(empty($mon) or empty($jahr)) return;
   $addon=self::this_addon;
   #
   $lnkmod=2;
   $monate=kal_termine_kalender::kal_monate();
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0)  $menmob=$i;  // Monatsblatt
      endfor;
   $jahrneu=$jahr;
   #
   # --- vorheriger/folgender Monat (Monatsmenue)
   if(abs($kont)==2) $lnkmod=4;
   #
   # --- vorheriger Monat (Monatsblatt oder Monatsmenue)
   if($kont==-3 or $kont==-2 or $kont==-1):
     $monneu=intval($mon)-1;
     if($monneu<=0):
       $monneu=$monneu+12;
       $jahrneu=$jahr-1;
       endif;
     $linktext='<span class="kal_icon">'.$addon::AWE_VORHER.'</span>';
     endif;
   #
   # --- aktueller Monat Monatsmenue/Monatsblatt
   if($kont==0 or $kont==4):
     $monneu=$mon;
     $jahrneu=$jahr;
     $linktext=$monate[intval($monneu)].'&nbsp;'.$jahr;
     if($kont==0) $lnkmod=-1;
     if($kont==4) $lnkmod=5;
     endif;
   #
   # --- folgender Monat (Monatsblatt oder Monatsmenue)
   if($kont==3 or $kont==2 or $kont==1):
     $monneu=intval($mon)+1;
     if($monneu>12):
       $monneu=$monneu-12;
       $jahrneu=$jahr+1;
       endif;
     $linktext='<span class="kal_icon">'.$addon::AWE_NACHHER.'</span>';
     endif;
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1) $title=$monate[intval($monneu)].' '.$jahrneu;
   $men='';
   if(abs($kont)<=2) $men=$menmom;
   if(abs($kont)==3 or $kont==4) $men=$menmob;
   if(abs($kont)==1):
     $par=array($addon::KAL_MONAT=>$monneu, $addon::KAL_MODUS=>1, $addon::KAL_JAHR=>$jahrneu, $addon::KAL_MENUE=>$men);
     else:
     $par=array($addon::KAL_MONAT=>$monneu, $addon::KAL_JAHR=>$jahrneu, $addon::KAL_MENUE=>$men);
     endif;
   return $addon::kal_link($par,$linktext,$title,$lnkmod);
   }
public static function kal_blaettern_wochen($kw,$jahr,$kont) {
   #   Das aktuelle Wochenblatt um eine Woche weiterblaettern.
   #   $kw             Nummer der Kalenderwoche
   #   $jahr           Jahreszahl
   #   $kont           fuer das Wochenblatt:
   #                      =-1: Blaettern zur Vorwoche
   #                           Linktext: AWE_VORHER           modus=2
   #                      = 0: Ueberschrift ueber die Woche
   #                           Linktext: (Ueberschrift)       modus=-1 (kein Link)
   #                      = 1: Blaettern zur Folgewoche
   #                           Linktext: AWE_NACHHER          modus=2
   #                   fuer das Monatsmenue:
   #                      = 2: Blaettern zum aktuellen Wochenblatt
   #                           Linktext: AWE_MONAT            modus=1
   #                      POST-Parameter: $addon::KAL_MENUE
   #                                      $addon::KAL_KW
   #                                      $addon::KAL_JAHR
   #
   if(empty($kw) or empty($jahr)) return;
   $addon=self::this_addon;
   #
   $lnkmod=2;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'chenblatt')>0)  $menwob=$i;  // Wochenblatt
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
     $linktext='<span class="kal_icon">'.$addon::AWE_VORHER.'</span>';
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
     $linktext='<span class="kal_icon">'.$addon::AWE_NACHHER.'</span>';
     endif;
   $bis=kal_termine_kalender::kal_datum_vor_nach($von,6);
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)>=1)
     $title='Woche '.$kwneu.' ('.$jahrneu.'), '.substr($von,0,6).'-'.substr($bis,0,6);
   $par=array($addon::KAL_KW=>$kwneu, $addon::KAL_JAHR=>$jahrneu, $addon::KAL_MENUE=>$menwob);
   return $addon::kal_link($par,$linktext,$title,$lnkmod);
   }
public static function kal_blaettern_tage($datum,$kont) {
   #   Das aktuelle Tagesblatt um einen Tag weiterblaettern.
   #   $datum          Datum des aktuellen Tagesblatts
   #   $kont           fuer das Tagesblatt:
   #                      =-1: Blaettern zum Vortag
   #                           Linktext: AWE_VORHER           modus=2
   #                      = 0: Ueberschrift fuer den Tag
   #                           Linktext: (Ueberschrift)       modus=-1 (kein Link)
   #                      = 1: Blaettern zum Folgetag
   #                           Linktext: AWE_NACHHER          modus=2
   #                   fuer das Monatsmenue:
   #                      = 2: Blaettern zum aktuellen Tag
   #                           Linktext: (Datum)              modus=1
   #                   fuer das Terminblatt:
   #                      = 3: Blaettern zum aktuellen Tag
   #                           Linktext: AWE_VORHER           modus=2
   #                      POST-Parameter: $addon::KAL_MENUE
   #                                      $addon::KAL_DATUM
   #
   if(empty($datum)) return;
   $addon=self::this_addon;
   #
   $lnkmod=2;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'gesblatt')>0)   $mentab=$i;  // Tagesblatt
   $datumneu=$datum;
   #
   # --- vorheriger Tag
   if($kont==intval(-1)):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,-1);
     $linktext='<span class="kal_icon">'.$addon::AWE_VORHER.'</span>';
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
     $linktext='<span class="kal_icon">'.$addon::AWE_VORHER.'</span>';
     $lnkmod=2;
     endif;
   #
   # --- folgender Tag
   if($kont==1):
     $datumneu=kal_termine_kalender::kal_datum_vor_nach($datum,1);
     $linktext='<span class="kal_icon">'.$addon::AWE_NACHHER.'</span>';
     endif;
   #
   # --- weitere Linkparameter
   $title='';
   if(abs($kont)==1 or $kont==3) $title=$datumneu;
   if($kont==2):
     $feiertag=kal_termine_kalender::kal_datum_feiertag($datumneu);
     if(empty($feiertag)):
       $title=$datumneu;
       else:
       $title=$datumneu.' ('.$feiertag.')';
       endif;
     endif;     
   $par=array($addon::KAL_DATUM=>$datumneu, $addon::KAL_MENUE=>$mentab);
   return $addon::kal_link($par,$linktext,$title,$lnkmod);
   }
public static function kal_terminblatt($termin) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins.
   #   Die Ueberschriftzeile enthaelt die Bezeichnung des Termins sowie ggf.
   #   einige Ruecklinks. Im Backend oder auf dem Smartphone wird darueber eine
   #   Funktionsleiste zum Bearbeiten des Termins angezeigt.
   #   $termin         assoziatives Array des Termins
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   #
   # --- Termindaten
   $pid       =$termin[$addon::TAB_KEY[0]];
   $kid       =$termin[$addon::TAB_KEY[1]];
   $name      =$termin[$addon::TAB_KEY[2]];
   $datum     =$termin[$keydat];
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
   if($pid<=0) $name='... kein Termin vorhanden/angegeben ...';
   #
   # --- entweder Ruecklinks oder Funktionsleiste
   if(cms_interface::$FRONTEND_EDIT or cms_interface::backend()):
     #     Funktionsleiste
     $ueber=array('','','');
     $menues=$addon::kal_define_menues();
     for($i=1;$i<=count($menues);$i=$i+1)
        if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
     $funkl=kal_termine_formulare::kal_funktionsleiste($termin,$menteb,'',$kid,$pid);
     $clbox='kal_basecol';
     else:
     #     Ruecklinks
     $ueber=array(self::kal_blaettern_basismonat($datum),self::kal_blaettern_tage($datum,3),
                  self::kal_blaettern_uebersicht(2));
     $funkl='';
     $clbox='kal_box';
     endif;
   #
   # --- Ueberschrift-Zeile
   if(!empty($ueber[0])):
     $seite=$funkl.'
<table class="kal_table '.$clbox.'">
    <tr><td class="termblatt_th">
            <table>
                <tr valign="middle">
                    <td class="kal_botpad">
'.$ueber[0].'
                    </td>
                    <td class="kal_botpad">
'.$ueber[1].'
                    </td>
                </tr>
            </table></td>
        <td class="termblatt_td">
            <table>
                <tr valign="middle">
                    <td class="kal_botpad termblatt_ter">
                        '.$name.'</td>
                    <td class="kal_botpad">
'.$ueber[2].'</td></tr>
            </table>
        </td></tr>';
     else:
     $seite=$funkl.'
<table class="kal_table '.$clbox.'">
    <tr valign="top">
        <td align="center" colspan="2" class="kal_botpad termblatt_ter">
            '.$name.'</td></tr>';
     endif;
   #
   # --- kein Termin angegeben
   if($pid<=0) return $seite.'
</table>
';
   #
   # --- echtes Datum (Datum des Basistermins bei Folge-/Wiederholungsterminen)
   if($tage>1 or $wochen>0 or $monate>0):
     $terbas=kal_termine_tabelle::kal_select_termin($pid);
     $realdate=$terbas[$keydat];
     else:
     $realdate=$datum;
     endif;
   #
   # --- Datum, Enddatum
   $arr=explode('.',$realdate);
   $strrealdate=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   $realdatend=kal_termine_kalender::kal_datum_vor_nach($realdate,$tage-1);
   $arr=explode('.',$realdatend);
   $strdatend=intval($arr[0]).'.'.intval($arr[1]).'.'.$arr[2];
   if($wochen<=0 and $monate<=0):
     $warealdate=', '.$strrealdate;
     $wadatend  =', '.$strdatend;
     else:
     $warealdate='';
     $wadatend  ='';
     endif;
   #
   # --- Wochentag
   $wotag=kal_termine_kalender::kal_wotag($realdate);
   if($wochen>0 or $monate>0):
     $wot=kal_termine_kalender::kal_wochentag($realdate);
     $wotag=$wot.'s';
     if($monate>0):
       $nr=kal_termine_kalender::kal_wotag_im_monat($realdate);
       if($tage<=1):
         $wotag='Jeweils '.$nr.'. '.$wot.' im Monat';
         else:
         $wotag='Ab jeweils '.$nr.'. '.$wot.' im Monat';
         endif;
       endif;
     endif;
   #
   # --- Startzeit, Endzeit
   $str=$wotag.$warealdate;
   if($tage<=1):
     if(!empty($beginn)) $str=$str.', '.$beginn;
     if(!empty($beginn) and !empty($ende)) $str=$str.' - ';
     if(!empty($ende)) $str=$str.$ende;
     if(!empty($beginn) or !empty($ende)) $str=$str.' Uhr';
     ;else:
     if(!empty($beginn)) $str=$str.', '.$beginn.' Uhr';
     $str=$str.' &nbsp; - &nbsp; '.kal_termine_kalender::kal_wotag($realdatend).$wadatend;
     if(!empty($ende)) $str=$str.', '.$ende.' Uhr';
     endif;
   #
   # --- Vorsicht beim Loeschen: woechentl./monatl. Wiederh., mehrt. Termine
   $warnung='';
   #     woechentliche Wiederholungstermine
   if($wochen>0):
     $warnung='<b>wöchentlich</b>, über '.$wochen.' Wochen, ab '.$strrealdate;
     if($tage>1) $warnung=$warnung.', <b>mehrtägig</b>, '.$tage.' Tage';
     $str=$str.'<br>
            ('.$warnung.')';
     endif;
   #     monatliche Wiederholungstermine
   if($monate>0):
     $warnung='<b>monatlich</b>, über '.$monate.' Monate, ab '.$strrealdate;
     if($tage>1) $warnung=$warnung.', <b>mehrtägig</b>, '.$tage.' Tage';
     $str=$str.'<br>
            ('.$warnung.')';
     endif;
   #     mehrtaegige Termine (Folgetermine)
   if($tage>1 and $wochen<=0 and $monate<=0):
     $warnung='<b>mehrtägig</b>, '.$tage.' Tage, ab '.$strrealdate;
     $str=$str.'<br>
            ('.$warnung.')';
     endif;
   #
   # --- Termin (Zusammenfassung von Datum und Uhrzeit)
   $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Termin:</th>
        <td class="termblatt_td termblatt_box">
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
   # --- Datum
   if($tage>1 or $wochen >0 or $monate>0):   // ansonsten alle Infos schon in 'Termin'
     $wotdat=kal_termine_kalender::kal_wotag($datum).', '.$datum;
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Datum:</th>
        <td class="termblatt_td termblatt_box">
            '.$wotdat.'</td></tr>';
     endif;
   #
   # --- Ausrichter
   if(!empty($ausrichter))
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Ausrichter:</th>
        <td class="termblatt_td termblatt_box">
            '.$ausrichter.'</td></tr>';
   #
   # --- Veranstaltungsort
   if(!empty($ort))
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Ort:</th>
        <td class="termblatt_td termblatt_box">
            '.$ort.'</td></tr>';
   #
   # --- Link
   if(!empty($link)):
     $lnktxt=substr($link,0,50);
     if(strlen($link)>50) $lnktxt=$lnktxt.' . . . ';
     if(!cms_interface::backend())
       $lnktxt='<a href="'.$link.'" target="_blank">'.$lnktxt.'</a>';
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Link:</th>
        <td class="termblatt_td termblatt_box">
            '.$lnktxt.'</td></tr>';
     endif;
   #
   # --- Hinweise
   if(!empty($komm))
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Hinweise:</th>
        <td class="termblatt_td termblatt_box">
            '.$komm.'</td></tr>';
   #
   # --- Kategorie
   $kategorie=$addon::kal_kategorie_name($kid);
   $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th">Kategorie:</th>
        <td class="termblatt_td">'.$kategorie.'</td></tr>';
   #
   # --- Warnung wegen Wiederholungs-/Folgetermin im Falle der Loeschung
   if((cms_interface::$FRONTEND_EDIT or cms_interface::backend())
      and !empty($warnung) and ($tage>1 or $wochen>0 or $monate>0))
     $seite=$seite.'
    <tr valign="top">
        <th class="termblatt_th kal_fail">
            Vorsicht:</th>
        <td class="termblatt_td kal_fail">
            '.$warnung.'</td></tr>';
   #
   $seite=$seite.'
</table>
';
   return $seite;
   }
public static function kal_neuer_termin($kid,$datum) {
   #   Rueckgabe eines HTML-Codes zur Darstellung eines Links zur Eingabe eines
   #   neuen Termins.
   #   $kid            Id der Terminkategorie
   #   $datum          Datum des neuen Termins
   #
   $addon=self::this_addon;
   if(cms_interface::$FRONTEND_EDIT or cms_interface::backend()):
     #
     # --- Link auf das Eingabeformular
     $menues=$addon::kal_define_menues();
     for($i=1;$i<=count($menues);$i=$i+1)
        if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
     $parp=array($addon::KAL_DATUM=>$datum, $addon::ACTION_NAME=>$addon::ACTION_INSERT, $addon::KAL_MENUE=>$menins);
     $titp='am '.$datum.' einen neuen Termin eintragen';
     $linktext='<span class="kal_plus">&nbsp;+&nbsp;</span>';
     $link=$addon::kal_link($parp,$linktext,$titp,1);
     return '    <!----------- neuer Termin ------------------------------>
<div align="right" class="kal_newterm">
<table class="kal_table">
    <tr valign="middle">
        <td>'.$link.'</td>
      </tr>
</table>
</div>
    <!------------------------------------------------------->
';
     endif;
   }
#
#----------------------------------------- Monatsmenue
public static function kal_monatsmenue($kid,$mon,$jahr,$modus) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalendermenues fuer einen Monat
   #   eines Jahres. Falls der Monat oder das Jahr nicht angegeben sind, wird der
   #   aktuelle Monat angenommen. Die Tage, an denen Termine anstehen, werden durch
   #   Schraffur markiert. Die Ausgabe des Kalendermenues erfolgt im aktuellen
   #   Browserfenster.
   #   $kid            Beruecksichtigung der Termine aller erlaubten Kategorien
   #                   oder nur der Termine einer einzelnen Kategorie
   #                   =0/$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $kid
   #   $mon            Nummer des Monats eines Jahres
   #                   im Format 'mm' oder 'm'
   #                   falls intval($mon)<=0 oder >12 ist, werden $mon
   #                   und $jahr aus dem aktuellen Datum entnommen
   #   $jahr           Kalenderjahr im Format 'yyyy';
   #                   falls intval($jahr)<=0 ist, werden $mon und $jahr aus dem
   #                   aktuellen Datum entnommen; falls intval($jahr)<=99 ist,
   #                   wird $jahr durch $jahr+2000 ersetzt
   #   $modus          >=2: das Menue erhaelt Links auf alle zugehoerigen Tagesblaetter
   #                        und Wochenblaetter, auf das Monatsblatt sowie auf Vorjahr,
   #                        Vormonat, Folgemonat, Folgejahr;
   #                        Tage, an denen Termine anstehen, werden schraffiert
   #                   ==1: Das Menue enthaelt fuer jeden Tag im Monat einen Link,
   #                        der beim Anklicken dessen Datum in das Datums-Inputfeld
   #                        des Termineingabeformulars schreibt 
   #                   <=0: das Menue enthaelt keine Links und keine Schraffuren, und
   #                        es werden keine Termine ausgelesen
   #
   $addon=self::this_addon;
   $keydat=$addon::TAB_KEY[3];   // datum
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;  // Tagesblatt
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
      endfor;
   #
   # --- Parameter fuer das einrahmende Eingabeformular ($modus==1)
   if($modus==1):
     $keys=$addon::TAB_KEY;
     $partr=array();
     for($i=0;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        $val=$addon::kal_post_in($key);
        if(!empty($val)) $partr[$key]=$val;
        endfor;
     $partr[$addon::ACTION_NAME]=$addon::kal_post_in($addon::ACTION_NAME);
     $partr[$addon::KAL_PID]    =$addon::kal_post_in($addon::KAL_PID,'int');
     $partr[$addon::KAL_MENUE]  =$menins;
     endif;
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
   # --- Termine in diesem Monat auslesen ($modus>=2)
   $termine=array();
   if($modus>=2):
     $dat1='01.'.$strmon.'.'.$strjahr;
     $dat2=$anztage.'.'.$strmon.'.'.$strjahr;
     $kids=$addon::kal_allowed_terminkategorien($kid);
     $termine=kal_termine_tabelle::kal_get_termine($dat1,$dat2,$kids,'',1);
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
    <tr><td class="mm_kw">
            KW&nbsp;</td>';
   # --- Wochentage (2-Zeichen-Kuerzel)
   for($i=1;$i<=count($wt);$i=$i+1)
      $kopf=$kopf.'
        <td class="mm_wot">
            '.$wt[$i].'</td>';
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
      # --- Link auf die Kalenderwoche ($modus>=2)
      $link=$kw.'&nbsp;';
      if($modus>=2) $link=self::kal_blaettern_wochen($kw,$strjahr,2);
      $zeil=$zeil.'
        <td class="mm_kw">'.$link.'</td>';
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
           $title=' title="'.substr($datumvn,0,6).'"';
           $temp='
        <td class="mm_tag mm_xx_box"'.$title.'">
            '.$tagvn.'</td>';
           else:
           #
           # --- Tag im Monat (Schraffur und Link auf das Tagesblatt: $modus>=2)
           $ntag=$tag;
           if($ntag<=9) $ntag='0'.$ntag;
           $schraff='';
           if($daterm[$tag]>0 and $modus>=2) $schraff='id="hatch"';
           $datum=$ntag.'.'.$strmon.'.'.$strjahr;
           if($k<=6 and empty(kal_termine_kalender::kal_datum_feiertag($datum))):
             $class='mm_tag mm_wo_box';
             else:
             $class='mm_tag mm_so_box';
             endif;
           if($datum==$heute) $class='mm_tag mm_he_box';
           $strtag=$tag;
           if($strtag<=9) $strtag='&nbsp;'.$strtag;
           if($modus<=0) $link=$strtag;
           if($modus>=2):
             $par=array($addon::KAL_DATUM=>$datum, $addon::KAL_MENUE=>$mentab);
             $link=$addon::kal_link($par,$strtag,substr($datum,0,6),1);
             endif;
           if($modus==1):
             $par=$partr;
             $par[$keydat]=$datum;
             $link=$addon::kal_link($par,$strtag,substr($datum,0,6),1);
             endif;
           $temp='
        <td '.$schraff.' class="'.$class.'">'.$link.'</td>';
           endif;
         $zeil=$zeil.$temp;
         endfor;
      $zeil=$zeil.'</tr>';
      $zeilen=$zeilen.$zeil;
      if($tag>=$anztage) break;
      endfor;
   #
   # --- Links zum Blaettern
   $ruecklink1='';
   $ruecklink2='';
   $ueber     ='
<span class="kal_lightbig">'.$monat.'&nbsp;'.$strjahr.'</span>';
   $ruecklink3='';
   $ruecklink4='';
   $ruecklink5='';
   if($modus==1):
     $ruecklink1=self::kal_blaettern_jahre($strmon,$strjahr,-1);
     $ruecklink2=self::kal_blaettern_monate($strmon,$strjahr,-1);
     $ruecklink3=self::kal_blaettern_monate($strmon,$strjahr,1);
     $ruecklink4=self::kal_blaettern_jahre($strmon,$strjahr,1);
     endif;
   if($modus>=2):
     $ruecklink1=self::kal_blaettern_jahre($strmon,$strjahr,-2);
     $ruecklink2=self::kal_blaettern_monate($strmon,$strjahr,-2);
     $ueber     =self::kal_blaettern_monate($strmon,$strjahr,4);
     $ruecklink3=self::kal_blaettern_monate($strmon,$strjahr,2);
     $ruecklink4=self::kal_blaettern_jahre($strmon,$strjahr,2);
     $ruecklink5=self::kal_blaettern_uebersicht(4);
     endif;
   #
   $string='
    <tr><th colspan="8">
            <table class="kal_table kal_100pro">
                <tr>
                    <td class="kal_basecol kal_botpad">'.$ruecklink1.'</td>
                    <td class="kal_basecol kal_botpad">'.$ruecklink2.'</td>
                    <td class="mm_ueber kal_basecol kal_botpad">'.$ueber.'</td>
                    <td class="kal_basecol kal_botpad">'.$ruecklink3.'</td>        
                    <td class="kal_basecol kal_botpad">'.$ruecklink4.'</td>
                    <td class="kal_basecol kal_botpad">'.$ruecklink5.'</td></tr>
            </table>
        </th></tr>';
   #
   # --- Zusammenstellung Ausgabezeilen
   $string='
<table class="kal_table kal_box">'.
   $string.$kopf.$zeilen.'
</table>'
;
   #
   # --- neuen Termin erzeugen
   $nt='';
   if($modus>=2):
     if(substr($heute,3,2)==substr($dat1,3,2)):
       $datx=$heute;
       else:
       $datx=$dat1;
       endif;
     $nt=self::kal_neuer_termin($kid,$datx);
     endif;
   if(!empty($nt)) $string=$string.'
<div class="mm_newter"><br>
'.$nt.'</div>'
;
   return $string;
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
   #
   $addon=self::this_addon;
   $settings=$addon::kal_get_config();
   $stauhrz=$settings[$addon::STAUHRZ_KEY];
   $enduhrz=$settings[$addon::ENDUHRZ_KEY];
   $sizeuhr=$enduhrz-$stauhrz;
   #
   # --- Uhrzeiten-Leiste
   $stunden='
    <tr class="mwt_uhrzlst">
        <td></td>';
   for($k=$stauhrz+1;$k<$enduhrz;$k=$k+2):
      $kk=$k;
      if($k<=9) $kk='&nbsp;'.$k;
      $stunden=$stunden.'
        <td colspan="2" class="mwt_uhrzei_wid">
            '.$kk.':00</td>';
      endfor;
   if(2*intval($sizeuhr/2)<$sizeuhr)
     $stunden=$stunden.'
        <td align="right" class="mwt_lineal_wid">
            '.$enduhrz.':</td>';
   $stunden=$stunden.'</tr>';
   #
   # --- Stundenstrich-Leiste (Lineal)
   $stdlineal='
    <tr class="mwt_uhrzlst">
        <td class="mwt_th"></td>';
   for($k=$stauhrz;$k<$enduhrz-1;$k=$k+1)
      $stdlineal=$stdlineal.'
        <td class="mwt_lineal_wid mwt_lineal">
            &nbsp;</td>';
   $stdlineal=$stdlineal.'
        <td class="mwt_lineal_wid mwt_lineal mwt_lineal_re">
            &nbsp;</td>
   </tr>';
   #
   return $stunden.$stdlineal;
   }
public static function kal_eval_start_ende($termin) {
   #   Rueckgabe der Startuhrzeit und der Enduhrzeit eines Termins. Falls
   #   Start-/Enduhrzeit nicht definiert sind, werden sie sinnvoll ergaenzt.
   #   Rueckgabe als assoziatives Arrays (Start-/Enduhrzeit).
   #   - Falls der Terminbeginn leer ist:
   #                   der Beginn wird aus Zusatzzeiten 2 bis 5 abgeleitet
   #   - Falls der Terminbeginn und die Zusatzzeiten 2 bis 5 leer sind:
   #                   es wird 'ganztaegig' angenommen und Anfang/Ende als
   #                   Startuhrzeit/Enduhrzeit berechnet
   #   - Falls das Terminende leer ist:
   #                   das Ende wird aus den die Zusatzzeiten 2 bis 5 abgeleitet
   #   $termin         Daten eines Termins (Array)
   #
   $addon=self::this_addon;
   $keynam=$addon::TAB_KEY[2];   // name
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   $keyze2=$addon::TAB_KEY[13];  // zeit2
   $keyze3=$addon::TAB_KEY[15];  // zeit3
   $keyze4=$addon::TAB_KEY[17];  // zeit4
   $keyze5=$addon::TAB_KEY[19];  // zeit5
   $settings=$addon::kal_get_config();
   $stauhrz=$settings[$addon::STAUHRZ_KEY];
   $enduhrz=$settings[$addon::ENDUHRZ_KEY];
   #
   # --- Startzeit bei fehlendem Beginn eines Termins
   #     falls eine der Zusatzzeiten nicht leer ist, ersetze Beginn
   #     durch die kleinste der nicht-leeren Zusatzzeiten;
   #     falls auch diese leer ist, ersetze Startzeit durch Startuhrzeit
   $beginn=$termin[$keybeg];
   $zeitmax=max($termin[$keyze2],$termin[$keyze3],$termin[$keyze4],$termin[$keyze5]);
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
   if(empty($beginn)) $beginn=$stauhrz;
   $arr=explode(':',$beginn);
   if(empty($arr[1])) $arr[1]='00';
   $beginn=intval($arr[0]).':'.$arr[1];
   #
   # --- Endzeit bei fehlendem Ende eines Termins
   #     falls die groesste der Zusatzzeiten ($maxz) nicht leer ist, ersetze die
   #     Endzeit durch $maxz+$diff ($diff = Zeitdifferenz zwischen 1. und 2.
   #     Zusatzzeit oder, falls diese 0 ist, alternativ 30 Min.);
   #     falls auch $maxz leer ist, ersetze die Endzeit durch die Enduhrzeit
   $ende=$termin[$keyend];
   if(empty($ende) and !empty($zeitmax)):
     if(!empty($termin[$keyze2]) and !empty($termin[$keyze3])):
       $arr=explode(':',$termin[$keyze2]);
       $brr=explode(':',$termin[$keyze3]);
       $crr=explode(':',$zeitmax);
       $std=$crr[0]+($brr[0]-$arr[0]);
       $min=$crr[1]+($brr[1]-$arr[1]);
       if($min<0):
         $std=$std-1;
         $min=60-abs($min);
         endif;
       if($min>=60):
         $std=$std+1;
         $min=$min-60;
         endif;
       $ende=$std.':'.$min;
       endif;
     endif;
   if(empty($ende)) $ende=$enduhrz;
   $arr=explode(':',$ende);
   if(empty($arr[1])) $arr[1]='00';
   $ende=intval($arr[0]).':'.$arr[1];
   #
   return array($keybeg=>$beginn, $keyend=>$ende);
   }
public static function kal_termin_poslen($termin) {
   #   Rueckgabe von Massangaben, die eine halbgrafische Darstellung von Lage
   #   und Dauer eines vorgegebenen Termins innerhalb eines Tages ermoeglichen.
   #   Bestimmt werden der Zeitraum vor Beginn des Termins sowie dessen Dauer.
   #   Die Daten sind jeweils prozentuale Anteile der Gesamtlaenge einer
   #   Tabellenzelle, in die die Darstellung eingebettet ist. Die Gesamtlaenge
   #   selbst ist relativ (100%).
   #   Bei der Darstellung von Lineal/Stundenleiste ist der Tabellen-border-space
   #   bereits in der Stunden- bzw. Zweistundenbreite beruecksichtigt.
   #   $termin         Daten eines Termins (Array)
   #   Rueckgabe in Form eines assoziativen Arrays:
   #      $proz['vor']     % der Gesamtlaenge VOR BEGINN des Termins
   #                         (=0  bei leerem Termin)
   #      $proz['dauer']   % der Gesamtlaenge DAUER des Termins
   #                         (=0  bei leerem Termin)
   #
   $addon=self::this_addon;
   $keynam=$addon::TAB_KEY[2];   // name
   $keybeg=$addon::TAB_KEY[4];   // beginn
   $keyend=$addon::TAB_KEY[5];   // ende
   if(empty($termin[$keynam])) return array('vor'=>0, 'dauer'=>0);
   #
   # --- Bemassung der Stundenleiste
   $settings=$addon::kal_get_config();
   $stauhrz=$settings[$addon::STAUHRZ_KEY];
   $stunden=$settings[$addon::ENDUHRZ_KEY]-$stauhrz;
   $eps=0.2;   // prozentualer Abstand zum linken/rechten Rand
   #
   # --- ggf Start-/Enduhrzeit sinnvoll ergaenzen
   $sez=self::kal_eval_start_ende($termin);
   $beginn=$sez[$keybeg];
   $ende  =$sez[$keyend];
   #
   # --- Laenge vor dem Termin in Prozent der Gesamtlaenge
   $nziff=10;   // mit so vielen Ziffern wird gerechnet
   $voll=100;   // Prozentzahl der vollen Länge
   $arr=explode(':',$beginn);
   $voruhr=substr(floatval($arr[0]+$arr[1]/60),0,$nziff);
   $stdvor=$voruhr-$stauhrz;
   $prozvor=substr(floatval($stdvor*$voll/$stunden),0,$nziff);
   $links=FALSE;
   if($prozvor<$eps):
     $prozvor=$eps;                     // (*) kl. Randabstand vorne ...
     $links=TRUE;
     endif;
   #
   # --- Laenge des Termins in Prozent der Gesamtlaenge
   $arr=explode(':',$ende);
   $nachuhr=substr(floatval($arr[0]+$arr[1]/60),0,$nziff);
   $stddau=$nachuhr-$voruhr;
   $prozdau=substr(floatval($stddau*$voll/$stunden),0,$nziff);
   if($links) $prozdau=$prozdau-$eps;   // (*) ... bei der Dauer abgezogen
   if($prozvor+$prozdau>$voll-$eps)
     $prozdau=$prozdau-2*$eps;          // kl. Randabstand hinten abziehen
   #
   # --- Dezimalkomma ggf. durch Dezimalpunkt ersetzen
   $prozvor=str_replace(',','.',$prozvor);
   $prozdau=str_replace(',','.',$prozdau);
   #
   return array('vor'=>$prozvor, 'dauer'=>$prozdau);
   }
public static function kal_termin_titel($termin) {
   #   Rueckgabe des div-Container-Titels eines Termins im Tages-/Wochen-/Monatsblatt.
   #   Er enthaelt die Termin-Parameter Name, Beginn, Ende, Ort, Ausrichter.
   #   $termin         Daten eines Termins (assoziatives Array)
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
     $arr=self::kal_eval_start_ende($termin);
     $title=$arr[$keybeg].' - '.$arr[$keyend].': ';
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
   #   $class          Name einer temporaer erzeugten CSS-Klasse,
   #                   Klasse wird hier definiert
   #
   $addon=self::this_addon;
   $keypid=$addon::TAB_KEY[0];   // pid
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $pid  =$termin[$keypid];
   $name =$termin[$keynam];
   $datum=$termin[$keydat];
   #
   $proz=self::kal_termin_poslen($termin);
   #
   # --- kein Termin an diesem Tag
   if($proz['vor']<=0 and $proz['dauer']<=0)
     return '<div class="mwt_leertermin">&nbsp;</div>';
   #
   # --- div-Container (Streifen) mit zugemessener Position/Laenge und Termin-Link
   #     Link auf das Terminblatt
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
   $linktext=$name;
   $par=array($addon::KAL_DATUM=>$datum, $addon::KAL_PID=>$pid, $addon::KAL_MENUE=>$menteb);
   $link=$addon::kal_link($par,$linktext,self::kal_termin_titel($termin),1);
   #     div-Container
   $cont='<style>
               .'.$class.' { margin-left:'.$proz['vor'].'%; width:'.$proz['dauer'].'%; }
               @media screen and (max-width:'.$addon::CSS_MOBILE.'em) {
                   .'.$class.' { margin-left:0; width:initial; }
                 }
            </style>';
   $cont=$cont.'
            <div class="mwt_termin '.$class.'">'.$link.'
            </div>';
   return $cont;
   }
public static function kal_mowotablatt($kid,$mon,$kw,$jahr,$datum) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalenderblatts fuer entweder
   #   - einen Kalendermonat (nicht leer: $mon, $jahr, leer: $kw, $datum) oder
   #   - eine Kalenderwoche  (nicht leer: $kw, $jahr,  leer: $mon, $datum ) oder
   #   - einen einzelnen Tag (nicht leer: $datum,      leer: $mon, $kw, $jahr)
   #   Falls alle 4 Datumsparameter leer sind, wird der heutige Tag als
   #   einzelner Tag angenommen.
   #   $kid            Beruecksichtigung der Termine aller erlaubten Kategorien
   #                   oder nur der Termine ener einzelnen Kategorie
   #                   =0/$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $kid
   #   $mon            Nummer des Monats eines Jahres im Format 'mm' oder 'm'
   #   $kw             Nummer der Woche (<=53) im Format 'ww' oder 'w'
   #                   falls die 53. Kalenderwoche nicht existiert, wird die
   #                   erste Kalenderwoche des Folgejahres angenommen
   #   $jahr           Kalenderjahr im Format 'yyyy' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   $datum          Datum im Format 'tt.mm.yyyy'' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
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
   $menues=$addon::kal_define_menues();
   for($j=1;$j<=count($menues);$j=$j+1)
      if(strpos($menues[$j]['name'],'gesblatt')>0) $men=$j;   // Tagesblatt
   #
   # --- Kategorien
   $kids=$addon::kal_allowed_terminkategorien($kid);
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
   $settings=$addon::kal_get_config();
   $stauhrz=$settings[$addon::STAUHRZ_KEY];
   $enduhrz=$settings[$addon::ENDUHRZ_KEY];
   $colspan=$enduhrz-$stauhrz;  // Anzahl der Tabellen-(Stunden-)Spalten
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
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[$end],$kids,'',1);
     endif;
   #     Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     #     Montag (Starttag) der Woche als Datum
     $dat[1]=kal_termine_kalender::kal_kw_montag($kw,$strjahr);
     #     restliche Tage der Woche als Datum
     for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[7],$kids,'',1);
     endif;
   #     Einzeldatum
   if(!empty($datum)):
     $dat[1]=$stdatum;
     $term=kal_termine_tabelle::kal_get_termine($dat[1],$dat[1],$kids,'',1);
     endif;
   #
   # --- Ruecklinks und Ueberschriften
   #     Monatsblatt
   if(!empty($mon) and !empty($jahr)):
     $ruecklink1=self::kal_blaettern_basismonat($heute);
     $ruecklink2=self::kal_blaettern_monate($strmon,$jahr,-3);
     $ueber     =self::kal_blaettern_monate($strmon,$jahr,0);
     $ruecklink3=self::kal_blaettern_monate($strmon,$jahr, 3);
     endif;
   #     Wochenblatt
   if(!empty($kw) and !empty($jahr)):
     $donnerstag=kal_termine_kalender::kal_datum_vor_nach($dat[1],3);
     $kwmon=substr($donnerstag,3,2);   // zur Woche gehoeriger Monat
     $ruecklink1=self::kal_blaettern_basismonat($donnerstag);
     $ruecklink2=self::kal_blaettern_wochen($kw,$jahr,-1);
     $ueber     =self::kal_blaettern_wochen($kw,$jahr,0);
     $ruecklink3=self::kal_blaettern_wochen($kw,$jahr, 1);
     endif;
   #     Tagesblatt
   if(!empty($datum)):
     $datmon=substr($stdatum,3,2);
     $ruecklink1=self::kal_blaettern_basismonat($stdatum);
     $ruecklink2=self::kal_blaettern_tage($stdatum,-1);
     $ueber     =self::kal_blaettern_tage($stdatum,0);
     $ruecklink3=self::kal_blaettern_tage($stdatum, 1);
     endif;
   #     Ruecklink auf die Terminuebersicht
   $ruecklink4=self::kal_blaettern_uebersicht(2);
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
   # --- Rahmen
   $string='
<table class="mwt_table kal_box kal_100pro"><tr><td>';
   #
   # --- Ueberschriftszeile
   $string=$string.'
<table class="kal_table">
    <tr><td class="kal_basecol kal_botpad mwt_head">'.$ruecklink1.'</td>
        <td class="kal_basecol kal_botpad mwt_head">'.$ruecklink2.'</td>
        <td class="kal_basecol kal_botpad mwt_ueber">'.$ueber.'</td>
        <td class="kal_basecol kal_botpad mwt_head">'.$ruecklink3.'</td>
        <td class="kal_basecol kal_botpad mwt_head">'.$ruecklink4.'</td></tr>
</table>';
   #
   # --- Stundenleiste
   $string=$string.'
<table class="mwt_table kal_100pro">'.self::kal_stundenleiste();
   #
   # --- Tageszeilen
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
            <hr class="mwt_hr">
            ';
         $cont=$cont.$content[$i][$k];
         endfor;
      $title=$shdat;
      if(!empty($feiertag) or $wt=='So'):
        if(!empty($feiertag)) $title=$feiertag.', '.$dat[$i];
        $class='mwt_so_box';
        else:
        $class='mwt_wo_box';
        endif;
      if($dat[$i]==$heute) $class='mwt_he_box';
      $tgg=$wt.', '.$shdat;
      if(!empty($jahr)):
        #     Monats-/Wochenblatt: Link auf den Tag
        $par=array($addon::KAL_DATUM=>$dat[$i], $addon::KAL_MENUE=>$men);
        $tgtext=$addon::kal_link($par,$tgg,$title,1);
        else:
        $tgtext='            '.$tgg;
        endif;
      $string=$string.'
    <tr valign="top">
        <td class="mwt_th">'.$tgtext.'
        </td>
        <td colspan="'.$colspan.'" class="kal_100pro '.$class.'">
            '.$cont.'</td></tr>';
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
   #
   # --- Rahmenende
   $string=$string.'
</td></tr></table><br>
';
   #
   # --- voreingestellter Tag ($datx) fuer einen neuen Termin (= $heute, falls
   #     $heute im Datumsbereich liegt, sonst = 1. Tag des Datumsbereichs)
   if(!empty($mon) and !empty($jahr)):   // Monatsblatt
     if(substr($heute,3,2)==substr($dat[1],3,2)):
       $datx=$heute;
       else:
       $datx=$dat[1];
       endif;
     endif;
   if(!empty($kw) and !empty($jahr)):   // Wochenblatt
     $dat2=kal_termine_kalender::kal_datum_vor_nach($dat[1],6);
     if(kal_termine_kalender::kal_datum1_vor_datum2($heute,$dat[1]) or
        kal_termine_kalender::kal_datum1_vor_datum2($dat2,$heute)     ):
       $datx=$dat[1];
       else:
       $datx=$heute;
       endif;
     endif;
   if(!empty($datum)) $datx=$datum;   // Tagesblatt
   return $string.self::kal_neuer_termin($kid,$datx);
   }
public static function kal_monatsblatt($kid,$mon,$jahr) {
   if(empty($mon) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $mon=substr($heute,3,2);
     $jahr=substr($heute,6);
     endif;
   return self::kal_mowotablatt($kid,$mon,'',$jahr,'');
   }
public static function kal_wochenblatt($kid,$kw,$jahr) {
   if(empty($kw) or empty($jahr)):
     $heute=kal_termine_kalender::kal_heute();
     $kw=intval(kal_termine_kalender::kal_kw($heute));
     $jahr=intval(substr($heute,6));
     endif;
   return self::kal_mowotablatt($kid,'',$kw,$jahr,'');
   }
public static function kal_tagesblatt($kid,$datum) {
   if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
   return self::kal_mowotablatt($kid,'','','',$datum);
   }
#
#----------------------------------------- Terminuebersicht
public static function kal_termin_uebersicht_intern($termin) {
   #   Rueckgabe einer Liste aller Termine (Datenbank-/Spieldaten) in
   #   Form eines HTML-Codes. Die Termine sind ggf. mittels Kategorie und/
   #   oder Suchstring gefiltert. Sie sind, soweit noetig, bereits in
   #   Einzeltermine aufgespalten und NACH DATUM SORTIERT.
   #   $termin         Array der Termine
   #
   $addon=self::this_addon;
   #
   # --- keine Termine gefunden
   if(count($termin)<=0):
     $suchen=$addon::kal_post_in($addon::KAL_SUCHEN);
     $kid   =$addon::kal_post_in($addon::KAL_KATEGORIE,'int');
     if($kid<=0 or $kid==$addon::SPIEL_KATID):
       $kat='alle Kategorien';
       else:
       if($kid<$addon::SPIEL_KATID):
         $kats=$addon::kal_conf_terminkategorien();
         else:
         $kats=$addon::kal_get_spielkategorien();
         endif;
       for($i=0;$i<count($kats);$i=$i+1)
          if($kats[$i]['id']==$kid):
            $kat='Kategorie \''.$kats[$i]['name'].'\'';
            break;
            endif;
       endif;
     return '
<div><br>keine Termine gefunden ('.$kat.', Zeichenfolge \''.$suchen.'\')</div>';
     endif;
   #
   $keypid=$addon::TAB_KEY[0];
   $keykat=$addon::TAB_KEY[1];
   $keynam=$addon::TAB_KEY[2];
   $keydat=$addon::TAB_KEY[3];
   $keytag=$addon::TAB_KEY[6];
   $keyaus=$addon::TAB_KEY[9];
   $keyort=$addon::TAB_KEY[10];
   $keylnk=$addon::TAB_KEY[11];
   $keykom=$addon::TAB_KEY[12];
   #
   # --- Monatsmenue-Nummer, Terminblatt-Nummer
   $menmom=0;
   $menteb=0;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
      endfor;
   #
   # --- Terminuebersicht
   $string='';
   $monate=kal_termine_kalender::kal_monate();
   $heute=kal_termine_kalender::kal_heute();
   $arr=explode('.',$heute);
   $heutag=intval($arr[0]);
   $heumon=intval($arr[1]);
   $heujah=intval($arr[2]);
   $startdatum=$termin[1][$keydat];
   $aktdatum=kal_termine_kalender::kal_datum_vor_nach($startdatum,-1);
   $arr=explode('.',$heute);
   $akttag=intval($arr[0]);
   $aktmon=intval($arr[1]);
   $aktjah=intval($arr[2]);
   $string=$string.'
<br><br>
<table class="kal_table">';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      $pid   =$term[$keypid];
      $datum=$term[$keydat];
      $kat_id=$term[$keykat];
      if($kat_id>$addon::SPIEL_KATID) $kat_id=$kat_id-$addon::SPIEL_KATID;
      if($kat_id<=9) $kat_id='0'.$kat_id;
      $zeile='';
      #
      # --- Datum
      $wot=kal_termine_kalender::kal_wotag($datum);
      $arr=explode('.',$datum);
      $tag=intval($arr[0]);     
      $mon=intval($arr[1]);     
      $jah=intval($arr[2]);     
      $datfor=$tag.'.'.$mon.'.'.$jah;
      $arr=explode('.',$aktdatum);
      $akttag=intval($arr[0]);
      $aktmon=intval($arr[1]);     
      $aktjah=intval($arr[2]);
      #
      # --- Monatszeile einschieben
      if($aktmon<$mon or ($aktmon>$mon and $aktjah<$jah) or $i==1):
        $strmon=$mon;
        if(strlen($strmon)<=1) $strmon='0'.$strmon;
        $strm='
    <!-========== '.$strmon.'.'.$jah.' ==================================->
    <tr><td colspan="2"><a name="'.$strmon.'.'.$jah.'"></a></td></tr>
    <tr><td colspan="2" class="tview_monat kal_bold kal_bigbig">
            '.$monate[$mon].' '.$jah.'</td></tr>';
        else:
        $strm='';
        endif;
      #
      # --- heutiges Datum als Leerzeile einschieben
      $strh='';
      if(kal_termine_kalender::kal_datum1_vor_datum2($aktdatum,$heute) and
         kal_termine_kalender::kal_datum1_vor_datum2($heute,$datum) and
         !kal_termine_kalender::kal_datum1_vor_datum2($heute,$startdatum)):
        $wt=kal_termine_kalender::kal_wotag($heute);
        $anker='<a name="'.$heute.'"></a>';
        $dstr='
            '.$anker.'<div class="kal_small">'.$wt.'</div>
            <div class="kal_bigbig tview_heute">'.$heutag.'</div>';
        $strh='
    <!----------- '.$heute.' -------------------------------->
    <tr valign="top">
        <td class="tview_datum">'.$dstr.'</td>
        <td class="tview_termin">
            &nbsp;</td></tr>';
        #     kein weiterer Termin in diesem Monat, Monatszeile davor
        if(($aktmon<$heumon and $heumon<$mon) or
           ($aktmon>$heumon and $heumon<$mon and $aktjah<$heujah) or
           ($aktmon<$heumon and $heumon>$mon and $heujah<$jah))
          $strh='
    <tr><td colspan="2" class="tview_monat kal_bold kal_bigbig">
            '.$monate[$heumon].' '.$heujah.'</td></tr>'.$strh;
        endif;
      #
      if(!empty($strm) or !empty($strh)):
        if($heumon<$mon or ($heumon>$mon and $heujah<$jah)):
          $zeile=$zeile.$strh.$strm;
          else:
          $zeile=$zeile.$strm.$strh;
          endif;
        endif;
      #
      # --- Datumsanzeige nur bei einem neuen Tag
      if($datum==$heute):
        $tlh=' tview_heute';
        $anker='<a name="'.$heute.'"></a>';
        else:
        $tlh='';
        $anker='';
        endif;
      $datstr='';
      if($tag!=$akttag)
        $datstr='
            '.$anker.'<div class="kal_small">'.$wot.'</div>
            <div class="kal_bigbig'.$tlh.'">'.$tag.'</div>';
      $zeile=$zeile.'
    <!----------- '.$datum.' -------------------------------->
    <tr valign="top">
        <td class="tview_datum">'.$datstr.'</td>
        <td class="tview_termin termbord_'.$kat_id.'">';
      #
      # --- Veranstaltungsbezeichnung
      $td='';
      $name=$term[$keynam];
      $td=$td.'
    '.$name;
      #
      # --- Uhrzeiten aufbereiten
      $uhrz=kal_termine_tabelle::kal_uhrzeit_string($term);
      if(!empty($uhrz))
        $td=$td.'<br>
    '.$uhrz;
      #
      # --- Ort
      $ort=$term[$keyort];
      if(!empty($ort))
        $td=$td.'<br>
    <span class="termlist_ort">'.$ort.'</span>';
      #
      # --- Ausrichter
      $ausrichter=$term[$keyaus];
      if(!empty($ausrichter)):
        if(empty($td)):
          $td=$td.'<br>';
          else:
          $td=$td.', ';
          endif;
        $td=$td.'
    <span class="termlist_ausrichter">'.$ausrichter.'</span>';
        endif;
      #
      # --- Link
      $link=$term[$keylnk];
      if(!empty($link)):
        $tar='';
        if(substr($link,0,4)=='http' and strpos($link,'://')>0) $tar=' target="_blank"';
        if(empty($td)):
          $td=$td.'<br>';
          else:
          $td=$td.', ';
          endif;
        $td=$td.'<a href="'.$link.'"'.$tar.'>Hinweise des Ausrichters</a>';
        endif;
      #
      # --- Zusatzzeiten aufbereiten
      $zusatz=kal_termine_tabelle::kal_zusatzzeiten_string($term);
      if(!empty($zusatz))
        $td=$td.$zusatz;
      #
      # --- Hinweise zur Veranstaltung
      $hinw=$term[$keykom];
      if(!empty($hinw))
        $td=$td.'<br>
    '.$hinw;
      #
      $par=array($addon::KAL_DATUM=>$datum, $addon::KAL_PID=>$pid, $addon::KAL_MENUE=>$menteb);
      $tit='Terminblatt: &nbsp; '.$datfor.' &nbsp; ('.$name.')';
      $link=$addon::kal_link($par,$td,$tit,1);
      $zeile=$zeile.$link.'</td></tr>';
   #
      $string=$string.$zeile;
      $aktdatum=$datum;
      endfor;
   $string=$string.'
</table>
';
   return $string;
   }
public static function kal_termin_uebersicht($selkid) {
   #   Rueckgabe eines Menues zur Auflistung aller Termine (Datenbank-/
   #   Spieldaten) in Form eines HTML-Codes. Die Termine koennen mittels
   #   Kategorie und/oder Suchstring gefiltert werden. Sie sind, soweit
   #   noetig, bereits in Einzeltermine aufgespalten und NACH DATUM SORTIERT.
   #   $selkid         Beruecksichtigung der Termine aller erlaubten Kategorien
   #                   oder nur der Termine einer einzelnen Kategorie
   #                   =0/=$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0/>$addon::SPIEL_KATID: nur Termine der Kategorie $kid
   #   $katids         Array der fuer den Redakteur erlaubten Kategorie-Ids
   #                   (Nummerierung ab 1, Spieldaten: alle Kategorien)
   #
   $addon=self::this_addon;
   $keykat=$addon::TAB_KEY[1];   // kat_id
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
   $heute=kal_termine_kalender::kal_heute();
   $monate=kal_termine_kalender::kal_monate();
   #
   $menueb=0;
   $menmom=0;
   $menteb=0;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'erminüber')>0) $menueb=$i;  // Terminuebersicht
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
      endfor;
   #
   $string='';
   #
   # --- Am Anfang auf den heutigen Tag ausrichten
   if($addon::kal_post_in($addon::KAL_MENUE)!=$menueb):
     $vor='http://';
     if(!empty($_SERVER['HTTPS'])) $vor='https://';
     $url=$vor.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].'#'.$heute;
     $string=$string.'
<script onload="history.replaceState({},,\''.$url.'\');">
    history.replaceState({},,\''.$url.'\');
</script>';
     endif;
   #
   # --- Textfilter-Feld und Kategorie-Auswahl auslesen
   $suchen=$addon::kal_post_in($addon::KAL_SUCHEN);
   $kid   =$addon::kal_post_in($addon::KAL_KATEGORIE,'int');
   if(!isset($_POST[$addon::KAL_KATEGORIE])) $kid=$selkid;
   if($kid<=0 and $selkid>=$addon::SPIEL_KATID) $kid=$addon::SPIEL_KATID;
   $kids=$addon::kal_allowed_terminkategorien($kid);
   #
   # --- Termine auslesen (sind nach Datum sortiert)
   $termin=kal_termine_tabelle::kal_get_termine('','',$kids,$suchen,1);
   #
   # --- Array der Monate aus den Termindaten
   $heumon=substr($heute,3);
   $mons=array();
   $m=0;
   $altmon='00.0000';
   for($i=1;$i<=count($termin);$i=$i+1):
      $term=$termin[$i];
      $datum=$term[$keydat];
      $mon=substr($datum,3);
      if($mon!=$altmon):
        $m=$m+1;
        $mons[$m]=$mon;
        $altmon=$mon;
        endif;
      endfor;
   #
   # --- aktueller Monat (nach mon. Blaettern) bzw. aktuelles Datum (nach Blaettern nach heute)
   $aktmon=$addon::kal_post_in($addon::KAL_ANKER);
   if(empty($aktmon)):
     $aktmon=$heumon;
     else:
     $brr=explode('.',$aktmon);
     if(count($brr)>=3) $aktmon=substr($aktmon,3);
     endif;
   #
   # --- jeweiliger Vormonat / Folgemonat
   $am=intval(substr($aktmon,0,2));
   $aj=substr($aktmon,3);
   #     vorheriger Monat
   for($k=count($termin);$k>=1;$k=$k-1):
      $datum=$termin[$k][$keydat];
      $vormon=substr($datum,3,2);
      $vorjahr=substr($datum,6);
      if(($vormon<$am and $vorjahr==$aj) or $vorjahr<$aj) break;
      endfor;
   #     folgender Monat
   for($k=1;$k<=count($termin);$k=$k+1):
      $datum=$termin[$k][$keydat];
      $nxtmon=substr($datum,3,2);
      $nxtjahr=substr($datum,6);
      if(($nxtmon>$am and $nxtjahr==$aj) or $nxtjahr>$aj) break;
      endfor;
   #
   # --- Kopfzeile
   $modus=1;
   $class='tview_men';
   if($kid>=$addon::SPIEL_KATID) $class=$class.' tview_spmen';
   $string=$string.'
<div align="right" class="'.$class.'">
<table class="tview_table">
    <tr valign="middle">';
   #
   #     Ruecklink auf das aktuelle Monatsmenue
   $mon=intval(substr($heute,3,2));
   $monat=$monate[intval($mon)];
   $title=$monat.' '.substr($heute,6);
   $string=$string.'
    <!----------- Rücklink Monatsmenü ----------------------->
        <td class="kal_basecol" title="'.$title.'">
'.self::kal_blaettern_basismonat($heute).'</td>';
   #
   #     vorheriger Monat
   $parv=array($addon::KAL_ANKER=>$vormon.'.'.$vorjahr, $addon::KAL_MENUE=>$menueb);
   $titv=$monate[intval($vormon)].' '.$vorjahr;
   $linktext='<span class="kal_icon">'.$addon::AWE_VORMON.'</span>';
   $string=$string.'
    <!----------- Blättern Vormonat ------------------------->
        <td class="kal_basecol tview_pad" title="'.$titv.'">
'.$addon::kal_link($parv,$linktext,$titv,$modus).'</td>';
   #
   #     heutiger Tag
   $parh=array($addon::KAL_ANKER=>$heute, $addon::KAL_MENUE=>$menueb);
   $tith='heutiger Tag';
   $linktext='<span class="kal_icon">'.$addon::AWE_HEUTE.'</span>';
   $string=$string.'
    <!----------- Blättern heutiger Tag --------------------->
        <td class="kal_basecol tview_pad" title="'.$tith.'">
'.$addon::kal_link($parh,$linktext,$tith,$modus).'</td>';
   #
   #     folgender Monat
   $parn=array($addon::KAL_ANKER=>$nxtmon.'.'.$nxtjahr, $addon::KAL_MENUE=>$menueb);
   $titn=$monate[intval($nxtmon)].' '.$nxtjahr;
   $linktext='<span class="kal_icon">'.$addon::AWE_NACHMON.'</span>';
   $string=$string.'
    <!----------- Blättern Folgemonat ----------------------->
        <td class="kal_basecol tview_pad" title="'.$titn.'">
'.$addon::kal_link($parn,$linktext,$titn,$modus).'</td>';
   #
   #     Textfilter und Kategorieauswahl
   if($kid<$addon::SPIEL_KATID):
    $kats=$addon::kal_conf_terminkategorien();
    else:
    $kats=$addon::kal_get_spielkategorien();
    endif;
   if(count($kats)<=1):
     #     keine Kategorieauswahl
     $bstr='';
     else:
     #     mit Kategorieauswahl
     $katids=array();
     for($i=0;$i<count($kats);$i=$i+1) $katids[$i+1]=$kats[$i]['id'];
     $bstr='<br>
'.$addon::kal_select_kategorie($addon::KAL_KATEGORIE,$kid,$katids,TRUE);
     endif;
   $string=$string.'
    <!----------- Textfilter / Kategorieauswahl ------------->
        <td class="kal_basecol tview_pad tview_width">
            <form method="post">
            <input type="text" class="tview_bgcol kal_100pro" title="Textfilter"
                   name="'.$addon::KAL_SUCHEN.'" value="'.$suchen.'">'.$bstr;
   #
   #     Suchen-Button
   $linktext='<span class="kal_icon">'.$addon::AWE_SUCHEN.'</span>';
   $string=$string.'
        <td class="kal_basecol">
            <input type="hidden" name="'.$addon::KAL_MENUE.'" value='.$menueb.'>
            <button type="submit" class="kal_transp kal_linkbut">
            '.$linktext.'</button>
            </form></td>';
   $string=$string.'
    <!------------------------------------------------------->
      </tr>
</table>
</div>
';
   $string=$string.self::kal_termin_uebersicht_intern($termin);
   return $string.'<br>'.self::kal_neuer_termin($kid,$heute);
   }
#
#----------------------------------------- Menuewechsel
public static function kal_menue($selkid,$mennr) {
   #   Rueckgabe des HTML-Codes zur Anzeige des gewaehlten Menues.
   #   $selkid         Beruecksichtigung der Termine aller erlaubten Kategorien
   #                   oder nur der Termine einer einzelnen Kategorie
   #                   =0/$addon::SPIEL_KATID: Termine aller erlaubten Kategorien
   #                   >0: nur Termine der Kategorie $selkid
   #   $mennr          Nummer des ersten anzuzeigenden Menues (Startmenue)
   #                   >0: wird als Startmenue-Nummer benutzt, falls sie einem
   #                       Menues entspricht (ausser 7=Terminliste)
   #                   =0/leer/falsch: als Startmenue wird das Monatsmenue genommen
   #
   $addon=self::this_addon;
   $keys=$addon::TAB_KEY;
   $keydat=$keys[3];   // datum
   #
   # --- Menuenummern
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'natsblatt')>0) $menmob=$i;  // Monatsblatt
      if(strpos($menues[$i]['name'],'chenblatt')>0) $menwob=$i;  // Wochenblatt
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;  // Tagesblatt
      if(strpos($menues[$i]['name'],'erminüber')>0) $menueb=$i;  // Terminübersicht
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
      if(strpos($menues[$i]['name'],'datumform')>0) $mendat=$i;  // Datumsformular
      if(strpos($menues[$i]['name'],'öschform')>0)  $mendel=$i;  // Loeschformular
      if(strpos($menues[$i]['name'],'zeitform')>0)  $menuhr=$i;  // Uhrzeitformular
      if(strpos($menues[$i]['name'],'extformul')>0) $mentxt=$i;  // Textformular
      endfor;
   #
   # --- POST-Parameter auslesen
   $monat  =$addon::kal_post_in($addon::KAL_MONAT);
   $kw     =$addon::kal_post_in($addon::KAL_KW,'int');
   $jahr   =$addon::kal_post_in($addon::KAL_JAHR);
   $datum  =$addon::kal_post_in($addon::KAL_DATUM);
   $suchen =$addon::kal_post_in($addon::KAL_SUCHEN);
   $men    =$addon::kal_post_in($addon::KAL_MENUE,'int');
   $pid    =$addon::kal_post_in($addon::KAL_PID,'int');
   $kid    =$addon::kal_post_in($addon::KAL_KATEGORIE,'int');
   $action =$addon::kal_post_in($addon::ACTION_NAME);
   $modus  =$addon::kal_post_in($addon::KAL_MODUS);
   #
   # --- ggf. Uhrzeit herausfiltern (z.B. men = '10_beginn_11:15_480')
   #     bzw. Text herausfiltern (z.B. men = '11_ort_Hamburg')
   $arr=explode('_',$men);
   $keyuhr ='';
   $uhrzeit='';
   $scrwid ='';
   $keytxt='';
   $text  ='';
   if(count($arr)>1):
     $men   =$arr[0];
     $keyneu=$arr[1];
     $valneu=$arr[2];
     if($men==$menuhr) $scrwid=$arr[3];
     endif;
   #
   # --- vorausgewaehlt: Startmenue und Terminkategorie
   if(!isset($_POST[$addon::KAL_MENUE]))     $men=$mennr;
   if(!isset($_POST[$addon::KAL_KATEGORIE])) $kid=$selkid;
   #
   # --- Startmenue
   if($men<=0 and $mennr>0) $men=$mennr;
   if($men<=0 or $men>count($menues)) $men=$menmom;
   if($men==$menins and $modus>0) $men==$menmom;   // kann im Eingabeformular passieren
   #
   # --- Monatsmenue
   if($men==$menmom):
     if(empty($modus)) $modus=2;
     return self::kal_monatsmenue($kid,$monat,$jahr,$modus);
     endif;
   #
   # --- Terminuebersicht
   if($men==$menueb)
     return self::kal_termin_uebersicht($kid);
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
     $termin=kal_termine_tabelle::kal_select_termin($pid);
     $termin[$keydat]=$datum;
     return self::kal_terminblatt($termin);
     endif;
   #
   # --- Eingabeformular
   if($men==$menins):
     $keydat=$addon::TAB_KEY[3];   // datum
     return kal_termine_formulare::kal_eingabeformular($action,$kid,$pid,$keydat,$datum);
     endif;
   #
   # --- Loeschformular
   if($men==$mendel)
     return kal_termine_formulare::kal_loeschformular($pid);
   #
   # --- Uhrzeitformular
   if($men==$menuhr)
     return kal_termine_formulare::kal_uhrzeitformular($keyneu,$valneu,$scrwid);
   #
   # --- Textformular
   if($men==$mentxt)
     return kal_termine_formulare::kal_textformular($keyneu,$valneu);
   }
public static function kal_spielmenue() {
   #   Anzeige des Monatsmenues der Spieldaten.
   #
   $addon=self::this_addon;
   $addon::$SPIELDATEN=TRUE;
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'natsmenü')>0)   $menmom=$i;  // Monatsmenue
   return self::kal_menue($addon::SPIEL_KATID,$menmom);
   }
}
?>