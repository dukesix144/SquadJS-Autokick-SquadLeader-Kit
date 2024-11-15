This project, **SquadJS-Autokick-SquadLeader-Kit**, will kick a squad leader without a kit.

Key features:
- **Feature 1:** Autokick Squadleader is for Squad-JS.
- **Feature 1:** Php script for generating webstats from Squad-JS database by combining data to new database.
- **Feature 1:** Python script for generating ELO for new player stat database.
- 
## Usage ##

To get started with **SquadJS-Autokick-SquadLeader-Kit**, follow the steps below:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/dukesix144/SquadJS-Autokick-SquadLeader-Kit.git

2. Update the config file for SquadJS:

<code>
       {
      "plugin": "AutoKickNoSquadLeaderKit",
      "enabled": true,
      "warningMessage": "You must equip a squad leader kit to stay in this role.",
      "kickMessage": "No squad leader kit equipped - automatically removed",
      "frequencyOfWarnings": 30,
      "kickTimeout": 300,
      "playerThreshold": 51 
      },
</code>
