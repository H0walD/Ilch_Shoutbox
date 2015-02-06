<?php
#   Copyright by: Manuel Staechele
#   Support: www.ilch.de und www.fhag-gaming.de
#   modifiziert bei Puni
defined ('main') or die ( 'no direct access' );

$title = $allgAr['title'] . ' :: Shoutbox ' . $lang['archiv'];
$hmenu = 'Shoutbox ' . $lang['archiv'];
$design = new design ( $title , $hmenu );
$design->header();

if (is_siteadmin()) {
	# loeschen
	if ($menu->getA(1) == 'd' AND is_numeric($menu->getE(1))) {
		db_query("DELETE FROM `prefix_shoutbox` WHERE `id` = " . $menu->getE(1));
	}
	# alle loeschen
	if ($menu->get(1) == 'delall') {
		if (is_numeric($menu->get(2))) {
			$anz = db_result(db_query("SELECT COUNT(*) FROM `prefix_shoutbox`"),0) - $menu->get(2);
			if ($anz > 0) {
				db_query("DELETE FROM `prefix_shoutbox` ORDER BY `id` ASC LIMIT $anz");
			}
		} else {
			db_query("DELETE FROM `prefix_shoutbox`");
		}
	}
}

echo '<script type="text/javascript">
function del() {
	if (anz = prompt("Wieviele Einträge sollen erhalten bleiben?\n(Es werden die zuletzt geschriebenen erhalten)", "0")) {
		if (anz >= 0) {
			window.location.href = "index.php?shoutbox-delall-"+anz;
		} else alert("Du musst eine Zahl größer gleich 0 eingeben");
	}
}
</script>';

# mehrere seiten falls gefordert
$limit = 10; // Limit
$page = ($menu->getA(1) == 'p' ? $menu->getE(1) : 1 );
$MPL = db_make_sites($page,'',$limit ,'?shoutbox','shoutbox');
$anfang = ($page - 1) * $limit;
$class = 'Cnorm';
echo '<table width="100%" align="center" class="rand03" cellpadding="2" cellspacing="0" border="0"><tr class="CdarkGOW" height="30"><td><b><font color="#ffffff">Nachrichtenverlauf</font></b></td></tr></table>';
echo '<table width="100%" align="center" class="rand02" cellpadding="2" cellspacing="1" border="0" bgcolor="#cccccc">';
$erg = db_query('SELECT `prefix_shoutbox`.*, `prefix_user`.`name`, `prefix_user`.`avatar` FROM `prefix_shoutbox` LEFT JOIN `prefix_user` ON `prefix_user`.`name` = `prefix_shoutbox`.`nickname` ORDER BY `id` DESC LIMIT ' . $anfang . ',' . $limit . '');
while ($row = db_fetch_assoc($erg) ) {
	// Avatar pruefen und ggf. anzeigen
	if ($allgAr['sh_avatar'] == 1) {
		if ($row['name']) {
			if (file_exists($row['avatar'])) {
				$avatar = $row['avatar']; // $row->avatar sollte den Pfad zum Bild enthalten
			} else {
				$avatar = 'include/images/avatars/noavatar.jpg';
			}
		} else {
			$avatar = 'include/images/avatars/gast.png';
		}
		$avatar = '<img src="' . $avatar . '" border="0" width="50" height="63" />'; 
	} else {
		$avatar = '';
	}
	$class = ( $class == 'Cmite' ? 'Cnorm' : 'Cmite' );
	echo '<tr class="' . $class . '"><td width="110" align="center">';
	if ( is_siteadmin() ) {
		echo '<a href="index.php?shoutbox-d' . $row['id'] . '"><img src="include/images/icons/delete.png" alt="' . $lang['delete'] . '" title="' . $lang['delete'] . '"></a>&nbsp;';
	}
	$time = is_null($row['time']) ? '' : '<em>' . date('d.m.Y - H:i', $row['time']).' Uhr</em>&nbsp;';
	echo $avatar . '</td><td style="padding-left:6px"><b>' . $row['nickname'] . '</b> am ' . $time . ':<br><br> ' . smilies( bbcode( preg_replace( '/([^\s]{' . $allgAr['sb_maxwordlength'] . '})(?=[^\s])/', "$1\n", $row['textarea']) ) ) . '</td></tr>';
}
echo '</table>';
if (is_siteadmin()) {
	echo '<a href="javascript:del();">' . $lang['clearshoutbox'] . '</a>&nbsp;&nbsp;';
}
echo '<div align="center"><b>Seite:</b> ' . $MPL . '</div>';


$design->footer();
?>