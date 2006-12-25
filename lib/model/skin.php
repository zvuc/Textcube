<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function setTreeSetting($owner, $setting) {
	global $database;
	foreach ($setting as $key => $value)
		$setting[$key] = mysql_tt_escape_string($value);
	$sql = "
	UPDATE {$database['prefix']}SkinSettings
	SET 
		tree 					= '{$setting['tree']}',
		colorOnTree				= '{$setting['colorOnTree']}',
		bgColorOnTree 			= '{$setting['bgColorOnTree']}',
		activeColorOnTree		= '{$setting['activeColorOnTree']}',
		activeBgColorOnTree 	= '{$setting['activeBgColorOnTree']}',
		labelLengthOnTree 		= {$setting['labelLengthOnTree']},
		showValueOnTree 		= " . (empty($setting['showValueOnTree']) ? 0 : 1) . "
	WHERE owner = $owner";
	if (update($sql) > - 1)
		return true;
	else
		respondErrorPage(mysql_error());
}

function reloadSkin($owner)
{
	global $database, $service;
	$skinSetting = getSkinSetting($owner);
	$skinName = $skinSetting['skin'];
	if (file_exists(ROOT . "/skin/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $service['encoding']))
			return;
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			setUserSetting('noneCommentMessage', NULL);
		else
			setUserSetting('noneCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			setUserSetting('singleCommentMessage', NULL);
		else
			setUserSetting('singleCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			setUserSetting('noneTrackbackMessage', NULL);
		else
			setUserSetting('noneTrackbackMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			setUserSetting('singleTrackbackMessage', NULL);
		else
			setUserSetting('singleTrackbackMessage', $value);
	}
}

function selectSkin($owner, $skinName) {
	global $database, $service;
	if (empty($skinName))
		return _t('실패했습니다.');
		
	if (strncmp($skinName, 'customize/', 10) == 0) {
		if (strcmp($skinName, "customize/$owner") != 0)
			return _t('실패 했습니다');
	} else {
		$skinName = Path::getBaseName($skinName);
		if (($skinName === '.') || ($skinName ==='..'))
			return _t('실패 했습니다');
	}
		
	if (file_exists(ROOT . "/skin/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $service['encoding']))
			return _t('실패했습니다.');
		$assignments = array("skin='$skinName'");
		$value = $xmls->getValue('/skin/default/recentEntries');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "entriesOnRecent=$value");
		$value = $xmls->getValue('/skin/default/recentComments');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsOnRecent=$value");
		$value = $xmls->getValue('/skin/default/itemsOnGuestbook');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsOnGuestbook=$value");
		$value = $xmls->getValue('/skin/default/tagsInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagsOnTagbox=$value");
		$value = $xmls->getValue('/skin/default/sortInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagboxAlign=$value");
		$value = $xmls->getValue('/skin/default/recentTrackbacks');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "trackbacksOnRecent=$value");
		$value = $xmls->getValue('/skin/default/expandComment');
		if (isset($value))
			array_push($assignments, 'expandComment=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/expandTrackback');
		if (isset($value))
			array_push($assignments, 'expandTrackback=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/lengthOfRecentNotice');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentNoticeLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentEntry');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentEntryLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentComment');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentCommentLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentTrackback');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentTrackbackLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfLink');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "linkLength=$value");
		$value = $xmls->getValue('/skin/default/showListOnCategory');
		if (isset($value))
			array_push($assignments, 'showListOnCategory=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/showListOnArchive');
		if (isset($value))
			array_push($assignments, 'showListOnArchive=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/tree/color');
		if (isset($value))
			array_push($assignments, "colorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/bgColor');
		if (isset($value))
			array_push($assignments, "bgColorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeColor');
		if (isset($value))
			array_push($assignments, "activeColorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeBgColor');
		if (isset($value))
			array_push($assignments, "activeBgColorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/labelLength');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "labelLengthOnTree=$value");
		$value = $xmls->getValue('/skin/default/tree/showValue');
		if (isset($value))
			array_push($assignments, 'showValueOnTree=' . ($value ? '1' : '0'));
		$sql = "UPDATE {$database['prefix']}SkinSettings SET " . implode(',', $assignments) . " WHERE owner = $owner";
		
		// none/single/multiple
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			setUserSetting('noneCommentMessage', NULL);
		else
			setUserSetting('noneCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			setUserSetting('singleCommentMessage', NULL);
		else
			setUserSetting('singleCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			setUserSetting('noneTrackbackMessage', NULL);
		else
			setUserSetting('noneTrackbackMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			setUserSetting('singleTrackbackMessage', NULL);
		else
			setUserSetting('singleTrackbackMessage', $value);
	} else {
		setUserSetting('noneCommentMessage', NULL);
		setUserSetting('singleCommentMessage', NULL);
		setUserSetting('noneTrackbackMessage', NULL);
		setUserSetting('singleTrackbackMessage', NULL);
		$sql = "UPDATE {$database['prefix']}SkinSettings SET skin='{$skinName}' WHERE owner = $owner";
	}
	$result = DBQuery::query($sql);
	if (!$result) {
		return _t('실패했습니다.');
	}
	
	removeUserSetting("sidebarOrder");
	return true;
}

function writeSkinHtml($owner, $contents, $mode) {
	global $database;
	global $skinSetting;
	if ($mode != 'skin' && $mode != 'skin_keyword' && $mode != 'style')
		return _t('실패했습니다.');
	if ($skinSetting['skin'] != "customize/$owner") {
		if (!@file_exists(ROOT . "/skin/customize/$owner")) {
			if (!@mkdir(ROOT . "/skin/customize/$owner"))
				return _t('권한이 없습니다.');
			@chmod(ROOT . "/skin/customize/$owner", 0777);
		}
		deltree(ROOT . "/skin/customize/$owner");
		copyRecusive(ROOT . "/skin/{$skinSetting['skin']}", ROOT . "/skin/customize/$owner");
	}
	$skinSetting['skin'] = "customize/$owner";
	$sql = "UPDATE {$database['prefix']}SkinSettings SET skin = '{$skinSetting['skin']}' WHERE owner = $owner";
	$result = DBQuery::query($sql);
	if (!$result)
		return _t('실패했습니다.');
	if ($mode == 'style')
		$file = $mode . '.css';
	else
		$file = $mode . '.html';
	if (!is_writable(ROOT . "/skin/{$skinSetting['skin']}/$file"))
		return ROOT . _t('권한이 없습니다.') . " -> /skin/{$skinSetting['skin']}/$file";
	$handler = fopen(ROOT . "/skin/{$skinSetting['skin']}/$file", 'w');
	if (fwrite($handler, $contents) === false) {
		fclose($handler);
		return _t('실패했습니다.');
	} else {
		fclose($handler);
		@chmod(ROOT . "/skin/{$skinSetting['skin']}/$file", 0666);
		return true;
	}
}

function setSkinSetting($owner, $setting) {
	global $database;
	global $skinSetting;

	if (strncmp($skinSetting['skin'], 'customize/', 10) == 0) {
		if (strcmp($skinSetting['skin'], "customize/$owner") != 0)
			return false;
	} else {
		$skinSetting['skin'] = Path::getBaseName($skinSetting['skin']);
		if (($skinSetting['skin'] === '.') || ($skinSetting['skin'] ==='..'))
			return _t('실패 했습니다');
	}
	
	$skinpath = ROOT . '/skin/' . $skinSetting['skin'];
	if (!is_dir($skinpath))
		return _t('실패 했습니다');
	
	foreach ($setting as $key => $value) {
		$setting[$key] = mysql_tt_escape_string($value);
	}
	$sql = "
	UPDATE {$database['prefix']}SkinSettings 
	SET 
		skin 					= \"" . $skinSetting['skin'] . "\",
		entriesOnRecent			= " . $setting['entriesOnRecent'] . ',
		commentsOnRecent			= ' . $setting['commentsOnRecent'] . ',
		commentsOnGuestbook		= ' . $setting['commentsOnGuestbook'] . ',
		archivesOnPage	 		= ' . $setting['archivesOnPage'] . ',
		tagsOnTagbox			= ' . $setting['tagsOnTagbox'] . ',
		tagboxAlign				= ' . $setting['tagboxAlign'] . ',
		trackbacksOnRecent		= ' . $setting['trackbacksOnRecent'] . ',
		showListOnCategory		= ' . $setting['showListOnCategory'] . ',
		showListOnArchive		= ' . $setting['showListOnArchive'] . ',
		expandComment				= ' . $setting['expandComment'] . ',
		expandTrackback			= ' . $setting['expandTrackback'] . ',
		recentNoticeLength 		= ' . $setting['recentNoticeLength'] . ',
		recentEntryLength 		= ' . $setting['recentEntryLength'] . ',
		recentCommentLength 		= ' . $setting['recentCommentLength'] . ',
		recentTrackbackLength 	= ' . $setting['recentTrackbackLength'] . ',
		linkLength 				= ' . $setting['linkLength'] . '
	WHERE owner =' . $owner;
	if (update($sql) > - 1) {
	} else {
		return false;
	}
	$sql = "
	UPDATE {$database['prefix']}BlogSettings 
	SET 
		entriesOnPage 			= '{$setting['entriesOnPage']}',
		entriesOnList 			= '{$setting['entriesOnList']}'
	WHERE owner = $owner ";
	if (update($sql) > - 1) {
		return true;
	} else {
		return false;
	}
}
?>
