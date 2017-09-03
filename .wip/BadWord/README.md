# BadWord
![Release](https://img.shields.io/badge/release-v1.0-blue.svg)

BadWord is a PocketMine-MP plugin that
...

## Commands
| Command | Usage | Description | 
| ------- | ----- | ----------- |
| `/badword` | `/badword <word>` | Suggest a word to be added to the chat filter. |
| `/bw` | `/bw <word>` | Alias of /badword. |
| `/bwadmin` | `/bwadmin <approve\|list>` | Admin commands for BadWord. |

## Permissions
```yaml
badword.command:
 description: "Allows you to use /badword."
 default: true
badword.admin.command:
 description: "Allows you to use /bwadmin."
 default: op
```
