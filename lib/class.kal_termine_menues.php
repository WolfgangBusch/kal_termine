<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Februar 2019
 */
define ('KAL_MENUE',     'menue');
define ('KAL_MONAT',     'monat');
define ('KAL_KW',        'kw');
define ('KAL_JAHR',      'jahr');
define ('KAL_DATUM',     'datum');
define ('KAL_FEIERTAG',  'feiertag');
define ('KAL_PID',       'pid');
define ('KAL_KATEGORIE', 'kategorie');
define ('KAL_SUCHEN',    'suchen');
define ('KAL_VORHER',    'vorher');
#
class kal_termine_menues {
#
#----------------------------------------- Inhaltsuebersicht
#   Terminblatt
#         kal_terminblatt($termin)
#   Monatsmenue
#         kal_monatsmenue($kategorie,$mon,$jahr,$termtyp)
#         kal_monatsmenue_modus($kategorie,$mon,$jahr,$modus,$termtyp)
#   Monats-/Wochen-/Tagesblatt
#         kal_monatsblatt($kategorie,$mon,$jahr,$termtyp)
#         kal_wochenblatt($kategorie,$kw,$jahr,$termtyp)
#         kal_tagesblatt($kategorie,$datum,$termtyp)
#         kal_mowotablatt($kategorie,$mon,$kw,$jahr,$datum,$termtyp)
#         kal_termin_titel($termin)
#         kal_stundenleiste()
#         kal_terminpixel($termin)
#         kal_eval_start_ende($termin)
#   Termin-Auswahlmenue
#         kal_tages_search_menue($datum,$param,$termtyp)
#         kal_wochen_search_menue($kw,$jahr,$param,$termtyp)
#         kal_monats_search_menue($mon,$jahr,$param,$termtyp)
#         kal_search_menue($mon,$kw,$jahr,$datum,$param,$termtyp)
#   Startmenues
#         kal_link($par,$mennr,$linktext,$modus)
#         kal_define_menues()
#         kal_startmenues()
#         kal_menue($kategorie,$termtyp,$mennr)
#
#----------------------------------------- Terminblatt
static public function kal_terminblatt($termin) {
   #   Rueckgabe des HTML-Codes zur formatierten Ausgabe der Daten eines Termins
   #   $termin         assoziatives Array des Termins
   #   In der Ueberschrift-Zeile jedes Terminblatts sind zwei Links enthalten, und zwar
   #   auf das Kalendermenue des Monats (POST-Parameter: KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr):
   #      $monat     Nummer des aktuellen Monats
   #      $jahr      4-stellige Jahreszahl des aktuellen Jahres
   #      $men       =1: Link-Ziel ist das Monatsmenue des aktuellen Monats
   #      Linktext:  «
   #   und auf das aktuelle Tagesblatt (POST-Parameter: KAL_MENUE=$men, KAL_DATUM=$datum):
   #      $datum:    10-Zeichen-Datums-String des aktuellen Tages
   #      $men       =4: Link-Ziel ist das Tagesblatt des aktuellen Tages
   #      Linktext:  <
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_kalender::kal_monat_kw($datum)
   #      kal_termine_kalender::kal_monate()
   #
   if(count($termin)>2):
     $datum=$termin[datum];
     $name=$termin[name];
     else:
     $datum=kal_termine_kalender::kal_heute();
     $name='<small>... kein Termin vorhanden/angegeben ...</small>';
     endif;
   $mmm=kal_termine_kalender::kal_monat_kw($datum);
   $monate=kal_termine_kalender::kal_monate();
   $wt=kal_termine_kalender::kal_wotag($datum);
   $mon=$mmm[monat];
   $monat=$monate[$mon];
   $jahr=$mmm[jahr];
   #
   # --- Ueberschrift mit Ruecklinks und Link auf die Terminliste
   $menues=self::kal_define_menues();
   $menrr=$menues[1][1];
   $menr =$menues[4][1];
   $seite='<table class="kal_border">
    <tr valign="top">
        <td class="kal_txtb1" title="'.$menrr.' '.$monat.' '.$jahr.'">
            '.self::kal_link(KAL_MONAT.'='.$mon.'&'.KAL_JAHR.'='.$jahr,1,'&nbsp;&nbsp;&nbsp;&laquo;',1).'
        </td>
        <td class="kal_txtb1" title="'.$menr.' '.$datum.'">
            '.self::kal_link(KAL_DATUM.'='.$datum,4,'&nbsp;&nbsp;<small>&lt;</small>',1).'
        </td>
        <td width="50"> </td>
        <td class="kal_txt_titel">
            '.$name.'</td></tr>';
   #
   # --- return, falls kein Termin angegeben ist
   if(count($termin)<=2):
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Termin:</td>
        <td class="kal_termv kal_termval">
            '.$datum.'</td></tr>
</table>';
     return $seite;
     endif;
   #
   # --- Datum, Start- und Endzeit
   $str='';
   if(!empty($termin[beginn])) $str=$str.$termin[beginn];
   if(!empty($termin[ende])) $str=$str.' - '.$termin[ende];
   if(!empty($str)) $str='<br/>
            '.$str.' Uhr';
   $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Termin:</td>
        <td class="kal_termv kal_termval">
            '.$wt.', '.$datum.$str;
   #
   # --- eventuelle bis zu 4 Zusatzzeiten
   $z='';
   if(!empty($termin[zeit2]))
     $z=$z.'<br/>
            '.$termin[zeit2].' Uhr: &nbsp; '.$termin[text2];
   if(!empty($termin[zeit3]))
     $z=$z.'<br/>
            '.$termin[zeit3].' Uhr: &nbsp; '.$termin[text3];
   if(!empty($termin[zeit4]))
     $z=$z.'<br/>
            '.$termin[zeit4].' Uhr: &nbsp; '.$termin[text4];
   if(!empty($termin[zeit5]))
     $z=$z.'<br/>
            '.$termin[zeit5].' Uhr: &nbsp; '.$termin[text5];
   if(!empty($z)) $seite=$seite.$z;
   $seite=$seite.'</td></tr>';
   #
   # --- Ausrichter
   $ausrichter=$termin[ausrichter];
   if(!empty($ausrichter))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Ausrichter:</td>
        <td class="kal_termv kal_termval">
            '.$ausrichter.'</td></tr>';
   #
   # --- Veranstaltungsort
   $ort=$termin[ort];
   if(!empty($ort))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Ort:</td>
        <td class="kal_termv kal_termval">
            '.$ort.'</td></tr>';
   #
   # --- Link
   $link=$termin[link];
   if(!empty($link)):
     $tg='_blank';
     if(!empty($_GET[page])) $tg='_self';
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Link:</td>
        <td class="kal_termv kal_termval">
            <a href="'.$link.'" target="'.$tg.'">'.substr($link,0,50).' . . .</td></tr>';
     endif;
   #
   # --- Hinweise
   $komm=$termin[komm];
   if(!empty($komm))
     $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Hinweise:&nbsp;&nbsp;&nbsp;</td>
        <td class="kal_termv">
            '.$komm.'</td></tr>';
   #
   # --- Kategorie
   $seite=$seite.'
    <tr valign="top">
        <td colspan="3" class="kal_txtb2">
            Kategorie:</td>
        <td class="kal_termv">
            '.$termin[kategorie].'</td></tr>';
   $seite=$seite.'
</table>';
   return $seite;
   }
#
#----------------------------------------- Monatsmenue
static public function kal_monatsmenue($kategorie,$mon,$jahr,$termtyp) {
   return self::kal_monatsmenue_modus($kategorie,$mon,$jahr,1,$termtyp);
   }
static public function kal_monatsmenue_modus($kategorie,$mon,$jahr,$modus,$termtyp) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalendermenues fuer einen Monat.
   #   Der Monat wird bestimmt durch ein vorgegebenes Datum. Ist kein solches Datum
   #   vorgegeben, wird der aktuelle Monat angenommen.
   #   Die Tage, an denen Termine anstehen, werden durch Schraffur markiert.
   #   Die Ausgabe des Kalendermenues erfolgt im aktuellen Browserfenster.
   #   $kategorie      Kategorie der Termine, falls leer: Termine aller Kategorien
   #   $mon            Nummer des Monats eines Jahres
   #                   im Format 'mm' oder 'm'
   #                   falls intval($mon)<=0 oder >12 ist, werden $mon
   #                   und $jahr aus dem aktuellen Datum entnommen
   #   $jahr           Kalenderjahr im Format 'yyyy'
   #                   falls intval($jahr)<=0 ist, werden $mon und
   #                   $jahr aus dem aktuellen Datum entnommen
   #                   falls intval($jahr)<=99 ist, wird $jahr durch $jahr+2000 ersetzt
   #   $modus          =1:    das Menue erhaelt Links auf alle Tage des Monats
   #                          sowie auf Vorjahr, Vormonat, Folgemonat, Folgejahr
   #                          Tage, an denen Termine anstehen, werden schraffiert
   #                   sonst: das Menue enthaelt keine Links und keine Schraffuren
   #   $termtyp        ='Spieldaten': es handelt sich um die Spieldaten
   #                   ='':           es handelt sich um echte Termine
   #   Jeder Tag des Monats ist als Button-Link abgelegt mit der Nummer des Monatstages als
   #   Linktext und mit den POST-Parametern: KAL_MENUE=$men, KAL_DATUM=$datum, KAL_FEIERTAG=$feiertag
   #      $datum:    10-Zeichen-Datums-String des gewaehlten Tages
   #      $feiertag: Bez. des Feiertags, falls $datum ein Feiertag ist
   #      $men       =4: Link-Ziel ist das Tagesblatt des gewaehlten Tages
   #   Jede Kalenderwoche ist als Button-Link abgelegt mit der Nummer der Kalenderwoche
   #   als Linktext und mit den POST-Parametern: KAL_MENUE=3, KAL_KW=$kw, KAL_JAHR=$jahr
   #      $kw:       Nummer der gewaehlten Kalenderwoche
   #      $jahr:     4-stellige Jahreszahl des Jahres, zu dem die Kalenderwoche gehoert
   #                 (Jahresanfang/-ende!)
   #      $men       =3: Link-Ziel ist das Wochenblatt der gewaehlten Kalenderwoche
   #   Jeder Vor-/Folgemonat, jedes Vor-/Folgejahr ist als Button-Link abgelegt mit
   #   den Zeichen  « (Vorjahr),  < (Vormonat),  > (Folgemonat),  » (Folgejahr)
   #   als Linktext und mit den POST-Parametern: KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #      $monat     Nummer des gewaehlten Monats
   #      $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      $men       =2: Link-Ziel ist das Monatsblatt des gewaehlten Monats
   #   Die Ueberschrift des Monatsmenues ist unterlegt mit einem Button-Link
   #   mit den POST-Parametern: KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #      $monat     Nummer des aktuellen Monats
   #      $jahr      4-stellige Jahreszahl des aktuellen Jahres
   #      $men       =5: Link-Ziel ist das Monatsfiltermenue des aktuellen Monats
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_kalender::kal_wotag_nr($wt)
   #      kal_termine_kalender::kal_datum_feiertag($datum)
   #      kal_termine_kalender::kal_montag_kw($datum)
   #      kal_termine_tabelle::kal_get_monatstermine($von,$bis,$termtyp)
   #      kal_termine_tabelle::kal_filter_termine_kategorie($termine,$kategorie)
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
   for($i=1;$i<=$anztage;$i=$i+1) $daterm[$i]=0;
   #
   # --- Termine in diesem Monat auslesen
   $dat1='01.'.$strmon.'.'.$strjahr;
   $dat2=$anztage.'.'.$strmon.'.'.$strjahr;
   $termine=kal_termine_tabelle::kal_get_monatstermine($dat1,$dat2,$termtyp);
   $termine=kal_termine_tabelle::kal_filter_termine_kategorie($termine,$kategorie);
   #
   # --- Tage markieren, an denen ein Termin vorhanden ist
   for($k=1;$k<=count($termine);$k=$k+1):
      $datum=$termine[$k][datum];
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
        $kwjahr=$strjahr;
        if($kw<=1 and $strmon>=12): $kwjahr=$kwjahr+1; endif;
        else:
        $datum=33-$tagfirst;
        $vorjahr=$strjahr-1;
        $datum=$datum.'.12.'.$vorjahr;
        $kw=kal_termine_kalender::kal_montag_kw($datum);
        $kwjahr=$vorjahr;
        if($tagfirst<=4): $kwjahr=$strjahr; endif;
        endif;
      #
      # --- Link auf die Kalenderwoche
      $title='Kalenderwoche '.$kw.' ('.$strjahr.')';
      $zeil=$zeil.'
        <td align="right" class="kal_txt2" title="'.$title.'">
            '.self::kal_link(KAL_KW.'='.$kw.'&'.KAL_JAHR.'='.$strjahr,3,$kw.'&nbsp;',$modus).'
            </td>';
      #
      # --- Kalenderzellen
      for($k=1;$k<=count($wt);$k=$k+1):
         $tag=$tag+1;
         if($tag<1 or $tag>$anztage):
           #
           # --- Tag vor oder nach dem Monat
           $strjahrvn=$strjahr;
           if($tag<1):
             #  -  Tag vor dem Monat
             $strmonvn=$mon-1;
             if($strmonvn<1):
               $strmonvn=12;
               $strjahrvn=$strjahr-1;
               endif;
             if(strlen($strmonvn)<2) $strmonvn='0'.$strmonvn;
             $tagevn=kal_termine_kalender::kal_monatstage($strjahrvn);
             $tagvn=$tagevn[intval($strmonvn)]+$tag;
             else:
             #  -  Tag nach dem Monat
             $strmonvn=$mon+1;
             if($strmonvn>12):
               $strmonvn=1;
               $strjahrvn=$strjahr+1;
               endif;
             if(strlen($strmonvn)<2) $strmonvn='0'.$strmonvn;
             $tagvn=$tag-$anztage;
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
           if($daterm[$tag]>0 and $modus==1) $schraff='id="hatch"';
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
           $temp='
        <td '.$schraff.' class="'.$class.'" title="'.$title.'">
            '.self::kal_link(KAL_DATUM.'='.$datum.'&'.KAL_FEIERTAG.'='.$feiertag,4,$tag,$modus).'
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
   $ueber=$ueber.'
                <tr><td class="kal_txtb1" title="'.$monat.' '.$vjahr.'">
                        '.self::kal_link(KAL_MONAT.'='.$vmon.'&'.KAL_JAHR.'='.$vjahr,1,'&laquo;&nbsp;',$modus).'
                    </td>
                    <td class="kal_txtb1" title="'.$vormonat.' '.$vorjahr.'">
                        '.self::kal_link(KAL_MONAT.'='.$vormon.'&'.KAL_JAHR.'='.$vorjahr,1,'<small>&lt;</small>',$modus).'
                    </td>';
   #
   # ---  aktueller Monat (Ueberschrift, Link auf das Monatsblatt)
   $menues=self::kal_define_menues();
   $mentit=$menues[2][1];
   $ueber=$ueber.'
                    <td align="center" class="kal_txt_titel" title="'.$mentit.' '.$monat.' '.$strjahr.'">
                        '.self::kal_link(KAL_MONAT.'='.intval($strmon).'&'.KAL_JAHR.'='.$strjahr,2,$monat.'&nbsp;'.$strjahr,$modus).'
                    </td>';
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
   # --- Link auf den Folgemonat und das Folgejahr
   $ueber=$ueber.'
                    <td class="kal_txtb1" title="'.$nachmonat.' '.$nachjahr.'">
                        '.self::kal_link(KAL_MONAT.'='.$nachmon.'&'.KAL_JAHR.'='.$nachjahr,1,'<small>&gt;</small>',$modus).'
                    </td>
                    <td class="kal_txtb1" title="'.$monat.' '.$njahr.'">
                        '.self::kal_link(KAL_MONAT.'='.intval($nmon).'&'.KAL_JAHR.'='.$njahr,1,'&nbsp;&raquo;',$modus).'
                    </td></tr>
            </table></td></tr>';
   if($modus!=1)
     $ueber='
    <tr><td colspan="8" align="center" class="kal_txt_titel">'.$monat.'&nbsp;'.$strjahr.'</td></tr>';
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
static public function kal_monatsblatt($kategorie,$mon,$jahr,$termtyp) {
   return self::kal_mowotablatt($kategorie,$mon,'',$jahr,'',$termtyp);
   }
static public function kal_wochenblatt($kategorie,$kw,$jahr,$termtyp) {
   return self::kal_mowotablatt($kategorie,'',$kw,$jahr,'',$termtyp);
   }
static public function kal_tagesblatt($kategorie,$datum,$termtyp) {
   return self::kal_mowotablatt($kategorie,'','','',$datum,$termtyp);
   }
static public function kal_mowotablatt($kategorie,$mon,$kw,$jahr,$datum,$termtyp) {
   #   Rueckgabe des HTML-Codes zur Ausgabe eines Kalenderblatts fuer entweder
   #   - einen Kalendermonat (nicht leer: $mon, $jahr, leer: $kw, $datum) oder
   #   - eine Kalenderwoche  (nicht leer: $kw, $jahr,  leer: $mon, $datum ) oder
   #   - einen einzelnen Tag (nicht leer: $datum,      leer: $mon, $kw, $jahr)
   #   Falls alle 4 Datumsparameter leer sind, wird der heutige
   #   Tag als einzelner Tag angenommen
   #   $kategorie      Kategorie der Termine, falls leer: Termine aller Kategorien
   #   $mon            Nummer des Monats eines Jahres im Format 'mm' oder 'm'
   #   $kw             Nummer der Woche (<=53) im Format 'ww' oder 'w'
   #                   falls die 53. Kalenderwoche nicht existiert, wird die
   #                   erste Kalenderwoche des Folgejahres angenommen
   #   $jahr           Kalenderjahr im Format 'yyyy' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   $datum          Datum im Format 'tt.mm.yyyy'' (akzeptiert wird auch
   #                   das Format 'yy', wobei dann '20' vorne ergaenzt wird)
   #   $termtyp        ='Spieldaten': es handelt sich um die Spieldaten
   #                   ='':           es handelt sich um echte Termine
   #      ----- JEDES Terminblatt enthaelt einen Button-Link
   #      1) auf das zugehoerige Monatsmenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #            Linktext   «
   #            $men       =1 (Typ des Menues: Monatsmenue)
   #            $monat     Nummer des gewaehlten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      2), ... auf alle Terminblaetter der angezeigten Termine mit den
   #         POST-Parametern: KAL_MENUE=$men, KAL_PID=$pid
   #            Linktext   Bezeichnung des Termins
   #            $men       =8 (Typ des Menues: Terminblatt)
   #            $datum     Datum des gewaehlten Tages
   #            $pid       Nummer des Termins in der Datenbanktabelle
   #      ----- Das Monatsblatt enthaelt ferner diesen Button-Link
   #      3) auf das zugehoerige Monatsfiltermenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr,
   #         KAL_KATEGORIE=, KAL_SUCHEN=, KAL_VOHER=
   #            Linktext   Ueberschrift des Monatsblatts
   #            $men       =5 (Typ des Menues: Monatsfiltermenue)
   #            $monat     Nummer des gewaehlten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #         Die Filter-Parameter KAL_KATEGORIE, KAL_SUCHEN, KAL_VORHER sind hier leer
   #      ----- Das Wochenblatt enthaelt ferner diese Button-Links
   #      3) auf das zugehoerige Monatsblatt mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #             Linktext   <
   #            $men       =2 (Typ des Menues: Monatsblatt)
   #            $monat     Nummer des zugehoerigen Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      4) auf das zugehoerige Wochenfiltermenue mit den POST-Parametern: KAL_MENUE=$men,
   #         KAL_KW=$kw, KAL_JAHR=$jahr, KAL_KATEGORIE=, KAL_SUCHEN=, KAL_VORHER=
   #            Linktext   Ueberschrift des Wochenblatts
   #            $men       =6 (Typ des Menues: Wochenfiltermenue)
   #            $kw        Nummer der gewaehlten Kalenderwoche
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem die Woche gehoert
   #         Die Filter-Parameter KAL_KATEGORIE, KAL_SUCHEN, KAL_VORHER sind hier leer
   #      ----- Das Tagesblatt enthaelt ferner diese Button-Links
   #      3) auf das zugehoerige Wochenblatt mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr
   #            Linktext   <
   #            $men       =3 (Typ des Menues: Wochenblatt)
   #            $kw        Nummer der zugehoerigen Kalenderwoche
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem die Woche gehoert
   #      4) auf das zugehoerige Tagesfiltermenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_DATUM=$datum, KAL_KATEGORIE=, KAL_SUCHEN=, KAL_VORHER=
   #            Linktext   Ueberschrift des Tagesblatts
   #            $men       =7 (Typ des Menues: Tagesfiltermenue)
   #            $datum     Datum des gewaehlten Tages
   #         Die Filter-Parameter KAL_KATEGORIE, KAL_SUCHEN, KAL_VORHER sind hier leer
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_terminpixel($termin)
   #      self::kal_stundenleiste()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      self::kal_termin_titel($termin)
   #      kal_termine_config::kal_define_stundenleiste()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_standard_datum($datum)
   #      kal_termine_kalender::kal_wochentage()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_feiertage($jahr)
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_kw($datum)
   #      kal_termine_kalender::kal_wotag($datum)
   #      kal_termine_kalender::kal_monat_kw($datum)
   #      kal_termine_tabelle::kal_get_monatstermine($von,$bis,$termtyp)
   #      kal_termine_tabelle::kal_filter_termine_kategorie($termin,$kategorie)
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_get_wochentermine($dat1,$dat2,$termtyp)
   #      kal_termine_tabelle::kal_get_tagestermine($dat,$termtyp)
   #
   # --- falls alle Terminbereiche leer sind, wird der heutige Tag als Einzeltermin angenommen
   $stdatum=$datum;
   $heute=kal_termine_kalender::kal_heute();
   if(empty($mon) and empty($kw) and empty($datum)) $stdatum=$heute;
   #
   # --- Formatierungen
   $strmon=$mon;
   if(intval($strmon)>0 and strlen($strmon)==1) $strmon='0'.$strmon;
   $strkw=$kw;
   $strjahr=$jahr;
   if(strlen($strjahr)==2) $strjahr='20'.$strjahr;
   $strjahr=substr($strjahr,0,4);
   if(!empty($stdatum)) $stdatum=kal_termine_kalender::kal_standard_datum($stdatum);
   #
   # --- mehrfach verwendete Daten
   $monate=kal_termine_kalender::kal_monate();   // alle Monatsnamen
   $bl='&nbsp;&nbsp;&nbsp;';  // Blanks als Abstandshalter
   $menues=self::kal_define_menues();
   #
   # --- Definition des Tageszeilen-Formats
   $daten=kal_termine_config::kal_define_stundenleiste();
   $colspan=$daten[2]-$daten[1];  // Anzahl der Tabellen-(Stunden-)Spalten
   #
   # --- Datums-Array $dat[] und Auslesen der Termine  (Monat/Woche/Datum)
   #     --- Kalendermonat
   if(!empty($mon) and !empty($jahr)):
     $mtage=kal_termine_kalender::kal_monatstage($strjahr);
     $end=$mtage[intval($mon)];
     for($i=1;$i<=$end;$i=$i+1):
        $tag=$i;
        if(strlen($tag)<2) $tag='0'.$tag;
        $dat[$i]=$tag.'.'.$strmon.'.'.$strjahr;
        endfor;
     $term=kal_termine_tabelle::kal_get_monatstermine($dat[1],$dat[$end],$termtyp);
     endif;
   #     --- Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     #     Montag (Starttag) der Woche als Datum
     $dat[1]=kal_termine_kalender::kal_kw_montag($kw,$strjahr);
     #     restliche Tage der Woche als Datum
     for($i=2;$i<=7;$i=$i+1) $dat[$i]=kal_termine_kalender::kal_datum_vor_nach($dat[1],$i-1);
     $term=kal_termine_tabelle::kal_get_wochentermine($dat[1],$dat[7],$termtyp);
     endif;
   #     --- Einzeldatum
   if(!empty($stdatum)):
     $dat[1]=$stdatum;
     $term=kal_termine_tabelle::kal_get_tagestermine($dat[1],$termtyp);
     endif;
   #     --- Herausfiltern der Termine der vorgegebenen Kategorie
   $term=kal_termine_tabelle::kal_filter_termine_kategorie($term,$kategorie);
   #
   # --- Ruecklinks, Ueberschrift inkl. Link auf das Terminmenue (Monat/Woche/Datum)
   $menrr=$menues[1][1];      // Monatsmenue
   #     --- Kalendermonat
   if(!empty($mon) and !empty($jahr)):
     $mentit=$menues[5][2];   // Monatsfiltermenue
     $menr  ='';              // (entfaellt)
     $titleu=$mentit;
     #     Ruecklink auf den Startmonat
     $von=$dat[1];
     $bis=$dat[$mtage[intval($mon)]];
     $backmon=intval(substr($von,3,2));
     $backjahr=substr($von,6);
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink1='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,1,'&laquo;',1);
     #     Ruecklink auf den Kalendermonat entfaellt hier
     $title2='';
     $ruecklink2='';
     #     Link auf die Terminliste (Monatsfiltermenue)
     $ueber=self::kal_link(KAL_MONAT.'='.$strmon.'&'.KAL_JAHR.'='.$strjahr.'&'.KAL_KATEGORIE.'='.$kategorie.'&'.
        KAL_SUCHEN.'=&'.KAL_VORHER.'=',5,'Termine im '.$monate[intval($mon)].' ('.$strjahr.')',1);
     endif;
   #     --- Kalenderwoche
   if(!empty($kw) and !empty($jahr)):
     $mentit=$menues[6][2];   // Wochenfiltermenue
     $menr  =$menues[2][1];   // Monatsblatt
     #     Ruecklink auf den Startmonat
     $mmm=kal_termine_kalender::kal_monat_kw($dat[1]);
     $backmon=$mmm[monat];
     $backjahr=$mmm[jahr];
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink1='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,1,'&laquo;',1);
     #     Ruecklink auf den Kalendermonat
     $title2=$menr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink2='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,2,'<small>&lt;</small>',1);
     #     Link auf die Terminliste (Wochenfiltermenue)
     $ueber=self::kal_link(KAL_KW.'='.$strkw.'&'.KAL_JAHR.'='.$strjahr.'&'.KAL_KATEGORIE.'='.$kategorie.'&'.
        KAL_SUCHEN.'=&'.KAL_VORHER.'=',6,'Termine in der Kalenderwoche '.$strkw.' ('.$strjahr.')',1);
     endif;
   #     --- Einzeltag
   if(!empty($stdatum)):
     $mentit=$menues[7][2];   // Tagesfiltermenue
     $menr  =$menues[3][1];   // Wochenblatt
      #     Ruecklink auf den Startmonat
     $mmm=kal_termine_kalender::kal_monat_kw($stdatum);
     $backmon=$mmm[monat];
     $backjahr=$mmm[jahr];
     $title1=$menrr.' '.$monate[$backmon].' '.$backjahr;
     $ruecklink1='
            '.self::kal_link(KAL_MONAT.'='.$backmon.'&'.KAL_JAHR.'='.$backjahr,1,'&laquo;',1);
     #     Ruecklink auf die Kalenderwoche
     $strkw=kal_termine_kalender::kal_kw($stdatum);
     $title2=$menr.' Kalenderwoche '.$strkw.' ('.$backjahr.')';
     $ruecklink2='
            '.self::kal_link(KAL_KW.'='.$strkw.'&'.KAL_JAHR.'='.$backjahr,3,'<small>&lt;</small>',1);
     #     Link auf die Terminliste (Tagesfiltermenue)
     $ueber=self::kal_link(KAL_DATUM.'='.$stdatum.'&'.KAL_KATEGORIE.'='.$kategorie.'&'.
        KAL_SUCHEN.'=&'.KAL_VORHER.'=',7,'Termine am '.$stdatum,1);
     endif;
   #
   # --- Zuordnen der Termine in tagesbezogene Arrays:
   #     $termin[$i][$k] ($k=1, 2, ...) Array der Termine pro Tag $i
   for($i=1;$i<=count($dat);$i=$i+1):
      $m=0;
      $termin[$i][1][datum]=$dat[$i];
      for($k=1;$k<=count($term);$k=$k+1):
         if($term[$k][datum]==$dat[$i]):
          $m=$m+1;
           $termin[$i][$m]=$term[$k];
           endif;
         endfor;
      endfor;
   #
   # --- Zusammenstellen der Tageszeilen
   #     $content[$i][$k] ($k=1, 2, ...) Array der Zeileninhalte (Termine) pro Tag $i
   for($i=1;$i<=count($dat);$i=$i+1):
      for($k=1;$k<=count($termin[$i]);$k=$k+1):
         #  -  Pixel-Breite des Termins
         $pixel=self::kal_terminpixel($termin[$i][$k]);
         $str='';
         if($pixel[vor]>0 and $pixel[dauer]>0):
           # --- div-Container (Streifen) mit zugemessener Position/Laenge und Termin-Link
           $title=self::kal_termin_titel($termin[$i][$k]);
           $param=KAL_DATUM.'='.$termin[$i][$k][datum].'&'.KAL_PID.'='.$termin[$i][$k][pid];
           $str=$str.'
            <div style="margin-left:'.$pixel[vor].'px; width:'.$pixel[dauer].'px;" title="'.$title.'" class="kal_termintag">
            '.kal_termine_menues::kal_link($param,8,$termin[$i][$k][name],1);
           else:
           $str=$str.'
            &nbsp;';
           endif;
         $str=$str.'</div>';
         $content[$i][$k]=$str;
         endfor;
      endfor;
   #
   # --- alle Feiertage
   $ft=kal_termine_kalender::kal_feiertage($strjahr);
   for($i=1;$i<=count($dat);$i=$i+1):
      $feiertag[$i]='';
      for($k=1;$k<=count($ft);$k=$k+1):
         if(substr($dat[$i],0,6)==substr($ft[$k][datum],0,6)):
           $feiertag[$i]=$ft[$k][name];
           break;
           endif;
         endfor;
      endfor;
   #
   # --- Ausgabe Ueberschriftszeile, Stundenleiste
   $string='
<table class="kal_border">
    <tr><td align="center" class="kal_txtb1" title="'.$title1.'">'.$ruecklink1.'</td>
        <td align="center" class="kal_txtb1" title="'.$title2.'">'.$ruecklink2.'</td>
        <td colspan="'.$colspan.'" class="kal_txt_titel" title="'.$titleu.'">
            '.$ueber.'</td>
        <td> </td></tr>
'.self::kal_stundenleiste();
   #
   # --- Ausgabe Tageszeilen
   $wtage=kal_termine_kalender::kal_wochentage();
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
      endfor;
   $string=$string.'
</table>';
   return $string;
   }
static public function kal_termin_titel($termin) {
   #   Rueckgabe des div-Container-Titels eines Termins im Tages-/Wochen-/Monatsblatt.
   #   Er enthaelt die Termin-Parameter beginn, ende, name, ort, ausrichter
   #   $termin         Daten eines Termins (assoziatives Array)
   #
   $title='';
   if(!empty($termin[beginn])):
     $title=$termin[beginn];
     if(!empty($termin[ende])) $title=$title.'-'.$termin[ende];
     if(!empty($title)) $title=$title.' Uhr: &nbsp; ';
     endif;
   if(empty($termin[beginn]) and empty($termin[beginn]) and
      empty($termin[zeit2])  and empty($termin[zeit3])  and
      empty($termin[zeit4])  and empty($termin[zeit5])     )
      $title='ganzt&auml;gig: &nbsp; ';
   if(!empty($termin[name]))
     $title=$title.$termin[name];
   $ort=$termin[ort];
   if(!empty($ort)) $title=$title.' ('.$ort.')';
   $ausrichter=$termin[ausrichter];
   if(!empty($ausrichter)) $title=$title.', Ausrichter: '.$ausrichter;
   return $title;
   }
static public function kal_stundenleiste() {
   #   Rueckgabe einer Stundenleiste in Form des Codes einer HTML-Tabelle
   #   in der folgenden Form:
   #          9:00       11:00       13:00       15:00
   #     |     |     |     |     |     |     |     |     |
   #   Uhrzeiten im 2-Stunden-Abstand
   #   Striche im 1-Stunden-Abstand (CSS-Style: border-left)
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
static public function kal_terminpixel($termin) {
   #   Rueckgabe von Pixel-Anzahlen, die eine halbgrafische Darstellung eines
   #   vorgegebenen Termins ermoeglichen, in Form eines assoziativen Arrays:
   #                   $pixel[vor]     Anzahl Pixel vor Beginn des Termins
   #                   $pixel[dauer]   Anzahl Pixel der Dauer des Termins
   #   Falls der Termin leer ist: $pixel[vor] = $pixel[dauer] = 0
   #   $termin         Daten eines Termins (Array)
   #   benutzte functions:
   #      self::kal_eval_start_ende($termin)
   #      kal_termine_config::kal_define_stundenleiste()
   #
   #
   if(empty($termin[name])):
     $pixel[vor]=0;
     $pixel[dauer]=0;
     return;
     endif;
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
   $beginn=$sez[beginn];
   $ende  =$sez[ende];
   #
   # --- Laenge vor dem Termin in Anzahl Pixel
   $pixel[vor]=0;
   $arr=explode(':',$beginn);
   $voruhr=intval($arr[0])+intval($arr[1])/60;
   $vor=$voruhr-$stauhr;
   $vor=intval($vor*$stdsiz);
   $vor=$vor-2;     // Feinkorrektur
   #
   # --- Laenge des Termins in Anzahl Pixel
   $pixel[dauer]=0;
   $arr=explode(':',$ende);
   $nachuhr=intval($arr[0])+intval($arr[1])/60;
   $dau=$nachuhr-$voruhr;
   $dau=intval($dau*$stdsiz);
   $dau=$dau+1;     // Feinkorrektur
   #
   return array('vor'=>$vor, 'dauer'=>$dau);
   }
static public function kal_eval_start_ende($termin) {
   #   Rueckgabe der Startuhrzeit und der Enduhrzeit eines Termins. Falls
   #   Start-/Enduhrzeit nicht definiert sind, werden sie sinnvoll ergaenzt.
   #   Rueckgabe als assoziatives Arrays (Start-/Enduhrzeit = [beginn]/[ende])
   #   - Falls der Terminbeginn leer ist:
   #                   der Beginn wird aus Zeit2/Zeit3/Zeit4/Zeit5 abgeleitet
   #   - Falls der Terminbeginn und Zeit2/Zeit3/Zeit4/Zeit5 leer sind:
   #                   es wird 'ganztaegig' angenommen und Anfang/Ende als
   #                   Startuhrzeit+eps/Enduhrzeit-eps berechnet (eps: siehe unten)
   #   - Falls das Terminende leer ist:
   #                   das Ende wird aus Zeit2/Zeit3/Zeit4/Zeit5 abgeleitet
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
   #     falls minz=min(zeit2,zeit3,zeit4,zeit5) nicht leer ist, ersetze Beginn durch minz
   #     falls auch minz leer ist, ersetze Startzeit durch $eps Minuten nach Startuhrzeit
   $beginn=$termin[beginn];
   $begm=min($termin[zeit2],$termin[zeit3],$termin[zeit4],$termin[zeit5]);
   if(empty($beginn) and !empty($begm)) $beginn=$begm;
   if(empty($beginn)) $beginn=$stauhr.':'.$eps;
   #
   # --- Endzeit bei fehlendem Ende eines Termins
   #     falls maxz=max(zeit2,zeit3,zeit4,zeit5) nicht leer ist, ersetze Endzeit durch maxz + $eps
   #     falls auch maxz leer ist, ersetze Endzeit durch $eps Minuten vor Enduhrzeit
   $ende=$termin[ende];
   $endm=max($termin[zeit2],$termin[zeit3],$termin[zeit4],$termin[zeit5]);
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
   return array('beginn'=>$beginn, 'ende'=>$ende);
   }
#
#----------------------------------------- Termin-Auswahlmenue
static public function kal_tages_search_menue($datum,$param,$termtyp) {
   return self::kal_search_menue('','','',$datum,$param,$termtyp);
   }
static public function kal_wochen_search_menue($kw,$jahr,$param,$termtyp) {
   return self::kal_search_menue('',$kw,$jahr,'',$param,$termtyp);
   }
static public function kal_monats_search_menue($mon,$jahr,$param,$termtyp) {
   return self::kal_search_menue($mon,'',$jahr,'',$param,$termtyp);
   }
static public function kal_search_menue($mon,$kw,$jahr,$datum,$param,$termtyp) {
   #   Rueckgabe des HTML-Codes fuer ein Menue zur Auswahl bzw. Suche von Terminen
   #   eines Tages (gemaess Parameter $datum) bzw.
   #   einer Woche (gemaess Parameter $kw und $jahr) bzw.
   #   eines Monats (gemaess Parameter $mon und $jahr)
   #   in Form eines Formulars
   #   $mon            Nummer des Monats eines Jahres
   #   $kw             Nummer der Woche
   #   $jahr           Kalenderjahr
   #   $datum          Datum im Format 'tt.mm.yyyy''
   #   $param          Array der Auswahlkriterien fuer die Termine
   #                   [1]:  Beschraenkung auf die hier angegebene Kategorie
   #                   [2]:  Suchbegriff (unabh. von Gross-/Kleinschreibung)
   #                         in einem dieser Termin-Parameter:
   #                            $termin[$i][name]
   #                            $termin[$i][komm]
   #                            $termin[$i][ausrichter]
   #                            $termin[$i][ort]
   #                   [3]:  ='on' (Checkbox), falls auch abgelaufene Termine
   #                         beruecksichtigt werden sollen
   #   $termtyp        ='Spieldaten': es handelt sich um die Spieldaten
   #                   ='':           es handelt sich um echte Termine
   #                   Das Formular liefert diese Parameter samt Werten:
   #                      KAL_KATEGORIE      $param[1]
   #                      KAL_SUCHEN         $param[2]
   #                      KAL_VORHER         $param[3]
   #                   und die 'hidden' gelieferten Parameter samt Werten:
   #                      KAL_DATUM          $datum       (bei Tagesterminen)
   #                      KAL_KW/KAL_JAHR    $kw/$jahr    (bei Wochenterminen)
   #                      KAL_MONAT/KAL_JAHR $monat/$jahr (bei Monatsterminen)
   #      Jedes Auswahlformular enthaelt diese beiden Button-Links:
   #      1) auf das zugehoerige Monatsmenue mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr
   #            Linktext   «
   #            $men       =1 (Typ des Menues: Monatsmenue)
   #            $monat     Nummer des gewaehlten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zu dem der Monat gehoert
   #      2) auf das zugehoerige Tages-, Wochen-, Monatsblatt der angezeigten
   #         Termine mit den POST-Parametern:
   #         KAL_MENUE=$men, KAL_DATUM=$datum                 (Tagesblatt)  bzw.
   #         KAL_MENUE=$men, KAL_KW=$kw, KAL_JAHR=$jahr       (Wochenblatt) bzw.
   #         KAL_MENUE=$men, KAL_MONAT=$monat, KAL_JAHR=$jahr (Monatsblatt)
   #            Linktext   <
   #            $men       =4/3/2 (Typ des Menues: Tages-/Wochen-/Monatsblatt)
   #            $datum     Datum des angezeigten Tages
   #            $kw        Nummer der angezeigten Woche
   #            $monat     Nummer des angezeigten Monats
   #            $jahr      4-stellige Jahreszahl des Jahres, zur Woche bzw. zum Monat
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #      kal_termine_config::kal_get_terminkategorien()
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_monatstage($jahr)
   #      kal_termine_kalender::kal_monate()
   #      kal_termine_kalender::kal_kw_montag($kw,$jahr)
   #      kal_termine_kalender::kal_datum_vor_nach($datum,$nzahl)
   #      kal_termine_kalender::kal_monat_kw($datum)
   #      kal_termine_tabelle::kal_datum_standard_mysql($datum)
   #      kal_termine_formulare::kal_terminliste($termin)
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_get_tagestermine($datum,$termtyp)
   #      kal_termine_tabelle::kal_get_wochentermine($von,$bis,$termtyp)
   #      kal_termine_tabelle::kal_get_monatstermine($von,$bis,$termtyp)
   #
   $strmon=$mon;
   if(strlen($strmon)<2) $strmon='0'.$strmon;
   $monate=kal_termine_kalender::kal_monate();
   $menues=self::kal_define_menues();
   #
   # --- Auslesen der Termine eines Tages
   if(!empty($datum)):
     $termin=kal_termine_tabelle::kal_get_tagestermine($datum,$termtyp);
     $hidden1='name="'.KAL_DATUM.'" value="'.$datum.'"';
     $hidden2='';
     $mmm=kal_termine_kalender::kal_monat_kw($datum);
     $startmonat=$mmm[monat];
     $startjahr=$mmm[jahr];
     $par2=KAL_DATUM.'='.$datum;
     $imam='am';
     $ueber=$datum;
     $men=4;
     $menfilter=7;
     $backtit=$menues[$men][1].' '.$ueber;
     endif;
   #
   # --- Auslesen der Termine einer Woche
   if(!empty($kw) and !empty($jahr)):
     $von=kal_termine_kalender::kal_kw_montag($kw,$jahr);
     $bis=kal_termine_kalender::kal_datum_vor_nach($von,6);
     $termin=kal_termine_tabelle::kal_get_wochentermine($von,$bis,$termtyp);
     $hidden1='name="'.KAL_KW.'" value="'.$kw.'"';
     $hidden2='name="'.KAL_JAHR.'" value="'.$jahr.'"';
     $mmm=kal_termine_kalender::kal_monat_kw($von);
     $startmonat=$mmm[monat];
     $startjahr=$mmm[jahr];
     $par2=KAL_KW.'='.$kw.'&'.KAL_JAHR.'='.$jahr;
     $imam='in der';
     $ueber='Kalenderwoche '.$kw.' ('.$jahr.')';
     $men=3;
     $menfilter=6;
     $backtit=$menues[$men][1].' '.$ueber;
     endif;
   #
   # --- Auslesen der Termine eines Monats
   if(!empty($mon) and !empty($jahr)):
     $von='01.'.$strmon.'.'.$jahr;
     $mtage=kal_termine_kalender::kal_monatstage($jahr);
     $mo=intval($strmon);
     $end=$mtage[$mo];
     $bis=$end.'.'.$strmon.'.'.$jahr;
     $termin=kal_termine_tabelle::kal_get_monatstermine($von,$bis,$termtyp);
     $hidden1='name="'.KAL_MONAT.'" value="'.$mo.'"';
     $hidden2='name="'.KAL_JAHR.'" value="'.$jahr.'"';
     $startmonat=intval($strmon);
     $startjahr=$jahr;
     $par2=KAL_MONAT.'='.$mo.'&'.KAL_JAHR.'='.$jahr;
     $imam='im';
     $ueber=$monate[$mo].' '.$jahr;
     $men=2;
     $menfilter=5;
     $backtit=$menues[$men][1].' '.$ueber;
     endif;
   #
   # --- Heraussuchen der Kategorien der Termine: $kat[]
   $kateg=kal_termine_config::kal_get_terminkategorien();
   for($i=1;$i<=count($kateg);$i=$i+1) $ind[$i]=0;
   for($i=1;$i<=count($termin);$i=$i+1):
      $kkk=$termin[$i][kategorie];
      for($k=1;$k<=count($kateg);$k=$k+1):
         if($kkk==utf8_encode($kateg[$k])):
           $ind[$k]=1;
           break;
           endif;
         endfor;
      endfor;
   $k=0;
   for($i=1;$i<=count($kateg);$i=$i+1):
      if($ind[$i]>0):
        $k=$k+1;
        $kat[$k]=$kateg[$i];
        endif;
      endfor;
   #
   # --- Ausgaben
   $kategorie=$param[1];
   $search=$param[2];
   $vorher=$param[3];
   $string='<table class="kal_border">';
   #
   # --- Ruecklink zum Start-Monatsblatt und zu den Tages-/Wochen-/Monatsterminen
   $startit=$menues[1][1].' '.$monate[intval($startmonat)].' '.$startjahr;
   $string=$string.'
    <tr valign="top">
        <td rowspan="2" class="kal_search kal_search_th">
            <table class="kal_transparent"><tr>
                <td title="'.$startit.'">
                    '.self::kal_link(KAL_MONAT.'='.$startmonat.'&'.KAL_JAHR.'='.$startjahr,1,'&laquo;&nbsp;&nbsp;',1).'</td>
                <td title="'.$backtit.'">
                    '.self::kal_link($par2,$men,'&nbsp;<small>&lt;</small>',1).'</td>
            </tr></table></td>
        <td colspan="4" class="kal_search kal_search_td">
            <form method="post">
            Filterung der Termine '.$imam.' <b>'.$ueber.'</b></td></tr>';
   #
   # --- Auswahlmaske: Kategorien
   $string=$string.'
    <tr><td class="kal_search kal_search_td">Kategorie:<br/>
            <select name="'.KAL_KATEGORIE.'" class="kal_option kal_select">
                <option class="kal_option kal_select"></option>';
   for($i=1;$i<=count($kat);$i=$i+1):
      if($kategorie==utf8_encode($kat[$i])):
        $sel='class="kal_option kal_select" selected="selected"';
        else:
        $sel='class="kal_option"';
        endif;
      $option='
                <option '.$sel.'>'.$kat[$i].'</option>';
      $string=$string.utf8_encode($option);
      endfor;
   $string=$string.'
            </select></td>';
   #
   # --- Auswahlmaske: Stichwort
   $string=$string.'
        <td class="kal_search kal_search_td">enthaltene Zeichenfolge:<br/>
            <input name="'.KAL_SUCHEN.'" type="text" value="'.$search.'" class="kal_form kal_input" /></td>';
   #
   # --- Auswahlmaske: auch abgelaufene Termine beruecksichtigen
   if($vorher=='1' or strtolower($vorher)=='on'):
     $chk='checked="checked"';
     else:
     $chk='';
     endif;
   $string=$string.'
        <td class="kal_search kal_search_td">abgelaufene Termine:<br/>
            auch anzeigen &nbsp; <input type="checkbox" name="vorher" '.$chk.' /></td>';
   #
   # --- Auswahlmaske: hidden-Parameter und Submit-Button
   $string=$string.'
        <td class="kal_search kal_search_td"><br/>
            <input type="hidden" '.$hidden1.' />';
   if(!empty($hidden2)) $string=$string.'
            <input type="hidden" '.$hidden2.' />';
   $string=$string.'
            <input type="hidden" name="'.KAL_MENUE.'" value="'.$menfilter.'" />
            <button type="submit" class="kal_form kal_submit">Termine suchen</button>
            </form></td></tr>
</table>';
   #
   # --- Herausfiltern der Termine, die zur gewaehlten Kategorie gehoeren
   if(!empty($kategorie)):
     $m=0;
     for($i=1;$i<=count($termin);$i=$i+1):
        if($termin[$i][kategorie]==$kategorie):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     unset($termin);
     for($i=1;$i<=count($term);$i=$i+1) $termin[$i]=$term[$i];
     unset($term);
     endif;
   #
   # --- alle Termine heraussuchen, die das Stichwort enthalten
   if(!empty($search)):
     $m=0;
     for($i=1;$i<=count($termin);$i=$i+1):
        if(!empty(stristr($termin[$i][name],$search)) or
           !empty(stristr($termin[$i][ausrichter],$search)) or
           !empty(stristr($termin[$i][ort],$search)) or
           !empty(stristr($termin[$i][komm],$search)) or
           !empty(stristr($termin[$i][text2],$search)) or
           !empty(stristr($termin[$i][text3],$search)) or
           !empty(stristr($termin[$i][text4],$search)) or
           !empty(stristr($termin[$i][text5],$search))):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     unset($termin);
     for($i=1;$i<=count($term);$i=$i+1) $termin[$i]=$term[$i];
     unset($term);
     endif;
   #
   # --- alle Termine heraussuchen, die nach dem heutigen Tage liegen
   if(empty($vorher)):
     $heutesql=kal_termine_tabelle::kal_datum_standard_mysql(kal_termine_kalender::kal_heute());
     $m=0;
     for($i=1;$i<=count($termin);$i=$i+1):
        if(kal_termine_tabelle::kal_datum_standard_mysql($termin[$i][datum])>=$heutesql):
          $m=$m+1;
          $term[$m]=$termin[$i];
          endif;
        endfor;
     unset($termin);
     for($i=1;$i<=count($term);$i=$i+1) $termin[$i]=$term[$i];
     unset($term);
     endif;
   #
   # --- Ausgabe der gesuchten Termine
   $string=$string.'
<div align="left"><br/>';
   $anz=count($termin);
   if($anz<=0):
     $string=$string.'+++++ keine entsprechenden Termine gefunden</div>';
     else:
     $ter='Termine';
     if($anz==1) $ter='Termin';
     $string=$string.'<u>'.$anz.' '.$ter.' ausgew&auml;hlt:</u></div>'.
        kal_termine_formulare::kal_terminliste($termin);
     endif;
   return $string;
   }
#
#----------------------------------------- Anzeige des Startmenues
static public function kal_link($par,$mennr,$linktext,$modus) {
   #   Rueckgabe einer Referenz in Form eines Submit-Buttons
   #   $par            Link-Parameter-String in der Form 'par1=PAR1&par2=PAR2&...'
   #   $mennr          Nummer des Menues, auf das die Referenz verweisen soll
   #   $linktext       anzuzeigender Linktext
   #   $modus          =1:    es wird ein Link zurueck gegeben
   #                   sonst: es wird statt des Links nur der Linktext zurueck gegeben
   #
   if($modus!=1) return $linktext;
   #
   $str='<form method="post" action="'.$_SERVER['REQUEST_URI'].'">';
                             ########## notwendig??? ############
   $arr=explode('&',$par);
   for($i=0;$i<count($arr);$i=$i+1):
      $brr=explode('=',$arr[$i]);
      $str=$str.'<input type="hidden" name="'.$brr[0].'" value="'.$brr[1].'" />';
      endfor;
   $str=$str.'<button type="submit" name="'.KAL_MENUE.'"  value="'.$mennr.'" class="kal_transparent kal_linkbutton">'.$linktext.'</button>';
   $str=$str.'</form>';
   return $str;
   }
static public function kal_define_menues() {
   #   Rueckgabe der moeglichen Startmenues in der Reihenfolge:
   #      [1]  Monatsmenue
   #      [2]  Monatsblatt
   #      [3]  Wochenblatt
   #      [4]  Tagesblatt
   #      [5]  Monatsfiltermenue
   #      [6]  Wochenfiltermenue
   #      [7]  Tagesfiltermenue
   #      [8]  Terminblatt
   #   Jedes Startmenue ist ein nummeriertes Array mit diesen Elementen:
   #         [1]  Bezeichnung des Menues
   #         [2]  Erlaeuterungstext fuer das Menue
   #
   $mome=array(1=>'Monatsmen&uuml;', 2=>'Monatsmen&uuml; inkl. Darstellung der '.
      'wesentlichen christlichen Feiertage. Alle Tage, an denen Termine eingetragen '.
      'sind, werden durch Schraffur gekennzeichnet und enthalten einen Link auf das '.
      'zugeh&ouml;rige Tagesblatt.');
   $mobl=array(1=>'Monatsblatt', 2=>'Monatsblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an den zugeh&ouml;rigen Tagen. '.
      'Jeder Termin enth&auml;lt einen Link auf ein Blatt mit seinen Daten.');
   $wobl=array(1=>'Wochenblatt', 2=>'Wochenblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an den zugeh&ouml;rigen Tagen. '.
      'Jeder Termin enth&auml;lt einen Link auf ein Blatt mit seinen Daten.');
   $tabl=array(1=>'Tagesblatt', 2=>'Tagesblatt mit einer halbgrafischen '.
      'Darstellung des Uhrzeitbereichs der Termine an diesem Tag. '.
      'Jeder Termin enth&auml;lt einen Link auf ein Blatt mit seinen Daten.');
   $mfm=array(1=>'Monatsfiltermen&uuml;', 2=>'Liste der Termine eines Monats mit '.
      'Filterfunktionen zur Verk&uuml;rzung der Liste');
   $wfm=array(1=>'Wochenfiltermen&uuml;', 2=>'Liste der Termine einer Woche mit '.
      'Filterfunktionen zur Verk&uuml;rzung der Liste');
   $tfm=array(1=>'Tagesfiltermen&uuml;', 2=>'Liste der Termine eines Tages mit '.
      'Filterfunktionen zur Verk&uuml;rzung der Liste');
   $tebl=array(1=>'Terminblatt', 2=>'Tabellarische Darstellung der Daten eines Termins');
   $menue=array(1=>$mome, 2=>$mobl, 3=>$wobl, 4=>$tabl, 5=>$mfm, 6=>$wfm, 7=>$tfm, 8=>$tebl);
   return $menue;
   }
static public function kal_startmenues() {
   #   Rueckgabe eines HTML-Menues zur Festlegung eines Termin-Startmenues
   #   inkl. Kurzbeschreibungen aller verfuegbaren Terminmenues
   #   benutzte functions:
   #      self::kal_define_menues()
   #      self::kal_link($par,$mennr,$linktext,$modus)
   #
   # --- Auswahl des Startmenues
   $men=self::kal_define_menues();
   $string='<div align="center">
<p><big><b>Verf&uuml;gbare Terminmen&uuml;s</b></big><br/>
auf Monats-, Wochen- oder Tagesbasis</p>
<table class="kal_border">';
   for($i=1;$i<=count($men);$i=$i+1)
      $string=$string.'
    <tr valign="top">
        <td class="kal_txtb2" title="'.$men[$i][1].'">
            '.self::kal_link(KAL_MENUE.'='.$i,$i,$men[$i][1],1).'</td>
        <td class="kal_termv kal_termval">'.$men[$i][2].'</td></tr>';
   $string=$string.'
</table>
</div>';
   return $string;
   }
static public function kal_menue($kategorie,$termtyp,$mennr) {
   #   Rueckgabe des HTML-Codes zur Anzeige des gewaehlten Startmenues
   #   $kategorie      Kategorie der Termine (falls leer: alle Kategorien)
   #   $termtyp        ='Spieldaten': es handelt sich um die Spieldaten
   #                   ='':           es handelt sich um echte Termine
   #   $mennr          vorgegebene Nummer des Startmenues (nur benutzt, falls diese
   #                   nicht als POST-Parameter eingelesen wurde und $mennr>0)
   #   benutzte functions:
   #      self::kal_startmenues()
   #      self::kal_monatsmenue($kategorie,$mon,$jahr,$termtyp)
   #      self::kal_terminblatt($termin)
   #      self::kal_tagesblatt($kategorie,$datum,$termtyp)
   #      self::kal_wochenblatt($kategorie,$kw,$jahr,$termtyp)
   #      self::kal_monatsblatt($kategorie,$monat,$jahr,$termtyp)
   #      self::kal_tages_search_menue($datum,$param,$termtyp)
   #      self::kal_wochen_search_menue($kw,$jahr,$param,$termtyp)
   #      self::kal_monats_search_menue($mon,$jahr,$param,$termtyp)
   #      kal_termine_kalender::kal_heute()
   #      kal_termine_kalender::kal_kw($datum)
   #   die Datenbank nutzende functions:
   #      kal_termine_tabelle::kal_get_tagestermine($datum,$termtyp)
   #
   # --- Parameter auslesen
   $men     =$_POST[KAL_MENUE];
   if(empty($men) and $mennr>0) $men=$mennr;
   $monat   =$_POST[KAL_MONAT];
   $kw      =$_POST[KAL_KW];
   $jahr    =$_POST[KAL_JAHR];
   $datum   =$_POST[KAL_DATUM];
   $pid     =$_POST[KAL_PID];
   $param[1]=$_POST[KAL_KATEGORIE];
   $param[2]=$_POST[KAL_SUCHEN];
   $param[3]=$_POST[KAL_VORHER];
   #
   # --- Einschraenkung auf eine Kategorie ggf. beibehalten
   if(empty($param[1]) and !empty($kategorie)) $param[1]=$kategorie;
   #
   # --- Auswahlmenue Startmenue
   if(empty($men))
     return self::kal_startmenues();
   #
   # --- Monatsmenue
   if($men==1):
     if(empty($monat) or empty($jahr)) $heute=kal_termine_kalender::kal_heute();
     if(empty($monat)) $monat=intval(substr($heute,3,2));
     if(empty($jahr))  $jahr=intval(substr($heute,6));
     return self::kal_monatsmenue($kategorie,$monat,$jahr,$termtyp);
     endif;
   #
   # --- Monatsblatt
   if($men==2):
     if(empty($monat) or empty($jahr)) $heute=kal_termine_kalender::kal_heute();
     if(empty($monat)) $monat=intval(substr($heute,3,2));
     if(empty($jahr))  $jahr=intval(substr($heute,6));
     return self::kal_monatsblatt($kategorie,$monat,$jahr,$termtyp);
     endif;
   #
   # --- Wochenblatt
   if($men==3):
     if(empty($kw) or empty($jahr)) $heute=kal_termine_kalender::kal_heute();
     if(empty($kw))   $kw=intval(kal_termine_kalender::kal_kw($heute));
     if(empty($jahr)) $jahr=intval(substr($heute,6));
     return self::kal_wochenblatt($kategorie,$kw,$jahr,$termtyp);
     endif;
   #
   # --- Tagesblatt
   if($men==4):
     if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
     return self::kal_tagesblatt($kategorie,$datum,$termtyp);
     endif;
   #
   # --- Monatsfiltermenue
   if($men==5):
     if(empty($monat) or empty($jahr)) $heute=kal_termine_kalender::kal_heute();
     if(empty($monat)) $monat=intval(substr($heute,3,2));
     if(empty($jahr))  $jahr=intval(substr($heute,6));
     return self::kal_monats_search_menue($monat,$jahr,$param,$termtyp);
     endif;
   #
   # --- Wochenfiltermenue
   if($men==6):
     if(empty($kw) or empty($jahr)) $heute=kal_termine_kalender::kal_heute();
     if(empty($kw))   $kw=intval(kal_termine_kalender::kal_kw($heute));
     if(empty($jahr)) $jahr=intval(substr($heute,6));
     return self::kal_wochen_search_menue($kw,$jahr,$param,$termtyp);
     endif;
   #
   # --- Tagesfiltermenue
   if($men==7):
     if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
     return self::kal_tages_search_menue($datum,$param,$termtyp);
     endif;
   #
   # --- Terminblatt
   if($men==8):
     if(empty($datum)) $datum=kal_termine_kalender::kal_heute();
     $term=kal_termine_tabelle::kal_get_tagestermine($datum,$termtyp);
     $m=1;
     for($i=1;$i<=count($term);$i=$i+1) if($term[$i][pid]==$pid) $m=$i;
     return self::kal_terminblatt($term[$m]);
     endif;
   }
}
?>
