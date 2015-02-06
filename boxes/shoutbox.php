<?php
#   Copyright by Manuel
#   Support www.ilch.de

defined ('main') or die ( 'no direct access' );

//Smilies in der Textarea ausgeben
function smilies ($string) {
 global $smilies_array;
  if (!isset($smilies_array)) {
    $smilies_array = array();
    $erg = db_query("SELECT ent, url, emo FROM `prefix_smilies`");
	  while ($row = db_fetch_object($erg) ) {
		  $smilies_array[$row->ent] = $row->emo.'#@#-_-_-#@#'.$row->url;
	  }
  }
  foreach ($smilies_array as $k => $v) {
    list($emo, $url) = explode('#@#-_-_-#@#', $v);
    $string = str_replace($k,''.$url.'',$string);
  }
  return $string;
}


//Smilies in die Textarea übermitteln
function smiliesshb () {
  global $lang;
  $zeilen = 5; $i = 0;
	$b = '<script language="JavaScript" type="text/javascript">function moreSmilies () { var x = window.open("about:blank", "moreSmilies", "width=620,height=240,left=650,top=300,status=no,scrollbars=yes,resizable=yes"); ';
  $a = '';
  $erg = db_query('SELECT emo, ent, url FROM `prefix_smilies`');
	while ($row = db_fetch_object($erg) ) {

    $b .= 'x.document.write ("<a href=\"javascript:opener.put_shb(\''.addslashes(addslashes($row->ent)).'\')\">");';
    $b .= 'x.document.write ("<img style=\"border: 0px; padding: 5px;\" src=\"include/images/smiles/'.$row->url.'\" title=\"'.$row->emo.'\"></a>");';

    if ($i<0) {
      # float einbauen
      if($i%$zeilen == 0 AND $i <> 0) { $a .= '<br /><br />'; }
      $a .= '<a href="javascript:put_shb(\''.addslashes($row->ent).'\')">';
      $a .= '<img style="margin: 2px;" src="include/images/smiles/'.$row->url.'" border="0" title="'.$row->emo.'"></a>';
    }
    $i++;
	}
  $b .= ' x.document.write("<br /><br /><center><a href=\"javascript:window.close();\">'.$lang['close'].'</a></center>"); x.document.close(); }</script>';
  if ($i>0) { $a .= '<br /><center><a href="javascript:moreSmilies();">Smilies</a></center>'; }
  $a = $b.$a;
  return ($a);
}



// IP und Zeit ausgeben
  $shoutbox_VALUE_name2 = getenv("REMOTE_ADDR");
  $datum = date("j.n.Y");
  $zeit = date(" H:i ");

//Avatar prüfen und ggf. anzeigen
  $abf = 'SELECT avatar FROM prefix_user WHERE name = "'.$_SESSION['authname'].'"';
  $erg = db_query($abf);
  $row = db_fetch_object($erg);

  if ($allgAr['sh_avatar'] == 1) {
   if (loggedin ())  {
    if (file_exists($row->avatar)) {
       $avatar = $row->avatar; // $row->avatar sollte den Pfad zum Bild enthalten
    } else {
       $avatar = 'include/images/avatars/noavatar.jpg';
    }
    } else {
    $avatar = 'include/images/avatars/gast.png';
    }
    $avatar = '<img src="'.$avatar.'" border="0" width="50" height="63"/>'; 
    } else {
    $avatar = '';
    }

//Nickname und Gast 
 if ( loggedin() ) {
    $shoutbox_VALUE_name = $_SESSION['authname'];
  } else {
    $shoutbox_VALUE_name = 'Gast';
  }

