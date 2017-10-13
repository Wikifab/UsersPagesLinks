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
		$pageName = $request->getText( 'pageName' );
		$pageTitle = \Title::newFromText($pageName);
		var_dump($pageTitle);
		$typeButton = $request->getText( 'typeButton' );
		
		$usersList = Buttons::getUsersListHtml($pageTitle, $typeButton);
		
		$output->addHTML($usersList);
	}
}

