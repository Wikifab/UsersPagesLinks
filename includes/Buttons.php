<?php

namespace UsersPagesLinks;

use Symfony\Component\VarDumper\VarDumper;

class Buttons  {

	public static function onParserFirstCallInit($parser ) {
		$parser->setFunctionHook( 'usersPagesLinksButton', 'UsersPagesLinks\\Buttons::parserButton' );
		$parser->setFunctionHook( 'usersPagesLinksUsersList', 'UsersPagesLinks\\Buttons::parserUsersPagesLinksUsersList' );

		return true;
	}


	public static function onBeforePageDisplay( $out ) {

		$out->addModuleStyles(
				array(
						'ext.userspageslinks.css'
				)
		);
		$out->addModules( 'ext.userspageslinks.js' );
		$out->addHTML(self::getConnectionRequiredModal($out));
	}

	public static function onSkinTemplateNavigation( &$page, &$content_navigation ) {
		global $wgUser, $wgUsersPagesLinksFoNamespaces;


		// if no button defined for this namespace, return
		if ($wgUsersPagesLinksFoNamespaces) {
			$ns = $page->getTitle()->getNamespace();
			if( ! isset($wgUsersPagesLinksFoNamespaces[$ns])) {
				return true;
			}
		}

		if (! $page->getTitle()->exists()) {
			// do not add buttons on inexistent page
			return true;
		}

		$pagesLinksActives = UsersPagesLinksCore::getInstance()->getUserPageLinks($wgUser, $page->getTitle());
		$pagesLinksCounters= UsersPagesLinksCore::getInstance()->getPageCounters($page->getTitle());

		if ( ! isset($content_navigation['NetworksLinks']) ) {
			$content_navigation['NetworksLinks'] = [];
		}


		foreach ($pagesLinksCounters as $type => $count) {
			if ($wgUsersPagesLinksFoNamespaces && ! in_array($type, $wgUsersPagesLinksFoNamespaces[$ns])) {
				//if this button not include for this namespace, skip it
				continue;
			}
			$content_navigation['NetworksLinks'][$type] = [
					'buttonType' => 'counter',
					'type' => $type,
					'count' => $count,
					'redundant' => true,
					'active' => in_array($type, $pagesLinksActives),
					'activebis' => isset($pagesLinksActives[$type]),
					'pageUri' => $page->getTitle()->getPrefixedDBkey()
			];
		}

		return true;
	}

	/**
	 * get users list fomated in html cards
	 * Warning, result depends on the user logged (to add watch buttons)
	 *
	 * @param array $users array of Users
	 * @param string $class class to add on divs
	 * @return string
	 */
	public static function formatUsersList($users, $class = '') {
	    global $wgUser,$wgUserProfileDisplay;

		$out = '<div class="row">';
		foreach ($users as $user) {

			$out .= '<div class="col-md-4 col-sm-6 col-xs-12 ">';
			$out .= '<div class="UserCard">';
			$data = [];

			$data['id'] = $user->getId();
			$data['url'] = $user->getUserPage()->getLinkURL();
			$avatar = new \wAvatar( $data['id'], 'ml' );
			$data['avatar'] = $avatar->getAvatarURL();
			$data['name'] = $user->getRealName();
			$data['username'] = $user->getName();
			$data['followButton'] ='';

			//Get the user's 'about' section
			$profile = new \UserProfile($user->getName());
			$profile_data = $profile->getProfile();
			$data['aboutUser'] = $profile_data['about'];

			$pageEditProfile = \SpecialPage::getTitleFor( 'UpdateProfile' );
			$linkUpdateProfileUser='<span class="UpdateProfileLink"><a href="'.$pageEditProfile->getFullURL().
			                         '"><i class="fa fa-edit"></i></a></span>';

			if ( ! $data['name']) {
				$data['name'] = $user->getName();
			}
			//When user connected belongs to the list : don't display "follow button"
			if ($user->getId() !=  $wgUser->getId()){
				$data['followButton'] = \UsersWatchButton::getHtml($data['username']);
                $linkUpdateProfileUser = '';
            }

            // If user didn't complete the "about" section in his profile
            if ($data['aboutUser'] ==''){
                $data['aboutUser'] = wfMessage('user-about-section-empty');
            }

            $out .= '<div class="UserListCardAvatar">'
                        . '<a href="'.$data['url'].'">' . $data['avatar'] . '</a>'
                   		. '</div>'
                   		. '<div class="UserListCardInfo">'
                   		. '<span class="UserListCardName"><a href="'.$data['url'].'">' . $data['name'] . '</a></span>'
                        . $linkUpdateProfileUser
                        . $data['followButton'] .
                        '<div class="UserListCardAbout">' . $data['aboutUser'] . '</div>
                    </div>
                  </div>
                </div>';
		}
		$out .= '</div>';
		return $out;

	}

