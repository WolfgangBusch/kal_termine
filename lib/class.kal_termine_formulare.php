<?php
/**
 * Terminkalender Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2025
*/
#
class kal_termine_formulare extends kal_termine_menues {
#
#----------------------------------------- Methoden
#   Basismethoden
#      kal_proof_termin($termin)
#   Text- und Uhrzeitformular
#      kal_uhrmap($imgmapid,$canvid,$uhrwid)
#      kal_uhrzeitformular($keyuhr,$uhrzeit,$scrwid)
#      kal_textformular($keytxt,$text)
#   Terminformulare
#      kal_eingabeform($termin,$action,$kid,$error)
#      kal_funktionsleiste($termin,$men,$action,$kid,$pid)
#      kal_eingabeformular($action,$kid,$pid,$keyneu,$valneu)
#      kal_loeschformular($pid)
#
#----------------------------------------- Konstanten
const this_addon=kal_termine::this_addon;   // Name des AddOns
#
#----------------------------------------- Basismethoden
public static function kal_proof_termin($termin) {
   #   Ueberpruefen der Felder eines Termin-Arrays, u.a. auf
   #   - leere Pflichtfelder
   #   - Einhaltung der Restriktionen
   #   $termin         Termindaten in Form eines assoziativen Arrays
   #   Rueckgabe entsprechender Fehlermeldungen in farbiger Schrift
   #   (<span class="kal_fail">...</span>). Andernfalls leere Ruckgabe.
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keypid=$addon::TAB_KEY[0];   // pid
   $keynam=$addon::TAB_KEY[2];   // name
   $keydat=$addon::TAB_KEY[3];   // datum
   $keytag=$addon::TAB_KEY[6];   // tage
   $keywch=$addon::TAB_KEY[7];   // wochen
   $keymon=$addon::TAB_KEY[8];   // monate
   $datum =$termin[$keydat];
   $wochen=$termin[$keywch];
   $monate=$termin[$keymon];
   #
   $vor='<span class="kal_fail">';
   #
   # --- Pruefen der Pflichtfelder und der Restriktionen
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $restr=$cols[$key][3];
      if(empty($restr)) continue;
      $name=$cols[$key][1];
      if(str_contains($name,'Wiederholung')) $name=$name.' '.$cols[$key][2];
      $val=$termin[$key];
      if((str_contains($restr,'nicht leer') and empty($val))     or
         (str_contains($restr,'&gt;0')      and intval($val)<=0) or
         (str_contains($restr,'&ge;1')      and intval($val)<1)  or
         (str_contains($restr,'&ge;0')      and intval($val)<0)    )
        return $vor.'Das Feld <tt>\''.$name.'\'</tt> muss <tt>\''.$restr.'\'</tt> sein</span>';
      endfor;
   #
   # --- nicht zugleich woechentliche und monatliche Wiederholung
   $e1=$cols[$keywch][1].' '.$cols[$keywch][2];
   $e2=$cols[$keymon][1].' '.$cols[$keymon][2];
   if($wochen>0 and $monate>0)
     return $vor.'Nicht zugleich <tt>\'&gt;0\'<tt>: \''.$e1.'\'</tt> und <tt>\''.$e2.'\'</tt></span>';
   #
   # --- Ueberpruefen, dass beim monatl. Termin kein 5. Wochentag auftritt
   if($monate>0):
     if(intval(substr($datum,0,2))>28):
       $wotag=kal_termine_kalender::kal_wochentag($datum);
       return $vor.'monatlicher Termin, Datum \''.$datum.'\': Tageszahl &gt;28 (wäre 5. '.$wotag.')</span>';
       endif;
     endif;
   }
#
#----------------------------------------- Text- und Uhrzeitformular
public static function kal_textformular($keytxt,$text) {
   #   Rueckgabe eines HTML-Formulars zur Eingabe eines Fliesstextes in ein Inputfeld.
   #   Das Formular muss ausserhalb dieser function mit '<form...>' und '</form>'
   #   eingerahmt werden.
   #   $keytxt         Id des Ziel-Inputfeldes
   #   $text           vorbelegter Text, Wert des Ziel-Inputfeldes
   #
   $addon=self::this_addon;
   #
   # --- Parameter fuer das einrahmende Eingabeformular
   $hidden='';
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$addon::kal_post_in($key);
      $typ=$cols[$key][0];
      $typ=explode(' ',$typ)[0];
      $typ=explode('(',$typ)[0];
      if($typ=='varchar' or $typ=='text') $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'" id="'.$key.'">';
      if($typ!='varchar' and $typ!='text' and !empty($val)) $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'">';
      endfor;
   $action=$addon::kal_post_in($addon::ACTION_NAME);
   $pid   =$addon::kal_post_in($addon::KAL_PID,'int');
   $hidden=$hidden.'
    <input  type="hidden" name="'.$addon::ACTION_NAME.'" value="'.$action.'">
    <input  type="hidden" name="'.$addon::KAL_PID.'" value="'.$pid.'">';
   #
   # --- Menue-Nr. des Eingabeformulars
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
   #
   # --- Eingabefeld
   $str='
<!---------- Textformular ----------------------------------------->
<div align="center">
<form method="post">'.$hidden.'
<textarea id="textarea" rows="10" class="form_text kal_normal" autofocus>'.$text.'</textarea>';
   #
   # --- Menue darunter
   $str=$str.'
<br>
<table class="kal_table">';
   #     abbrechen, zurueck zum Eingabeformular
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig">
        abbrechen
    </button>';
   $str=$str.'
    <tr><td>'.$link.'</td>';
   #     Abstand
   $str=$str.'
        <td width="20">&nbsp;</td>';
   #     OK
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig"
            onclick="var text=document.getElementById(\'textarea\').value;
                     document.getElementById(\''.$keytxt.'\').value=text;">
        OK
    </button>';
   $str=$str.'
        <td>'.$link.'</td></tr>
</table>
</form>
</div>
<!---------- Ende Textformular ------------------------------------>
';
   return $str;
   }
public static function kal_uhrmap($imgmapid,$canvid,$uhrwid) {
   #   Rueckgabe einer Imagemap zum Bestimmen der Uhrzeit per Mausklick.
   #   Der Klick schreibt die gewaehlte Stunde bzw. Minute zweiziffrig in ein
   #   Inputfeld mit vorgegebener Id.
   #   $imgmapid       =$addon::STUNDE:  Stundenziffernblatt oder
   #                   =$addon::MINUTEN: Minutenziffernblatt
   #                   Id des Inputfeldes fuer die erklickte Stunde/Minuten,
   #                   zugleich Name/Id der Imagemap
   #   $canvid         canvas-Id, im canvas wird der Zeiger auf die geklickte
   #                   Stunde bzw. Minute grafisch dargestellt
   #   $uhrwid         Breite (und Hoehe) der Ziffernblaetter in Anzahl Pixel
   #
   $addon=self::this_addon;
   #
   # --- Radius des Ziffernblatts
   $radius=intval(0.5*$uhrwid);
   $radzif=array();
   $radsha=array();
   $radzif[1]=0.82*$radius;     // Radius aeuserer Kranz
   $radsha[1]=0.17*$radius;     // Shape-Radius im aeusseren Kranz
   if($imgmapid==$addon::KAL_STUNDE):
     $radzif[2]=0.51*$radius;   // Radius innerer Kranz
     $radsha[2]=0.13*$radius;   // Shape-Radius in inneren Kranz
     $picurl=cms_interface::url_assets().$addon::UHR_STD;
     $step  =1;
     else:
     $picurl=cms_interface::url_assets().$addon::UHR_MIN;
     $step  =5;
     endif;
   #
   # --- Image
   $canvid='canv_'.$imgmapid;
   $str='
<img src="'.$picurl.'" usemap="#'.$imgmapid.'" id="'.$imgmapid.'"  width="'.$uhrwid.'" height="'.$uhrwid.'">';
   #
   # --- Imagemap
   $anz=12;                     // Anzahl der Ziffern/Shapes pro Kranz
   $halb=$anz/2;
   $farben=$addon::kal_farben();
   $backcol=$farben[5]['rgb'];  // Hintergrundfarbe der angeklickten Shapes
   $str=$str.'
<map name="'.$imgmapid.'">';
   for($k=1;$k<=count($radzif);$k=$k+1):
      $radz=$radzif[$k];
      $rads=$radsha[$k];
      for($i=1;$i<=$anz;$i=$i+1):
         $stri=$i*$step;
         if($k>=2) $stri=$i+$anz;
         if($stri<=9) $stri='0'.$stri;
         if($stri=='60') $stri='00';   // Minuten
         if($stri=='12') $stri='00';   // Stunden
         if($stri=='24') $stri='12';   // Stunden
         $alpha=$i*M_PI/$halb;
         $x=$radius+$radz*sin($alpha);
         $y=$radius-$radz*cos($alpha);
         #     Inputfeld zur Aufnahme der erklickten Stunde/Minuten
         $str=$str.'
    <area shape="circle" coords="'.$x.', '.$y.', '.$rads.'" class="kal_linkbut"
          onclick="var canvas=document.getElementById(\''.$canvid.'\');
                   clearCanvas(canvas);
                   drawCircleLine('.$x.','.$y.','.$rads.',\''.$backcol.'\',canvas);
                   document.getElementById(\''.$imgmapid.'\').value=\''.$stri.'\';">';
         endfor;
      endfor;
   $str=$str.'
</map>
';
   return $str;
   }
public static function kal_uhrzeitformular($keyuhr,$uhrzeit,$scrwid) {
   #   Rueckgabe eines HTML-Formulars zur Eingabe einer Uhrzeit in ein Inputfeld.
   #   Das Formular muss ausserhalb dieser function mit '<form...>' und '</form>'
   #   eingerahmt werden.
   #   $keyuhr         Id des Ziel-Inputfeldes
   #   $uhrzeit        vorbelegte Uhrzeit, Wert des Ziel-Inputfeldes
   #   $scrwid         Viewport in Anzahl Pixel, ermittelt per Javascript
   #                   (beim Aufruf des Uhrzeitformulars, kal_eingabeform()
   #
   $addon=self::this_addon;
   $uhrwid=$addon::kal_uhrzeit_width($scrwid);   // Breite/Hoehe der Ziffernblaetter
   #
   # --- Parameter fuer das einrahmende Eingabeformular
   $hidden='';
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   for($i=0;$i<count($keys);$i=$i+1):
      $key=$keys[$i];
      $val=$addon::kal_post_in($key);
      $typ=$cols[$key][0];
      $typ=explode(' ',$typ)[0];
      $typ=explode('(',$typ)[0];
      if($typ=='time') $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'" id="'.$key.'">';
      if($typ!='time' and !empty($val)) $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'">';
      endfor;
   $action=$addon::kal_post_in($addon::ACTION_NAME);
   $pid   =$addon::kal_post_in($addon::KAL_PID,'int');
   $hidden=$hidden.'
    <input  type="hidden" name="'.$addon::ACTION_NAME.'" value="'.$action.'">
    <input  type="hidden" name="'.$addon::KAL_PID.'" value="'.$pid.'">';
   #
   # --- Menue-Nr. des Eingabeformulars
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1)
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
   #
   # --- Eingabefelder (Stunde und Minuten)
   $arr=explode(':',$uhrzeit);
   $hh=$arr[0];
   $mm=$arr[1];
   $std=$addon::KAL_STUNDE;
   $min=$addon::KAL_MINUTEN;
   $idstd='std_map';
   $idmin='min_map';
   $str='
<!---------- Uhrzeitformular -------------------------------------->
<style>
    .kal_uhr_up { position:relative; top:-'.$uhrwid.'px; }
</style>
<div align="center">
    <input id="'.$std.'" class="kal_uhr_feld form_readonly" name="'.$std.'" value="'.$hh.'" readonly>
    <span class="kal_uhr_feld">&nbsp;:&nbsp;</span>
    <input id="'.$min.'" class="kal_uhr_feld form_readonly" name="'.$min.'" value="'.$mm.'" readonly>
</div>
<br>
<form method="post">'.$hidden;
   #
   # --- Stunden-Map
   $canvid='canv_'.$std;
   $str=$str.'
             <!---------- Stunden-Map --------------->
<div id="'.$idstd.'" align="center">
<div class="kal_uhrline">
<canvas id="'.$canvid.'" width="'.$uhrwid.'" height="'.$uhrwid.'"></canvas>
<div class="kal_uhr_up">'.self::kal_uhrmap($std,$canvid,$uhrwid).'</div>
</div>';
   #
   # --- Menue darunter
   $str=$str.'
             <!---------- Funktionsmenue darunter --->
<br>
<table class="kal_table kal_uhr_up">';
   #     abbrechen, zurueck zum Eingabeformular
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig">
        abbrechen
    </button>';
   $str=$str.'
    <tr><td>'.$link.'</td>';
   #     Abstand
   $str=$str.'
        <td width="20">&nbsp;</td>';
   #     loeschen, in der Funktionsleiste vorhanden:
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig"
            onclick="document.getElementById(\''.$keyuhr.'\').value=\'\';">
        löschen
    </button>';
   $str=$str.'
        <td>'.$link.'</td>';
   #     Abstand
   $str=$str.'
        <td width="20">&nbsp;</td>';
   #     OK
   $link='
<a class="kal_linkbut kal_bigbig kal_basecol" title="Stunden übernehmen und Minuten auswählen"
   onclick="document.getElementById(\''.$idmin.'\').style.display=\'block\';
            document.getElementById(\''.$idstd.'\').style.display=\'none\';">
OK</a>';
   $str=$str.'
        <td>'.$link.'</td></tr>
</table>
</div>';
   #
   # --- Minuten-Map
   $canvid='canv_'.$min;
   $str=$str.'
             <!---------- Minuten-Map --------------->
<div id="'.$idmin.'" class="kal_hide" align="center">
<div class="kal_uhrline">
<canvas id="'.$canvid.'" width="'.$uhrwid.'" height="'.$uhrwid.'"></canvas>
<div class="kal_uhr_up">'.self::kal_uhrmap($min,$canvid,$uhrwid).'</div>
</div>';
   #
   # --- Menue darunter
   $str=$str.'
             <!---------- Funktionsmenue darunter --->
<br>
<table class="kal_table kal_uhr_up">';
   #     abbrechen, zurueck zum Eingabeformular
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig">
        abbrechen
    </button>';
     $str=$str.'
    <tr><td>'.$link.'</td>';
   #     Abstand
   $str=$str.'
        <td width="20">&nbsp;</td>';
   #     OK
   $link='
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_linkbut kal_transp kal_basecol kal_bigbig"
            onclick="var std=document.getElementById(\''.$std.'\').value;
                     var min=document.getElementById(\''.$min.'\').value;
                     var uhrz=std+\':\'+min;
                     if(!min || min.lenght===0) uhrz=std+\':00\';
                     if(!std || std.length===0) uhrz=\'\';
                     document.getElementById(\''.$keyuhr.'\').value=uhrz;">
        OK
    </button>';
   $str=$str.'
        <td>'.$link.'</td></tr>
</table>
</div>
</form>
<!---------- Ende Uhrzeitformular --------------------------------->
';
   return $str;
   }
#
#----------------------------------------- Terminformulare
public static function kal_eingabeform($termin,$action,$kid,$error) {
   #   Rueckgabe eines HTML-Formular zum Eintragen oder Korrigieren eines Termins
   #   in der Datenbanktabelle in Form einer 2-spaltigen Tabelle. Es ist kein
   #   Formularanfang/-ende enthalten. Die erste Spalte der Tabelle hat aber eine
   #   feste Breite, sodass die Tabelle nahtlos eregaenzt werden kann.
   #   Falls die eingegebenen Daten formal korrekt sind, werden die Datums- und
   #   Zeitangaben dabei weitestgehend standardisiert, d.h. in die Formate
   #   'tt.mm.yyyy' bzw. 'hh:mm' gebracht.
   #   Mit Durchfuehrung der Aktion werden diese POST-Parameter uebergeben:
   #   $keys[$i]       ($keys = array_keys($cols), $i=1,2,...,count($keys)-1,
   #                            $cols=Tabellenspalten-Namen, ohne pid)
   #   $termin         Array eines Termins fuer das Eingabeformular
   #   $action         Name der Aktion
   #   $kid            Id einer Terminkategorie oder =0/$addon::SPIEL_KATID
   #   $error          Fehlermeldung nach Eingabe von Daten
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keypid=$keys[0];   // pid
   $keykat=$keys[1];   // kat_id
   $keydat=$keys[3];   // datum
   $keytag=$keys[6];   // tage
   $keywch=$keys[7];   // wochen
   $keymon=$keys[8];   // monate
   $heute=kal_termine_kalender::kal_heute();
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'natsmenü')>0)  $menmom=$i;  // Monatsmenue
      if(strpos($menues[$i]['name'],'umsformu')>0)  $mendat=$i;  // Datumsformular
      if(strpos($menues[$i]['name'],'zeitform')>0)  $menuhr=$i;  // Uhrzeitformular
      if(strpos($menues[$i]['name'],'extformul')>0) $mentxt=$i;  // Textformular
      endfor;
   $pid  =$termin[$keypid];
   $datum=$termin[$keydat];
   #
   # --- erlaubte Kategorien
   $katids=$addon::kal_allowed_terminkategorien($kid);
   #
   # --- Hinweis auf Pflichtfelder
   $str='
<div><b>Felder mit &nbsp;<big>*</big>&nbsp; sind auszufüllen</b></div>
<table class="kal_table">';
   #
   # --- Schleife ueber die Formularzeilen
   $hide=TRUE;
   $zei=array();
   $m=0;
   for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
      $key=$keys[$i];
      $val=$termin[$key];
      #
      # --- Zeilen-/Parameterdaten
      $type=$cols[$key][0];
         $type=explode(' ',$type)[0];
         $type=explode('(',$type)[0];
      $titel=$cols[$key][1];
      $restr=$cols[$key][3];
         if(!empty($restr)) $restr=' &nbsp;'.$restr;
      $format='';
         if($type=='time') $format=' &nbsp;Uhr';
         if(str_contains($restr,'&ge;')) $format=$restr;
      $descr='';
         if(str_contains($restr,'&ge;')):
           $descr=' &nbsp;('.$cols[$key][2].')';
           $title='';
           if($key==$keytag) $title=$addon::TIP_TAGE;
           if($key==$keywch) $title=$addon::TIP_WOCHEN;
           if($key==$keymon) $title=$addon::TIP_MONATE;
           $descr=' &nbsp;
            <a class="kal_basecol kal_linkbut" title="'.$title.'"
               onclick="alert(\''.$title.'\');">'.$descr.'</a>';
           endif;
      #
      # --- Ausgabe einer Zwischenzeile (Link auf die Zusatzzeiten)
      if(str_contains($titel,'Beginn 2')) $str=$str.'
</table>';
      #
      # --- Pflichtfelder anzeigen
      $spname=$titel;
      if(str_contains($titel,'Kateg') or
         str_contains($restr,'nicht leer')) $spname='<b>*</b>'.$spname;
      #
      # --- bei leerer Eingabe ggf. Defaults einfuegen
      if(str_contains($titel,'Kateg')  and empty($val)) $val=1;
      if(str_contains($titel,'Tage')   and empty($val)) $val=1;
      if(str_contains($titel,'Wieder') and empty($val)) $val=0;
      #
      if($key==$keykat or $key==$keytag or $key==$keywch or $key==$keymon):
        #
        # --- Zeile mit der Kategorieauswahl, Anzahl Tage/Wochen/Monate
        if($key==$keykat):
          $wert=$addon::kal_select_kategorie($key,$val,$katids,FALSE);
          else:
          $wert=$addon::kal_select_anzahl($key,$val).$format.$descr;
          endif;
        else:
        #
        # --- sonstige Zeilen
        $class='kal_normal kal_linkbut';
        $wot='';
        $min='';
        $ro='';
        $intype='text';
        if($type=='varchar' or $type=='text')
          $class='form_text form_text2 '.$class;
        if($type=='date'):
          $class='form_date form_right '.$class;
          if(!empty($val)) $wot=kal_termine_kalender::kal_wotag($val).', ';
          $ro=' readonly';
          endif;
        if($type=='time'):
          $class='form_time form_right '.$class;
          $ro=' readonly';
          endif;
        if($type=='int'):
          $class='form_int form_right '.$class;
          $intype='number';
          if($key==$keytag) $min=' min="1"';
          if($key==$keywch or $key==$keymon) $min=' min="0"';
          endif;
        #
        # --- Inputfeld
        if($type=='varchar' or $type=='text'):
          $input='<div class="'.$class.'" name="'.$key.'" title="bearbeiten">'.$val.'</div>';
          else:
          $input='<input class="'.$class.'" type="'.$intype.'"'.$min.' name="'.$key.'" value="'.$val.'" title="bearbeiten"'.$ro.'>';
          endif;
        #     Inputfeld als Link (Texte)
        if($type=='varchar' or $type=='text'):
          $text_id='text_'.$i;
          $input='
            <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$mentxt.'" id="'.$text_id.'"
                    class="kal_transp kal_normal kal_linkbut"
                    onclick="var value=\''.$mentxt.'\'+\'_\'+\''.$key.'\'+\'_\'+\''.$val.'\';
                             document.getElementById(\''.$text_id.'\').value=value;">
                '.$input.'
            </button>';
          endif;
        #     Inputfeld als Link (Datum)
        if($type=='date'):
          $mon=substr($datum,3,2);
          $jahr=substr($datum,6);
          #     Parameter fuer das Monatsmenue ($action und $pid fuer das
          #     anschliessende Eingabemenue in der Funktionsleiste vorhanden)
          $input='
            <input  type="hidden" name="'.$addon::KAL_MONAT.'" value="'.$mon.'">
            <input  type="hidden" name="'.$addon::KAL_JAHR.'" value="'.$jahr.'">
            <input  type="hidden" name="'.$addon::KAL_MODUS.'" value="1">
            <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menmom.'"
                    class="kal_transp kal_normal kal_linkbut">
                '.$input.'
            </button>';
          endif;
        #     Inputfeld als Link (Uhrzeiten)
        if($type=='time'):
          $time_id='timeid_'.$i;
          $input='
            <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menuhr.'" id="'.$time_id.'"
                    class="kal_transp kal_normal kal_linkbut"
                    onclick="var value=\''.$menuhr.'\'+\'_\'+\''.$key.'\'+\'_\'+\''.$val.'\';
                             value=value+\'_\'+screen.width;   // Display-Viewport
                             document.getElementById(\''.$time_id.'\').value=value;">
                '.$input.'
            </button>';
          endif;
        $wert=$wot.$input.$format.$descr;
        endif;
      #
      # --- Zeile zusammenbauen
      $zeile='
    <tr valign="top">
        <th class="form_th">
            '.$spname.':</th>
        <td>'.$wert.'</td></tr>';
      if(substr($titel,0,1)==' '):
        if(!empty($val)) $hide=FALSE;
        $m=$m+1;
        $zei[$m]=$zeile;
        else:
        $str=$str.$zeile;
        endif;
      endfor;
   #
   # --- Zeilen für die Zusatzzeiten
   if($hide):
     $class=' class="kal_hide"';
     $class2='';
     $link1='
<a class="kal_black kal_linkbut"
   onclick="document.getElementById(\'hidden\').style.display=\'none\';
            document.getElementById(\'hidden_head\').style.display=\'block\';">
<span title="Formular verkürzen und Zwischenzeiten verbergen" class="kal_updown"> &nbsp; '.$addon::AWE_VORMON.' &nbsp; </span></a>';
     $link2='
<a class="kal_black kal_linkbut"
   onclick="document.getElementById(\'hidden\').style.display=\'block\';
            document.getElementById(\'hidden_head\').style.display=\'none\';">
<span title="Formular erweitern und Zwischenzeiten anzeigen" class="kal_updown"> &nbsp; '.$addon::AWE_NACHMON.' &nbsp; </span></a>';
     else:
     $class='';
     $class2=' class="kal_hide"';
     $link1='';
     $link2='';
     endif;
   $head='<b>ggf. bis zu 4 Zwischenzeiten: &nbsp; </b> &nbsp;';
   $str=$str.'
<div class="form_empline">&nbsp;</div>
<div id="hidden_head">'.$head.$link2.'</div>
<div id="hidden"'.$class.'>
<div'.$class2.'>'.$head.$link1.'</div>
<table class="kal_table">';
   for($m=1;$m<=count($zei);$m=$m+1) $str=$str.$zei[$m];
   $str=$str.'
</table>
</div>
';
   return $str;
   }
public static function kal_funktionsleiste($termin,$men,$action,$kid,$pid) {
   #   Rueckgabe eines HTML-Formulars fuer die Darstellung einer Funktionsleiste
   #   ueber verschiedenen Menues.
   #   $termin         Array eines Termins
   #   $men            Nummer des Menues, ueber dem die Funktionsleiste
   #                   angebracht ist:
   #                   =8: Eingabeformular (Abbrechen, Speichern)
   #                   =5: Terminblatt (Abbrechen, Editieren, Kopieren, Loeschen)
   #   $action         Aktion (Eintragen, Korrigieren, Kopieren)
   #   $kid            Id einer Terminkategorie oder =0/$addon::SPIEL_KATID
   #   $pid            Termin-Id oder 0
   #
   $addon=self::this_addon;
   $cols=$addon::kal_define_tabellenspalten();
   $keys=array_keys($cols);
   $keydat=$keys[3];   // datum
   $termdat='';
   if(count($termin)>0) $termdat=$termin[$keydat];
   #
   # --- Menuenummern
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
      if(strpos($menues[$i]['name'],'öschform')>0)  $mendel=$i;  // Loeschformular
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;  // Tagesblatt
      endfor;
   #
   # --- Menue
   $str='
<table class="funktl_table kal_100pro">
    <tr valign="middle">';
   #
   # --- Abbrechen (zurueck zum Tagesblatt)
   $para=array($addon::KAL_DATUM=>$termdat, $addon::KAL_MENUE=>$mentab);
   $tita='Abbrechen';
   $linktext='<span class="kal_icon">'.$addon::AWE_CANCEL.'</span>';
   $str=$str.'
        <!----------- Abbrechen --------------------------------->
        <td class="kal_basecol funktl_td" title="'.$tita.'">'
       .$addon::kal_link($para,$linktext,$tita,1).'</td>';
   #
   # --- Abstand
   $str=$str.'
        <td class="funktl_td">&nbsp;</td>';
   #
   # --- Korrigieren (weiter zum Eingabeformular)
   if($men==$menteb):
     $datum='';
     if(!empty($termin[$keydat])) $datum=$termin[$keydat];
     $parb=array($addon::ACTION_NAME=>$addon::ACTION_UPDATE, $addon::KAL_PID=>$pid, $addon::KAL_DATUM=>$datum, $addon::KAL_MENUE=>$menins);
     $titb='Bearbeiten';
     $linktext='<span class="kal_icon">'.$addon::AWE_EDIT.'</span>';
     $str=$str.'
        <!----------- Korrigieren ------------------------------->
        <td class="kal_basecol funktl_td" title="'.$titb.'">'.$addon::kal_link($parb,$linktext,$titb,1).'</td>';
     else:
     $str=$str.'
        <td class="funktl_td">&nbsp;</td>';
     endif;
   #
   # --- Kopieren (weiter zum Eingabeformular)
   if($men==$menteb):
     $parc=array($addon::ACTION_NAME=>$addon::ACTION_COPY, $addon::KAL_PID=>$pid, $addon::KAL_DATUM=>'', $addon::KAL_MENUE=>$menins);
     $titc='Kopieren (als Einzeltermin)';
     $linktext='<span class="kal_icon">'.$addon::AWE_COPY.'</span>';
     $str=$str.'
        <!----------- Kopieren ---------------------------------->
        <td class="kal_basecol funktl_td" title="'.$titc.'">'.$addon::kal_link($parc,$linktext,$titc,1).'</td>';
     else:
     $str=$str.'
        <td class="funktl_td">&nbsp;</td>';
     endif;
   #
   # --- Loeschen (weiter zum Loeschformular)
   if($men==$menteb):
     $repeat=$addon::kal_post_in($addon::ACTION_REPEAT,'int');
     $parl=array($addon::KAL_PID=>$pid, $addon::ACTION_REPEAT=>intval($repeat+1), $addon::KAL_MENUE=>$mendel);
     $titl='Loeschen';
     $linktext='<span class="kal_icon">'.$addon::AWE_DELETE.'</span>';
     $str=$str.'
        <!----------- Loeschen ---------------------------------->
        <td class="kal_basecol funktl_td" title="'.$titl.'">'.$addon::kal_link($parl,$linktext,$titl,1).'</td>
        <!------------------------------------------------------->';
     endif;
   #
   # --- Speichern (weiter zum Eingabeformular)
   if($men==$menins):
     $repeat=$addon::kal_post_in($addon::ACTION_REPEAT,'int');
     $tits='Speichern';
     $linktext='<span class="kal_icon">'.$addon::AWE_SAVE.'</span>';
     #     Terminparameter weitergeben durch hidden Input-Felder
     $hidden='';
     for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
        $key=$keys[$i];
        $val=$termin[$key];
        $typ=$cols[$key][0];
        $typ=explode(' ',$typ)[0];
        $typ=explode('(',$typ)[0];
        if($typ=='time'):
          $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'" id="'.$key.'">';
          else:
          if(!empty($val)) $hidden=$hidden.'
    <input  type="hidden" name="'.$key.'" value="'.$val.'">';
          endif;
        endfor;
     #     weitere hidden-Input-Felder
     $hidden=$hidden.'
    <input  type="hidden" name="'.$addon::ACTION_NAME.'" value="'.$action.'">
    <input  type="hidden" name="'.$addon::KAL_PID.'" value="'.$pid.'">
    <input  type="hidden" name="'.$addon::ACTION_REPEAT.'" value="'.intval($repeat+1).'">';
     #     Button
     $str=$str.'
        <!----------- Speichern --------------------------------->
        <td class="kal_basecol funktl_td" title="'.$tits.'">
<form method="post">'.$hidden.'
    <button type="submit" name="'.$addon::KAL_MENUE.'" value="'.$menins.'"
            class="kal_transp kal_linkbut">'.$linktext.'</button></td>';
     endif; 
   #
   $str=$str.'
      </tr>
</table>
';
   return $str;
   }
public static function kal_eingabeformular($action,$kid,$pid,$keyneu,$valneu) {
   #   Rueckgabe eines HTML-Formulars zum Eintragen, Korrigieren oder Kopieren
   #   eines Termins in die Datenbanktabelle. Zusaetzlich wird eine Fehlermeldung
   #   zurueck gegeben, falls die Termindaten unvollstaendig bzw. fehlerhaft sind.
   #   Die neue Termin-Id (Eintragen, Kopieren) wird in $_POST[$addon::KAL_PID]
   #   zurueck gegeben.
   #   $action         Aktion (Eintragen, Korrigieren, Kopieren)
   #   $kid            Id einer Terminkategorie oder =0/$addon::SPIEL_KATID
   #   $pid            Termin-Id oder =0
   #   $keyneu         Key eines neu einzutragenden Parameters
   #   $valneu         Wert des neu einzutragenden Parameters, er wurde in einem
   #                   sekundaeren Formular eingegeben
   #   =========================================================
   #   im Content-Managment-System sollte die Methode NUR EINMAL
   #   AUFGERUFEN werden (entweder im Frontend oder im Backend)
   #   =========================================================
   #
   $addon=self::this_addon;
   $keys=$addon::TAB_KEY;
   $keypid=$keys[0];   // pid
   $keykat=$keys[1];   // kat_id
   $keynam=$keys[2];   // name
   $keydat=$keys[3];   // datum
   $keytag=$keys[6];   // tage
   $keywch=$keys[7];   // wochen
   $keymon=$keys[8];   // monate
   $keykom=$keys[12];  // komm
   #
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'gabeform')>0)  $menins=$i;  // Eingabeformular
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;  // Tagesblatt
      endfor;
   $repeat=$addon::kal_post_in($addon::ACTION_REPEAT,'int');
   #
   # --- Formulardaten einlesen ($terform)
   $terform=$addon::kal_post_in_termin();
   #     war das Formular leer?
   $nz=0;
   for($i=1;$i<count($keys);$i=$i+1)   // ohne pid
      if(!empty($terform[$keys[$i]])) $nz=$nz+1;
   if($nz<4):   // mind. 4 nicht-leere Terminparameter: kat_id, name, datum, tage
     $empty=TRUE;
     else:
     $empty=FALSE;
     endif;
   #
   $frage='<span class="kal_fail">Sollen die Daten so übernommen werden?</span>';
   $spida='<span class="kal_fail kal_bold kal_big">Spieldaten können nicht gespeichert werden!</span>';
   $error='';
   $pidneu='';
   if(intval($pid)<=0):
     #
     # --- neuer Termin ($term), i.W. leere Daten
     $term=$terform;
     for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
        $key=$keys[$i];
        $val=$terform[$key];
        #     Kategorie-Id>0 und tage>0 setzen
        if($action==$addon::ACTION_INSERT and $key==$keykat and empty($val))
          $val=1;
        if($action==$addon::ACTION_INSERT and $key==$keytag and empty($val))
          $val=1;
        #     statt leerem Datum heutiges Datum
        if($action==$addon::ACTION_INSERT and $key==$keydat and empty($val))
          $val=kal_termine_kalender::kal_heute();
        $term[$key]=$val;
        endfor;
     else:
     #
     # --- zu korrigierenden/kopierenden Termin aus der Datenbanktabelle holen
     $term=kal_termine_tabelle::kal_select_termin($pid);
     if(count($term)<=0):
       $error='<span class="kal_fail">Der Termin ('.$keypid.'='.$pid.') wurde nicht gefunden</span>';
       for($i=0;$i<count($keys);$i=$i+1) $term[$keys[$i]]='';
       endif;
     #
     # --- neu eingegebene Parameter einfuegen (nur bei nicht-leerem Formular)
     if(!$empty):
       for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
          $key=$keys[$i];
          if($term[$key]!=$terform[$key]) $term[$key]=$terform[$key];
          endfor;
       endif;
     #
     # --- Kopie als Einzeltermin anpassen
     if($action==$addon::ACTION_COPY):
       for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
          $key=$keys[$i];
          $val=$term[$key];
          #     Termin-Kopie: zum Einzeltermin machen
          if($key==$keytag) $val=1;
          if($key==$keywch) $val=0;
          if($key==$keymon) $val=0;
          if($key==$keykom and empty($val)) $val='!!! ERSATZTERMIN !!!';
          $term[$key]=$val;
          endfor;
       endif;
     endif;
   #
   # --- formale Ueberpruefung der Termindaten
   $error=self::kal_proof_termin($term);
   #
   # --- erklickten Parameter eintragen, danach noch einmal fragen
   if(!empty($keyneu) and !empty($valneu)):
     $term[$keyneu]=$valneu;
     if(empty($error)) $error=$frage;
     endif;
   #
   $datum='';
   $pidneu=0;
   if(empty($error)):
     $msg='';
     #
     # --- Termin eintragen ($term) und neue Termin-Id zurueck geben
     if($action==$addon::ACTION_INSERT):
       if($repeat>0):
         if($addon::$SPIELDATEN):
           $pidneu=$spida;
           else:
           $pidneu=kal_termine_tabelle::kal_insert_termin($term);
           endif;
         else:
         $pidneu=$frage;
         endif;
       if(intval($pidneu)>0):
         $term=kal_termine_tabelle::kal_select_termin($pidneu);
         $datum=$term[$keydat];
         $titel=$term[$keynam];
         if(strlen($titel)>15) $titel=substr($titel,0,15).'...';
         $msg='<span class="kal_msg">Der Termin <code>'.$titel.'</code> wurde neu angelegt</span>';
         $term[$keypid]=$pidneu;
         else:
         $error=$pidneu;
         endif;
       endif;
     #
     # --- Termin korrigieren ($term)
     if($action==$addon::ACTION_UPDATE):
       if($repeat<=0):
         $error=$frage;
         else:
         #     weiterer Durchlauf: auch leere Eingabedaten beruecksichtigen
         $term=$addon::kal_post_in_termin();
         $term[$keypid]='';
         #     formale Ueberpruefung der Termindaten
         $error=self::kal_proof_termin($term);
         if(empty($error)):
           #     Termindaten neu abspeichern
           if($addon::$SPIELDATEN):
             $ret=$spida;
             else:
             $ret=kal_termine_tabelle::kal_update_termin($pid,$term);
             endif;
           $pidneu=$pid;
           if(empty($ret)):
             $datum=$term[$keydat];
             $titel=$term[$keynam];
             if(strlen($titel)>15) $titel=substr($titel,0,15).'...';
             $msg='<span class="kal_msg">Der Termin <code>'.$titel.'</code> wurde korrigiert</span>';
             else:
             $error=$ret;
             $_POST[$addon::ACTION_REPEAT]=0;
             endif;
           endif;
         endif;
       endif;
     #
     # --- Termin kopieren ($term)
     if($action==$addon::ACTION_COPY):
       if($repeat<=0):
         $error=$frage;
         else:
         #     weiterer Durchlauf: auch leere Eingabedaten beruecksichtigen
         for($i=1;$i<count($keys);$i=$i+1):   // ohne pid
            $key=$keys[$i];
            $val=$addon::kal_post_in($key);
            $term[$key]=$val;
            endfor;
         #     formale Ueberpruefung der Termindaten
         $error=self::kal_proof_termin($term);
         if(empty($error)):
           #     Termindaten abspeichern und neue Termin-Id zurueck geben
           if($addon::$SPIELDATEN):
             $pidneu=$spida;
             else:
             $pidneu=kal_termine_tabelle::kal_insert_termin($term);
             endif;
           if(intval($pidneu)>0):
             $datum=$term[$keydat];
             $titel=$term[$keynam];
             if(strlen($titel)>15) $titel=substr($titel,0,15).'...';
             $msg='<span class="kal_msg">Die Kopie von <code>'.$titel.'</code> wurde als Einzeltermin neu angelegt</span>';
             $term[$keypid]=$pidneu;
             else:
             $error=$pidneu;
             $_POST[$addon::ACTION_REPEAT]=0;
             endif;
           endif;
         endif;
       endif;
     endif;
   #
   if(!empty($msg) and empty($error)):
     #
     # --- erfolgreich, zum Tagesblatt schalten
     $_POST[$addon::KAL_DATUM]    =$datum;
     $_POST[$addon::KAL_PID]      =$pidneu;
     $_POST[$addon::KAL_KATEGORIE]=$kid;
     $_POST[$addon::KAL_MENUE]    =$mentab;
     $_POST[$addon::ACTION_REPEAT]=0;
     $formular='
<div><br>'.$msg.'<br>&nbsp;</div>'.kal_termine_menues::kal_menue(0,0);
     else:
     #
     # --- zurueck zum Eingabeformular
     $ueber='Eintragen';
     $zwtxt='neuen ';
     $zus='';
     if($action==$addon::ACTION_UPDATE):
       $ueber='Korrigieren';
       $zwtxt='';
       endif;
     if($action==$addon::ACTION_COPY):
       $ueber='Kopieren';
       $zwtxt='';
       $zus=' (als Einzeltermin)';
       endif;
     $formular='
<!---------- Terminformular -------------------------------------------->
<div id="'.$addon::ID_INPUTFORM.'">'.
     self::kal_funktionsleiste($term,$menins,$action,$kid,$pid).'
<h4 align="center">'.$ueber.' eines '.$zwtxt.'Termins'.$zus.'</h4>
'.self::kal_eingabeform($term,$action,$kid,$error);
     if(!empty($error))
       $formular=$formular.'
<div class="form_empline">&nbsp;</div>
<div>'.$error.'</div>
</form>
</div>
<!---------- Ende Terminformular --------------------------------------->
';
     endif;
   #
   return $formular;
   }
public static function kal_loeschformular($pid) {
   #   Rueckgabe eines HTML-Formulars zum Loeschen eines Termins in der
   #   Datenbanktabelle auf der Basis des Terminblatts. Zusaetzlich wird
   #   eine Fehlermeldung zurueck gegeben, falls der Termin nicht geloescht
   #   werden konnte.
   #   $pid            Termin-Id oder =0
   #   =========================================================
   #   im Content-Managment-System sollte die Methode NUR EINMAL
   #   AUFGERUFEN werden (entweder im Frontend oder im Backend)
   #   =========================================================
   #
   $addon=self::this_addon;
   $keys=$addon::TAB_KEY;
   $keypid=$keys[0];   // pid
   $keynam=$keys[2];   // name
   $keydat=$keys[3];   // datum
   #
   # --- Menuenummern
   $menues=$addon::kal_define_menues();
   for($i=1;$i<=count($menues);$i=$i+1):
      if(strpos($menues[$i]['name'],'gesblatt')>0)  $mentab=$i;  // Tagesblatt
      if(strpos($menues[$i]['name'],'minblatt')>0)  $menteb=$i;  // Terminblatt
      endfor;
   #
   $action=$addon::ACTION_DELETE;
   $_POST[$addon::ACTION_NAME]=$action;
   #
   # --- Daten des zu loeschenden Termins aus der Datenbanktabelle holen
   $error='';
   $termin=array();
   if(intval($pid)<=0):
     $error='<span class="kal_fail">Kein zu entfernender Termin angegeben</span>';
     else:
     $termin=kal_termine_tabelle::kal_select_termin($pid);
     if(count($termin)<=0):
       $error='<span class="kal_fail">Der Termin (Id '.$pid.') wurde nicht gefunden</span>';
       $datum='';
       else:
       $datum=$termin[$keydat];
       endif;
     endif;
   #
   # --- noch einmal zur Sicherheit das Terminblatt anzeigen
   $repeat=$addon::kal_post_in($addon::ACTION_REPEAT,'int');
   if($repeat<=1)
     $error='<span class="kal_fail">Soll dieser Termin wirklich gelöscht werden?</span>';
   #
   # --- Loeschen durchfuehren
   $msg='';
   if(empty($error))
     if($addon::$SPIELDATEN):
       $error='<span class="kal_fail kal_bold kal_big">Spieldaten können nicht gelöscht werden!</span>';
       else:
       $error=kal_termine_tabelle::kal_delete_termin($pid);
       endif;
   if(empty($error)):
     $msg='<span class="kal_msg">Der Termin <code>'.substr($termin[$keynam],0,15).'...</code> wurde gelöscht</span>';
     else:
     $error='
<div><br>'.$error.'</div>
';
     endif;
   #
   # --- bei Erfolg zum Tagesblatt
   if(!empty($msg) and empty($error)):
     $_POST[$addon::KAL_MENUE]=$mentab;
     $_POST[$addon::KAL_DATUM]=$datum;
     $_post[$addon::ACTION_REPEAT]=0;
     $_post[$addon::KAL_PID]=$pid;
     return '
<div><br>'.$msg.'<br>&nbsp;</div>
'.kal_termine_menues::kal_menue(0,0);
     else:
     #
     # --- zurueck zum Terminblatt
     return kal_termine_menues::kal_terminblatt($termin).$error;     
     endif;
   }
}
?>
