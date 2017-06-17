[tr]
{% for squad in platoon.squads %}
	{# outputs "Squad Name - Squad #1 #}
	@{{ squad.name }} - Squad #@{{ loop.index }}

	{% if loop.index is divisibleBy(2) %}
		{# end the current row and start a new one #}
		[/tr][tr]
	{% endif %}

{% endfor %}
[/tr]