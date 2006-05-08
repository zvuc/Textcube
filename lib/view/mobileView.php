<?php

function printMobileHtmlHeader($title = '') {
	global $blogURL, $blog;
	$title = htmlspecialchars($blog['title']) . ' :: ' . $title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title><?php echo $title?></title>
	</head>
	<body>
		<div id="header">
			<h1><a href="<?php echo $blogURL?>" accesskey="0"><?php echo htmlspecialchars($blog['title'])?></a></h1>
		</div>
		<hr/>
<?php
}

function printMobileHtmlFooter() {
?>
		<hr/>
		Powered by <a href="http://www.tattertools.com">Tattertools</a>
	</body>
</html>
<?php
}

function printMobileNavigation($entry, $jumpToComment = true, $jumpToTrackback = true, $paging = null) {
	global $suri, $blogURL;
?>
<hr/>
<div id="navigation">
	<ul>
		<?php
	if (isset($paging['prev'])) {
?>
		<li><a href="<?php echo$blogURL?>/<?php echo $paging['prev']?>" accesskey="1"><?php echo _t('이전 글 보기')?></a></li>
		<?php
	}
	if (isset($paging['next'])) {
?>
		<li><a href="<?php echo $blogURL?>/<?php echo $paging['next']?>" accesskey="2"><?php echo _t('다음 글 보기')?></a></li>
		<?php
	}
	if (!isset($paging)) {
?>	
		<li><a href="<?php echo $blogURL?>/<?php echo $entry['id']?>" accesskey="3"><?php echo _t('포스트 보기')?></a></li>
		<?php
	}
	if ($jumpToComment) {
?>
		<li><a href="<?php echo $blogURL?>/comment/<?php echo $entry['id']?>" accesskey="4"><?php echo _t('답글 보기')?> (<?php echo $entry['comments']?>)</a></li>
		<?php
	}
	if ($jumpToTrackback) {
?>
		<li><a href="<?php echo $blogURL?>/trackback/<?php echo $entry['id']?>" accesskey="5"><?php echo _t('트랙백 보기')?> (<?php echo $entry['trackbacks']?>)</a></li>
		<?php
	}
	if ($suri['directive'] != '/m/pannels') {
?>
		<li><a href="<?php echo $blogURL?>/pannels/<?php echo $entry['id']?>" accesskey="6"><?php echo _t('다른 메뉴 보기')?></a></li>
		<?php
	}
?>
	</ul>
</div>
<?php
}

function printMobileTrackbackView($entryId) {
	$trackbacks = getTrackbacks($entryId);
	if (count($trackbacks) == 0) {
?>
		<div class="trackback">
			<?php echo _t('트랙백이 없습니다')?>
		</div>
		<?php
	} else {
		foreach (getTrackbacks($entryId) as $trackback) {
?>
		<div class="trackback">
			<div class="name">
				<strong><?php echo htmlspecialchars($trackback['subject'])?></strong>
				(<?php echo Timestamp::format5($trackback['written'])?>)
			</div>
			<div class="body"><?php echo htmlspecialchars($trackback['excerpt'])?></div>
		</div>
		<hr/>
		<?php
		}
	}
}

function printMobileCommentView($entryId) {
	global $blogURL;
	$comments = getComments($entryId);
	if (count($comments) == 0) {
?>
		<div class="comment">
			<?php echo _t('답글이 없습니다')?>
		</div>
		<hr/>
		<?php
	} else {
		foreach ($comments as $commentItem) {
?>
		<div class="comment">
			<div class="name">
				<strong><?php echo htmlspecialchars($commentItem['name'])?></strong>
				<a href="<?php echo $blogURL?>/comment/comment/<?php echo $commentItem['id']?>">RE</a>
				<a href="<?php echo $blogURL?>/comment/delete/<?php echo $commentItem['id']?>">DEL</a><br/>
				(<?php echo Timestamp::format5($commentItem['written'])?>)
			</div>
			<div class="body"><?php echo nl2br(addLinkSense(htmlspecialchars($commentItem['comment'])))?></div>
			<?php
			foreach (getCommentComments($commentItem['id']) as $commentSubItem) {
?>
			<blockquote>
				<div class="name">
					<strong><?php echo htmlspecialchars($commentSubItem['name'])?></strong>
					<a href="<?php echo $blogURL?>/comment/delete/<?php echo $commentSubItem['id']?>">DEL</a><br/>
					(<?php echo Timestamp::format5($commentSubItem['written'])?>)
				</div>
				<div class="body"><?php echo nl2br(addLinkSense(htmlspecialchars($commentSubItem['comment'])))?></div>
			</blockquote>
			<?php
			}
?>
		</div>
		<hr/>
		<?php
		}
	}
	printMobileCommentFormView($entryId);
}

function printMobileCommentFormView($entryId) {
?>
	<fieldset>
	<form method="post" action="add/<?php echo $entryId?>">	
		<?php
	if (!doesHaveOwnership()) {
?>
		<input type="hidden" name="id" value="<?php echo $entryId?>"/>
		<label for="secret_<?php echo $entryId?>"><?php echo _t('비밀글로 등록')?></label>
		<input type="checkbox" id="secret_<?php echo $entryId?>" name="secret_<?php echo $entryId?>"/>
		<br/>
		<label for="name_<?php echo $entryId?>"><?php echo _t('이름')?></label>
		<input type="text" id="name_<?php echo $entryId?>" name="name_<?php echo $entryId?>"/>
		<br/>
		<label for="password_<?php echo $entryId?>"><?php echo _t('비밀번호')?></label>
		<input type="password" id="password_<?php echo $entryId?>" name="password_<?php echo $entryId?>"/>
		<br/>
		<label for="homepage_<?php echo $entryId?>"><?php echo _t('홈페이지')?></label>
		<input type="text" id="homepage_<?php echo $entryId?>" name="homepage_<?php echo $entryId?>"/>
		<br/>
		<?php
	}
?>
		<label for="comment_<?php echo $entryId?>"><?php echo _t('내용')?></label>
		<textarea rows="5" id="comment_<?php echo $entryId?>" name="comment_<?php echo $entryId?>"></textarea>
		<br/>
		<input type="submit" value="<?php echo _t('등록')?>"/>
	</form>
	</fieldset>
	<?php
}

function printMobileErrorPage($messageTitle, $messageBody, $redirectURL) {
	printMobileHtmlHeader('Error');
?>
<h2><?php echo htmlspecialchars($messageTitle)?></h2>
<p><?php echo htmlspecialchars($messageBody)?></p>
<a href="<?php echo $redirectURL?>"><?php echo _t('이전 페이지로')?></a>
<?php
	printMobileHtmlFooter();
}

function printMobileSimpleMessage($message, $redirectMessage, $redirectURL, $title = '') {
	printMobileHtmlHeader($title);
?>
<h2><?php echo htmlspecialchars($message)?></h2>
<a href="<?php echo $redirectURL?>"><?php echo htmlspecialchars($redirectMessage)?></a>
<?php
	printMobileHtmlFooter();
}
?>
