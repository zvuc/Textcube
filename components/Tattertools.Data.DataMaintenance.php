<?php
class DataMaintenance {
	/*@static@*/
	function removeAll($removeAttachments = true) {
		global $database, $owner;
		
		$tags = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE owner = $owner");
		
		mysql_query("UPDATE {$database['prefix']}BlogStatistics SET visits = 0 WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}DailyStatistics WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Categories WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Attachments WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Comments WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}TrackbackLogs WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}TagRelations WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Entries WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Links WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}RefererLogs WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}RefererStatistics WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner");
		//mysql_query("DELETE FROM {$database['prefix']}UserSettings WHERE user = $owner");
		
		mysql_query("DELETE FROM {$database['prefix']}Filters WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}FeedStarred WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}FeedReads WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}FeedGroupRelations WHERE owner = $owner");
		mysql_query("DELETE FROM {$database['prefix']}FeedGroups WHERE owner = $owner AND id <> 0");
		
		if (count($tags) > 0) 
		{
			$tagliststr = implode(', ', $tags);
			$nottargets = DBQuery::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
			if (count($nottargets) > 0) {
				$nottargetstr	= implode(', ', $nottargets);
				DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
			} else {
				DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) ");
			}
		}
		
		if (file_exists(ROOT . "/cache/rss/$owner.xml"))
			unlink(ROOT . "/cache/rss/$owner.xml");
		
		if ($removeAttachments) {
			Path::removeFiles(Path::combine(ROOT, 'attach', $owner));
			mysql_query("UPDATE {$database['prefix']}BlogSettings SET logo = '' WHERE owner = $owner");
		}
	}
}
?>
