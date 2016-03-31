# Innografie Tool

## Wordpress Installation

1. Download and install the latest version of WordPress from https://de.wordpress.org/
2. Follow the offical documentation to setup a WordPress Network http://codex.wordpress.org/Create_A_Network

## LimeSurvey Installation

1. Download and install the latest version of LimeSurvey from https://www.limesurvey.org/de/
2. Enable the LimeSurvey RemoteControl API under global settings, using JSON-RPC

## Extending the LimeSurvey API

Unfortunately the LimeSurvey Plugin architecture does not allow for extending the RPC-API. You'll need to replace the corresponding file manually.

The remotecontrol_handle.php file in limesurvey/application/helpers/remotecontrol/ will add four more methods to the API:

- add_team_answers
- get_team_question_id
- export_survey
- export_custom_responses_by_title

You must ensure that they are available, otherwise the communication between WordPress and LimeSurvey will not work. Please make sure that after upgrading your LimeSurvey installation all changes

## Mandatory WordPress changes

In order to correctly setup your wordpress installation, you will need to install a few plugins and use the provided Innografie theme.

Login to your admin account and install/activate the following plugins:

- https://wordpress.org/plugins/timber-library/
- https://de.wordpress.org/plugins/cimy-user-extra-fields/

Adjust the configuration to:

- Allow self registration and creation of new websites

Go to your Main Site Dashboard and update your widget configuration. Make sure to enable the following widgets:

- Login Widget
- Umfrage Widget

Disable all other Widgets.

Setup two more custom user fields unter Settings -> Cimy User Extra Fields:

Company:

    - Order: 1
    - Name: COMPANY
    - Label: Unternehmen
    - Max Length: 200
    - Show the field in the registration: true
    - Show the field in User's profile: true
    - Show the field in Users Extended section: true

Phone:

    - Order: 2
    - Name: PHONE
    - Label: Telefon: (optional)
    - Max Length: 200
    - Can be empty: true
    - Show the field in the registration: true
    - Show the field in User's profile: true
    - Show the field in Users Extended section: true


## Installation and Configuration of the Innografie WordPress plugin

Copy the Innografie plugin from wordpress/plugins/innografie to your WordPress plugin directory located under wp-content/ and activate it.

Open the Innografie plugin folder and copy the config.php-dist file naming it config.php. Edit and adjust the settings to your needs:

- %LIMESURVEY_ADMIN_PASSWORD%: Your LimeSurvey Admin password
- %LIMESURVEY_URL%: The URL your LimeSurvey Installation hosted at

## Installation of the Innografie Theme

Copy the provided Theme from wordpress/themes/innografie to you WordPress theme directory located under wp-content/ and activate it.

## Using the provided content

You can import the wordpress/content/wordpress.content.xml file using the wordpress import tool to get started with a collection of different sites. It is recommended to remove all current sites before the import.

Go to Settings -> Read. Set your homepage to a static site and your article site to dummy. Create a new menu, adjust the structure to your needs and set it as the primary menu.

## Using the provided survey

There is an survey template in surveys/ that you can use. Go to the LimeSurvey Admin Backend and import the survey. Adjust the survey to your needs. All groups must contain at least one question.

In your Wordpress Admin Dashboard of your main site navigate to Fragebögen -> Fragebögen, where you can now register this survey for use in all sites.

The provided R-Script must be a single file or a zip archive containing a file named "main.R", which serves as you main script file. This file will be executed whenever an evaluation needs to be created. You can use the files under scripts/ for a reference to create your own evaluation script.

Please make sure, that your Webserver can run R Scripts on the Command Line.

## Additional Resources

You can find a logic file template for the included survey in the docs/ directory.