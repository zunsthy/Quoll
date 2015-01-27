<?php
class UTILITY {
	
	static public function page($rpp, $count, $opts = [], $pagename = 'page'){
		$pages = ceil($count / $rpp);
		if(isset($opts['lastpagedefault']) && $opts['lastpagedeault']){
			$pagedefault = floor(($count - 1) / $rpp);
			if($pagedefault < 0)
				$pagedefault = 0;
		} else {
			$pagedefault = 0;
		}
		
		if(isset($_GET[$pagename])){
			$page = 0 + $_GET[$pagename];
			if($page < 0)
				$page = 0;
			elseif($page > $pages - 1)
				$page = $pages - 1;
		} else
			$page = $pagedefault;
		
		$start = $page * $rpp;
		return array($pages, $page, " LIMIT $start,$rpp");
	}
	
}