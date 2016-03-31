<h1>Übersicht Umfagen </h1>

<?php
    // wordpress sites
    $wpSites = wp_get_sites();
    $projectSites = array();
    foreach ($wpSites as $wpSite) {
        // skip blog with id 1
        if ($wpSite['blog_id'] == 1) {
            continue;
        }

        // get the company name
        $companyName = get_blog_option($wpSite['blog_id'], "cimy_uef_COMPANY", "");

        $projectSites[] = array(
            'blogId'        => $wpSite['blog_id'],
            'companyName'   => $companyName);
    }

    $selected = 0;
    $openSurveys = array();
    $closedSurveys = array();
    $deletedSurveys = array();

    if (isset($_POST['blogId'])) {
        $selected = $_POST['blogId'];

        // get project surveys
        $surveys = fbpsych_get_surveys_full($selected);

        $openSurveys = array_filter($surveys, function($survey) {
            return (!isset($survey['deleted']) || $survey['deleted'] != true);
        });

        $closedSurveys = array_filter($surveys, function($survey) {
            if (isset($survey['limesurvey']['expires'])) {
                $expires = $survey['limesurvey']['expires'];

                $expiresDate = new \DateTime($expires);
                $nowDate = new \Datetime('NOW');

                return ($expiresDate < $nowDate);
            }
        });

        $deletedSurveys = array_filter($surveys, function($survey) {
            return (isset($survey['deleted']) && $survey['deleted']== true);
        });
    }

    //$lsSurveys = fbpsych_get_surveys();



   // Übersicht über gelöschte, offene und geschlossene Befragungen pro Unternehmen mit ID der Umfrage

?>
<form action="admin.php?page=admin_menu_overview" method="post" enctype="multipart/form-data">
    <div>Bitte wählen Sie eine Projektseite aus.</div>
    <select name="blogId" onchange="this.form.submit();">
        <option value="0"<?php if ($selected == 0) echo ' selected="selected"'; ?>>Bitte auswählen...</option>
        <option value="-1" disabled="disabled">-------------</option>

        <?php foreach ($projectSites as $projectSite) : ?>
            <option value="<?php echo $projectSite['blogId']; ?>"<?php if ($selected == $projectSite['blogId']) echo ' selected="selected"'?>><?php echo $projectSite['companyName']; ?></option>
        <?php endforeach; ?>
    </select>

    <h3>Offene Umfragen</h3>
    <table>
        <tr>
            <th>Id</th>
            <th>Id des originalen Fragebogens</th>
        </tr>

        <?php $index = 0; ?>
        <?php foreach ($openSurveys as $openSurvey) : ?>
            <tr style="background-color: <?php printf("%s", ($index % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
                <td style="text-align: left; padding: 5px;"><?php echo $openSurvey['surveyId']; ?></td>
                <td style="text-align: left; padding: 5px;"><?php echo $openSurvey['templateId']; ?></td>
            </tr>
            <?php $index++; ?>
        <?php endforeach; ?>
    </table>

    <h3>Geschlossene Umfragen</h3>
    <table>
        <tr>
            <th>Id</th>
            <th>Id des originalen Fragebogens</th>
        </tr>

        <?php $index = 0; ?>
        <?php foreach ($closedSurveys as $closedSurvey) : ?>
            <tr style="background-color: <?php printf("%s", ($index % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
                <td style="text-align: left; padding: 5px;"><?php echo $closedSurvey['surveyId']; ?></td>
                <td style="text-align: left; padding: 5px;"><?php echo $closedSurvey['templateId']; ?></td>
            </tr>
            <?php $index++; ?>
        <?php endforeach; ?>
    </table>

    <h3>Gelöschte Umfragen</h3>
    <table>
        <tr>
            <th>Id</th>
            <th>Id des originalen Fragebogens</th>
        </tr>

        <?php $index = 0; ?>
        <?php foreach ($deletedSurveys as $deletedSurvey) : ?>
            <tr style="background-color: <?php printf("%s", ($index % 2 === 0) ? "#E6E6E6": "#F2F2F2"); ?>;">
                <td style="text-align: left; padding: 5px;"><?php echo $deletedSurvey['surveyId']; ?></td>
                <td style="text-align: left; padding: 5px;"><?php echo $deletedSurvey['templateId']; ?></td>
            </tr>
            <?php $index++; ?>
        <?php endforeach; ?>
    </table>
</form>