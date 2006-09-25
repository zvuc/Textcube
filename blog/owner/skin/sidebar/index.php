<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu33.php';

function pretty_dress($view)
{
	global $owner, $blog, $blogURL, $database, $service, $stats, $skinSetting;
	
	if (isset($_REQUEST['safe'])) {
		// safe mode
		return '<div class="sidebar-element-safebox">&hellip;</div>';
	}
	
	$writer = fetchQueryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = $owner");
	$pageTitle = _t('페이지 제목');

	dress('page_title', htmlspecialchars($pageTitle), $view);
	dress('blogger', htmlspecialchars($writer), $view);
	dress('title', htmlspecialchars($blog['title']), $view);
	dress('desc', htmlspecialchars($blog['description']), $view);
	if (!empty($blog['logo']))
		dress('image', "{$service['path']}/attach/$owner/{$blog['logo']}", $view);
	else
		dress('image', "{$service['path']}/image/spacer.gif", $view);
	dress('blog_link', "$blogURL/", $view);
	dress('keylog_link', "$blogURL/keylog", $view);
	dress('localog_link', "$blogURL/location", $view);
	dress('taglog_link', "$blogURL/tag", $view);
	dress('guestbook_link', "$blogURL/guestbook", $view);
	
	list($view, $searchView) = Skin::cutSkinTag($view, 'search');
	dress('search_name', 'search', $searchView);
	dress('search_text', isset($search) ? htmlspecialchars($search) : '', $searchView);
	dress('search_onclick_submit', "try{window.location.href='$blogURL/search/' + document.getElementsByName('search')[0].value.replaceAll('%', '%25'); return false;}catch(e){}", $searchView);
	dress('search', $searchView, $view);
	
	$totalPosts = getEntriesTotalCount($owner);
	$categories = getCategories($owner);
	dress('category', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true), $view);
	dress('category_list', getCategoriesView($totalPosts, $categories, isset($category) ? $category : true, true), $view);
	dress('count_total', $stats['total'], $view);
	dress('count_today', $stats['today'], $view);
	dress('count_yesterday', $stats['yesterday'], $view);
	
	list($view, $archiveView) = Skin::cutSkinTag($view, 'archive_rep');
	dress('archive_rep', getArchivesView(getArchives($owner), $archiveView), $view);
	dress('calendar', getCalendarView(getCalendar($owner, true)), $view);
	list($view, $randomView) = Skin::cutSkinTag($view, 'random_tags');
	dress('random_tags', getRandomTagsView(getRandomTags($owner), $randomView), $view);

	list($view, $recentNoticeItem) = Skin::cutSkinTag($view, 'rct_notice_rep');	
	list($view, $noticeView) = Skin::cutSkinTag($view, 'rct_notice');
	$notices = getNotices($owner);
	if (sizeof($notices) == 0) {
		$notices = array( array('title' => _t('공지 제목'), 'id' => -1));
	}
	if (sizeof($notices) > 0) {
		$itemsView = '';
		foreach ($notices as $notice) {
			$itemView = $recentNoticeItem;
			dress('notice_rep_title', htmlspecialchars(fireEvent('ViewNoticeTitle', UTF8::lessenAsEm($notice['title'], $skinSetting['recentNoticeLength']), $notice['id'])), $itemView);
			dress('notice_rep_link', "$blogURL/notice/{$notice['id']}", $itemView);
			$itemsView .= $itemView;
		}
		dress('rct_notice_rep', $itemsView, $noticeView);
		dress('rct_notice', $noticeView, $view);
	}
	
	list($view, $recentEntry) = Skin::cutSkinTag($view, 'rctps_rep');	
	dress('rctps_rep', getRecentEntriesView(getRecentEntries($owner), $recentEntry), $view);
	list($view, $recentComments) = Skin::cutSkinTag($view, 'rctrp_rep');	
	dress('rctrp_rep', getRecentCommentsView(getRecentComments($owner), $recentComments), $view);
	list($view, $recentTrackback) = Skin::cutSkinTag($view, 'rcttb_rep');	
	dress('rcttb_rep', getRecentTrackbacksView(getRecentTrackbacks($owner), $recentTrackback), $view);
	list($view, $s_link_rep) = Skin::cutSkinTag($view, 'link_rep');	
	dress('link_rep', getLinksView(getLinks($owner), $s_link_rep), $view);
	dress('rss_url', "$blogURL/rss", $view);
	dress('owner_url', "$blogURL/owner", $view);
	dress('tattertools_name', TATTERTOOLS_NAME, $view);
	dress('tattertools_version', TATTERTOOLS_VERSION, $view);
	
	return $view;
}
?>
						<script type="text/javascript">
							//<![CDATA[
						
							//]]>
						</script>
						
						<form id="part-sidebar-order" class="part" method="post" action="sidebar/register">
							<h2 class="caption"><span class="main-text"><?php echo _t('사이드바 기능을 관리합니다');?></span></h2>
							
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('사이드바의 출력을 수정합니다.');?></p>
							</div>
							
							<div id="sidebar-box" class="data-inbox">
								<h3><?php echo _t('사이드바')?></h3>
