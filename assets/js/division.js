 $(".toplist tbody tr").click(function() {
 	window.location.href = "member/" + $(this).attr('data-id');
 });


 $(function() {

 	$( ".unassigned" ).draggable({
 		revert: true,
 	});
 	$( ".platoon" ).droppable({
 		hoverClass: "platoon-state-hover",
 		drop: function( event, ui ) {
 			var platoon = $(this);
 			var playerName = $(ui.draggable).text();
 			var platoonName = platoon.find(".list-group-item-heading").text();
 			var draggableId = ui.draggable.attr("data-member-id");
 			var droppableId = platoon.attr("data-platoon-id");

 			if (confirm("Assign "+playerName+" to "+platoonName+"?")) {

 				$.ajax({
 					type: 'POST',
 					url: 'do/assign-to-platoon',
 					data: {
 						platoon_id: droppableId,
 						member_id: draggableId
 					},
 					dataType: 'json',
 					success: function(response) {

 						if (typeof response.success === "undefined") {
 							$(".alert-box").stop().html("<div class='alert alert-danger'><i class='fa fa-times'></i> Oops...something went wrong.</div>").effect('highlight').delay(1000).fadeOut();
 						} else {
 							$(ui.draggable).remove();

 							$(".alert-box").stop().html("<div class='alert alert-success'><i class='fa fa-check'></i> " + playerName + " has been assigned to " + platoonName + "</div>").show().delay(1000).fadeOut();
 							var memberCount = $(".unassigned-members .unassigned").length;
 							$(".unassigned-members-container .panel-heading .badge").text(memberCount);
 							if ($(".unassigned-members h4").length < 1) {
 								$(".unassigned-members-container").fadeOut();
 							}
 						}
 					},
 				});
}
}
});
});
