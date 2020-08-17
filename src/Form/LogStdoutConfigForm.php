<?php

namespace Drupal\log_stdout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class LogStdoutConfigForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'log_stdout_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('log_stdout.settings');

    $form['format'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Log format'),
      '#default_value' => $config->get('format'),
      '#description'   => t('Specify the format of the log entry. Available variables are: <dl>' . 
        '<dt><code>@base_url</code></dt><dd>Base URL of the site.</dd>'.
        '<dt><code>@timestamp</code></dt><dd>Unix timestamp of the log entry.</dd>'.
        '<dt><code>@type</code></dt><dd>The category to which this message belongs.</dd>'.
        '<dt><code>@ip</code></dt><dd>IP address of the user triggering the message.</dd>'.
        '<dt><code>@request_uri</code></dt><dd>The requested URI.</dd>'.
        '<dt><code>@referer</code></dt><dd>HTTP Referer if available.</dd>'.
        '<dt><code>@severity</code></dt><dd>The severity level of the event; ranges from 0 (Emergency) to 7 (Debug).</dd>'.
        '<dt><code>@uid</code></dt><dd>User ID.</dd>'.
        '<dt><code>@link</code></dt><dd>A link to associate with the message.</dd>'.
        '<dt><code>@message</code></dt><dd>The message to store in the log.</dd></dl>'),
    ];

    return $form;
  }

    /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('log_stdout.settings');

    $config->set('format', $form_state->getValue('format'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'log_stdout.settings',
    ];
  }
}

?>