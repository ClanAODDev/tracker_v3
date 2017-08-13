{# member.handle.fullUrl = https://playoverwatch.com/en-us/career/pc/us/MyName#9999 #}
{%- if member.handle -%}
	[url=@{{ member.handle.fullUrl | replace('#', '-') }}]
	@{{ member.handle.pivot.value }}
	[/url]
{%- endif -%}