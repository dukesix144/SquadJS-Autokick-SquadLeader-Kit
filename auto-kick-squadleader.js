import BasePlugin from './base-plugin.js';

export default class AutoKickNoSquadLeaderKit extends BasePlugin {
  static get description() {
    return 'Automatically kicks squad leaders without a squad leader kit after a specified amount of time.';
  }

  static get defaultEnabled() {
    return true;
  }

  static get optionsSpecification() {
    return {
      kickMessage: {
        required: false,
        description: 'Message to send to players when they are kicked for not having a squad leader kit',
        default: 'You must equip a squad leader kit to stay in this role.'
      },
      kickTimeout: {
        required: false,
        description: 'Time in seconds a player can be squad leader without a squad leader kit before being kicked',
        default: 300 // 5 minutes
      },
      playerThreshold: {
        required: false,
        description: 'Minimum player count required for AutoKick to start',
        default: 51
      }
    };
  }

  constructor(server, options, connectors) {
    super(server, options, connectors);
    this.trackedPlayers = {}; // Store players without the squad leader kit

    this.onPlayerRoleChange = this.onPlayerRoleChange.bind(this);
    this.onPlayerDisconnect = this.onPlayerDisconnect.bind(this);
  }

  async mount() {
    this.server.on('PLAYER_ROLE_CHANGED', this.onPlayerRoleChange);
    this.server.on('PLAYER_DISCONNECTED', this.onPlayerDisconnect);
  }

  async unmount() {
    this.server.removeEventListener('PLAYER_ROLE_CHANGED', this.onPlayerRoleChange);
    this.server.removeEventListener('PLAYER_DISCONNECTED', this.onPlayerDisconnect);
  }

  async onPlayerRoleChange({ player, newRole }) {
    // Only track if the player is squad leader without the correct kit
    if (player.leader && !newRole.includes('squad_leader')) {
      this.trackPlayer(player);
    } else if (newRole.includes('squad_leader')) {
      this.untrackPlayer(player.eosID); // Player now has the correct kit
    }
  }

  trackPlayer(player) {
    // Ensure a player count threshold before tracking
    if (this.server.players.length < this.options.playerThreshold) return;

    // Remove any existing timeout to reset the timer
    this.untrackPlayer(player.eosID);

    this.trackedPlayers[player.eosID] = setTimeout(() => {
      this.server.rcon.kick(player.steamID, this.options.kickMessage);
      this.untrackPlayer(player.eosID); // Remove from tracking after kick
    }, this.options.kickTimeout * 1000);

    this.logger.verbose(`Tracking player ${player.name} for not having a squad leader kit.`);
  }

  untrackPlayer(eosID) {
    if (this.trackedPlayers[eosID]) {
      clearTimeout(this.trackedPlayers[eosID]);
      delete this.trackedPlayers[eosID];
      this.logger.verbose(`Stopped tracking player with EOS ID ${eosID}.`);
    }
  }

  onPlayerDisconnect({ player }) {
    this.untrackPlayer(player.eosID);
  }
}

