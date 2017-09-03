# BadWord
![Release](https://img.shields.io/badge/release-v1.0-blue.svg)

BadWord is a PocketMine-MP plugin intended to limit the use of capital letters in chat. It allows you to set a limit of capital letters per message sent by players. If the limit is exceeded, the message will not be sent.

## Commands
| Command | Usage | Description | 
| ------- | ----- | ----------- |
| `/limit` | `/limit <value>` | Sets the capital letters limit. |

## Permissions
```yaml
 capslimiter.command:
  default: op
 capslimiter.bypass:
  default: false
```
