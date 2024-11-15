This project, **SquadJS-Autokick-SquadLeader-Kit**, will kick a squad leader without a kit.

Key features:
- **Feature 1:** Autokick Squadleader is for Squad-JS.
- **Feature 2:** Php script for generating webstats from Squad-JS database by combining data to new database.
- **Feature 3:** Python script for generating ELO for new player stat database.
- **Feature 4:** Generate custom MOTD from player stat database.

## Usage ##

To get started with **SquadJS-Autokick-SquadLeader-Kit**, follow the steps below:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/dukesix144/SquadJS-Autokick-SquadLeader-Kit.git

2. **Update the config file for SquadJS**:
<pre>
       {
      "plugin": "AutoKickNoSquadLeaderKit",
      "enabled": true,
      "warningMessage": "You must equip a squad leader kit to stay in this role.",
      "kickMessage": "No squad leader kit equipped - automatically removed",
      "frequencyOfWarnings": 30,
      "kickTimeout": 300,
      "playerThreshold": 51
      },
</pre>

4. **MOTD Sample**
<pre>
Top 10 RR Leaderboard:
    ELO       PLAYER
| 2,208.21 | theseus |
| 1,507.34 | Targetlockon |
| 1,370.07 | GoldenPancake |
| 1,362.74 | TypicDonkey11 |
| 1,353.20 | Martin_Wong |
| 1,320.15 | Zeref |
| 1,309.48 | LunaTicxx |
| 1,289.66 | L33P |
| 1,276.68 | CREEPY UNCLE SOLDIER |
| 1,273.40 | DerpyDuck-_ |
</pre>
