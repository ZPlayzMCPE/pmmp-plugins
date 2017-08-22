# ChatLogger
[![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/kenygamer/pmmp-plugins.svg)](http://isitmaintained.com/project/kenygamer/pmmp-plugins "Average time to resolve an issue")
![Total downloads](https://img.shields.io/github/downloads/kenygamer/pmmp-plugins/total.svg)

ChatLogger is a PocketMine-MP plugin that logs your server chat and allows you to access messages with ease later. You can create reports to get messages from a specific player on a given date.

## Commands
This plugin has no commands.
## Permissions
```yaml
chatlogger.bypass:
 default: false
```

## Developers
ChatLogger has events you can listen to.
### Getting access
Make sure to add the following to your plugin.yml
```yml
depend: ["ChatLogger"]
```
**Note**: If you use `softdepend` you will need to check if ChatLogger is installed.

Now you can listen to the [**PlayerChatLogEvent**](https://github.com/kenygamer/pmmp-plugins/blob/master/ChatLogger/src/kenygamer/ChatLogger/event/PlayerChatLogEvent.php)
#### Example 
```php
public function onPlayerChatLog(PlayerChatLogEvent $event){
  $player = $event->getPlayer(); // Returns a Player object
  if(in_array($player->getName(), $this->vips)){
    $event->setCancelled();
  }
}
```

Remember the `use` operator at the start of the file
```php
use kenygamer\ChatLogger\event\PlayerChatLogEvent;
```