<?php
$sidebarPluginArray = array();
for ($i=0; $i<count($sidebarMappings); $i++) {
	$sidebarPluginArray[$sidebarMappings[$i]['plugin'] . '/' . $sidebarMappings[$i]['handler']]=
		array( 
			'type' => 3, 'id' => $sidebarMappings[$i]['handler'],
			'plugin' => $sidebarMappings[$i]['plugin'], 'title' =>$sidebarMappings[$i]['title'], 
			'display' => $sidebarMappings[$i]['display'],
			'identifier' => implode(':', array(3,$sidebarMappings[$i]['plugin'],$sidebarMappings[$i]['handler']))
		);
}

$skin = new Skin($skinSetting['skin']);
$usedSidebarBasicModule = array();
$sidebarCount = count($skin->sidebarBasicModules);

// 사용중인 사이드바 모듈 리스트 출력.
$bFirstRadio = true;
$sidebarConfig = getSidebarModuleOrderData($sidebarCount);

if (is_null($sidebarConfig)) {
	for ($i=0; $i<$sidebarCount; $i++) {
		$sidebarConfig[$i] = array();
	}
}

for ($i=0; $i<$sidebarCount; $i++) {
	$orderConfig = $sidebarConfig[$i];
?>
								<div class="section">
									<h4><input type="radio" id="sidebar-<?php echo $i + 1;?>" class="radio" name="sidebarNumber" value="<?php echo $i;?>"<?php echo $bFirstRadio ? " checked" : NULL;?> /><label for="sidebar-<?php echo $i + 1;?>"><?php echo _t('사이드바').' '.($i + 1);?></label></h4>
									
									<ul id="sidebar-ul-<?php echo $i;?>" class="sidebar">
<?php
	for ($j=0; $j<count($orderConfig); $j++) {
		if ($orderConfig[$j]['type'] == 1) { // skin text
			$skini = $orderConfig[$j]['id'];
			$skinj = $orderConfig[$j]['parameters'];
?>
										<li class="sidebar-module sidebar-basic-module" style="border:1px solid">
											<h5><?php echo $skin->sidebarBasicModules[$skini][$skinj]['title'];?></h5>
											<div class="button-box">
<?php
			if ($j == 0) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
			} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j - 1;?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
			}
				
			if ($j == count($orderConfig) - 1) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
			} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j + 1;?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
			}
?>
											
												<a href="sidebar/delete/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
											</div>
											<div><?php echo pretty_dress($skin->sidebarBasicModules[$skini][$skinj]['body']);?></div>
										</li>
