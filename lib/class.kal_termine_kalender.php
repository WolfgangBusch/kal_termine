<?php
/* Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
 */
class kal_termine_kalender {
#
#----------------------------------------- Methoden
#   Basismethoden
#      kal_heute()
#      kal_monate()
#      kal_wochentage()
#      kal_monatstage($jahr)
#         kal_jahrestage_intern($jahr1,$jahr2)
#      kal_standard_datum($datum)
#      kal_wotag($datum)
#      kal_wochentag($datum)
#      kal_wotag_nr($wt)
#   weitere Methoden
#      kal_datum1_vor_datum2($datum1,$datum2)
#      kal_datumsdifferenz($datum1,$datum2)
#      kal_noch_tage($datum)
#      kal_schon_tage($datum)
#      kal_datum_vor_nach($datum,$anztage)
#      kal_wotag_im_monat($datum)
#      kal_gleicher_wotag_im_folgemonat($datum)
#   Kalenderwochen-Methoden
#      kal_montag_kw($montag)
#      kal_first_montag($jahr)
#      kal_kw_montag($kw,$jahr)
#      kal_montag_vor($datum)
#      kal_kw($datum)
#   Feiertage-Methoden
#      kal_ostersonntag($jahr,$kont)
#      kal_unbewegliche_feiertage($jahr)
#      kal_bewegliche_feiertage($jahr)
#      kal_feiertage($jahr)
#      kal_datum_feiertag($datum)
#
#----------------------------------------- Basismethoden
public static function kal_heute() {
   #   Rueckgabe des aktuellen Datums im Format 'tt.mm.yyyy'.
   #
   return date('d.m.Y');
   }
public static function kal_monate() {
   #   Rueckgabe der Monatsnamen eines Jahres als nummeriertes Array,
   #   beginnend bei 1.
   #
   $mon=array(
        1=>'Januar', 2=>'Februar', 3=>'März', 4=>'April',
        5=>'Mai', 6=>'Juni', 7=>'Juli', 8=>'August',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Dezember');
   return $mon;
   }
public static function kal_wochentage() {
   #   Rueckgabe der Wochentags-Kuerzel als nummeriertes Array, beginnend bei 1.
   #
   $wday=array(1=>'Mo', 2=>'Di', 3=>'Mi', 4=>'Do', 5=>'Fr', 6=>'Sa', 7=>'So');
   return $wday;
   }
public static function kal_monatstage($jahr)  {
   #   Rueckgabe der Anzahl Tage der Monate eines Jahres als nummeriertes Array,
   #   beginnend bei 1.
   #   Schaltjahre werden beruecksichtigt
   #
   $tage=array(1=>31, 2=>28, 3=>31,  4=>30,  5=>31,  6=>30,
               7=>31, 8=>31, 9=>30, 10=>31, 11=>30, 12=>31);
   $ja=strval($jahr);
   #
   # --- alle 4 Jahre Schaltjahr
   $r=intval(intval($ja)/4);
   $je=strval(4*$r);
   if($je==$ja) $tage[2]=29;
   #
   # --- alle 100 Jahre kein Schaltjahr
   $r=intval(intval($ja)/100);
   $je=strval(100*$r);
   if($je==$ja) $tage[2]=28;
   #
   # --- alle 400 Jahre doch ein Schaltjahr
   $r=intval(intval($ja)/400);
   $je=strval(400*$r);
   if($je==$ja) $tage[2]=29;
   return $tage;
   }
public static function kal_jahrestage_intern($jahr1,$jahr2) {
   #   Rueckgabe der Anzahl Tage in einer Folge von aufeinander folgenden Jahren,
   #   Schaltjahre werden beruecksichtigt.
   #   $jahr1          erstes Jahr, Jahreszahl im Format 'yyyy')
   #   $jahr2          letztes Jahr, Jahreszahl im Format 'yyyy')
   #   Die Rueckgabezahl ist immer positiv, auch wenn $jahr2<$jahr1
   #
   $jadif=$jahr2-$jahr1;
   #
   # --- 2. Jahr = 1. Jahr: 365/366 Tage
   if(abs($jadif)<=0):
     $tt=self::kal_monatstage($jahr1);
     $tagjahre=365;
     if($tt[2]==29) $tagjahre=366;
     return $tagjahre;
     endif;
   #
   # --- Jahresdifferenz >0
   $j1=0;
   if($jadif>=1):
     $j1=$jahr1;
     $j2=$jahr2;
     endif;
   if($jadif<=-1):
     $j1=$jahr2;
     $j2=$jahr1;
     endif;
   $tagjahre=0;
   for($i=$j1;$i<=$j2;$i=$i+1):
      $tt=self::kal_monatstage($i);
      $plus=365;
      if($tt[2]==29) $plus=366;
      $tagjahre=$tagjahre+$plus;
      endfor;
   return $tagjahre;
   }
public static function kal_standard_datum($datum) {
   #   Rueckgabe eines Datum im Standardformat 'tt.mm.yyyy'. Falls das gegebene
   #   Datum nicht 2 Punkte (.) enthaelt, werden Monat '00' und Jahr '0000'
   #   zurueck gegeben.
   #
   $arr=explode('.',$datum);
   #   Tag
   $ta=$arr[0];
   if(empty($ta)):
     $ta='00';
     else:
     if(strlen($ta)<2) $ta='0'.$ta;
     endif;
   #   Monat
   if(count($arr)<=1):
     $mo='00';
     else:
     $mo=$arr[1];
     if(strlen($mo)<2) $mo='0'.$mo;
     endif;
   #   Jahr
   if(count($arr)<=2):
     $ja='0000';
     else:
     $ja=$arr[2];
     if(strlen($ja)==2) $ja='20'.$ja;
     endif;
   return $ta.'.'.$mo.'.'.$ja;
   }
public static function kal_wotag($datum) {
   #   Rueckgabe des Wochentages (2-Zeichen-Kuerzel) eines Datums.
   #   $datum          gegebenes Datum im Format 'tt.mm.yyyy'
   #                   auch akzeptiert: verkuerzte Formate bis hin zu 't.m.yy'
   #
   # --- Formatierung des Eingabedatums
   $date=self::kal_standard_datum($datum);
   $arr=explode('.',$date);
   $it=intval($arr[0]);   // Tag
   $im=intval($arr[1]);   // Monat
   $ja=intval($arr[2]);   // Jahr
   #
   # --- Bestimmung der Wochentagsnummer: (0,1,2,...,6)=(So,Mo,Di,...,Sa)
   $dat=getdate(mktime(0,0,0,$im,$it,$ja));
   $wdat=$dat['wday'];
   if($wdat<=0) $wdat=7;   // So -> 7
   #
   # --- Wochentagskuerzel
   $wday=self::kal_wochentage();
   return $wday[$wdat];
   }
public static function kal_wochentag($datum) {
   #   Rueckgabe des Wochentages (im Klartext) eines Datums.
   #   $datum          gegebenes Datum im Format 'tt.mm.yyyy'
   #                   auch akzeptiert: verkuerzte Formate bis hin zu 't.m.yy'
   #
   # --- Formatierung des Eingabedatums
   $date=self::kal_standard_datum($datum);
   $arr=explode('.',$date);
   $it=intval($arr[0]);             // Tag
   $im=intval($arr[1]);             // Monat
   $ja=intval($arr[2]);             // Jahr
   #
   # --- Bestimmung der Wochentagsnummer (0=Sonntag)
   $dat=getdate(mktime(0,0,0,$im,$it,$ja));
   $wdat=$dat['wday'];
   if($wdat<=0) $wdat=7;
   #
   # --- Wochentagsbezeichnung
   if($wdat==1) return 'Montag';
   if($wdat==2) return 'Dienstag';
   if($wdat==3) return 'Mittwoch';
   if($wdat==4) return 'Donnerstag';
   if($wdat==5) return 'Freitag';
   if($wdat==6) return 'Samstag';
   if($wdat==7) return 'Sonntag';
   }
public static function kal_wotag_nr($wt) {
   #   Rueckgabe der Wochentags-Nummer zu einem Wochentagskuerzel, bei falschem
   #   Wochentagskuerzel wird der Wert 0 zurueckgegeben.
   #   $wt             gegebenes Wochentagskuerzel, passend zu den
   #                   Rueckgaben der Methode kal_wochentage()
   #
   $nr=0;
   $wot=self::kal_wochentage();
   for($i=1;$i<=7;$i=$i+1):
      if($wot[$i]==$wt):
        $nr=$i;
        break;
        endif;
      endfor;
   return $nr;
   }
#
#----------------------------------------- weitere Methoden
public static function kal_datum1_vor_datum2($datum1,$datum2) {
   #   Entscheidung, ob ein Datum vor einem anderen Datum liegt.
   #   $datum1         gegebenes erstes Datum im Format 'tt.tt.yyyy'
   #   $datum2         gegebenes zweites Datum im Format 'tt.tt.yyyy'
   #
   # --- Formatierung der Datumsangaben
   $date1=self::kal_standard_datum($datum1);
   $date1=substr($datum1,6).'.'.substr($datum1,3,2).'.'.substr($datum1,0,2);
   $date2=substr($datum2,6).'.'.substr($datum2,3,2).'.'.substr($datum2,0,2);
   if($date1<$date2) return TRUE;
   }
public static function kal_datumsdifferenz($datum1,$datum2)  {
   #   Differenz in Anzahl Tage zwischen zwei Datumsangaben
   #   positiv, falls $datum2 spaeter als $datum1
   #   negativ, falls $datum2 frueher als $datum1
   #   Jedes Datum muss im Format 'tt.mm.yyyy' (oder im
   #   verkuerzten Format bis hin zu 't.m.yy') angegeben werden
   #   $datum1         erstes Datum
   #   $datum2         zweites Datum
   #
   # --- Umsetzen der Datumsangaben auf Standardformat
   $dat1=self::kal_standard_datum($datum1);
   $dat2=self::kal_standard_datum($datum2);
   #
   # --- Zahlen fuer die Tage, Monate, Jahre
   $tage1 =intval(substr($dat1,0,2));
   $mon1  =intval(substr($dat1,3,2));
   $jahr1 =intval(substr($dat1,6));
   $tagej1=self::kal_monatstage($jahr1);
   $tage2 =intval(substr($dat2,0,2));
   $mon2  =intval(substr($dat2,3,2));
   $jahr2 =intval(substr($dat2,6));
   $tagej2=self::kal_monatstage($jahr2);
   #
   # --- beide Tage im gleichen Monat im gleichen Jahr
   if($mon1==$mon2 and $jahr1==$jahr2) return $tage2-$tage1;
   #
   # --- beide Tage im gleichen Jahr, aber in unterschiedlichen Monaten
   if($jahr1==$jahr2):
     $monatstage=0;
     if($mon1<$mon2):
       for($i=$mon1;$i<$mon2;$i=$i+1) $monatstage=$monatstage+$tagej1[$i];
       $dif=$tage2-$tage1+$monatstage;
       else:
       for($i=$mon2;$i<$mon1;$i=$i+1) $monatstage=$monatstage+$tagej1[$i];
       $dif=$tage2-$tage1-$monatstage;
       endif;
     return $dif;
     endif;
   #
   # --- ggf. mehr als 1 Jahr Differenz: $tagjahre hinzu addieren
   $tagjahre=0;
   if($jahr2-$jahr1> 1) $tagjahre=self::kal_jahrestage_intern($jahr1+1,$jahr2-1);
   if($jahr2-$jahr1<-1) $tagjahre=self::kal_jahrestage_intern($jahr2+1,$jahr1-1);
   $monatstage=0;
   #
   # --- beide Tage in unterschiedlichen Jahren
   if($jahr1<$jahr2):
     for($i=$mon1;$i<=12;$i=$i+1) $monatstage=$monatstage+$tagej1[$i];
     for($i=1;$i<$mon2;$i=$i+1)   $monatstage=$monatstage+$tagej2[$i];
     $dif=$tage2-$tage1+$monatstage+$tagjahre;
     return $dif;
     endif;
   if($jahr1>$jahr2):
     for($i=$mon2;$i<=12;$i=$i+1) $monatstage=$monatstage+$tagej2[$i];
     for($i=1;$i<$mon1;$i=$i+1)   $monatstage=$monatstage+$tagej1[$i];
     $dif=$tage2-$tage1-$monatstage-$tagjahre;
     return $dif;
     endif;
   }
public static function kal_noch_tage($datum) {
   #   Rueckgabe der Anzahl Tage von einem gegebenen Datum bis zum Ende des
   #   Jahres.
   #   $datum          gegebenes Datum im Format 'tt.tt.yyyy'
   #
   # --- Formatierung des Datums
   $date=self::kal_standard_datum($datum);
   $tag  =substr($date,0,2);
   $monat=substr($date,3,2);
   $jahr =substr($date,6);
   #
   # --- Anzahl Tage im Monat
   $mon=self::kal_monatstage($jahr);
   #
   # --- Berechnung
   $ntbis=$mon[intval($monat)]-$tag;
   for($i=$monat+1;$i<=12;$i=$i+1) $ntbis=$ntbis+$mon[$i];
   return $ntbis;
   }
public static function kal_schon_tage($datum) {
   #   Rueckgabe der Anzahl Tage vom Anfang des Jahres bis zu einem gegebenen
   #   Datum.
   #   $datum          gegebenes Datum im Format 'tt.mm.yyyy'
   #
   # --- Formatierung des Datums
   $date=self::kal_standard_datum($datum);
   $tag  =substr($date,0,2);
   $monat=substr($date,3,2);
   $jahr =substr($date,6);
   #
   # --- Anzahl Tage im Monat
   $mon=self::kal_monatstage($jahr);
   #
   # --- Berechnung
   $ntseit=$tag-1;
   for($i=1;$i<=$monat-1;$i=$i+1) $ntseit=$ntseit+$mon[$i];
   return $ntseit;
   }
public static function kal_datum_vor_nach($datum,$anztage) {
   #   Rueckgabe des Datums einer Anzahl von Tagen vor/nach einem Datum.
   #   Das zurueck gegebene Datum ist im Standardformat 'tt.mm.yyyy'.
   #   $datum          Ausgangsdatum im Format 'tt.mm.yyyy'
   #                   (moeglich auch verkuerzte Formate bis 't.m.yy')
   #   $anztage        >0: Anzahl Tage nach dem Datum
   #                   <0: Anzahl Tage vor    dem Datum
   #                   =0: Es wird das Datum $datum zurueck gegeben
   #
   if($anztage==0) return $datum;
   #
   # --- Datum formatieren
   $dat=self::kal_standard_datum($datum);
   $tag     =intval(substr($dat,0,2));
   $monat=intval(substr($dat,3,2));
   $jahr    =intval(substr($dat,6));
   #
   # --- spaeteres Datum
   if($anztage>0):
     # --- Anzahl Tage bis Jahresende: $ntbis
     $mon=self::kal_monatstage($jahr);
     $ntbis=$mon[$monat]-$tag;
     for($i=$monat+1;$i<=12;$i=$i+1) $ntbis=$ntbis+$mon[$i];
     if($anztage<=$ntbis):
       # --- neues Datum im aktuellen Jahr
       $neujahr=$jahr;
       $startmonat=$monat;
       $neutag=$tag+$anztage;
       else:
       # --- neues Datum im naechsten Jahr
       $neujahr=$jahr+1;
       $startmonat=1;
       $neutag=$anztage-$ntbis;
       endif;
     #
     # --- Anzahl Tage im Monat im naechsten Jahr
     $mon=self::kal_monatstage($neujahr);
     #
     # --- Monat und Tag berechnen
     for($i=$startmonat;$i<=12;$i=$i+1):
        $neumonat=$i;
        if($neutag<=$mon[$neumonat]) break;
        if($i<12) $neutag=$neutag-$mon[$neumonat];
        endfor;
     #
     # --- noch weitere Jahre voraus
     if($neujahr>=$jahr+1 and $neumonat>=12 and $neutag>$mon[12]):
       $neumonat=1;
       $neujahr=$neujahr+1;
       $break=0;
       for($nj=$jahr+2;$nj<=$jahr+100;$nj=$nj+1):
          $mon=self::kal_monatstage($nj);
          for($i=1;$i<=12;$i=$i+1):
             if($neutag<=$mon[$neumonat]):
               $break=1;
               break;
               endif;
             $neujahr=$nj;
             $neutag=$neutag-$mon[$neumonat];
             $neumonat=$i;
             endfor;
          if($break>0) break;
          endfor;
       endif;
     endif;
   #
   # --- frueheres Datum
   if($anztage<0):
     # --- Anzahl Tage seit Jahresanfang: $ntseit
     $mon=self::kal_monatstage($jahr);
     $ntseit=$tag-1;
     for($i=1;$i<=$monat-1;$i=$i+1) $ntseit=$ntseit+$mon[$i];
     if(-$anztage<=$ntseit):
       # --- neues Datum im aktuellen Jahr
       $neujahr=$jahr;
       $startmonat=$monat;
       $neutag=$tag+$anztage;
       else:
       # --- neues Datum im vorigen Jahr
       $neujahr=$jahr-1;
       $startmonat=12;
       $neutag=$ntseit+$anztage+$mon[12]+1;
       endif;
     #
     # --- Anzahl Tage im Monat im vorigen Jahr
     $mon=self::kal_monatstage($neujahr);
     #
     # --- Monat und Tag berechnen
     for($i=$startmonat;$i>=1;$i=$i-1):
        $neumonat=$i;
        if($neutag>=1) break;
        if($i>1) $neutag=$neutag+$mon[$i-1];
        endfor;
     #
     # --- noch weitere Jahre zurueck
     if($neujahr<=$jahr-1 and $neumonat<=1 and $neutag<=0):
       $neumonat=12;
       $neujahr=$neujahr-1;
       $break=0;
       for($nj=$jahr-2;$nj>=$jahr-100;$nj=$nj-1):
          $mon=self::kal_monatstage($nj);
          for($i=12;$i>=1;$i=$i-1):
             if($neutag<=$mon[$neumonat]):
               if($neutag<=0) $neutag=$neutag+$mon[$neumonat];
               $break=1;
               break;
               endif;
             $neujahr=$nj;
             if($neutag<0) $neutag=$neutag+$mon[$neumonat];
             $neumonat=$i;
             endfor;
          if($break>0) break;
          endfor;
       endif;
     endif;
   #
   # --- neues Datum im Standardformat
   return self::kal_standard_datum($neutag.'.'.$neumonat.'.'.$neujahr);
   }
public static function kal_wotag_im_monat($datum) {
   #   Bestimmung des Wochentags eines vorgegeben Datums und Bestimmung, ob das
   #   Datum der 1., 2., 3., 4. oder 5. Wochentag im betreffenden Monat ist.
   #   $datum          Ausgangsdatum im Format 'tt.mm.yyyy'
   #                   (moeglich auch verkuerzte Formate bis 't.m.yy')
   #
   $dat=self::kal_standard_datum($datum);
   $tag=intval(substr($dat,0,2));
   if($tag>= 1 and $tag<= 7) return 1;
   if($tag>= 8 and $tag<=14) return 2;
   if($tag>=15 and $tag<=21) return 3;
   if($tag>=22 and $tag<=28) return 4;
   if($tag>=29 and $tag<=31) return 5;
   }
public static function kal_gleicher_wotag_im_folgemonat($datum) {
   #   Zu einem Datum wird das Datum mit dem gleichen Wochentag im Folgemonat
   #   bestimmt (z.B. 3. Freitag im Monat liefert 3. Freitag im Folgemonat).
   #   Rueckgabe des Datums im Standardformat.
   #   $datum          Ausgangsdatum im Format 'tt.mm.yyyy'
   #                   (moeglich auch verkuerzte Formate bis 't.m.yy')
   #
   # --- Nummer des Wochentages
   $nr=self::kal_wotag_im_monat($datum);
   #
   # --- gleicher Wochentag im Folgemonat
   $mon=intval(substr($datum,3,2));
   $datfm=self::kal_datum_vor_nach($datum,28);   // evtl. eine Woche zu frueh
   $monfm=intval(substr($datfm,3,2));
   if(self::kal_wotag_im_monat($datfm)<$nr or $monfm==$mon)
     $datfm=self::kal_datum_vor_nach($datfm,7);
   #
   return $datfm;
   }
#
#----------------------------------------- Kalenderwochen-Methoden
public static function kal_montag_kw($montag) {
   #   Rueckgabe der Kalenderwoche (Nr.) zum Datum des ersten Tages der Woche
   #   (also eines Montags).
   #   $montag         Datum des ersten Tages im Format 'tt.mm.yyyy'
   #                   moeglich ist auch ein verkuerztes Format bis
   #                   hin zu 't.m.yy'
   #   Rueckgabe 0, falls das vorgegebene Datum kein Montag ist
   #
   # --- Formatierung des Datums
   $datum=self::kal_standard_datum($montag);
   $ntseit=self::kal_schon_tage($datum);
   #
   # --- Abbruch, falls $montag kein Montag ist
   if(self::kal_wotag($datum)!='Mo') return 0;
   #
   # --- Spezialfall 1. Kalenderwoche
   $jahr=intval(substr($datum,6));
   $jt=self::kal_jahrestage_intern($jahr,$jahr);
   if($jt-$ntseit<4 or $ntseit<4) return 1;
   #
   # --- Berechnung der Kalenderwoche
   $kww=intval($ntseit/7);
   $kw=$kww+2;
   if(intval(7*$kww)==$ntseit) $kw=$kww+1;
   $wtnj=self::kal_wotag('01.01.'.$jahr);
   if($wtnj=='Fr' or $wtnj=='Sa' or $wtnj=='So') $kw=$kw-1;
   return $kw;
   }
public static function kal_first_montag($jahr) {
   #   Rueckgabe des Datums des Montags, mit dem die erste Woche  eines Jahres
   #   beginnt.
   #   $jahr           Jahr, in der die Kalenderwoche liegt
   #                   im Format 'yyyy'  (akzeptiert wird auch 'yy',
   #                   wobei dann '20' vorne ergaenzt wird)
   #
   $strjahr=$jahr;
   if(strlen($strjahr)==2) $strjahr='20'.$strjahr;
   $wt=self::kal_wotag('01.01.'.$strjahr);
   $k=self::kal_wotag_nr($wt);
   $tag=9-$k;
   $mon='01';
   if($tag>4):
     $tag=31-7+$tag;
     $mon='12';
     $strjahr=$strjahr-1;
     endif;
   if(strlen($tag)<2) $tag='0'.$tag;
   return $tag.'.'.$mon.'.'.$strjahr;
   }
public static function kal_kw_montag($kw,$jahr) {
   #   Rueckgabe des Datums des Montags, mit dem eine vorgegebene Kalenderwoche
   #   eines Jahres beginnt.
   #   $kw             Nummer der Kalenderwoche (<=53)
   #                   falls die 53. Kalenderwoche nicht existiert, wird der Montag
   #                   der ersten Kalenderwoche des Folgejahres zurueckgegeben
   #   $jahr           Jahr, in der die Kalenderwoche liegt
   #                   im Format 'yyyy'  (akzeptiert wird auch 'yy',
   #                   wobei dann '20' vorne ergaenzt wird)
   #
   # --- Jahreszahl formatieren
   $strjahr=$jahr;
   if(strlen($strjahr)==2) $strjahr='20'.$strjahr;
   #
   # --- Montag der ersten Kalenderwoche
   $montag1=self::kal_first_montag($strjahr);
   #
   # --- Montag der gefragten Kalenderwoche
   $anztage=7*($kw-1);
   return self::kal_datum_vor_nach($montag1,$anztage);
   }
public static function kal_montag_vor($datum) {
   #   Rueckgabe des ersten Datums in der Woche (Montag) zu einem vorgegebenen
   #   Datum.
   #   $datum          vorgegebenes Datum im Format 'tt.mm.yyyy'
   #
   $wt=self::kal_wotag($datum);
   $wtnr=self::kal_wotag_nr($wt);
   $montag=self::kal_datum_vor_nach($datum,1-$wtnr);
   return $montag;
   }
public static function kal_kw($datum) {
   #   Rueckgabe der Kalenderwoche (Nr.), zu der ein vorgegebenes Datum gehoert.
   #   $datum          vorgegebenes Datum im Format 'tt.mm.yyyy'
   #
   $montag=self::kal_montag_vor($datum);
   return self::kal_montag_kw($montag);
   }
#
#----------------------------------------- Feiertage-Methoden
public static function kal_ostersonntag($jahr,$kont=0) {
   #   Rueckgabe des Datums fuer den Ostersonntag eines Jahres (Algorithmus nach
   #   Gauss/Kinkelin, vergl. Osterformel bei Wikipedia).
   #   $jahr           Jahreszahl (4-stellig)
   #   $kont           =1:    Testausgabe von Zwischenergebnissen
   #                   sonst: keine Testausgaben
   #
   $k = intval($jahr)/100;
   $hilf=intval((3*$k+3)/4);
   $m = 15 + $hilf - intval((8*$k+13)/25);
   $s = 2 - $hilf;
   $a = intval($jahr)%19;
   $d = (19*$a+$m)%30;
   $r = intval(($d + intval($a/11))/29);
   $og = 21 + $d + $r;
   $sz = 7 - (intval($jahr) + intval($jahr/4) + $s)%7;
   $oe = 7 - ($og - $sz)%7;
   $osm = $og + $oe;
   $os=$osm;
   $mon=3;
   if($os>31):
     $os=$os-31;
     $mon=4;
     endif;
   $tag=$os;
   if($tag<10) $tag='0'.$tag;
   $monat='0'.$mon;
   $datum=$tag.'.'.$monat.'.'.$jahr;
   if($kont==1):
     $strr='
<div><hr>
<table cellpadding="0" cellspacing="0">
    <tr><td align="right">
            0) &nbsp; </td>
        <td>Jahr:</td>
        <td align="right">
            <u>'.$jahr.':</u></td></tr>
    <tr><td align="right">
            1) &nbsp; </td>
        <td>Säkularzahl: &nbsp; </td>
        <td align="right">
            '.$k.'</td></tr>
    <tr><td align="right">
            2) &nbsp; </td>
        <td>säkulare Mondschaltung: &nbsp; </td>
        <td align="right">
            '.$m.'</td></tr>
    <tr><td align="right">
            3) &nbsp; </td>
        <td>säkulare Sonnenschaltung: &nbsp; </td>
        <td align="right">
            '.$s.'</td></tr>
    <tr><td align="right">
            4) &nbsp; </td>
        <td>säkulare Sonnenschaltung: &nbsp; </td>
        <td align="right">
            '.$a.'</td></tr>
    <tr><td align="right">
            5) &nbsp; </td>
        <td>Keim für den ersten Vollmond im Frühling &nbsp; </td>
        <td align="right">
            '.$a.'</td></tr>
    <tr><td align="right">
            6) &nbsp; </td>
        <td>kalendarische Korrekturgröße: &nbsp; </td>
        <td align="right">
            '.$r.'</td></tr>
    <tr><td align="right">
            7) &nbsp; </td>
        <td>Ostergrenze: &nbsp; </td>
        <td align="right">
            '.$og.'</td></tr>
    <tr><td align="right">
            8) &nbsp; </td>
        <td>erster Sonntag im März: &nbsp; </td>
        <td align="right">
            '.$sz.'</td></tr>
    <tr><td align="right">
            9) &nbsp; </td>
        <td>Entfernung des Ostersonntags von der Ostergrenze: &nbsp; </td>
        <td align="right">
            '.$oe.'</td></tr>
    <tr><td align="right">
            10) &nbsp; </td>
        <td>Tag des Ostersonntags als Märzdatum: &nbsp; </td>
        <td align="right">
            '.$osm.'</td></tr>
    <tr><td align="right">
            11) &nbsp; </td>
        <td>Tag des Ostersonntags: &nbsp; </td>
        <td align="right">
            '.$os.'</td></tr>
    <tr><td align="right">
            12) &nbsp; </td>
        <td>Monat des Ostersonntags: &nbsp; </td>
        <td align="right">
            '.$mon.'</td></tr>
    <tr><td align="right">
            12) &nbsp; </td>
        <td>Datum des Ostersonntags: &nbsp; </td>
        <td align="right">
            <u>'.$datum.'</u></td></tr>
