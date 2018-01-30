<?php
namespace UsersPagesLinks;
use SpecialPage ;


class SpecialDisplayUsersList extends SpecialPage {
	function __construct() {
		parent::__construct( 'DisplayUsersList' );
	}

	function execute( $par ) {
	    global $wgUsersListNbrElementsByPage;
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		// Déclaration de mes variables
		$pageName = $request->getText( 'pageName' );
		$pageTitle = \Title::newFromText($pageName);
		$typeButton = $request->getText( 'typeButton' );
		$numPage = $request->getInt('numPage',1);

		$allFollowers = UsersPagesLinksCore::getInstance()->getPageCounters($pageTitle);
		$nbrTotalPages = ceil($allFollowers[$typeButton]/$wgUsersListNbrElementsByPage);


		if($numPage > $nbrTotalPages OR $numPage<=1){
			$numPage = 1;
		}
		// Si le nom de la page est vide ou inexistant alors on affiche le message d'erreur
		if ($pageTitle===null || ! $pageTitle->exists()){
			errorMessages();
		}
		//Sinon on affiche toute la page spéciale
		else {

			// Les différents titres des pages en fonction de quels utilisateurs on veut
			switch($typeButton){
				case 'star' :
					$output -> setPageTitle($this -> msg("userspageslinks-special-list-title-star",$pageName));
					break;

				case 'ididit' :
					$output -> setPageTitle($this -> msg("userspageslinks-special-list-title-ididit",$pageName));
					break;

				default:
					errorMessages();
					break;
			}
			//La ligne suivante est équivalente à : $displayLinkPreviousPage = $this->getPageTitle();
			$displayLinkPreviousPage= \SpecialPage::getTitleFor('DisplayUsersList');
			//On récupère l'url entière (sans paramètres autre que le nom de la page)
			$urlPreviousPage = $pageTitle->getFullURL();
			// Bouton qui permet de retourner à la page
			$btnGoBack = '<a href="'.$urlPreviousPage.'" class="buttonGoBack"><button class="btn btn-primary">';
			$btnGoBack .= wfMessage("userspageslinks-special-list-button-goback");
			$btnGoBack .= '</button></a>';

			$output->addHTML($btnGoBack);

			$usersList = Buttons::getUsersListHtml($pageTitle, $typeButton, $wgUsersListNbrElementsByPage, $numPage);
			$output->addHTML($usersList);

			$this->displayPagination($output,$wgUsersListNbrElementsByPage, $numPage, $pageTitle, $typeButton );
		}
	}

	function errorMessages (){

		$output -> setStatusCode(404);
		$output -> setPageTitle('Erreur 404');
		$output -> addHTML( "Erreur 404, la page demandée n'existe pas, ou est nulle.");
	}

	private function displayPagination ($output, $nbreResult, $numPage, \Title $pageTitle, $typeButton ){

		$numPagePrevious = $numPage - 1;
		$numPageNext = $numPage + 1;
		$i=1;

		$allFollowers = UsersPagesLinksCore::getInstance()->getPageCounters($pageTitle);
		$nbrTotalPages = ceil($allFollowers[$typeButton]/$nbreResult);


		// Paramètres de l'url quand on clic sur suivant
		$urlParamsNext = array(
				'pageName' => $pageTitle->getText(),
				'typeButton' => $typeButton,
				'numPage'=> $numPageNext,
		);
		// Paramètres de l'url quand on clic sur précédent
		$urlParamsPrevious = array(
				'pageName' => $pageTitle->getText(),
				'typeButton' => $typeButton,
				'numPage'=> $numPagePrevious,
		);

		$urlParamsChoose = array (
				'pageName' => $pageTitle->getText(),
				'typeButton'=> $typeButton,
				'numPage' => 1,
		);


		$specialTitlePage = $this->getPageTitle();

		// Les deux url suivantes sont par défaut à numPage=1
		$urlPreviousUsers= $specialTitlePage->getFullURL($urlParamsPrevious);
		$urlNextUsers= $specialTitlePage->getFullURL($urlParamsNext);


		if ($nbrTotalPages==1){
			return;
		}

		$pageLimit = 3;
		$output->addHTML('<div class="allPagesNumber">');

		//Display cursor for previous "<"
		if ($numPage > 1){
			$output->addHTML('<a href="'.$urlPreviousUsers.'"> < </a>');
		}
		if ($numPage>$pageLimit){
			$output->addHTML('<span class="pageHidding"> ... </span>');
		}
		for($i = ($numPage - $pageLimit) ; $i < $numPage ; $i++) {
			$urlParamsChoose['numPage']=$i;
			$urlChooseUsers = $specialTitlePage->getFullURL($urlParamsChoose);

			if($i > 0) {
				$output->addHTML('<span><a href="'.$urlChooseUsers.'" class="nbrAround">'.$i.'</a></span>');
			}

		}
		if ($i==$numPage){
			$output->addHTML('<span class="pageSelect">'.$i.'</span>');
		}
		$allFollowers[$typeButton] = 0;
		// Comment faire pour ne pas avoir à re-définir $urlChooseUsers ??
		// Comment ajouter 3 points avant et après si il reste des pages à afficher ?
		for($i = ($numPage + 1) ; $i <= $nbrTotalPages ; $i++) {
			$urlParamsChoose['numPage']=$i;
			$urlChooseUsers = $specialTitlePage->getFullURL($urlParamsChoose);

			if($allFollowers[$typeButton]  < $pageLimit) {
				$output->addHTML('<span><a href="'.$urlChooseUsers.'" class="nbrAround">'.$i.'</a></span>');
				$allFollowers[$typeButton] ++;
			}
		}
		if (($numPage+$pageLimit)<$nbrTotalPages){
			$output->addHTML('<span class="pageHidding"> ... </span>');
		}


		//Display cursor for next ">"
		if ($numPage < $nbrTotalPages){
			$output->addHTML('<a href="'.$urlNextUsers.'"> > </a>');
		}

		$output->addHTML('</div>');

	}
}


