## FluxBB Importer

This [Flarum](https://flarum.org/) extension is specific for TheRoyals.it forum (https://www.theroyals.it/forum/).
You might find its code useful to implement your own solution.
It is based on work of flarum-import-fluxbb of ArchLinux Community and Fedora-Fr.org suggestions of llaumgui.

### Need to know before migration
- You need a fresh Flarum installation. This Importer will remove all previus data (tags, users, discussions, avatar, etc.) inserted in your flarum website.
- Working (or near working) Fluxbb forum. This Importer read database taken from "config.php" file and avatars dir inside your Fluxbb directory.
- Check if you have some accounts with the same email and fix them. (See: https://discuss.flarum.org/d/3867-fluxbb-to-flarum-migration-tool/11)
- It will import only validated users (no fluxbb group_id=0).

### Installation

```sh
composer require archlinux-de/flarum-import-fluxbb
```
And activate the extension.

### Usage

```sh
./flarum app:import-from-fluxbb  [<fluxbb-directory>]
```

Remeber, in fluxbb-directory must be a config.php with the correct information to connect to the fluxbb database.
