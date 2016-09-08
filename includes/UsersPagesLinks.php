<?php
namespace UsersPagesLinks;
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
		return true;
	}
}