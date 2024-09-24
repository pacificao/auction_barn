<?php
/***************************************************************************
 *   copyright				: (C) 2008 - 2017 WeBid
 *   copyright              : (C) 2023 - 2024 Barnealogy (Full Refactor)
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 ***************************************************************************/

define('InAdmin', 1);
$current_page = 'interface';
include '../common.php';
include INCLUDE_PATH . 'functions_admin.php';
include 'loggedin.inc.php';

// Define the upload path for logos
define('UPLOAD_PATH', '/var/www/html/auction_barn/uploaded/');

// Ensure the directory exists for uploading the logo
if (!file_exists(UPLOAD_PATH . 'logo/')) {
    mkdir(UPLOAD_PATH . 'logo/', 0755, true);
}

// Handle form submission for updating the logo
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    if (isset($_FILES['logo']['tmp_name']) && !empty($_FILES['logo']['tmp_name'])) {

        // Allowed file types
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $inf = getimagesize($_FILES['logo']['tmp_name']); // Validates if the uploaded file is an image

        // File type and size validation
        if (!in_array(strtolower($ext), $allowed)) {
            $template->assign_block_vars('alerts', array('TYPE' => 'error', 'MESSAGE' => 'Invalid file type.'));
        } elseif ($_FILES['logo']['size'] > 5000000) { // Limit file size to ~5000KB
            $template->assign_block_vars('alerts', array('TYPE' => 'error', 'MESSAGE' => 'File size is too large.'));
        } elseif ($inf[2] < 1 || $inf[2] > 3) { // Ensures the file is a valid image
            $template->assign_block_vars('alerts', array('TYPE' => 'error', 'MESSAGE' => $ERR_602));
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['logo']['tmp_name'], UPLOAD_PATH . 'logo/' . $_FILES['logo']['name'])) {
                // Write setting for new logo
                $system->writesetting("logo", $_FILES['logo']['name'], "str");
                $template->assign_block_vars('alerts', array('TYPE' => 'success', 'MESSAGE' => 'Logo uploaded successfully.'));
            } else {
                // Display error message if file upload fails
                $template->assign_block_vars('alerts', array('TYPE' => 'error', 'MESSAGE' => 'File upload failed. Please try again.'));
            }
        }
    }
}

// Get the logo URL for display purposes
$logoURL = $system->SETTINGS['siteurl'] . 'uploaded/logo/' . $system->SETTINGS['logo'];

// Display the current logo and upload option
loadblock($MSG['your_logo'], $MSG['current_logo'], 'image', 'logo', $system->SETTINGS['logo']);
loadblock('', $MSG['upload_new_logo'], 'upload', 'logo', $system->SETTINGS['logo']);

// Assign template variables for displaying the logo
$template->assign_vars(array(
    'SITEURL' => $system->SETTINGS['siteurl'],
    'IMAGEURL' => $logoURL,
));

// Include header, display the body, and footer
include 'header.php';
$template->set_filenames(array(
    'body' => 'logo_upload.tpl'
));
$template->display('body');
include 'footer.php';
?>
