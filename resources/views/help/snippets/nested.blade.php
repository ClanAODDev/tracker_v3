{% for platoon in division.platoons %}
@{{ platoon.name }}
{% for squad in platoon.squads %}
@{{ squad.name }}
{% endfor %}
{% endfor %}