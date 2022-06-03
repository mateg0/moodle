<?php
defined('MOODLE_INTERNAL') || die();


class course_show_more_external extends external_api
{

    public static function get_course($courseid)
    {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir. "/formslib.php");

        $params = self::validate_parameters(self::get_course_parameters(),
                        array('courseid' => $courseid));

        $isEnrollable = true;
        
        //retrieve the course
        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        if ($course->id != SITEID) {
            // Check course format exist.
            if (!file_exists($CFG->dirroot . '/course/format/' . $course->format . '/lib.php')) {
                throw new moodle_exception('cannotgetcoursecontents', 'webservice', '', null,
                                            get_string('courseformatnotfound', 'error', $course->format));
            } else {
                require_once($CFG->dirroot . '/course/format/' . $course->format . '/lib.php');
            }
        }

        // now security checks
        $context = context_course::instance($course->id, IGNORE_MISSING);

        //create return value
        $coursecontents = array();

        //retrieve sections
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();
        $courseformat = course_get_format($course);
        $coursenumsections = $courseformat->get_last_section_number();
        $stealthmodules = array();   // Array to keep all the modules available but not visible in a course section/topic.

        $completioninfo = new completion_info($course);

        //for each sections (first displayed to last displayed)
        $modinfosections = $modinfo->get_sections();
        foreach ($sections as $key => $section) {

            // This becomes true when we are filtering and we found the value to filter with.
            $sectionfound = false;

            // Filter by section id.
            if (!empty($filters['sectionid'])) {
                if ($section->id != $filters['sectionid']) {
                    continue;
                } else {
                    $sectionfound = true;
                }
            }

            // Filter by section number. Note that 0 is a valid section number.
            if (isset($filters['sectionnumber'])) {
                if ($key != $filters['sectionnumber']) {
                    continue;
                } else {
                    $sectionfound = true;
                }
            }

            // reset $sectioncontents
            $sectionvalues = array();
            $sectionvalues['id'] = $section->id;
            $sectionvalues['name'] = get_section_name($course, $section);
            $sectionvalues['visible'] = $section->visible;

            $options = (object) array('noclean' => true);
            list($sectionvalues['summary'], $sectionvalues['summaryformat']) =
                    external_format_text($section->summary, $section->summaryformat,
                            $context->id, 'course', 'section', $section->id, $options);
            $sectionvalues['section'] = $section->section;
            $sectionvalues['hiddenbynumsections'] = $section->section > $coursenumsections ? 1 : 0;
            $sectionvalues['uservisible'] = $section->uservisible;
            if (!empty($section->availableinfo)) {
                $sectionvalues['availabilityinfo'] = \core_availability\info::format_info($section->availableinfo, $course);
            }

            $sectioncontents = array();

            // For each module of the section.
            if (empty($filters['excludemodules']) and !empty($modinfosections[$section->section])) {
                foreach ($modinfosections[$section->section] as $cmid) {
                    $cm = $modinfo->cms[$cmid];

                    // Stop here if the module is not visible to the user on the course main page:
                    // The user can't access the module and the user can't view the module on the course page.
                    if (!$cm->uservisible && !$cm->is_visible_on_course_page()) {
                        continue;
                    }

                    // This becomes true when we are filtering and we found the value to filter with.
                    $modfound = false;

                    // Filter by cmid.
                    if (!empty($filters['cmid'])) {
                        if ($cmid != $filters['cmid']) {
                            continue;
                        } else {
                            $modfound = true;
                        }
                    }

                    // Filter by module name and id.
                    if (!empty($filters['modname'])) {
                        if ($cm->modname != $filters['modname']) {
                            continue;
                        } else if (!empty($filters['modid'])) {
                            if ($cm->instance != $filters['modid']) {
                                continue;
                            } else {
                                // Note that if we are only filtering by modname we don't break the loop.
                                $modfound = true;
                            }
                        }
                    }

                    $module = array();

                    $modcontext = context_module::instance($cm->id);

                    //common info (for people being able to see the module or availability dates)
                    $module['id'] = $cm->id;
                    $module['name'] = external_format_string($cm->name, $modcontext->id);
                    $module['instance'] = $cm->instance;
                    $module['contextid'] = $modcontext->id;
                    $module['modname'] = (string) $cm->modname;
                    $module['modplural'] = (string) $cm->modplural;
                    $module['modicon'] = $cm->get_icon_url()->out(false);
                    $module['indent'] = $cm->indent;
                    $module['onclick'] = $cm->onclick;
                    $module['afterlink'] = $cm->afterlink;
                    $module['customdata'] = json_encode($cm->customdata);
                    $module['completion'] = $cm->completion;
                    $module['noviewlink'] = plugin_supports('mod', $cm->modname, FEATURE_NO_VIEW_LINK, false);

                    // Check module completion.
                    $completion = $completioninfo->is_enabled($cm);
                    if ($completion != COMPLETION_DISABLED) {
                        $completiondata = $completioninfo->get_data($cm, true);
                        $module['completiondata'] = array(
                            'state'         => $completiondata->completionstate,
                            'timecompleted' => $completiondata->timemodified,
                            'overrideby'    => $completiondata->overrideby,
                            'valueused'     => core_availability\info::completion_value_used($course, $cm->id)
                        );
                    }

                    if (!empty($cm->showdescription) or $module['noviewlink']) {
                        // We want to use the external format. However from reading get_formatted_content(), $cm->content format is always FORMAT_HTML.
                        $options = array('noclean' => true);
                        list($module['description'], $descriptionformat) = external_format_text($cm->content,
                            FORMAT_HTML, $modcontext->id, $cm->modname, 'intro', $cm->id, $options);
                    }

                    //url of the module
                    $url = $cm->url;
                    if ($url) { //labels don't have url
                        $module['url'] = $url->out(false);
                    }

                    $canviewhidden = has_capability('moodle/course:viewhiddenactivities',
                                        context_module::instance($cm->id));
                    //user that can view hidden module should know about the visibility
                    $module['visible'] = $cm->visible;
                    $module['visibleoncoursepage'] = $cm->visibleoncoursepage;
                    $module['uservisible'] = $cm->uservisible;
                    if (!empty($cm->availableinfo)) {
                        $module['availabilityinfo'] = \core_availability\info::format_info($cm->availableinfo, $course);
                    }

                    // Availability date (also send to user who can see hidden module).
                    if ($CFG->enableavailability && ($canviewhidden || $canupdatecourse)) {
                        $module['availability'] = $cm->availability;
                    }

                    // Return contents only if the user can access to the module.
                    if ($cm->uservisible) {
                        $baseurl = 'webservice/pluginfile.php';

                        // Call $modulename_export_contents (each module callback take care about checking the capabilities).
                        require_once($CFG->dirroot . '/mod/' . $cm->modname . '/lib.php');
                        $getcontentfunction = $cm->modname.'_export_contents';
                        if (function_exists($getcontentfunction)) {
                            $contents = $getcontentfunction($cm, $baseurl);
                            $module['contentsinfo'] = array(
                                'filescount' => count($contents),
                                'filessize' => 0,
                                'lastmodified' => 0,
                                'mimetypes' => array(),
                            );
                            foreach ($contents as $content) {
                                // Check repository file (only main file).
                                if (!isset($module['contentsinfo']['repositorytype'])) {
                                    $module['contentsinfo']['repositorytype'] =
                                        isset($content['repositorytype']) ? $content['repositorytype'] : '';
                                }
                                if (isset($content['filesize'])) {
                                    $module['contentsinfo']['filessize'] += $content['filesize'];
                                }
                                if (isset($content['timemodified']) &&
                                        ($content['timemodified'] > $module['contentsinfo']['lastmodified'])) {

                                    $module['contentsinfo']['lastmodified'] = $content['timemodified'];
                                }
                                if (isset($content['mimetype'])) {
                                    $module['contentsinfo']['mimetypes'][$content['mimetype']] = $content['mimetype'];
                                }
                            }

                            if (empty($filters['excludecontents']) and !empty($contents)) {
                                $module['contents'] = $contents;
                            } else {
                                $module['contents'] = array();
                            }
                        }
                    }

                    // Assign result to $sectioncontents, there is an exception,
                    // stealth activities in non-visible sections for students go to a special section.
                    if (!empty($filters['includestealthmodules']) && !$section->uservisible && $cm->is_stealth()) {
                        $stealthmodules[] = $module;
                    } else {
                        $sectioncontents[] = $module;
                    }

                    // If we just did a filtering, break the loop.
                    if ($modfound) {
                        break;
                    }

                }
            }
            $sectionvalues['modules'] = $sectioncontents;

            // assign result to $coursecontents
            $coursecontents[$key] = $sectionvalues;

            // Break the loop if we are filtering.
            if ($sectionfound) {
                break;
            }
        }

