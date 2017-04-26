<?php
// Include the library
require_once 'vendor/autoload.php';

// Require the Silverpop Namespace
use Silverpop\EngagePod;

function civicrm_api3_silverpop_getlists($params) {

  $silverpop = new EngagePod(array(
    'username'       => $params['username'],
    'password'       => $params['password'],
    'engage_server'  => 4,
  ));

// Fetch all contact lists
  $lists = $silverpop->GetLists();
  $templates = $silverpop->getMailingTemplates(FALSE);


}
