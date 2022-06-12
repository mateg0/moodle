<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'mdl';
$CFG->dbuser    = 'moodle';  // ЗАМЕНИТЬ
$CFG->dbpass    = 'm0oD!e';  // ЗАМЕНИТЬ
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' =>  3307,
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_bin',
);

$CFG->wwwroot   = 'http://moodle';  // ЗАМЕНИТЬ
$CFG->dataroot  = 'd:\moodledata';  // ЗАМЕНИТЬ
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

$CFG->customfrontpage = 'startpage/';

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
