<?php
/** Project facts. Port of src/config/site.ts. */

if (!defined('ABSPATH')) {
    exit;
}

const HEAL_SITE = [
    'name'          => 'HEAL',
    'fullName'      => 'COM-B & Exposome Partnership for Health Engagement',
    'description'   => 'HEAL is a research project in Limassol testing personalised, context-aware nudges to support active mobility and health.',
    'projectNumber' => 'POST-DOC/0925/ROandOther/0027',
    'contactEmail'  => 'TODO@cut.ac.cy',
];

/** Public deliverables per table B4.4. Every other deliverable is confidential. */
const HEAL_PUBLIC_DELIVERABLES = ['D4', 'D10', 'D11', 'D12', 'D14'];
