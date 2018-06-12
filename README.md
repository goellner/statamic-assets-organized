# statamic-assets-organized
Organizes Statamic assets in subdirectories.

## Install

Copy AssetsOrganized folder to `site/addons/`

## Usage

Change `assets` fields to `assets_organized`, where you want to have the assets organized in subfolders.
Upload assets.
Save.
Profit.

## Notes

Assets will be moved to a subfolder which is named after the entry's slug. Tested with local assets and assets on Amazon S3.


## Examples

When you have the following setting in your fieldset:

```
fields:
  images:
    type: assets_organized
    container: main
    folder: gallery
```

The assets will get moved to `assets/gallery/example-slug/`