//Shoutbox , Namenschutz 
  if (has_right($allgAr['sb_recht'])){
   if (!empty($_POST['shoutbox_submit']) AND chk_antispam ('shoutbox')) {
    $insert = true;
    if (!loggedin()) {
        $shoutbox_nickname = escape($_POST['shoutbox_nickname'], 'string');
        $shoutbox_nickname = substr($shoutbox_nickname, 0, 15);
        if (db_count_query("SELECT COUNT(*) FROM prefix_user WHERE name LIKE '%$shoutbox_nickname%'") > 0) {
            $insert = false;
            echo '<center><font color="#ff0000">Benutzen sie einen anderen Namen, dieser ist bereits vergeben.</font></center><br />';
        }
    } else {
        $shoutbox_nickname = escape($_SESSION['authname'], 'string');
    }
    $shoutbox_textarea = escape($_POST['shoutbox_textarea'], 'textarea');
    $shoutbox_textarea = preg_replace("/\[.?(url|b|i|u|img|code|quote)[^\]]*?\]/i", "", $shoutbox_textarea);
    $shoutbox_textarea = strip_tags($shoutbox_textarea);
    if (!empty($shoutbox_nickname) AND !empty($shoutbox_textarea) AND $insert) {
        db_query('INSERT INTO `prefix_shoutbox` (`nickname`,`textarea`,`time`) VALUES ( "' . $shoutbox_nickname . '" , "' . $shoutbox_textarea . '", ' . time() . ' ) ');
    }
}
?>
<script type="text/javascript">
// S-Box-Smileys START
function simple_insert_shb(aTag,eTag) {

  var input = document.forms['shoutbox'].elements['shoutbox_textarea'];
  input.focus();
  /* für Internet Explorer */
  if(typeof document.selection != 'undefined') {
    /* Einfügen des Formatierungscodes */
    var range = document.selection.createRange();
    var insText = range.text;
    range.text = aTag + insText + eTag;
    /* Anpassen der Cursorposition */
    range = document.selection.createRange();
    if (insText.length == 0) {
      range.move('character', -eTag.length);
    } else {
      range.moveStart('character', aTag.length + insText.length + eTag.length);
    }
    range.select();
  }
  /* für neuere auf Gecko basierende Browser */
  else if(typeof input.selectionStart != 'undefined')
  {
    /* Einfügen des Formatierungscodes */
    var start = input.selectionStart;
    var end = input.selectionEnd;
    var insText = input.value.substring(start, end);
    input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
    /* Anpassen der Cursorposition */
    var pos;
    if (insText.length == 0) {
      pos = start + aTag.length;
    } else {
      pos = start + aTag.length + insText.length + eTag.length;
    }
    input.selectionStart = pos;
    input.selectionEnd = pos;
  }
  /* für die übrigen Browser */
  else
  {
    /* Abfrage der Einfügeposition */
    var pos = input.value.length;

    /* Einfügen des Formatierungscodes */
    var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
    input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
  }
}

function  put_shb ( towrite ) {
 simple_insert_shb ( towrite, '' );

}
// S-Box-Smileys END
</script>
<?php

    echo '<form method="POST" name="shoutbox">';

    echo '<table width="96%" cellpadding="0" cellspacing="0" border="0" class="shoutboxmitte">
          <tr><td align="left">
		    <table width="96%" cellpadding="0" cellspacing="0" border="0" class="shoutboxeingabe">
          <tr><td align="center">
              <input type="text" size="11" name="shoutbox_nickname" value="'.$shoutbox_VALUE_name.'" onFocus="if (value == \''.$shoutbox_VALUE_name.'\') {value = \'\'}" onBlur="if (value == \'\') {value = \''.$shoutbox_VALUE_name.'\'}" maxlength="15">
			  <input type="submit" value="'.$lang['formsub'].'" name="shoutbox_submit" class="shoutabsenden">
              <textarea style="width: 96%" cols="14" rows="3" name="shoutbox_textarea"></textarea>'.smiliesshb ('shoutbox', 0).'
              </td></tr></table></td></tr>
          </table>';

    echo '<table width="96%" cellpadding="2" cellspacing="1" border="0">
          <tr><td align="left">'.get_antispam ('shoutbox', 0).' 
              </td></tr>
          </table>';

    echo '</form>';
    } else { 
  //Gaeste
    echo '<table width="96%" cellpadding="2" cellspacing="0" border="0">
          <tr><td style="font-size:11px; text-align:center;"><b>Zum Posten<br />Bitte Einloggen!</td></tr>
          </table>';
  } 
  echo '<table width="96%" cellpadding="2" cellspacing="0" class="shouttext">';
  $erg = db_query('SELECT `prefix_shoutbox`.*, `prefix_user`.name, `prefix_user`.avatar FROM `prefix_shoutbox` LEFT JOIN `prefix_user` ON `prefix_user`.name =  `prefix_shoutbox`.nickname ORDER BY id DESC LIMIT ' . (is_numeric($allgAr['sb_limit'])?$allgAr['sb_limit']:5));
  $class = 'Cnorm';
  while ($row = db_fetch_object($erg)) { 
  
  $erg2 = db_query('SELECT * FROM prefix_user');
  while ($row2 = db_fetch_object($erg2)) {
	  if ($row->nickname == $row2->name) {
		  $id= $row2->id;
		  $staat= $row2->staat!="" ? $row2->staat : "na.gif";
	  }
	 }
    // Avatar prüfen und ggf. anzeigen
    if ($allgAr['sh_avatar'] == 1) {
    if ($row->name)  {
    if (file_exists($row->avatar)) {
       $avatar = $row->avatar; // $row->avatar sollte den Pfad zum Bild enthalten
    } else {
       $avatar = 'include/images/avatars/noavatar.jpg';
    }
    } else {
    $avatar = 'include/images/avatars/gast.png';
    }
    $avatar = '<img src="'.$avatar.'" border="0" width="50" height="63"/>'; 
    } else {
    $avatar = '';
    }
		if ($row->uid) { 
	$nickname = '<a href="index.php?user-details-'.$row->uid.'">'.$row->nickname.'</a>'; 
	} else { 
	$nickname = $row->nickname; 
	}

	
	  $class = ( $class == 'Cmite' ? 'Cnorm' : 'Cmite' );
         $time = is_null($row->time) ? '<br />' : '<br /><em style="font-size:0.9em;">'.date('d.m.y - H:i',$row->time).' Uhr</em><br />';
echo '
  <tr>
	<td class="shoutboxmitte">
	  <table width="90%" class="shoutboxname">
		<tr>
		  <td valign="middle" align="left">
			<span class="shout_name"><img src="include/images/flags/'.$staat.'" border="0" alt="Land: '.$staat.'" title="Land: '.$staat.'" /> '.$nickname.'</span><span class="shout_datum">'.$time.'</span>
		  </td>
		 </tr>
	  </table>
	  <table width="100%" class="shoutboxoben">
		<tr>
		  <td align="left">
		    <div style="float:left; width:30%;"><a href="index.php?user-details-'.$id.'">'.$avatar.'</a></div>
           '.smilies(preg_replace( '/([^\s]{'.$allgAr['sb_maxwordlength'].'})(?=[^\s])/', "$1\n", bbcode($row->textarea))).'
		   </td>
		 </tr>
	  </table>
	</td>
  </tr>
  <tr>
	<td height="13"></td>
  </tr>';
}
  
echo '<tr><td><div class="archivausen"><div class="archivinnen"><a href="index.php?shoutbox">ARCHIV</a></div></div></td></tr></table>';

?>