<?php
global $params;

$context = Timber::get_context();
$context['survey_id'] = $params['survey_id'];

Timber::render('survey.twig', $context);