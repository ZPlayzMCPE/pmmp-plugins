# Coords
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/kenygamer/pmmp-plugins.svg)](http://isitmaintained.com/project/kenygamer/pmmp-plugins "Average time to resolve an issue")
![Total downloads](https://img.shields.io/github/downloads/kenygamer/pmmp-plugins/total.svg)

Coords is a plugin for PocketMine-MP that allows you to get your coordinates (multi-world support) in a single command. This plugin features the ability to see the coordinates of other online players and even choose a custom format.

**Coords 3.1 is not compatible with older Coords and PocketMine versions.**

## Commands
| Command | Usage | Description |
| ------- | ----- | ----------- |
| `/coords` | `/coords [player]` | Prints out your or other players' coordinates. |
| `/coordtags` | `/coordtags` | Lists available tags for formatting the /coords message. |
| `/coordupdate` | `/coordupdate <message>` | Sets the /coords message. |

## Permissions
```yaml
 coords.command:
  default: true
 coords.command.see:
  default: op
 coords.command.tags:
  default: op
 coords.command.update:
  default: op
```
