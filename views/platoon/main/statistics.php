<div class='panel panel-default'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
	</div>
</div>

<div class='panel panel-default'>
	<div class='panel-heading'>Percentage AOD Games</div>
	<div class='panel-body count-detail-big follow-tool striped-bg' title='Excludes all zero values'><span class='count-animated percentage'>{$overall_aod_percent}</span>
	</div>
</div>

<!-- show squad if squad leader in platoon being viewed -->
<div class='panel panel-default'>
	<div class='panel-heading'><strong> Your Squad</strong> {$squadCount}<span class='pull-right text-muted'>Last seen</span></div>

	<div class='list-group' id='squad'>
		{$my_squad}
	</div>
</div>