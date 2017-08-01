{% for squad in platoon.squads %}

Current iteration: @{{ loop.index }}
Is it the first iteration? @{{ loop.first ? 'yes' : 'no' }}
Is it the last iteration? @{{ loop.last ? 'yes' : 'no' }}
Total number of items: @{{ loop.length }}

{% endfor %}