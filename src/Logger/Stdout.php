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

    $request_uri = $context['request_uri'];
    $referrer_uri = $context['referer'];
    $variables = $this->parser->parseMessagePlaceholders($message, $context);
    $input_message = strip_tags(t($message, $variables));

    $message = t('WATCHDOG: [@severity] [@type] @message | user: @user | uri: @request_uri | referer: @referer_uri', [
      '@severity'    => $severity,
      '@type'        => $context['channel'],
      '@message'     => $input_message,
      '@user'        => $username,
      '@request_uri' => $request_uri,
      '@referer_uri' => $referrer_uri,
    ]);

    fwrite($output, $message . "\r\n");
    fclose($output);
  }

}
