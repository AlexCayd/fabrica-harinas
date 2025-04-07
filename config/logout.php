<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: /fabrica-harinas/index.php");
    exit;
