<?php
namespace UsersPagesLinks;
use SpecialPage ;
use Title; 

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
		$pageTitle = Title::newFromText($pageName);
		
		$typeButton = $request->getText( 'typeButton' );
		
		$testHTML = Buttons::getUsersListHtml($pageTitle, $typeButton);
		
		$output->addHTML($testHTML);
	}
}