</table>
<hr></div>';
     else:
     $strr='';
     endif;
   return $strr.$datum;
   }
public static function kal_unbewegliche_feiertage($jahr) {
   #   Rueckgabe der gesetzlichen Feiertage fuer ein Jahr als nummeriertes Array,
   #   beginnend bei 1 in der Form:
   #                   $ft[$i]['datum']   Datum des Feiertages
   #                   $ft[$i]['name']    Bezeichnung des Feiertages
   #   $jahr           vorgegebenes Jahr
   #   die Feiertage sind jedes Jahr an demselben Datum
   #
   $key1='datum';
   $key2='name';
   $tage=array(
      1=>array($key1=>'01.01.'.$jahr, $key2=>'Neujahr'),
      2=>array($key1=>'01.05.'.$jahr, $key2=>'Tag der Arbeit'),
      3=>array($key1=>'03.10.'.$jahr, $key2=>'Tag der deutschen Einheit'),
      4=>array($key1=>'31.10.'.$jahr, $key2=>'Reformationstag'),
      5=>array($key1=>'01.11.'.$jahr, $key2=>'Allerheiligen'),
      6=>array($key1=>'25.12.'.$jahr, $key2=>'1. Weihnachtstag'),
      7=>array($key1=>'26.12.'.$jahr, $key2=>'2. Weihnachtstag'));
   return $tage;
   }
