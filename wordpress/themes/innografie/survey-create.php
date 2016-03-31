<?php
$context = Timber::get_context();

$context['formStep'] = 'init';
$context['formErrors'] = array();
$context['formData'] = array();

if (isset($_GET['step'])) {
    $context['formStep'] = $_GET['step'];
} elseif (isset($_POST['step'])) {
    $context['formStep'] = $_POST['step'];
}

$formNameKeys = array(
                    'surveyName',
                    'templateId',
                    'template',
                    'tokenCount',
                    'teams');

$predefinedValues = array();

if (isset($_GET['step'])) {
    foreach ($formNameKeys as $keyName) {
        $predefinedValues[$keyName] = $_GET[$keyName];
    }
} elseif (isset($_POST)) {
    foreach ($formNameKeys as $keyName) {
        if (isset($_POST[$keyName])) {
            $predefinedValues[$keyName] = $_POST[$keyName];
        }
    }
} elseif (isset($_POST['hidden'])) {
    foreach ($formNameKeys as $keyName) {
        $predefinedValues[$keyName] = $_POST['hidden'][$keyName];
    }
}

$hiddenData = array();
if (isset($_POST['hidden'])) {
    $hiddenData = $_POST['hidden'];
} elseif (isset($_GET['step'])) {
    $hiddenData = $_GET;
}

$hasTemplateSurveys = false;

$blogSurveys = get_blog_option(get_current_blog_id(), "fbpsych_project_surveys", array());
foreach ($blogSurveys as $blogSurvey) {
    // skip deleted
    if (isset($blogSurvey['deleted']) && $blogSurvey['deleted'] == true) {
        continue;
    }

    if ($predefinedValues['templateId'] == $blogSurvey['templateId'] || $hiddenData['templateId'] == $blogSurvey['templateId']) {
        $hasTemplateSurveys = true;
        break;
    }
}

$hasTeamQuestion = false;
// check if the original ls survey has the team question
if (isset($predefinedValues['templateId'])) {
    $hasTeamQuestion = fbpsych_ls_has_team_questions($predefinedValues['templateId']);
}

$context['showTemplate'] = $hasTemplateSurveys && $hasTeamQuestion;
$context['showTeams'] = $hasTeamQuestion;

//////////////////
/// Init Step
//////////////////

if ($context['formStep'] == 'init') {
    // data for displaying the current form step
    $context['blogSurveys'] = fbpsych_get_blog_surveys(get_current_blog_id());

    // do we proceed?
    if (isset($_POST["next"])) {
        // if the current blog already has active surveys, next step is template.
        // Otherwise, we proceed to teams.
        if ($hasTemplateSurveys) {
            $context['formStep'] = 'template';
        } else {
            $context['formStep'] = 'teams';
        }
        unset($_POST['next']);

        // store data into hidden values
        $hiddenData['surveyName'] = $_POST['surveyName'];
        $hiddenData['templateId'] = $_POST['templateId'];
    }
}

//////////////////
/// Template Step
//////////////////

if ($context['formStep'] == 'template') {
    if (isset($predefinedValues['template'])) {
        $context['formData']['templateSelected'] = $predefinedValues['template'];
    }

    // data for displaying the current form step
    $blogSurveys = get_blog_option(get_current_blog_id(), "fbpsych_project_surveys", array());
    $surveyTemplates = array();
    foreach ($blogSurveys as $blogSurvey) {
        if ($predefinedValues['templateId'] != $blogSurvey['templateId']) {
            continue;
        }

        if (empty($blogSurvey['surveyName'])) {
            $date = new \DateTime($blogSurvey['creationDate']);
            $blogSurvey['surveyName'] = 'Fragebogen vom ' . $date->format('d.m.Y');
        }

        $surveyTemplates[] = $blogSurvey;
    }

    $context['surveyTemplates'] = $surveyTemplates;

    // do we proceed?
    if (isset($_POST["next"])) {
        $context['formStep'] = 'teams';
        unset($_POST['next']);

        // load values from selected template
        $template = $_POST['template'];
        $blogSurveys = get_option("fbpsych_project_surveys", array());
        $templateSurvey = null;

        foreach ($blogSurveys as $blogSurvey) {
            if ($blogSurvey['surveyId'] == $template) {
                $templateSurvey = $blogSurvey;
                break;
            }
        }

        $predefinedValues['tokenCount'] = $templateSurvey['tokenCount'];
        if (is_array($templateSurvey['teams'])) {
            $teams = $templateSurvey['teams'];
            if (isset($teams['de'])) {
                $teams = $teams['de'];
            }
            $predefinedValues['teams']['title'] = $teams;
        } else {
            $predefinedValues['teams']['title'] = array();
        }

        // store data into hidden values
        $hiddenData['template'] = $_POST['template'];
    }

    if (isset($_POST["next-skip"])) {
        // ignore template
        unset($_POST['template']);

        $context['formStep'] = 'teams';
        unset($_POST['next-skip']);
    }
}

//////////////////
/// Teams Step
//////////////////
///
if ($context['formStep'] == 'teams') {
    if (isset($_POST["delTeam"])) {
        // handle delete row
        array_splice($predefinedValues["teams"]["title"], $_POST["delTeam"], 1);
    }

    // data for displaying the current form step
    $context['formData']['tokenCount'] = $predefinedValues['tokenCount'];
    $context['formData']['teams'] = $predefinedValues['teams'];

    $context['formEvent']['addTeam'] = isset($_POST['addTeam']);

    // do we proceed?
    if (isset($_POST["next"])) {
        // form checks
        $countTotal = trim($_POST["tokenCount"]);
        if ($countTotal === "") {
            $context['formErrors']["total"] = "empty";
        } else {
            if (!is_numeric($countTotal)) {
                $context['formErrors']["total"] = "numeric";
            } else {
                if ((int) $countTotal < 0) {
                    $context['formErrors']["total"] = "negativ";
                } elseif ((int) $countTotal < 6) {
                    $context['formErrors']["total"] = "limit";
                }
            }
        }

        $teams =& $_POST["team"];
        if (!empty($teams)) {
            foreach ($teams["title"] as $title) {
                $title = trim($title);
                if ($title === "") {
                    $context['formErrors']["teams"][] = "empty title";
                    break;
                }
            }
        }

        if (empty($context['formErrors'])) {
            $context['formStep'] = "confirm";
            unset($_POST["next"]);
        }

        $hiddenData['tokenCount'] = $_POST['tokenCount'];
        $hiddenData['teams'] = $_POST['teams'];
    }
}

//////////////////
/// Confirm Step
//////////////////

if ($context['formStep'] == 'confirm') {
    // data for displaying the current form step
    $context['formData']['surveyName'] = $hiddenData['surveyName'];
    $context['formData']['tokenCount'] = $hiddenData['tokenCount'];
    $context['formData']['teams'] = $hiddenData['teams'];

    // do we proceed?
    if (isset($_POST['finish'])) {
        $data = array(
            "surveyName"    => $hiddenData["surveyName"],
            "surveyId"      => $hiddenData["templateId"],
            "tokenCount"    => $hiddenData["tokenCount"],
            "teams"         => $hiddenData["teams"]["title"]
        );
        $createResponse = fbpsych_create_survey($data);
        header('Location: ' . get_blog_details(get_current_blog_id())->siteurl . '/' . $createResponse["surveyId"]);
    } else if(isset($_POST['abort'])) {
        header('Location: ' . get_blog_details(get_current_blog_id())->siteurl);
    }
}

// hidden data
$context['hiddenData'] = $hiddenData;

Timber::render('survey-create.twig.php', $context);