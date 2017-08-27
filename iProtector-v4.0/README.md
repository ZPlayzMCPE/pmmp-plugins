# iProtector-v4.0
![Release](https://img.shields.io/badge/release-v4.0.3-blue.svg)

iProtector-v4.0 is a PocketMine-MP plugin that allows you to protect areas (includes but is not limited to prevent block breaking and placing). Feel free to switch these settings according to your needs.

## Features
Have a feature request? Submit it [here](https://github.com/kenygamer/pmmp-plugins/issues) and I'll consider adding it!
- Availability to enable or disable the plugin from the config.
- Enable or disable pretty printing for areas; if you handle several areas, you may be thinking of disabling this flag.
- Protect your areas of TNT and Creeper explosions with the `tnt` flag.
- See in which area you are standing with `/area here`.
- Send automated messages during certain events (for example, when you break a block, supposing is protected).
- Item frame protection in areas with the `edit` flag (default).

**iProtector-v4.0.3 is not compatible with older iProtector-v4.0 and PocketMine versions.**

## Commands
| Command | Usage | Description |
| ------- | ----- | ----------- |
| `/area` | `/area <pos1/pos2/create/here/list/flag/whitelist/delete>` | Commands for managing areas. |

## Permissions
```yaml
 iprotector:
  default: false
  children:
   iprotector.access:
    default: op
   iprotector.command:
    default: false
    children:
     iprotector.command.area:
      default: op
      children:
       iprotector.command.area.pos1:
        default: op
       iprotector.command.area.pos2:
        default: op
       iprotector.command.area.create:
        default: op
       iprotector.command.area.here:
        default: op
       iprotector.command.area.list:
        default: op
       iprotector.command.area.flag:
        default: op
       iprotector.command.area.delete:
        default: op
       iprotector.command.area.whitelist:
        default: op
```
