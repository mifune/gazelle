<?

// This is used to determine whether the '[Edit]' link should be shown
function can_edit_comment ($AuthorID, $EditedUserID, $AddedTime, $EditedTime) {
    global $LoggedUser;

    if (check_perms('site_moderate_forums')) {
        return true; // moderators can edit anything
    }

    if ($AuthorID != $LoggedUser['ID'] || ($EditedUserID && $EditedUserID != $LoggedUser['ID'])) {
        return false;
    }

    return ( time_ago($AddedTime)<USER_EDIT_POST_TIME || time_ago($EditedTime)<USER_EDIT_POST_TIME ) || check_perms ('site_edit_own_posts');
}

// This function is used to check if the user can submit changes to a comment.
// Prints error if not permitted.
function validate_edit_comment ($AuthorID, $EditedUserID, $AddedTime, $EditedTime) {
    global $LoggedUser;

    if (check_perms('site_moderate_forums')) {
        return; // moderators can edit anything
    }

    if ($AuthorID != $LoggedUser['ID']) {
        error(403, true);
    }

    if ($EditedUserID && $EditedUserID != $LoggedUser['ID']) {
        error("You are not allowed to edit a post that has been edited by moderators.", true);
    }

    if (!check_perms ('site_edit_own_posts') 
            && time_ago($AddedTime)>(USER_EDIT_POST_TIME+600)  && time_ago($EditedTime)>(USER_EDIT_POST_TIME+300) ) { // give them an extra 15 mins in the backend
        error("Sorry - you only have ". date('i\m s\s', USER_EDIT_POST_TIME). "  to edit your post before it is automatically locked." ,true);
    }
}

?>