        // Now that we have iterated over all the sections and activities, check the visibility.
        // We didn't this before to be able to retrieve stealth activities.
        foreach ($coursecontents as $sectionnumber => $sectioncontents) {
            $section = $sections[$sectionnumber];
            // Show the section if the user is permitted to access it OR
            // if it's not available but there is some available info text which explains the reason & should display OR
            // the course is configured to show hidden sections name.
            $showsection = $section->uservisible ||
                ($section->visible && !$section->available && !empty($section->availableinfo)) ||
                (!$section->visible && empty($courseformat->get_course()->hiddensections));

            if (!$showsection) {
                unset($coursecontents[$sectionnumber]);
                continue;
            }

            // Remove section and modules information if the section is not visible for the user.
            if (!$section->uservisible) {
                $coursecontents[$sectionnumber]['modules'] = array();
                // Remove summary information if the section is completely hidden only,
                // even if the section is not user visible, the summary is always displayed among the availability information.
                if (!$section->visible) {
                    $coursecontents[$sectionnumber]['summary'] = '';
                }
            }
        }

        // Include stealth modules in special section (without any info).
        if (!empty($stealthmodules)) {
            $coursecontents[] = array(
                'id' => -1,
                'name' => '',
                'summary' => '',
                'summaryformat' => FORMAT_MOODLE,
                'modules' => $stealthmodules
            );
        }