public static function kal_bewegliche_feiertage($jahr) {
   #   Rueckgabe der beweglichen kirchlichen Feiertage im Jahr, die mit
   #   Ostersonntag verknuepft sind:
   #      Karfreitag, Ostersonntag, Ostermontag, Himmelfahrt, Pfingstsonntag,
   #      Pfingstmontag, Allerheiligen
   #   als nummeriertes Array, Elemente 1, 2, 3, 4, 5, 6, 7 in der Form:
   #                   $ft[$i]['datum']   Datum des Feiertages
   #                   $ft[$i]['name']    Bezeichnung des Feiertages
   #   $jahr           Jahreszahl
   #
   # --- Ostersonntag
   $ostersonntag=self::kal_ostersonntag($jahr,0);
   #
   # --- Karfreitag, Ostersonntag, Ostermontag, Himmelfahrt, Pfingstsonntag, Pfingstmontag
   $bew=array();
   $bew[1]['datum']=self::kal_datum_vor_nach($ostersonntag,-2);
   $bew[1]['name'] ='Karfreitag';
   $bew[2]['datum']=$ostersonntag;
   $bew[2]['name'] ='Ostersonntag';
   $bew[3]['datum']=self::kal_datum_vor_nach($ostersonntag,1);
   $bew[3]['name'] ='Ostermontag';
   $bew[4]['datum']=self::kal_datum_vor_nach($ostersonntag,39);
   $bew[4]['name'] ='Himmelfahrt';
   $bew[5]['datum']=self::kal_datum_vor_nach($ostersonntag,49);
   $bew[5]['name'] ='Pfingstsonntag';
   $bew[6]['datum']=self::kal_datum_vor_nach($ostersonntag,50);
   $bew[6]['name'] ='Pfingstmontag';
   $bew[7]['datum']=self::kal_datum_vor_nach($ostersonntag,60);
   $bew[7]['name'] ='Fronleichnam';
   return $bew;
   }
