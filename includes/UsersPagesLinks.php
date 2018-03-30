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

	public static function onExtension() {

		global $sespSpecialProperties, $sespLocalPropertyDefinitions;

		//add property annotator to SESP
		$sespSpecialProperties[] = '_STAR';

		$sespLocalPropertyDefinitions['_STAR'] = [
		    'id'    => '___STAR',
		    'type'  => '_num',
		    'alias' => 'userspageslinks-star-prop',
		    'label' => 'Favorites',
		    'callback'  => function( $appFactory, $property, $semanticData ){

		    	$usersPagesLinksCore = new UsersPagesLinksCore();
		    	$favoritesCounter = intval($usersPagesLinksCore->getPageCounters( $semanticData->getSubject()->getTitle() )['star']);

		    	return new SMWDINumber($favoritesCounter);
		    }
		];

		$sespSpecialProperties[] = '_I_DID_IT';

        $sespLocalPropertyDefinitions['_I_DID_IT'] = [
            'id'    => '___I_DID_IT',
            'type'  => '_num',
            'alias' => 'userspageslinks-ididit-prop',
            'label' => 'I did it',
            'callback'  => function( $appFactory, $property, $semanticData ) {
                $usersPagesLinksCore = new UsersPagesLinksCore();
                $ididitCounter = intval($usersPagesLinksCore->getPageCounters( $semanticData->getSubject()->getTitle() )['ididit']);

                return new SMWDINumber($ididitCounter);
            }
        ];
	}
}