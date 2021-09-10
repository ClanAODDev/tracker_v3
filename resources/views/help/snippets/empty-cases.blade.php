{# show nothing when part-time members is empty #}
{% if partTimeMembers %}
	[b]@{{ member.name }}[/b]
{% endif %}

{# Show something when part-time members is empty by using the for else condition #}
{% for member in partTimeMembers %}
	[b]@{{ member.name }}[/b]
{% else %}
	[color=red]No part-time members assigned[/color]
{% endfor %}

{# Check for an empty object (like squad.leader or platoon.leader) #}
{% if squad.leader.exists() %}
	{# present() is a presenter class, not an attribute #}
	@{{ squad.leader.present().rankName }}
{% else %}
	TBA
{% endif %}