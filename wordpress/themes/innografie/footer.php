<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>
	
	</div>
	</div>
	
	<div id="footer">
        <div class="wrapper">
            <div id="credits">
            &copy; <?php echo date("Y");?> Online-Tool | <a href="impressum/">Impressum</a>
            </div>
        </div>
    </div>

<?php wp_footer(); ?>

	<script data-dojo-config="async: 1, dojoBlankHtmlUrl: '/blank.html',
        packages: [ {
            name: 'custom',
            location: '<?php echo get_template_directory_uri() ?>' + '/js/custom'
        } ]"
    src="//ajax.googleapis.com/ajax/libs/dojo/1.8.3/dojo/dojo.js"></script>
    
    <script>
    	require([ "custom/fbpsych", "dojo/domReady!" ], function(fbpsych, thinger)
    	{
        	var fbpsychModule = new fbpsych();
        	fbpsychModule.setup();
        });
    </script>

    <div data-dojo-type="dijit/Dialog" data-dojo-id="deleteDialog" title="Löschen bestätigen" style="width: 350px">
        <h1>Bestätigung</h1>

        <p>
            Bitte bestätigen Sie den Löschvorgang.
        </p>
        <br>

        <div>
            <button data-dojo-type="dijit/form/Button" data-dojo-id="deleteConfirm" type="button">Bestätigen</button>
            <button data-dojo-type="dijit/form/Button" data-dojo-id="deleteCancel" class="right" type="button">Abbrechen</button>
        </div>
    </div>
</div>
</body>
</html>