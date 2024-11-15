<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = [];

// Fetch player stats
$sqlPlayers = "SELECT steamID, lastName FROM DBLog_Players";
$resultPlayers = $conn->query($sqlPlayers);

if ($resultPlayers->num_rows > 0) {
    while ($row = $resultPlayers->fetch_assoc()) {
        $steamID = $row['steamID'];
        $playerName = trim($row['lastName']);

        // Initialize player stats
        $stats = [
            'steamid' => $steamID,
            'Name' => $playerName,
            'deathcount' => 0,
            'killcount' => 0,
            'wasrevivedcount' => 0,
            'revivecount' => 0,
            'tkcount' => 0,
            'suicidecount' => 0,
            'elo' => 1000.00,
            'knockedcount' => 0,
            'totaldamage' => 0,
        ];

        // Query for player-specific stats
        processPlayerStats($conn, $steamID, $stats);

        // Calculate ELO
        $stats['elo'] = calculateElo($stats);

        $data[] = $stats;
    }
}
$conn->close();

// Insert or update stats in the new database
updateDatabase($data);

function processPlayerStats($conn, $steamID, &$stats)
{
    $queries = [
        "SELECT attacker, victim, teamkill, damage FROM DBLog_Deaths WHERE attacker = '$steamID' OR victim = '$steamID'" => 'processDeaths',
        "SELECT reviver, victim FROM DBLog_Revives WHERE reviver = '$steamID' OR victim = '$steamID'" => 'processRevives',
        "SELECT attacker, victim, damage, teamkill FROM DBLog_Wounds WHERE attacker = '$steamID' OR victim = '$steamID'" => 'processWounds'
    ];

    foreach ($queries as $sql => $callback) {
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $callback($row, $steamID, $stats);
            }
        }
    }
}

function processDeaths($row, $steamID, &$stats)
{
    if ($row['attacker'] === $steamID) {
        if ($row['teamkill'] == 1) {
            $stats['tkcount']++;
        } elseif ($row['victim'] === $steamID) {
            $stats['suicidecount']++;
        } else {
            $stats['killcount']++;
        }
    }

    if ($row['victim'] === $steamID && $row['attacker'] !== $steamID) {
        $stats['deathcount']++;
    }
}

function processRevives($row, $steamID, &$stats)
{
    if ($row['reviver'] === $steamID) {
        $stats['revivecount']++;
    } elseif ($row['victim'] === $steamID) {
        $stats['wasrevivedcount']++;
    }
}

function processWounds($row, $steamID, &$stats)
{
    if ($row['attacker'] === $steamID && $row['teamkill'] != 1) {
        $stats['knockedcount']++;
        $stats['totaldamage'] += $row['damage'];
    }

    if ($row['victim'] === $steamID && $row['teamkill'] != 1) {
        $stats['totaldamage'] += $row['damage'];
    }
}

function calculateElo($stats)
{
    return 1000.00 + (0.1 * ($stats['killcount'] + $stats['knockedcount'] + $stats['revivecount']))
        - (0.1 * ($stats['deathcount'] + $stats['wasrevivedcount'] + $stats['tkcount'] + $stats['suicidecount']))
        + (0.01 * $stats['totaldamage']);
}

function updateDatabase($data)
{
    global $servername, $username, $password, $dbname;

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $selectStmt = $conn->prepare("
        SELECT deathcount, killcount, wasrevivedcount, revivecount, tkcount, suicidecount, elo, knockedcount, totaldamage 
        FROM squad_stats WHERE steamid = ?
    ");

    if (!$selectStmt) {
        die("Prepare failed: " . $conn->error);
    }

    foreach ($data as $row) {
        $steamID = $row['steamid'];

        $selectStmt->bind_param("s", $steamID);
        $selectStmt->execute();
        $selectStmt->store_result();

        if ($selectStmt->num_rows > 0) {
            updateExistingPlayer($conn, $row);
        } else {
            insertNewPlayer($conn, $row);
        }
    }

    $selectStmt->close();
    $conn->close();
}

function updateExistingPlayer($conn, $row)
{
    $stmt = $conn->prepare("
        INSERT INTO squad_stats (steamid, Name, deathcount, killcount, wasrevivedcount, revivecount, tkcount, suicidecount, elo, knockedcount, totaldamage, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            deathcount = VALUES(deathcount), 
            killcount = VALUES(killcount),
            wasrevivedcount = VALUES(wasrevivedcount), 
            revivecount = VALUES(revivecount),
            tkcount = VALUES(tkcount), 
            suicidecount = VALUES(suicidecount), 
            elo = VALUES(elo), 
            knockedcount = VALUES(knockedcount), 
            totaldamage = VALUES(totaldamage), 
            updated_at = NOW()
    ");

    if ($stmt) {
        $stmt->bind_param("ssiiiiiddid", 
            $row['steamid'], 
            $row['Name'], 
            $row['deathcount'], 
            $row['killcount'], 
            $row['wasrevivedcount'], 
            $row['revivecount'], 
            $row['tkcount'], 
            $row['suicidecount'], 
            $row['elo'], 
            $row['knockedcount'], 
            $row['totaldamage']
        );
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Failed to prepare update statement for player {$row['steamid']}: " . $conn->error);
    }
}

function insertNewPlayer($conn, $row)
{
    $stmt = $conn->prepare("
        INSERT INTO squad_stats (steamid, Name, deathcount, killcount, wasrevivedcount, revivecount, tkcount, suicidecount, elo, knockedcount, totaldamage, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if ($stmt) {
        $stmt->bind_param("ssiiiiiddid", 
            $row['steamid'], 
            $row['Name'], 
            $row['deathcount'], 
            $row['killcount'], 
            $row['wasrevivedcount'], 
            $row['revivecount'], 
            $row['tkcount'], 
            $row['suicidecount'], 
            $row['elo'], 
            $row['knockedcount'], 
            $row['totaldamage']
        );
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Failed to prepare insert statement for player {$row['steamid']}: " . $conn->error);
    }
}
?>

