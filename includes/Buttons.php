<?php

namespace UsersPagesLinks;

class Buttons  {

	public static function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'usersPagesLinksButton', 'UsersPagesLinks\\Buttons::parserButton' );
		return true;
	}


	public static function parserButton( \Parser $input, $type = 'star', $grouppage = null ) {

		$input->getOutput()->addModules( 'ext.userspageslinks.js' );

		if( ! $grouppage) {
			if (!$input->getTitle() ) {
				trigger_error("No title founded");
				return false;
			}
			$grouppage = $input->getTitle()->getPrefixedDBkey();
		}

		$pagesLinksActives = UsersPagesLinksCore::getInstance()->getUserPageLinks($input->getUser(), $input->getTitle());

		if (UsersPagesLinksCore::getInstance()->hasLink($input->getUser(), $input->getTitle(), $type)){
			$addClass='pagelinkhidden';
			$removeClass='pagelinkactive';
		} else {
			$addClass='pagelinkactive';
			$removeClass='pagelinkhidden';
		}


		$doLabel = wfMessage('userspageslinks-' . $type);
		$undoLabel = wfMessage('userspageslinks-un' . $type);

		$button = '<a class="addUsersPagesLinksButton '.$addClass.'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button>';
		$button .= $doLabel;
		$button .= '</button>';
		$button .= '</a>';

		$button .= '<a class="rmUsersPagesLinksButton '.$removeClass.'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button>';
		$button .= $undoLabel;
		$button .= '</button>';
		$button .= '</a>';


		return array( $button, 'noparse' => true, 'isHTML' => true );
	}


	public static function onBeforePageDisplay( $out ) {
		$out->addModules( 'ext.userspageslinks.js' );
	}


	/**
	 * Adds an "action" (i.e., a tab) to edit the current article with
	 * a form
	 */
	static function displayTab( $obj, &$links ) {


		$button = '<button class="addToGroupsPage" data-grouppage="Group:toto" data-page="Horloge_de_Fibonacci" > add to group</button>';

		$content_actions = &$links['views'];

		if ( method_exists ( $obj, 'getTitle' ) ) {
			$title = $obj->getTitle();
		} else {
			$title = $obj->mTitle;
		}
		$groupNameSpace = [ NS_GROUP, NS_GROUP_TALK];

		if ( !isset( $title ) ||
			( !in_array( $title->getNamespace(), $groupNameSpace ) ) ) {
					return true;
		}

		$form_create_tab = array(
			'class' => '',
			'text' => $button,
			'href' => $title->getLocalURL( 'action=formcreate' )
		);

		$content_actions['addtogroup'] = $form_create_tab;

		return true; // always return true, in order not to stop MW's hook processing!
	}
}
