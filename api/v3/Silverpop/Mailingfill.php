<?php
// Include the library
require_once 'vendor/autoload.php';

// Require the Silverpop Namespace
use Silverpop\EngagePod;

function civicrm_api3_silverpop_mailingfill($params) {

  $silverpop = new EngagePod(array(
    'username'       => $params['username'],
    'password'       => $params['password'],
    'engage_server'  => 4,
  ));

  $endDate = !empty($params['end_date']) ? $params['end_date'] : 'now';

  $mailings = $silverpop->getSentMailingsForOrg(
    $params['start_date'],
    $endDate
  );

 // $templates = $silverpop->getMailingTemplates(
 //   FALSE,
  //  date('m/d/Y H:i:s', strtotime($params['start_date'])),
  //  date('m/d/Y H:i:s', strtotime($endDate))
 // );
  $vaules = array();
  foreach ($mailings  as $mailing) {
    if (!isset($mailing['ParentTemplateId'])) {
      $e = $mailing;
    }
    $details = $silverpop->getMailingTemplate($mailing['ParentTemplateId']);
    $stats = $silverpop->getAggregateTrackingForMailing($mailing['MailingId'], $mailing['ReportId']);
    $campaign = civicrm_api3('Campaign', 'create', array('name' => $mailing['MailingId'], 'title' => $mailing['MailingId']));
    $result = civicrm_api3('Mailing', 'replace', array(
      'hash' => $mailing['MailingId'],
      'debug' => 1,
      'values' => array(array(
        'body_html' => !empty($details['HTMLBody']) ? $details['HTMLBody'] : '',
        'body_text' => !empty($details['TextBody']) ? $details['TextBody'] : '',
        'name' => $mailing['MailingName'],
        'subject' => $mailing['Subject'],
        'created_date' => $mailing['ScheduledTS'],
        'hash' => $mailing['MailingId'],
        'scheduled_date' => $mailing['ScheduledTS'],
      )),
    ));
    $values[] = $result['values'][$result['id']];
    civicrm_api3('MailingStats', 'create', array(
      'debug' => 1,
      'mailing_id' => $result['id'],
      'mailing_name' => $mailing['MailingName'],
      'is_completed' => TRUE,
      'created_date' => $mailing['ScheduledTS'],
      'start' => $mailing['SentTS'],
      //'finish' =>
      'recipients' => $mailing['NumSent'],
      'delivered' => $mailing['NumSent'] - $stats['NumBounceSoft'],
      // 'send_rate'
      'bounced' => $stats['NumBounceSoft'],
      'opened_total' => $stats['NumGrossOpen'],
      'opened_unique' => $stats['NumUniqueOpen'],
      'unsubscribed' => $stats['NumUnsubscribes'],
      'suppressed' => $stats['NumSuppressed'],
      // 'forwarded'
      'blocked' => $stats['NumGrossMailBlock'],
      // 'clicked_total' => $stats['NumGrossClick'],
      'abuse_complaints' => $stats['NumGrossAbuse'],
      // 'clicked_contribution_page'
      // 'contribution_count'
      // 'contribution_total'
    ));

  }
  return civicrm_api3_create_success($values);
}

function _civicrm_api3_silverpop_mailingfill_spec(&$params) {
  $params['start_date'] = array(
    'api.default' => '2017-01-01',
    'type' => CRM_Utils_Type::T_DATE,
  );

}
