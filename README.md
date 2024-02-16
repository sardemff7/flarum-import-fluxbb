## FluxBB Importer

This [Flarum](https://flarum.org/) extension should be pretty generic but is based on the one for for TheRoyals.it forum (https://www.theroyals.it/forum/).
You might find its code useful to implement your own solution.
It is based on Mario Santagiuliana’s fork of the flarum-import-fluxbb from the ArchLinux Community, with Fedora-Fr.org suggestions of llaumgui.
I added Filesystem abstraction support for the avatars migration, to support a Flarum installation storing them in e.g. an S3-compatible service.

### Need to know before migration
- You need a fresh Flarum installation. This Importer will remove all previus data (tags, users, discussions, avatar, etc.) inserted in your flarum website.
- Working (or near working) Fluxbb forum. This Importer read database taken from "config.php" file and avatars dir inside your Fluxbb directory.
- Check if you have some accounts with the same email and fix them. (See: https://discuss.flarum.org/d/3867-fluxbb-to-flarum-migration-tool/11)
- It will import only validated users (no fluxbb group_id=0).

### Installation

```sh
composer require --dev sardemff7/flarum-import-fluxbb "*@dev"
```
And activate the extension once your Flarum installation done.
```sh
php flarum extension:enable sardemff7-import-fluxbb
```

### Usage

```sh
./flarum app:import-from-fluxbb [<fluxbb-directory>]
```

Remember, in fluxbb-directory must be a config.php with the correct information to connect to the fluxbb database.
