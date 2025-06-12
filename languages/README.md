# SoMe Captions Client Translation Files

This directory contains translation files for the SoMe Captions Client plugin.

## Available Translations

- Danish (da_DK)

## Generating MO Files

To generate or update the MO files from the PO files, you need to use the `msgfmt` tool from the GNU gettext utilities:

```bash
# Install gettext utilities if not already installed
# On Ubuntu/Debian:
sudo apt install gettext

# On macOS (using Homebrew):
brew install gettext

# Generate MO file from PO file
msgfmt -o somecaptions-wpclient-da_DK.mo somecaptions-wpclient-da_DK.po
```

## Adding New Translations

1. Copy the `somecaptions-wpclient.pot` template file
2. Rename it to `somecaptions-wpclient-{locale}.po` (e.g., `somecaptions-wpclient-fr_FR.po` for French)
3. Translate the strings in the PO file
4. Generate the MO file using the `msgfmt` command as shown above

## Translation Tools

You can use tools like [Poedit](https://poedit.net/) to edit PO files and generate MO files with a graphical interface.

## Loading Translations

The plugin automatically loads translations from this directory based on the WordPress site language setting.
