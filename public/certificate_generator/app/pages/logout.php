<?php
declare(strict_types=1);

session_unset();
session_destroy();

session_start();
flash('success', 'You have signed out successfully.');
redirect(url('login'));