public static function kal_feiertage($jahr) {
   #   Rueckgabe aller (unbeweglichen und beweglichen) Feiertage im Jahr, als
   #   nummeriertes Array, beginnend bei 1 (nach Datum sortiert) in der Form:
   #                   $ft[$i]['datum']   Datum des Feiertages
   #                   $ft[$i]['name']    Bezeichnung des Feiertages
   #   $jahr           Jahreszahl
   #
   # --- unbewegliche Feiertage
   $uf=self::kal_unbewegliche_feiertage($jahr);
   $anz=count($uf);
   #
   # --- bewegliche Feiertage
   $bf=self::kal_bewegliche_feiertage($jahr);
   #
   # --- alle zusammen
   $ft=array();
   for($i=1;$i<=$anz;$i=$i+1) $ft[$i]=$uf[$i];
   for($i=1;$i<=count($bf);$i=$i+1):
      $k=$anz+$i;
      $ft[$k]=$bf[$i];
      endfor;
   #
   # --- Sortierung nach Datum
   for($i=1;$i<=count($ft);$i=$i+1)
      for($k=$i+1;$k<=count($ft);$k=$k+1):
         $dati=$ft[$i]['datum'];
         $dati=substr($dati,6).'-'.substr($dati,3,2).'-'.substr($dati,0,2);
         $datk=$ft[$k]['datum'];
         $datk=substr($datk,6).'-'.substr($datk,3,2).'-'.substr($datk,0,2);
         if($datk<$dati):
           $ftag=$ft[$k];
           $ft[$k]=$ft[$i];
           $ft[$i]=$ftag;
           endif;
         endfor;
   return $ft;
   }
public static function kal_datum_feiertag($datum) {
   #   Falls ein vorgegebenes Datum ein Feiertag ist, wird die Bezeichnung des
   #   Feiertages zurueck gegeben.
   #   $datum          vorgegebenes Datum im Standardformat
   #                   'tt.mm.yyyy'
   #
   $jahr=substr($datum,6);
   $ft=self::kal_feiertage($jahr);
   for($i=1;$i<=count($ft);$i=$i+1)
      if($ft[$i]['datum']==$datum) return $ft[$i]['name'];
   }
}
?>
