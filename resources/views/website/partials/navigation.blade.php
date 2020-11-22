<?php

$channel = (new ClanAOD\Twitch('clanaodstream'))->getChannel();
$status = is_null($channel->stream) ? "offline" : 'online';

?>


