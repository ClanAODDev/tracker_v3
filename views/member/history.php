<style>
	.thumbnail {
		padding:0px;
	}
	.panel {
		position:relative;
	}
	.panel>.panel-heading:after,.panel>.panel-heading:before{
		position:absolute;
		top:11px;left:-16px;
		right:100%;
		width:0;
		height:0;
		display:block;
		content:" ";
		border-color:transparent;
		border-style:solid solid outset;
		pointer-events:none;
	}
	.panel>.panel-heading:after{
		border-width:7px;
		border-right-color:#f7f7f7;
		margin-top:1px;
		margin-left:2px;
	}
	.panel>.panel-heading:before{
		border-right-color:#ddd;
		border-width:8px;
	}
</style>
<div class="col-xs-1">
	<div class="thumbnail">
		<img class="img-responsive user-photo" src="http://www.clanaod.net/forums/image.php?type=thumb&u=31832">
	</div>
</div>
<div class="col-xs-11">
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>myusername</strong> <span class="text-muted">commented 5 days ago</span>
		</div>
		<div class="panel-body">
			Panel content
		</div>
	</div>
</div>