<?php

if (empty($_SERVER['user_id'])) {

} else {


	Flight::route('/', array('UserController', '_index'));




}






