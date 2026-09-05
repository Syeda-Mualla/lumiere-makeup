<?php
/**
 * Logout Handler
 * LUMIÈRE - Luxury Makeup Brand
 */

require_once 'includes/auth.php';

logoutUser();

header('Location: index.php');
exit;
