[list]
{% for platoon in division.platoons %}
	[*] @{{ platoon.name }}
	[list]
	{% for squad in platoon.squads %}
		[*] @{{ squad.name }}
		[list]
		{% for member in squad.members %}
			[*] @{{ member.name }}
		{% endfor %}
		[/list]
	{% endfor %}
	[/list]
{% endfor %}
[/list]