        // get all enrol forms available in this course
        $enrols = enrol_get_plugins(true);
        $enrolinstances = enrol_get_instances($course->id, true);
        $forms = array();
        foreach($enrolinstances as $instance) {
            if (!isset($enrols[$instance->enrol])) {
                continue;
            }
            $form = $enrols[$instance->enrol]->enrol_page_hook($instance);
            if ($form) {
                $forms[$instance->id] = $form;
            }
        }

        if (!$forms) {
            $isEnrollable = false;
        }

        return [
            'course' => $course,
            'modules' => $coursecontents,
            'isEnrollable' => $isEnrollable
        ];
    }

    public static function get_course_parameters()
    {
        return new external_function_parameters(array(
            'courseid'=> new external_value(PARAM_INT, "Course id")
        ));
    }

    public static function get_course_returns()
    {
        return
        new external_single_structure([
            'course' => new external_single_structure(
                array(
                    'fullname' => new external_value(PARAM_RAW, 'Course name'),
                    'summary' => new external_value(PARAM_RAW, 'Course summary')
                )
            ),
            'modules' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'Section ID'),
                        'name' => new external_value(PARAM_RAW, 'Section name'),
                        'visible' => new external_value(PARAM_INT, 'is the section visible', VALUE_OPTIONAL),
                        'summary' => new external_value(PARAM_RAW, 'Section description'),
                        'summaryformat' => new external_format_value('summary'),
                        'section' => new external_value(PARAM_INT, 'Section number inside the course', VALUE_OPTIONAL),
                        'hiddenbynumsections' => new external_value(PARAM_INT, 'Whether is a section hidden in the course format',
                                                                    VALUE_OPTIONAL),
                        'uservisible' => new external_value(PARAM_BOOL, 'Is the section visible for the user?', VALUE_OPTIONAL),
                        'availabilityinfo' => new external_value(PARAM_RAW, 'Availability information.', VALUE_OPTIONAL),
                        'modules' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'activity id'),
                                        'url' => new external_value(PARAM_URL, 'activity url', VALUE_OPTIONAL),
                                        'name' => new external_value(PARAM_RAW, 'activity module name'),
                                        'instance' => new external_value(PARAM_INT, 'instance id', VALUE_OPTIONAL),
                                        'contextid' => new external_value(PARAM_INT, 'Activity context id.', VALUE_OPTIONAL),
                                        'description' => new external_value(PARAM_RAW, 'activity description', VALUE_OPTIONAL),
                                        'visible' => new external_value(PARAM_INT, 'is the module visible', VALUE_OPTIONAL),
                                        'uservisible' => new external_value(PARAM_BOOL, 'Is the module visible for the user?',
                                            VALUE_OPTIONAL),
                                        'availabilityinfo' => new external_value(PARAM_RAW, 'Availability information.',
                                            VALUE_OPTIONAL),
                                        'visibleoncoursepage' => new external_value(PARAM_INT, 'is the module visible on course page',
                                            VALUE_OPTIONAL),
                                        'modicon' => new external_value(PARAM_URL, 'activity icon url'),
                                        'modname' => new external_value(PARAM_PLUGIN, 'activity module type'),
                                        'modplural' => new external_value(PARAM_TEXT, 'activity module plural name'),
                                        'availability' => new external_value(PARAM_RAW, 'module availability settings', VALUE_OPTIONAL),
                                        'indent' => new external_value(PARAM_INT, 'number of identation in the site'),
                                        'onclick' => new external_value(PARAM_RAW, 'Onclick action.', VALUE_OPTIONAL),
                                        'afterlink' => new external_value(PARAM_RAW, 'After link info to be displayed.',
                                            VALUE_OPTIONAL),
                                        'customdata' => new external_value(PARAM_RAW, 'Custom data (JSON encoded).', VALUE_OPTIONAL),
                                        'noviewlink' => new external_value(PARAM_BOOL, 'Whether the module has no view page',
                                            VALUE_OPTIONAL),
                                        'completion' => new external_value(PARAM_INT, 'Type of completion tracking:
                                            0 means none, 1 manual, 2 automatic.', VALUE_OPTIONAL),
                                        'completiondata' => new external_single_structure(
                                            array(
                                                'state' => new external_value(PARAM_INT, 'Completion state value:
                                                    0 means incomplete, 1 complete, 2 complete pass, 3 complete fail'),
                                                'timecompleted' => new external_value(PARAM_INT, 'Timestamp for completion status.'),
                                                'overrideby' => new external_value(PARAM_INT, 'The user id who has overriden the
                                                    status.'),
                                                'valueused' => new external_value(PARAM_BOOL, 'Whether the completion status affects
                                                    the availability of another activity.', VALUE_OPTIONAL),
                                            ), 'Module completion data.', VALUE_OPTIONAL
                                        ),
                                        'contents' => new external_multiple_structure(
                                              new external_single_structure(
                                                  array(
                                                      // content info
                                                      'type'=> new external_value(PARAM_TEXT, 'a file or a folder or external link'),
                                                      'filename'=> new external_value(PARAM_FILE, 'filename'),
                                                      'filepath'=> new external_value(PARAM_PATH, 'filepath'),
                                                      'filesize'=> new external_value(PARAM_INT, 'filesize'),
                                                      'fileurl' => new external_value(PARAM_URL, 'downloadable file url', VALUE_OPTIONAL),
                                                      'content' => new external_value(PARAM_RAW, 'Raw content, will be used when type is content', VALUE_OPTIONAL),
                                                      'timecreated' => new external_value(PARAM_INT, 'Time created'),
                                                      'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                                                      'sortorder' => new external_value(PARAM_INT, 'Content sort order'),
                                                      'mimetype' => new external_value(PARAM_RAW, 'File mime type.', VALUE_OPTIONAL),
                                                      'isexternalfile' => new external_value(PARAM_BOOL, 'Whether is an external file.',
                                                        VALUE_OPTIONAL),
                                                      'repositorytype' => new external_value(PARAM_PLUGIN, 'The repository type for external files.',
                                                        VALUE_OPTIONAL),
    
                                                      // copyright related info
                                                      'userid' => new external_value(PARAM_INT, 'User who added this content to moodle'),
                                                      'author' => new external_value(PARAM_TEXT, 'Content owner'),
                                                      'license' => new external_value(PARAM_TEXT, 'Content license'),
                                                      'tags' => new external_multiple_structure(
                                                           \core_tag\external\tag_item_exporter::get_read_structure(), 'Tags',
                                                                VALUE_OPTIONAL
                                                       ),
                                                  )
                                              ), VALUE_DEFAULT, array()
                                          ),
                                        'contentsinfo' => new external_single_structure(
                                            array(
                                                'filescount' => new external_value(PARAM_INT, 'Total number of files.'),
                                                'filessize' => new external_value(PARAM_INT, 'Total files size.'),
                                                'lastmodified' => new external_value(PARAM_INT, 'Last time files were modified.'),
                                                'mimetypes' => new external_multiple_structure(
                                                    new external_value(PARAM_RAW, 'File mime type.'),
                                                    'Files mime types.'
                                                ),
                                                'repositorytype' => new external_value(PARAM_PLUGIN, 'The repository type for
                                                    the main file.', VALUE_OPTIONAL),
                                            ), 'Contents summary information.', VALUE_OPTIONAL
                                        ),
                                    )
                                ), 'list of module'
                        )
                    )
                )
            ),
            'isEnrollable' => new external_value(PARAM_BOOL, 'Course enroll able')
        ]);
    }
}