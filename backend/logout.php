<?php
session_start();
session_unset();
session_destroy();
header("Location: /GreenBin/frontend/login/login.html");
exit();