	private static function shortFormatUsersList($users, $class) {
	    $out = "";
	    foreach ($users as $user) {
	        $out .= '<div class="col-md-4 col-sm-6 col-xs-12 UserListcard">';
	        $data = [];
	        $data['id'] = $user->getId();
	        $data['url'] = $user->getUserPage()->getLinkURL();
	        $avatar = new \wAvatar( $data['id'], 'ml' );
	        $data['avatar'] = $avatar->getAvatarURL();
	        $data['name'] = $user->getRealName();
	        if ( ! $data['name']) {
	            $data['name'] = $user->getName();
	        }
	        $out .= '<a href="'.$data['url'].'">';
	        $out .= '<div class="avatar">' . $data['avatar'] . '</div>';
	        $out .= '<span class="name">' . $data['name'] . '</span>';
	        $out .= '</a>';
	        $out .= '</div>';
	    }
	    return $out;
	}

	public static function getUsersListHtml(\Title $page, $type, $nbreResult=0, $numPage=1) {
		$users = UsersPagesLinksCore::getInstance()->getPagesLinksUsers($page, $type, $nbreResult, $numPage);
		return self::formatUsersList($users, $type);
	}

	public static function getShortUsersListHtml(\Title $page, $type, $nbreResult=0, $numPage=1, $allFollowers) {
		$output = '<div class="usersPageLinksUsers row">';
		$users = UsersPagesLinksCore::getInstance()->getPagesLinksUsers($page, $type, $nbreResult, $numPage);
		$pageUsersList = \SpecialPage::getTitleFor( 'DisplayUsersList' )->getFullURL('pageName='.$page .'&typeButton='.$type . '&numPage='.$numPage);
		$output .= self::shortFormatUsersList($users, $type);
		if ($allFollowers>3)
		{
			$peopleHide = $allFollowers - 3 ;

			$output .='<div class=nbrHiddingPeople><a href="'.$pageUsersList .'">';
			$output .= wfMessage("userspageslinks-special-list-nbr-people-hidding",$peopleHide)->plain();
			$output .= '</a></div>';
		}
		$output .= '</div>';
		return $output;
	}


	public static function getConnectionRequiredModal($out) {

		$loginTitle = \SpecialPage::getSafeTitleFor( 'Userlogin' );
		$page = $out->getTitle();
		$urlaction = 'returnto=' . $page->getPrefixedDBkey();
		$loginUrl = $loginTitle->getLocalURL( $urlaction );
		$createAccountUrl = $loginTitle->getLocalURL( $urlaction . '&type=signup' );

		$ret = '
				<div class="modal fade" id="connectionRequiredModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">'.wfMessage('login').'</h4>
				</div>
				<div class="modal-body">
				'.wfMessage('userspageslinks-connectionmodal-content').'
				</div>
				<div class="modal-footer">
				<a href="'.$loginUrl.'"><button type="button" class="btn btn-default">'.wfMessage('pt-login-button').'</button></a>
				<a href="'.$createAccountUrl.'"><button type="button" class="btn btn-primary">'.wfMessage('createaccount').'</button></a>
				</div>
				</div>
				</div>
				</div>';
		return $ret;
	}

