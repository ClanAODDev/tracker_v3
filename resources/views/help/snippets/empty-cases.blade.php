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