<?php

function is_check_day(){
	if( date("N") < 6 )
		return true;

	return false;
}