<?php
			array_push($usedSidebarBasicModule, $orderConfig[$j]['id']);
		} else if ($orderConfig[$j]['type'] == 2) { // default handler
			// TODO : implement it!
		} else if ($orderConfig[$j]['type'] == 3) { // plugin
			$plugin = $orderConfig[$j]['id']['plugin'];
			$handler = $orderConfig[$j]['id']['handler'];
			include_once (ROOT . "/plugins/{$plugin}/index.php");
			$sidbarPluginIndex = $plugin . '/' . $handler;
			if (function_exists($handler)) {
			
?>
										<li class="sidebar-module sidebar-plugin-module" style="border:1px solid">
											<?php echo $sidebarPluginArray[$sidbarPluginIndex]['display'], '::', $sidebarPluginArray[$sidbarPluginIndex]['title'];?>
											<div class="button-box">
<?php
				if ($j == 0) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module_disabled.jpg" border="0" alt="<?php echo _t('위로');?>" />
<?php
				} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j - 1;?>" title="<?php echo _t('이 사이드바 모듈을 위로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_moveup_module.jpg" border="0" alt="<?php echo _t('위로');?>" /></a>
<?php
				}
				
				if ($j == count($orderConfig) - 1) {
?>
												<img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module_disabled.jpg" border="0" alt="<?php echo _t('아래로');?>" />
<?php
				} else {
?>
												<a href="sidebar/order/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>&amp;targetPos=<?php echo $j + 1;?>" title="<?php echo _t('이 사이드바 모듈을 아래로 이동합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_movedown_module.jpg" border="0" alt="<?php echo _t('아래로');?>" /></a>
<?php
				}
?>
												<a href="sidebar/delete/?sidebarNumber=<?php echo $i;?>&amp;targetSidebarNumber=<?php echo $i;?>&amp;modulePos=<?php echo $j;?>" title="<?php echo _t('이 사이드바 모듈을 삭제합니다.');?>"><img src="<?php echo $service['path'].$adminSkinSetting['skin'];?>/image/img_delete_module.jpg" border="0" alt="<?php echo _t('삭제');?>" /></a>
											</div>
										</li>
<?php
			}
		} else {
			// other type
		}
	}
?>
									</ul>
								</div>
<?php
	$bFirstRadio = false;
}
?>
							</div>
							
							<div class="data-subbox">
								<h3><?php echo _t('추가 가능한 사이드바 모듈');?></h3>
								
								<fieldset id="sidebar-basic-module-box" class="section">
									<legend><?php echo _t('추가 가능한 모듈(스킨 기본)');?></legend>
									
									<ul>
<?php
// 사용중이지 않은 스킨 내장형 사이드바 모듈 리스트 출력.
$sortedArray = array();
for ($i=0; $i<$sidebarCount; $i++) {
	$moduleCountInSidebar = count($skin->sidebarBasicModules[$i]);
	for ($j=0; $j<$moduleCountInSidebar; $j++) {
		array_push($sortedArray, 
			array('title' => $skin->sidebarBasicModules[$i][$j]['title'], 
				'body' => $skin->sidebarBasicModules[$i][$j]['body'],
				'identifier' => implode(':', array(1, $i, $j))
			)
		);
	}
}

foreach ($sortedArray as $nowKey) {
?>
										<li class="sidebar-module">
											<input type="radio" id="module<?php echo $nowKey['identifier'];?>" class="radio" name="moduleId" value="<?php echo $nowKey['identifier'];?>" /><label for="module<?php echo $nowKey['title'];?>"><?php echo $nowKey['title'];?></label><div><?php echo pretty_dress($nowKey['body']);?></div>
										</li>
<?php
}
?>
									</ul>
								</fieldset>
								
								<fieldset id="sidebar-plugin-module-box" class="section">
									<legend><?php echo _t('추가 가능한 플러그인');?></legend>
									
									<ul>
<?php
// 사이드바 플러그인 모듈을 리스트에 포함시킨다.
foreach ($sidebarPluginArray as $nowKey) {
?>
										<li class="sidebar-module">
											<input type="radio" id="module<?php echo $nowKey['identifier'];?>" class="radio" name="moduleId" value="<?php echo $nowKey['identifier'];?>" /><label for="module<?php echo $nowKey;?>"><?php echo $nowKey['display'], '::' , $nowKey['title'];?></label>
										</li>
<?php
}
?>	
									</ul>
								</fieldset>
								
								<div class="button-box">
									<input type="submit" class="input-button" value="<?php echo _t('모듈 추가');?>" title="<?php echo _t('사이드바에 선택된 모듈의 기능을 추가합니다.');?>"/>
									<a class="button" href="sidebar/initialize" onclick="if (!confirm('<?php echo _t('정말 사이드바 기능을 초기화하시겠습니까?');?>')) return false;" title="<?php echo _t('사이드바의 기능을 스킨 설정 상태로 초기화합니다.');?>"><span class="text"><?php echo _t('초기화');?></span></a>
								</div>
							</div>
						</form>
<?php
require ROOT . '/lib/piece/owner/footer1.php';
?>