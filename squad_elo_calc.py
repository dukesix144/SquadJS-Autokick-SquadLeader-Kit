import mysql.connector
import statistics
import time

# MySQL database configuration
db_config = {
    'user': '',
    'password': '',
    'host': '',
    'database': ''
}

# Step 1: Fetch player statistics for ELO calculation
def fetch_player_stats(retries=5):
    attempt = 0
    while attempt < retries:
        try:
            # Establish connection to the database
            conn = mysql.connector.connect(**db_config)
            cursor = conn.cursor(dictionary=True)

            query = """
                SELECT 
                    steamid,
                    Name,
                    COALESCE(killcount, 0) as killcount,
                    COALESCE(knockedcount, 0) as knockedcount,
                    COALESCE(deathcount, 0) as deathcount,
                    COALESCE(wasrevivedcount, 0) as wasrevivedcount,
                    COALESCE(tkcount, 0) as tkcount,
                    COALESCE(suicidecount, 0) as suicidecount,
                    COALESCE(revivecount, 0) as revivecount,
                    COALESCE(totaldamage, 0) as totaldamage
                FROM squad_stats
            """

            cursor.execute(query)
            players = cursor.fetchall()
            cursor.close()  # Close the cursor, but keep connection open for further updates
            return conn, players

        except mysql.connector.Error as err:
            if 'Lock wait timeout exceeded' in str(err) or 'Deadlock' in str(err):
                attempt += 1
                print(f"Retrying query due to lock... attempt {attempt}/{retries}")
                time.sleep(2 ** attempt)  # Exponential backoff
            else:
                print(f"Error accessing MySQL database: {err}")
                return None, None

# Step 2: Recalculate ELO based on performance metrics
def calculate_performance_elo(player):
    performance_elo = 1000
    # Positive metrics
    performance_elo += float(player['killcount']) * 1
    performance_elo += float(player['knockedcount']) * 0.5
    performance_elo += float(player['revivecount']) * 2
    performance_elo += float(player['totaldamage']) * 0.01  # Scale down damage impact
    # Negative metrics
    performance_elo -= float(player['deathcount']) * 2
    performance_elo -= float(player['wasrevivedcount']) * 1
    performance_elo -= float(player['tkcount']) * 10
    performance_elo -= float(player['suicidecount']) * 15
    return performance_elo

# Step 3: Rescale ELO to center around 1000
def rescale_elo(elo, mean_elo, std_dev_elo):
    normalized_elo = (elo - mean_elo) / std_dev_elo
    rescaled_elo = 1000 + normalized_elo * std_dev_elo
    return max(rescaled_elo, 0)  # Ensure no negative ELO

# Main Function
def update_player_elos(retries=5):
    conn, players = fetch_player_stats()

    if players:
        elos = []
        performance_stats = []

        # Collect performance-based ELO scores
        for player in players:
            player_elo = calculate_performance_elo(player)
            elos.append(player_elo)
            performance_stats.append((player['steamid'], player_elo))

        # Step 4: Calculate the mean and standard deviation of the newly calculated ELOs
        mean_elo = statistics.mean(elos)
        std_dev_elo = statistics.stdev(elos)

        attempt = 0
        while attempt < retries:
            try:
                cursor = conn.cursor()
                # Step 5: Update player ELOs in the database
                for steamid, performance_elo in performance_stats:
                    new_elo = rescale_elo(performance_elo, mean_elo, std_dev_elo)
                    cursor.execute("""
                        UPDATE squad_stats
                        SET elo = %s
                        WHERE steamid = %s
                    """, (new_elo, steamid))

                # Commit the updates
                conn.commit()
                cursor.close()
                break

            except mysql.connector.Error as err:
                if 'Lock wait timeout exceeded' in str(err) or 'Deadlock' in str(err):
                    attempt += 1
                    print(f"Retrying update due to lock... attempt {attempt}/{retries}")
                    time.sleep(2 ** attempt)  # Exponential backoff
                else:
                    print(f"Error during MySQL update: {err}")
                    return

        # Close the connection
        conn.close()
    else:
        print("Failed to fetch player stats.")

if __name__ == "__main__":
    update_player_elos()

