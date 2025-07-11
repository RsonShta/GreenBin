<?php
$password = 'Rson@0110';
echo password_hash($password, PASSWORD_BCRYPT);


//this is for super admin access which should be created at Database manually