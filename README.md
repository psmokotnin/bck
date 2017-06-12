# bck
Backup service

Read .gitignore and make backup files, which not following by Git.

.bckignore - ignore backup pathes

bck init - init backup service
create folder .bck

bck clean - delete all bck's data.

bck config
Show current config:

bck config ftp.server - show ftp.server for this folder
bck config global ftp.server - show ftp.server from global config

bck push - send files to ftp

bck stat - show files, needs for backup

.bck folder:
    config.ini - configuration file
    objects/ - file data (mtime and hash for each files under control)