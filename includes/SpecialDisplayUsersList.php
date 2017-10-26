<?php
namespace UsersPagesLinks;
use SpecialPage ;


class SpecialDisplayUsersList extends SpecialPage {
	function __construct() {
		parent::__construct( 'DisplayUsersList' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		# Get request data from, e.g.
		// Déclaration de mes variables
		$pageName = $request->getText( 'pageName' );
		$pageTitle = \Title::newFromText($pageName);
		$typeButton = $request->getText( 'typeButton' );
		$numPage = $request->getInt('numPage',1);
		$nbrElementsByPage = 30;

		$allFollowers = UsersPagesLinksCore::getInstance()->getPageCounters($pageTitle);
		$nbrTotalPages = ceil($allFollowers[$typeButton]/$nbrElementsByPage);


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

			$usersList = Buttons::getUsersListHtml($pageTitle, $typeButton, $nbrElementsByPage, $numPage);
			$output->addHTML($usersList);

			$this->displayPagination($output,$nbrElementsByPage, $numPage, $pageTitle, $typeButton );
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



		$specialTitlePage = $this->getPageTitle();

		// Les deux url suivantes sont par défaut à numPage=1
		$urlPreviousUsers= $specialTitlePage->getFullURL($urlParamsPrevious);
		$urlNextUsers= $specialTitlePage->getFullURL($urlParamsNext);

		if ($nbrTotalPages==1){

		}

		else {

			$output->addHTML('<div class="allPagesNumber">');

			if ($numPage > 1){
				$output->addHTML('<a href="'.$urlPreviousUsers.'"> < </a>');
			}

			for ($i = 1; $i<= $nbrTotalPages; $i++) {


				$urlParamsChoose = array (
						'pageName' => $pageTitle->getText(),
						'typeButton'=> $typeButton,
						'numPage' => $i,
				);
				$urlChooseUsers = $specialTitlePage->getFullURL($urlParamsChoose);
				if ($i==$numPage){
					$output->addHTML('<span class="pageSelect">'.$i.'</span>');
				}
				else {
					$output->addHTML('<span class="numberPages"> <a href="'.$urlChooseUsers.'">'.$i.'</a> </span>');
				}
			}



			if ($numPage < $nbrTotalPages){
				$output->addHTML('<a href="'.$urlNextUsers.'"> > </a>');
			}

			$output->addHTML('</div>');

		}
	}


}

