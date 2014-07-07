<?php
enforce_login();

function get_votes_array($RequestID)
{
    global $Cache, $DB;

    $RequestVotes = $Cache->get_value('request_votes_'.$RequestID);
    if (!is_array($RequestVotes)) {
        $DB->query("SELECT rv.UserID,
                            rv.Bounty,
                            u.Username
                        FROM requests_votes as rv
                            LEFT JOIN users_main AS u ON u.ID=rv.UserID
                        WHERE rv.RequestID = ".$RequestID."
                        ORDER BY rv.Bounty DESC");
        if ($DB->record_count() < 1) {
            error(0);
        } else {
            $Votes = $DB->to_array();

            $RequestVotes = array();
            $RequestVotes['TotalBounty'] = array_sum($DB->collect('Bounty'));

            foreach ($Votes as $Vote) {
                list($UserID, $Bounty, $Username) = $Vote;
                $VoteArray = array();
                $VotesArray[] = array('UserID' => $UserID,
                                        'Username' => $Username,
                                        'Bounty' => $Bounty);
            }

            $RequestVotes['Voters'] = $VotesArray;
            $Cache->cache_value('request_votes_'.$RequestID, $RequestVotes);
        }
    }

    return $RequestVotes;
}

function get_votes_html($RequestVotes)
{
    global $LoggedUser;

    ob_start();

    $VoteCount = count($RequestVotes['Voters']);

    $VoteMax = ($VoteCount < 10 ? $VoteCount : 10);
    $ViewerVote = false;
    for ($i = 0; $i < $VoteMax; $i++) {
        $User = array_shift($RequestVotes['Voters']);
        $Boldify = false;
        if ($User['UserID'] == $LoggedUser['ID']) {
            $ViewerVote = true;
            $Boldify = true;
        }
?>
                <tr>
                    <td>
                        <a href="user.php?id=<?=$User['UserID']?>"><?=$Boldify?'<strong>':''?><?=display_str($User['Username'])?><?=$Boldify?'</strong>':''?></a>
                    </td>
                    <td>
                        <?=$Boldify?'<strong>':''?><?=get_size($User['Bounty'])?><?=$Boldify?'</strong>':''?>
                    </td>
                </tr>
<?php 	}
    reset($RequestVotes['Voters']);
    if (!$ViewerVote) {
        foreach ($RequestVotes['Voters'] as $User) {
            if ($User['UserID'] == $LoggedUser['ID']) { ?>
                <tr>
                    <td>
                        <a href="user.php?id=<?=$User['UserID']?>"><strong><?=display_str($User['Username'])?></strong></a>
                    </td>
                    <td>
                        <strong><?=get_size($User['Bounty'])?></strong>
                    </td>
                </tr>
<?php 			}
        }
    }

    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}
