<?php
namespace local_startpage\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use stdClass;
use templatable;

class startpage implements renderable, templatable{

    public function export_for_template(renderer_base $output) {
        global $CFG, $SESSION;

        require_once($CFG->libdir . '/authlib.php');

        $authsequence = get_enabled_auth_plugins(true); // Get all auths, in sequence.
        $potentialidps = array();
        foreach ($authsequence as $authname) {
            $authplugin = get_auth_plugin($authname);
            $potentialidps = array_merge($potentialidps, $authplugin->loginpage_idp_list($SESSION->wantsurl));
        }

        if (!empty($potentialidps)) {
            $alts = [];

            foreach ($potentialidps as $idp) {
                $alt = new stdClass();
                $alt->url = htmlspecialchars_decode($idp['url']->out());
                $alt->name = s($idp['name']);
                $alt->icon = s($idp['iconurl']);

                $alts[] = $alt;
            }
        }

        return[
            'colorscheme' => $CFG->colorscheme,
            'token' => s(\core\session\manager::get_login_token()),
            'alts' => $alts
        ];
    }

    public function __construct()
    {

    }
}