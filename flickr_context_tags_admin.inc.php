<?php

function flickr_context_tags_settings() {
  $form = array();
  
  // Use node tags from vocabulaies
  $vocabs = array();
  $vocabs_raw = taxonomy_get_vocabularies();
  foreach ($vocabs_raw as $vocab) {
    $vocabs[$vocab->vid] = $vocab->name;
  }
  
  $mt_ns = variable_get('flickr_context_tags_mech_namespace', '');
  //Make a qualified guess
  if (empty($mt_ns)) {
    $matches = array();
    $site_name = variable_get('site_name','');
    if (preg_match('/^\s*([^\s\.]+)/', $site_name, $matches)) {
      $mt_ns = drupal_strtolower($matches[1]);
    }
  }
  
  $form['flickr_context_tags_mech_namespace'] = array(
    '#type' => 'textfield',
    '#title' => t('Machine-tag namespace'),
    '#default_value' => $mt_ns,
  );
  
  $form['flickr_context_tags_vocabs'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Context vocabularies'),
    '#description' => t('Vocabularies that should be used for context tags'),
    '#default_value' => variable_get('flickr_context_tags_vocabs', array()),
    '#options' => $vocabs,
  );
  
  // Tag configuration textarea
  $paths = variable_get('flickr_context_tags_contexts', array());
  $form['flickr_context_tags_contexts'] = array(
    '#type' => 'textarea',
    '#title' => t('Context tags for paths'),
    '#default_value' => flickr_context_tags_pack_contexts($paths),
    '#description' => t('Enter the path expression followed by a list of comma-separated tags'),
  );
  
  // Piggyback on the system settings form for buttons and so on
  $form = system_settings_form($form);
  // ...but replace the #submit function
  $form['#submit'] = array('flickr_context_tags_settings_submit');
  return $form;
}

function flickr_context_tags_pack_contexts($paths) {
  $txt = '';
  foreach($paths as $path => $tags) {
    $txt .= $path . ' ' . join(', ', $tags) . "\n";
  }
  return $txt;
}

function flickr_context_tags_unpack_contexts($txt) {
  $paths = array();
  $lines = split("\n", $txt);
  foreach ($lines as $line) {
    $line = trim($line);
    $matches = array();
    if (preg_match('/([^\s]+)\s+(.*)/', $line, $matches)) {
      $path = $matches[1];
      $tags = preg_split('/\s*,\s*/', $matches[2]);
      $paths[$path] = $tags;
    }
  }
  return $paths;
}

function flickr_context_tags_settings_submit($form, $state) {
  $values = $state['values'];
  $paths = flickr_context_tags_unpack_contexts($values['flickr_context_tags_contexts']);
  variable_set('flickr_context_tags_contexts', $paths);
  variable_set('flickr_context_tags_mech_namespace', $values['flickr_context_tags_mech_namespace']);
}