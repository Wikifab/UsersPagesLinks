<?php
namespace UsersPagesLinks;
use SMWDINumber;
/**
 *
 * @file
 * @ingroup Extensions
 *
 * @author Pierre Boutet
 */

class UsersPagesLinks {

	public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ) {

		$updater->addExtensionTable( 'userspageslinks',
				__DIR__ . '/tables.sql' );
		$updater->addExtensionIndex( 'userspageslinks', 'upl_user_page_link_pk',
				__DIR__ . '/patch_index_unique.sql' );
		return true;
	}

	public static function onArticleDeleteComplete( &$article, \User &$user, $reason, $id, \Content $content = null, \LogEntry $logEntry ) {
		//delete all user links to this page
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'userspageslinks', array('upl_page_id' => $id), __METHOD__);
	}
}