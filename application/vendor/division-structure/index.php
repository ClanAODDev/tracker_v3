<?php

include "../../lib.php";

$out = "
<p>This tool generates a copy of the bb-code skeleton for the division structure of the Battlefield division. It is up-to-date with all of the most recent changes on the forums at this time.";

	$out .= "
	<pre class='well code' id='div-gen'>" . generate_division_structure() . "</pre>";

	$out .= "
</div>";

?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title" id="myModalLabel"><strong>Generate</strong> Division Structure</h4>
</div>
<div class="modal-body">
	<?php echo $out; ?>
</div>
<div class="modal-footer">	
	<button type="button" onclick="selectText('div-gen')" class="btn btn-success">Select code</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

</div>