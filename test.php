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
 * Prints a particular instance of testmodule
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_testmodule
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace testmodule with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/forms/simpleform.php');


// Configure HTTP basic authorization: basicAuth
$config = Swagger\Client\Configuration::getDefaultConfiguration()
    ->setUsername('kermit')
    ->setPassword('kermit');

$engineApiInstance = new Swagger\Client\Api\EngineApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $engineApiInstance->getProperties();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EngineApi->getProperties: ', $e->getMessage(), PHP_EOL;
}

$processDefinitionsApiInstance = new Swagger\Client\Api\ProcessDefinitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

$processInstancesApiInstance = new Swagger\Client\Api\ProcessInstancesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

$tasksApiInstance = new Swagger\Client\Api\TasksApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

$formsApiInstance = new Swagger\Client\Api\FormsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

// define variables we need later to check regulary

$process_instance_suspended = false;
$process_instance_ended = false;
$process_instance_completed = false;
$task_id = null;
$task_name = null;
$taskDefinitionKey = null;



$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... testmodule instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('testmodule', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $testmodule  = $DB->get_record('testmodule', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $testmodule  = $DB->get_record('testmodule', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $testmodule->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('testmodule', $testmodule->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_testmodule\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $testmodule);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/testmodule/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($testmodule->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('testmodule-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($testmodule->intro) {
    echo $OUTPUT->box(format_module_intro('testmodule', $testmodule, $cm->id), 'generalbox mod_introbox', 'testmoduleintro');
}

// Replace the following lines with you own code.
echo $OUTPUT->heading('Yay! It works!');

function run_process() {
    $version = null; // int | Only return process definitions with the given version.
    $name = null; // string | Only return process definitions with the given name.
    $name_like = null; // string | Only return process definitions with a name like the given name.
    $key = "testttest"; // string | Only return process definitions with the given key.
    $key_like = null; // string | Only return process definitions with a name like the given key.
    $resource_name = null; // string | Only return process definitions with the given resource name.
    $resource_name_like = null; // string | Only return process definitions with a name like the given resource name.
    $category = null; // string | Only return process definitions with the given category.
    $category_like = null; // string | Only return process definitions with a category like the given name.
    $category_not_equals = null; // string | Only return process definitions which don�t have the given category.
    $deployment_id = null; // string | Only return process definitions with the given category.
    $startable_by_user = null; // string | Only return process definitions which are part of a deployment with the given id.
    $latest = "true"; // bool | Only return the latest process definition versions. Can only be used together with key and keyLike parameters, using any other parameter will result in a 400-response.
    $suspended = null; // bool | If true, only returns process definitions which are suspended. If false, only active process definitions (which are not suspended) are returned.
    $sort = "version"; // string | Property to sort on, to be used together with the order.
    
    try {
        // get processDefinition
        $result = $processDefinitionsApiInstance->getProcessDefinitions($version, $name, $name_like, $key, $key_like, $resource_name, $resource_name_like, $category, $category_like, $category_not_equals, $deployment_id, $startable_by_user, $latest, $suspended, $sort);
        // print_r($result);
        //print_r($result['data'][0]);
        // set variables for further use
        //print_r($result['data'][0]->id);
        $process_definition_id = $result['data'][0]->id;
        $processDeploymentId = $result['data'][0]->deploymentId;
    
        // attempt to start execution
        $requestArray = array(
            process_definition_id => $process_definition_id,
            business_key => "testkey"
        );
        $body = new \Swagger\Client\Model\ProcessInstanceCreateRequest($requestArray); // \Swagger\Client\Model\ProcessInstanceCreateRequest | 
    
        // attempt to create instance for process
        try {
            $result = $processInstancesApiInstance->createProcessInstance($body);
            // print_r($result);
            // get instance ID
    
            $process_instance_id = $result->getId();
            print_r($process_instance_id);
    
            // while (($process_instance_suspended == false) && ($process_instance_ended == false)) {
            //     try {
            //         $result = $processInstancesApiInstance->getProcessInstance($process_instance_id);
            //         print("PRINT INFO ABOUT PROCESS INSTANCE");
            //         print_r($result);
    
            //         sleep(1);
        
            //     } catch (Exception $e) {
            //         echo 'Exception when calling ProcessInstancesApi->getProcessInstance: ', $e->getMessage(), PHP_EOL;
            //     }
            // }
            
            // wait for task with form
            while (1) {
                try {
                    $result = $tasksApiInstance->getTasks(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, $process_instance_id, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
                    // print("PRINT TASKS CONNECTED TO PROCESS INSTANCE");
                    $task_id = $result['data'][0]->id;
                    $task_name = $result['data'][0]->name;
                    $taskDefinitionKey = $result['data'][0]->taskDefinitionKey;
                    print_r($result['data'][0]);
                    break;
                } catch (Exception $e) {
                    // echo 'Exception when calling TasksApi->getTasks: ', $e->getMessage(), PHP_EOL;
                    echo "Nope, not yet.";
                }
                sleep(1);
            }
    
            // get form 
    
            try {
                $result = $formsApiInstance->getFormData($task_id);
                print_r($result);
            } catch (Exception $e) {
                echo 'Exception when calling FormsApi->getFormData: ', $e->getMessage(), PHP_EOL;
            }
    
            // send data to form

            $mform = new simplehtml_form();
 
            //Form processing and displaying is done here
            if ($mform->is_cancelled()) {
                //Handle form cancel operation, if cancel button is present on form
            } else if ($fromform = $mform->get_data()) {
                //In this case you process validated data. $mform->get_data() returns data posted in form.
                $value1 = $fromform->email;
                $value2 = $fromform->name;
                
                $formArray = array(
                    action => "submit",
                    task_id => $task_id,
                    properties => array(
                        array(
                            id => new_property_1,
                            value => $value1
                        ),
                        array(
                            id => new_property_2,
                            value => $value2
                        )
                    )
                );
        
                $body = new \Swagger\Client\Model\SubmitFormRequest($formArray); // \Swagger\Client\Model\SubmitFormRequest | 
        
                try {
                    $result = $formsApiInstance->submitForm($body);
                    print_r($result);
                } catch (Exception $e) {
                    echo 'Exception when calling FormsApi->submitForm: ', $e->getMessage(), PHP_EOL;
                }
        
                try {
                    $result = $processInstancesApiInstance->getProcessInstance($process_instance_id);
                    print("PRINT INFO ABOUT PROCESS INSTANCE");
                    print_r($result);
        
                    sleep(1);
        
                } catch (Exception $e) {
                    echo 'Exception when calling ProcessInstancesApi->getProcessInstance: ', $e->getMessage(), PHP_EOL;
                }

            } else {
                // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
                // or on the first display of the form.
                
                //Set default data (if any)
                $mform->set_data($toform);
                //displays the form
                $mform->display();
            }
    
    
    
        } catch (Exception $e) {
            echo 'Exception when calling ProcessInstancesApi->createProcessInstance: ', $e->getMessage(), PHP_EOL;
        }
    
    } catch (Exception $e) {
        echo 'Exception when calling ProcessDefinitionsApi->getProcessDefinitions: ', $e->getMessage(), PHP_EOL;
    }
}

run_process();

// Finish the page.
echo $OUTPUT->footer();