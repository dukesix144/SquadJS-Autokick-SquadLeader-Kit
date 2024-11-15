<?php
// Local file path where MOTD.cfg will be saved
$local_file = '/home/rrgaming/public_html/squad_motd/MOTD.cfg';

// MySQL database configuration for Squad stats
$servername_webstats = "";
$username_webstats = "";
$password_webstats = "";
$dbname_webstats = "";

// MySQL database configuration for Banlist
$servername_banlist = "";
$username_banlist = "";
$password_banlist = "";
$dbname_banlist = "";

// Create connection for webstats
$conn_webstats = new mysqli($servername_webstats, $username_webstats, $password_webstats, $dbname_webstats);

// Check the connection for webstats
if ($conn_webstats->connect_error) {
    die("Webstats connection failed: " . $conn_webstats->connect_error);
}

// Create connection for banlist
$conn_banlist = new mysqli($servername_banlist, $username_banlist, $password_banlist, $dbname_banlist);

// Check the connection for banlist
if ($conn_banlist->connect_error) {
    die("Banlist connection failed: " . $conn_banlist->connect_error);
}

// Get the list of banned steam IDs
$banned_steamids = array();
$banlist_sql = "SELECT steamid FROM banlist";
$result_banlist = $conn_banlist->query($banlist_sql);

if ($result_banlist->num_rows > 0) {
    while ($row_banlist = $result_banlist->fetch_assoc()) {
        $banned_steamids[] = $row_banlist['steamid'];
    }
}

// Handle the case where there are banned players
if (!empty($banned_steamids)) {
    // Prepare a comma-separated list of banned steam IDs for SQL exclusion
    $banned_steamids_str = "'" . implode("','", $banned_steamids) . "'";
    $ban_condition = "WHERE steamid NOT IN ($banned_steamids_str)";
} else {
    // No banned players, so no need for a WHERE clause
    $ban_condition = "";
}

// SQL query to select top 10 players by ELO, excluding banned players
$sql = "
    SELECT 
        elo, 
        Name
    FROM squad_stats
    $ban_condition
    ORDER BY elo DESC 
    LIMIT 10
";
$result = $conn_webstats->query($sql);

// Initialize the MOTD content with the static text
$motd = <<<EOD
Welcome to ↳ЯR↰ Gaming! \n
Join our <a href="https://discord.gg/rrgaming">Discord (rrgaming)</a> \n
Need an Admin? Type !admin and message in chat to alert an admin. \n
For a Complete set of Rules join our Discord or visit the <a href="https://www.rrgaming.net">website: rrgaming.net.</a> \n
\n
Top 10 ↳ЯR↰ rrgaming.net Leaderboard:\n
\n
    ELO       PLAYER\n
EOD;

// Add the leaderboard data to the MOTD
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $player_name = $row['Name'];
        $elo = number_format($row['elo'], 2);
        $motd .= "| $elo | $player_name |\n";  // Format: | ELO | Playername |
    }
} else {
    $motd .= "No data available.\n";
}




// Close the database connections
$conn_webstats->close();
$conn_banlist->close();

// Convert the content to UTF-8 explicitly
$motd_utf8 = mb_convert_encoding($motd, 'UTF-8', 'auto');

// Write the MOTD content to a temporary file with UTF-8 encoding
file_put_contents($local_file, $motd_utf8);

echo "MOTD file successfully written to $local_file\n";
?>

