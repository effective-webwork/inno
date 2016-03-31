define([
    "dojo/_base/declare",
    "dojo/query",
    "dojo/fx",
    "dojo/dom-style",
    "dojo/on",
    "dijit/Dialog",
    "dijit/form/Button",
    "dijit/Tooltip",
    "dojo/parser",
    "dojo/NodeList-traverse"
], function(
	declare,
	Query,
	Fx,
	DomStyle,
	On,
	Dialog,
	Button,
	Tooltip,
	parser
)
{
	return declare(null,
	{
		setup: function() {
			parser.parse();

			this.setupWipe();
			this.setupDeleteConfirm();
		},
		
		setupWipe: function() {
			var nodeList = Query(".wipeSuccessor");
			
			if ( nodeList )
			{
				On(nodeList, "click", function(event)
				{
					var wipeNode = Query("div.wipeActor", event.target.parentNode.parentNode)[0];
					if ( wipeNode )
					{
						if ( DomStyle.get(wipeNode, "display") === "none" )
						{
							Fx.wipeIn({
								node: wipeNode
							}).play();
						}
						else
						{
							Fx.wipeOut({
								node: wipeNode
							}).play();
						}
					}
					
					event.preventDefault();
				});
			}
		},

		setupDeleteConfirm: function() {
			var handler = On(Query("button.delete_dialog"), "click", function(event) {
				var deleteButton = event.target;

				deleteDialog.show();

				deleteCancel.onClick = function(event) {
					deleteDialog.hide();
				};

				deleteConfirm.onClick = function(event) {
					deleteDialog.hide();

					handler.remove();
					deleteButton.click();
				};

				event.preventDefault();
			});
		}
	});
});