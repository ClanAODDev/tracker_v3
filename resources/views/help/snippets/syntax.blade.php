{# outputs the division name #}
@{{ division.name }}

{# does this platoon have a logo? #}
{% if platoon.logo %}
[img] @{{ platoon.logo }} [/img]
{% else %}
No platoon logo
{% endif %}
