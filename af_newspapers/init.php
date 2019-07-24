<?php
/*
 * af_newspapers/init.php
 * Plugin for TT-RSS 1.7.9
 *
 * Retrieves full article text for feeds from Movable Type Enterprise sites (commonly
 * used by newspapers).
 *
 * CHANGELOG:
 * Version 1.2 by craywolf 2013-04-19 @ 14:00 GMT
 * 	- Added api_version() call
 * 	- Removed cruft
 * Version 1.1 by craywolf 2013-04-17 @ 15:22 GMT
 * 	- Added fix for removal of $this->link in 1.7.9
 * 	- Added "more info" link to about()
 * Version 1.0 by craywolf 2013-03-26 @ 16:17 GMT
 * 	- Initial release
 */
class Af_newspapers extends Plugin {
	private $host;

	function about() {
		return array(1.2,
			"Turn newspaper feeds using Movable Type Enterprise (index.ssf in story link) into full-story feeds",
			"craywolf",
			"",
			"http://tt-rss.org/forum/viewtopic.php?f=22&t=1539");
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "/index.ssf/") !== FALSE) {
			if (strpos($article["plugin_data"], "newspapers-full,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);
					
					$entries = $xpath->query('(//div[@class="entry-content"])');
					foreach ($entries as $entry) {
						$basenode = $entry;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode); //, LIBXML_NOEMPTYTAG);
						$article["plugin_data"] = "newspapers-full,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
?>
