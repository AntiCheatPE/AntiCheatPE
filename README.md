# AntiCheatPE #INACTIVE
Note: most of hacks are already blocked by pocketmine, this plugin will only blocks hacks that aren't already blocked.
For now it is able to detect and block the following hacks :
- Fly Hack
- Jump Hack
- Speed Hack
- Damage Hack (Knockback, No-Damage, Onehit)
- Gamemode Hack

Please report bugs by opening an issue on this project, they will be fixed ASAP.

#Notice: about false positives
The problem of false positive as far as I know is experienced on servers with players with bad connections.
I've done several updates to the plugin these days and that problem can be resolved as following:
Put tags: -1 in the new config. This will disable players kicking when fly hack is detected, but the anti fly (adventure settings) packet will be sent to the player anyway. If it is a false positive, the player will just receive the packet and do nothing; if it is a real cheater, it will disable fly hack automatically.

For speed hack there is no such packet that could do the same thing. The only alternative, if you are experiencing too many false positives, is to put points: -1 in the new config. That will completely disable speed hack detection, but it's the only way for now


#To-Do list:
- [x] Make a To-Do list
- [x] Speed hack detection
- [x] Fly hack detection
- [x] Jump hack detection
- [x] Make this plugin fully customizable

This project is officially mantained by [ItalianDevs4PM](https://github.com/ItalianDevs4PM)
