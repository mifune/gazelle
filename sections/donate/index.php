<?

include(SERVER_ROOT . '/sections/donate/functions.php');


if (!isset($_REQUEST['action'])) {
    include(SERVER_ROOT . '/sections/donate/donate.php');
} else {
    switch ($_REQUEST['action']) {
        case 'my_donations':
            include(SERVER_ROOT . '/sections/donate/my_donations.php');
            break;

        case 'submit_donate':
            // user submits their donation
            include(SERVER_ROOT . '/sections/donate/take_donation.php');
            break;

        case 'submit_donate_manual':
            // user submits their donation
            include(SERVER_ROOT . '/sections/donate/take_manual_donation.php');
            break;
 
        default:
            include(SERVER_ROOT . '/sections/donate/donate.php');
            break;
    }
}
?>
