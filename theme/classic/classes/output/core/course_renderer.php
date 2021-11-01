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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_classic
 * @copyright  2018 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_classic\output\core;

defined('MOODLE_INTERNAL') || die;

use cm_info;
use coursecat_helper;
use core_course_category;
use single_select;
use lang_string;
use context_course;
use context_module;
use core_course_list_element;
use html_writer;
use moodle_url;
use coursecat;
use completion_info;
use pix_icon;
use stdClass;

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer
{


    /**
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(coursecat_helper $chelper, $coursecat)
    {
        // Reset the category expanded flag for this course category tree first.
        $this->categoryexpandedonload = false;
        $categorycontent = $this->coursecat_category_content($chelper, $coursecat, 0);
        if (empty($categorycontent)) {
            return '';
        }

        // Start content generation
        $content = '';
        /*
                if ($coursecat->get_children_count()) {
                    $content .= html_writer::link('#', $linkname, array('class' => implode(' ', $classes)));
                }
        */
        // if (!$coursecat->id) {
        //   if (isset($PAGE->theme->settings->courseliststyle) && ($PAGE->theme->settings->courseliststyle == 2)) {
        //     $content .= '<div class="courses row category-browse-3">';
        //   } else {
        //     $content .= '<div class="courses row courses_container">';
        //   }
        // }
        $content .= $categorycontent;
        // if (!$coursecat->id) {
        //   $content .= '</div>';
        // }

        return $content;
    }


    public function course_category($category)
    {
        global $CFG, $PAGE;
        $usertop = core_course_category::user_top();
        if (empty($category)) {
            $coursecat = $usertop;
        } else if (is_object($category) && $category instanceof core_course_category) {
            $coursecat = $category;
        } else {
            $coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
        }
        $site = get_site();
        $output = '';

        // if ($coursecat->can_create_course() || $coursecat->has_manage_capability()) {
        //   // Add 'Manage' button if user has permissions to edit this category.
        //   $managebutton = $this->single_button(new moodle_url('/course/management.php', array('categoryid' => $coursecat->id)), get_string('managecourses'), 'get');
        //   $this->page->set_button($managebutton);
        // }
        if (!$coursecat->id || !$coursecat->is_uservisible()) {
            $categorieslist = core_course_category::make_categories_list();
            $strcategories = get_string('categories');
            $this->page->set_title("$site->shortname: $strcategories");
        } else {

            $strfulllistofcourses = get_string('fulllistofcourses');
            $this->page->set_title("$site->shortname: $strfulllistofcourses");

            // Print the category selector
            $categorieslist = core_course_category::make_categories_list();
            // if (count($categorieslist) > 1) {
            $select = new single_select(new moodle_url('/course/index.php'), 'categoryid', core_course_category::make_categories_list(), $coursecat->id, null, 'switchcategory');
            // }
        }

        // 202003031234 - check that the user is within a course category and not just on the /courses/index.php page, because below demands a category ID
        if ($coursecat->id && $coursecat->is_uservisible()) {

            $output .= '<div class="row">
    						<div class="col-xl-4">
    							<div class="instructor_search_result style2">
    								<p class="mt10 fz15"><span class="color-dark pr5">' . $coursecat->get_courses_count() . ' </span> ' . get_string('courses') . '</p>
    							</div>
    						</div>
    						<div class="col-xl-8">
    							<div class="candidate_revew_select style2 text-right mb25">
    								<ul>
    									<li class="list-inline-item">' . $this->render($select) . '</li>
    									<li class="list-inline-item">' . /*$this->course_search_form() .*/ '</li>
    								</ul>
    							</div>
    						</div>
    					</div>
            ';

        } else {
            $output .= '<div class="row">
                <div class="col-xl-4">
                  <div class="instructor_search_result style2">
                    <p class="mt10 fz15"><span class="color-dark pr5">' . count($categorieslist) . ' </span> ' . get_string('categories') . '</p>
                  </div>
                </div>
                <div class="col-xl-8">
                  <div class="candidate_revew_select style2 text-right mb25">
                    <ul>

                      <li class="list-inline-item">' . /*$this->course_search_form() .*/ '</li>
                    </ul>
                  </div>
                </div>
              </div>
            ';

        }//End 202003031234

        // Print current category description
        $chelper = new coursecat_helper();
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            // $output .= $this->box($description, array('class' => 'generalbox info'));
        }
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)->set_attributes(array('class' => 'row category-browse-' . $coursecat->id));
        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->get_children_count()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->get_courses_count()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);

        $output .= $this->coursecat_tree($chelper, $coursecat);

        // Add action buttons
        $output .= '<div class="coursecat_action_btns">';
        if ($coursecat->can_create_course() || $coursecat->has_manage_capability()) {
            // Add 'Manage' button if user has permissions to edit this category.
            $managebutton = $this->single_button(new moodle_url('/course/management.php', array('categoryid' => $coursecat->id)), get_string('managecourses'), 'get');
            $output .= $managebutton;
        }
        if ($coursecat->is_uservisible()) {
            $context = get_category_or_system_context($coursecat->id);
            if (has_capability('moodle/course:create', $context)) {
                // Print link to create a new course, for the 1st available category.
                if ($coursecat->id) {
                    $url = new moodle_url('/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category'));
                } else {
                    $url = new moodle_url('/course/edit.php',
                        array('category' => $CFG->defaultrequestcategory, 'returnto' => 'topcat'));
                }
                $output .= $this->single_button($url, get_string('addnewcourse'), 'get');
            }
            ob_start();
            print_course_request_buttons($context);
            $output .= ob_get_contents();
            ob_end_clean();
        }
        $output .= '</div><!-- /.coursecat_action_btns -->';


        return $output;
    }

    protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth)
    {

        global $CFG, $PAGE;

        $categoryname = $coursecat->get_formatted_name();
        $category_link = new moodle_url('/course/index.php', array('categoryid' => $coursecat->id));

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_COUNT && ($coursescount = $coursecat->get_courses_count())) {
            $categoryname .= html_writer::tag('span', ' (' . $coursescount . ')', array('title' => get_string('numberofcourses'), 'class' => 'numberofcourse'));
        }

        $cat_summary_unclean = $chelper->get_category_formatted_description($coursecat);
        $cat_summary = preg_replace("/<img[^>]+\>/i", " ", $cat_summary_unclean);
        $children_courses = $coursecat->get_courses();
        $items_count = '';
        if ($coursecat->get_children_count() > 0) {
            $items_count .= $coursecat->get_children_count() . ' ' . get_string('categories');
        } else {
            $items_count .= count($coursecat->get_courses()) . ' ' . get_string('courses');
        }
        $cat_updated = get_string('modified') . ' ' . userdate($coursecat->timemodified, '%d %B %Y', 0);

        $contentimages = '';
        $src = '';
        if ($description = $chelper->get_category_formatted_description($coursecat)) {
            $dom = new \DOMDocument();
            $dom->loadHTML($description);
            $xpath = new \DOMXPath($dom);
            $src = $xpath->evaluate("string(//img/@src)");
        }
        if ($src) {
            $contentimages .= '<img class="img-whp" src="' . $src . '" alt="' . $categoryname . '">';
        } else {
            foreach ($children_courses as $child_course) {
                if ($child_course === reset($children_courses)) {
                    foreach ($child_course->get_course_overviewfiles() as $file) {
                        $isimage = $file->is_valid_image();
                        $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
                        if ($isimage) {
                            //TODO fix coursename
                            $coursename = '';
                            $contentimages .= '<img class="img-whp" src="' . $url . '" alt="' . $coursename . '">';
                        }
                    }
                }
            }
        }
        $content = '';
        $content .= '
             <div class="col-lg-6 top_courses has-footer">';
        if ($contentimages) {
            $content .= '<div class="thumb">
                 ' . $contentimages . '
                 <div class="overlay">
                   <div class="tag">' . $items_count . '</div>
                   <a class="tc_preview_course" href="' . $category_link . '">' . get_string('viewallcourses') . '</a>
                 </div>
               </div>';
        }
        $content .= '
               <div class="details">
                         <div class="tc_content">';
        if (isset($PAGE->theme->settings->coursecat_modified) && ($PAGE->theme->settings->coursecat_modified !== '1')) {
            $content .= '<p>' . $cat_updated . '</p>';
        }
        $content .= '
                           <h5><a href="' . $category_link . '">' . $categoryname . '</a></h5>
                           ' . $cat_summary . '

                         </div>
                         </div>
                         <div class="tc_footer">
                           <ul class="tc_meta float-left">
                             <li class="list-inline-item"><i class="flaticon-book"></i></li>
                             <li class="list-inline-item">' . $items_count . '</li>
                           </ul>
                         </div>

             </div>';
        return $content;
    }

    protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth)
    {
        global $CFG;
        $subcategories = array();
        if (!$chelper->get_categories_display_option('nodisplay')) {
            $subcategories = $coursecat->get_children($chelper->get_categories_display_options());
        }
        $totalcount = $coursecat->get_children_count();
        if (!$totalcount) {
            // Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
            // to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        $content = '';
        $content .= '<div class="courses row courses_container">';

        // prepare content of paging bar or more link if it is needed
        $paginationurl = $chelper->get_categories_display_option('paginationurl');
        $paginationallowall = $chelper->get_categories_display_option('paginationallowall');
        if ($totalcount > count($subcategories)) {
            if ($paginationurl) {
                // the option 'paginationurl was specified, display pagingbar
                $perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_categories_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar($totalcount, $page, $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage)));
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
                // the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $viewmoreurl->param('categoryid', $coursecat->id);
                }
                $viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink'));
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)), array('class' => 'paging paging-showperpage'));
        }

        // display list of subcategories
        // $content = html_writer::start_tag('div', array('class' => ''));

        // if (!empty($pagingbar)) {
        //     $content .= $pagingbar;
        // }