	public static function parserUsersPagesLinksUsersList( \Parser $input, $type = 'undifined', $grouppage = null ) {


		if( ! $grouppage) {
			if (!$input->getTitle() ) {
				trigger_error("No title founded");
				return false;
			}
			$page = $input->getTitle();
		} else {
			$page = \Title::newFromDBkey($grouppage);
		}

		$html = self::getUsersListHtml($page, $type,500);


		return array( $html, 'noparse' => true, 'isHTML' => true );
	}


	public static function parserButton( \Parser $input, $type = 'star', $grouppage = null ) {
		global $wgUsersPagesLinksTypesUndoLabelsKey;


		$input->getOutput()->addModuleStyles(
				array(
						'ext.userspageslinks.css'
				)
		);
		$input->getOutput()->addModules( 'ext.userspageslinks.js' );

		if( ! $grouppage) {
			if (!$input->getTitle() ) {
				trigger_error("No title founded");
				return false;
			}
			$grouppage = $input->getTitle()->getPrefixedDBkey();
		}

		$pagesLinksActives = UsersPagesLinksCore::getInstance()->getUserPageLinks($input->getUser(), $input->getTitle());
		$pagesLinksCounters= UsersPagesLinksCore::getInstance()->getPageCounters($input->getTitle());

		if (UsersPagesLinksCore::getInstance()->hasLink($input->getUser(), $input->getTitle(), $type)){
			$addClass='rmAction';
		} else {
			$addClass='addAction';
		}

		$counter = isset($pagesLinksCounters[$type]) ? $pagesLinksCounters[$type] : 0;

		$doLabel = wfMessage('userspageslinks-' . $type);
		$undoLabel = '';

		switch($type) {
			case 'star':
				$faClass ='fa fa-heart';
				break;
			case 'member':
				$faClass ='fa fa-group';
				break;
			case 'ididit':
				$faClass ='fa fa-hand-peace-o';
				break;
			default:
				$faClass ='fa fa-eye';
				break;
		}
		$wgUsersPagesLinksTypesUndoLabelsKey = [
				'member' => 'userspageslinks-unmember'
		];

		$undoLabel = isset($wgUsersPagesLinksTypesUndoLabelsKey[$type]) ? wfMessage($wgUsersPagesLinksTypesUndoLabelsKey[$type]) : "";


		if (UsersPagesLinksCore::getInstance()->hasLink($input->getUser(), $input->getTitle(), $type)){
			$addClass='rmAction';
			if ($undoLabel !== '') {
				$label = $undoLabel;
			} else {
				$label = $doLabel;
			}
		} else {
			$addClass='addAction';
			$label = $doLabel;
		}

		$button = '<a class="UsersPagesLinksButton '.$addClass.'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button class=" doActionLabel">';
		$button .= '<span ><i class="'.$faClass.' upl_icon"></i> ';
		$button .= '<i class="fa fa-spinner fa-spin upl_loading" style="display:none"></i> ';
		$button .= '<span class="labelText" data-doLabel="' . $doLabel . '" data-undoLabel="' . $undoLabel . '">' . $label.'</span>';
		$button .= '</span>';
		$button .= '</button>';
		$button .= '</a>';
		$button .= '<a class="UsersPagesLinksButtonCounter '.$addClass.'" title="'.wfMessage("wf-formgroup-tab-members-tabtitle")->plain().'" data-linkstype="'.$type.'" data-page="'.$grouppage.'" >';
		$button .= '<button>';
		$button .= $counter;
		$button .= '</button>';
		$button .= '</a>';




		return array( $button, 'noparse' => true, 'isHTML' => true );

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
