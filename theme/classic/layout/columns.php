<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The columns layout for the classic theme.
 *
 * @package   theme_classic
 * @copyright 2018 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes();
$blockspre = $OUTPUT->blocks('side-pre');
$blockspost = $OUTPUT->blocks('side-post');
$blockscenterpre = $OUTPUT->blocks('center-pre');
$blockscenterpost = $OUTPUT->blocks('center-post');
$blockshorizontal = $OUTPUT->blocks('horizontal');

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$hascenterpre = $PAGE->blocks->region_has_content('center-pre', $OUTPUT);
$hascenterpost = $PAGE->blocks->region_has_content('center-post', $OUTPUT);
$hashorizontal = $PAGE->blocks->region_has_content('horizontal', $OUTPUT);

$isAdmin = is_siteadmin();

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockspre,
    'sidepostblocks' => $blockspost,
    'centerpreblocks' => $blockscenterpre,
    'centerpostblocks' => $blockscenterpost,
    'horizontalblocks' => $blockshorizontal,
    'haspreblocks' => $hassidepre,
    'haspostblocks' => $hassidepost,
    'hascenterpre' => $hascenterpre,
    'hascenterpost' => $hascenterpost,
    'hashorizontal' => $hashorizontal,
    'bodyattributes' => $bodyattributes,
    'isAdmin' => $isAdmin
];

echo $OUTPUT->render_from_template('theme_classic/columns', $templatecontext);

