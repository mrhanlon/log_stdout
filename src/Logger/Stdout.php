<?php

namespace Drupal\log_stdout\Logger;

use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Logger\LogMessageParserInterface;

class Stdout implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Constructs a Stdout object.
   *
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(LogMessageParserInterface $parser) {
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $config = \Drupal::config('log_stdout.settings');

    if ($level <= RfcLogLevel::WARNING) {
      $output = fopen('php://stderr', 'w');
    }
    else {
      $output = fopen('php://stdout', 'w');
    }
    $severity = strtoupper(RfcLogLevel::getLevels()[$level]);
    $username = '';
    if (isset($context['user']) && !empty($context['user'])) {
      $username = $context['user']->getAccountName();
    }
    if (empty($username)) {
      $username = 'anonymous';
    }

    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $input_message = strip_tags(t($message, $variables));

    /* context
    [channel] => cron
    [link] =>
    [uid] => 0
    [request_uri] => http://oci.rokebi.com/cron/v5HeY2s06H-VAp14afM9cWWej0S9AwUpmLjJ7KYfzZEnJ691-ZkJVDKca5wU0QsxQyrCyz0b-g
    [referer] =>
    [ip] => 10.244.1.128
    [timestamp] => 1597300569
    */

    $message = t( $config->get('format'), [
      '@timestamp'   => $context['timestamp'],
      '@severity'    => $severity,
      '@type'        => $context['channel'],
      '@message'     => $input_message,
      '@uid'         => $context['uid'],
      '@request_uri' => $context['request_uri'],
      '@referer' => $context['referer'],
      '@ip' => $context['ip'],
      '@link' => $context['link'],
      '@date' => date('Y-m-d\TH:i:s', $context['timestamp']),
    ]);

    fwrite($output, $message . "\r\n");
    fclose($output);
  }

}