/*
        $categorycount = 0;
        $categoriesNumber = count($subcategories);
        foreach ($subcategories as $subcategory) {
            $categorycount++;
            if ($categorycount % 2) {
                $content .= '<div class="course-pair">';
            }
            $content .= $this->coursecat_category($chelper, $subcategory, $depth + 1);
            if (!($categorycount % 2) || $categorycount >= $categoriesNumber) {
                $content .= '</div>';
            }
        }*/

        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_category($chelper, $subcategory, $depth + 1);
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        // $content .= html_writer::end_tag('div');
        $content .= '</div>';
        return $content;
    }

    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '')
    {

        global $PAGE;

        // if (!isset($this->strings->summary)) {
        //     $this->strings->summary = get_string('summary');
        // }
        // if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
        //     return '';
        // }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $content = '';

        // if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
        $nametag = 'h3';
        // } else {
        //     $classes .= ' collapsed';
        //     $nametag = 'div';
        // }

        global $info_box;

        $info_box = html_writer::start_tag('div', array('class' => 'info'));


        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
        $info_box .= html_writer::start_tag('div', array('class' => 'moreinfo'));
        // if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
        // if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()
        //         || $course->has_custom_fields()) {
        // $url = new moodle_url('/course/info.php', array('id' => $course->id));
        // $image = $this->output->pix_icon('i/info', $this->strings->summary);
        // $info_box .= html_writer::link($url, $image, array('title' => $this->strings->summary));
        // Make sure JS file to expand course content is included.
        // $this->coursecat_include_js();
        // }
        // }
        $info_box .= html_writer::end_tag('div'); // .moreinfo

        // print enrolmenticons
        if ($icons = enrol_get_course_info_icons($course)) {
            $info_box .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pix_icon) {
                $info_box .= $this->render($pix_icon);
            }
            $info_box .= html_writer::end_tag('div'); // .enrolmenticons
        }

        $info_box .= html_writer::end_tag('div'); // .info

        $content .= $this->coursecat_coursebox_content($chelper, $course);

        return $content;
    }

    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course)
    {
        global $CFG, $PAGE, $info_box;
        // if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
        //     return '';
        // }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }
        $content = '';
        $courseid = $course->id;
        $contentimages = $contentfiles = '';
        $coursesummary = ($course->has_summary()) ? $chelper->get_course_formatted_summary($course) : '';
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = new moodle_url('/course/view.php', array('id' => $course->id));
        $course_context = context_course::instance($course->id);
        $numberofusers = count_enrolled_users($course_context);
        $category = format_text($PAGE->category->name, FORMAT_HTML, array('filter' => true));

        // Display course contacts. See core_course_list_element::get_course_contacts().
        if ($course->has_course_contacts()) {
            $course_contacts = '';
            foreach ($course->get_course_contacts() as $coursecontact) {
                $rolenames = array_map(function ($role) {
                    return $role->displayname;
                }, $coursecontact['roles']);
                $name = implode(", ", $rolenames) . ': ' .
                    html_writer::link(new moodle_url('/user/view.php',
                        array('id' => $coursecontact['user']->id, 'course' => SITEID)),
                        $coursecontact['username']);
                $course_contacts .= '<span class="course_meta_item mr10">' . $name . '</span>';
            }
        }
        $course_meta = !empty($course_contacts) ? $course_contacts : $category;
        $contenttext = '';
        if (
            (isset($PAGE->theme->settings->coursecat_enrolments) && ($PAGE->theme->settings->coursecat_enrolments != 1)) || (isset($PAGE->theme->settings->coursecat_announcements) && ($PAGE->theme->settings->coursecat_announcements != 1))
        ) {
            $topCoursesClass = 'has-footer';
        } else {
            $topCoursesClass = '';
        }
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages = '<img class="img-whp" src="' . $url . '" alt="' . $coursename . '">';
            }
        }
        //TODO fix default image
        if (!$contentimages) {
            $contentimages = '<img class="img-whp" src="/theme/classic/pix/course-default.png" alt="' . $coursename . '">';
        }
        $contenttext .= '
							<div class="col-lg-6 top_courses ' . $topCoursesClass . '">';
        if ($contentimages) {
            $contenttext .= '
								<div class="thumb">
									' . $contentimages . '
									<div class="overlay">
                    <div class="tag">' . $category . '</div>
										<a class="tc_preview_course" href="' . $coursenamelink . '">' . $coursename . '</a>
									</div>
								</div>';
        }
        $contenttext .= '
                <div class="details">
                          <div class="tc_content">
                            <p>' . $course_meta . '</p>
                            <h5><a href="' . $coursenamelink . '">' . $coursename . '</a></h5>
                            <p>' . $coursesummary . '</p>';
        $contenttext .= $info_box . '
                          </div>
                          </div>';
        if (
            (isset($PAGE->theme->settings->coursecat_enrolments) && ($PAGE->theme->settings->coursecat_enrolments != 1)) || (isset($PAGE->theme->settings->coursecat_announcements) && ($PAGE->theme->settings->coursecat_announcements != 1))
        ) {
            $contenttext .= '
                          <div class="tc_footer">
                            <ul class="tc_meta float-left">';
        }
        if (isset($PAGE->theme->settings->coursecat_enrolments) && ($PAGE->theme->settings->coursecat_enrolments != 1)) {
            $contenttext .= '
                              <li class="list-inline-item"><i class="flaticon-profile"></i></li>
                              <li class="list-inline-item">' . $numberofusers . '</li>';
        }
        if (isset($PAGE->theme->settings->coursecat_announcements) && ($PAGE->theme->settings->coursecat_announcements != 1)) {
            $contenttext .= '
                              <li class="list-inline-item"><i class="flaticon-comment"></i></li>
                              <li class="list-inline-item">' . $course->newsitems . '</li>';
        }
        if (
            (isset($PAGE->theme->settings->coursecat_enrolments) && ($PAGE->theme->settings->coursecat_enrolments != 1)) || (isset($PAGE->theme->settings->coursecat_announcements) && ($PAGE->theme->settings->coursecat_announcements != 1))
        ) {
            $contenttext .= '
                            </ul>
                          </div>';
        }
        $contenttext .= '

							</div>';

        $content .= $contenttext . $contentfiles;

        // display course category if necessary (for example in search results)
        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
            if ($cat = core_course_category::get($course->category, IGNORE_MISSING)) {
                $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                $content .= get_string('category') . ': ' .
                    html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                        $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                $content .= html_writer::end_tag('div'); // .coursecat
            }
        }

        return $content;
    }

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array())
    {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                    $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                    html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                    array('class' => 'movehere'));
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text cs_list mb0'));

        return $output;
    }


    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null)
    {
        global $CFG;
        if ($totalcount === null) {
            $totalcount = count($courses);
        }
        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // prepare content of paging bar if it is needed
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // there are more results that can fit on one page
            if ($paginationurl) {
                // the option paginationurl was specified, display pagingbar
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar($totalcount, $page, $perpage,
                    $paginationurl->out(false, array('perpage' => $perpage)));
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
                        get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link
                $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext),
                    array('class' => 'paging paging-morelink'));
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
            $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)), array('class' => 'paging paging-showperpage'));
        }

        // display list of courses
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);

        // if (!empty($pagingbar)) {
        //     $content .= '<div class="col-lg-12 mt30 mb30">'.$pagingbar.'</div>';
        // }

        $coursecount = 0;
        $coursesNumber = count($courses);
        foreach ($courses as $course) {
            $coursecount++;
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= $coursesNumber) {
                $classes .= ' last';
            }
//            if ($coursecount % 2) {
//                $content .= '<div class="course-pair">';
//            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
//            if (!($coursecount % 2) || $coursecount >= $coursesNumber) {
//                $content .= '</div>';
//            }
        }

        if (!empty($pagingbar)) {
            $content .= '<div class="col-lg-12 mt30 mb30">' . $pagingbar . '</div>';
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // .courses
        return $content;
    }

}

