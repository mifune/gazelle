<?php


header('Content-Type: application/json; charset=utf-8');

$term = trim($_GET['name']);

/*
SELECT t.ID, t.Name, t.Uses, Count(s.ID) As Synonyms FROM tags as t LEFT JOIN tag_synomyns AS s ON t.ID=s.TagID WHERE t.Name LIKE 'breast%' GROUP BY t.ID
UNION
SELECT t.ID, t.Name, t.Uses, Count(s.ID) As Synonyms FROM tags as t LEFT JOIN tag_synomyns AS s ON t.ID=s.TagID WHERE t.Name LIKE '%breast%' GROUP BY t.ID
ORDER BY Name, Uses DESC LIMIT 100;

//$Tags = $DB->to_array(false, MYSQLI_NUM); 
*/

$Data = $Cache->get_value('tag_search_'.$term);
if($Data===false) { 
    $esc_term = db_string($term); 
    $DB->query("(SELECT Name, Uses, '0' AS Sort FROM tags WHERE Name LIKE '$esc_term%')
                UNION
                (SELECT Name, Uses, '1' AS Sort FROM tags WHERE Name NOT LIKE '$esc_term%' AND Name LIKE '%$esc_term%')
                ORDER BY Sort, Uses DESC
                LIMIT 40;");     
    /*
    $DB->query("(SELECT Name, Uses FROM tags WHERE Name LIKE '$esc_term%')
                UNION
                (SELECT Name, Uses FROM tags WHERE Name LIKE '%$esc_term%')
                ORDER BY Uses DESC
                LIMIT 80;"); */
    $Data = array();
    while(list($tag, $num) = $DB->next_record(MYSQLI_NUM)) {
        $Data[] = array($tag, "$tag &nbsp;<span class=\"num\">($num)</span>");   //  "<a onclick=\"clicked('$tag');return false;\">$tag ($num)</a>");
    }
    $Cache->cache_value('tag_search_'.$term, $Data, 3600*24); 
}

echo json_encode(array($term, $Data));


?>
