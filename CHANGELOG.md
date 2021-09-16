# Release Notes for Glossary for Craft CMS

## 1.0.1 - 2021-09-16

### Added

- Added translation. Added German translation (thanks to @emsuiko).
- You can now iterate over terms in templates like `{{ for term in craft.glossary.terms.glossary('myGlossary').all() }}`. 

### Fixed

- Fixed an error when creating a new term and no default glossary exists.
- If the current user in the CP has only permissions to edit terms, the redirect will respect this now and redirects the user to the terms instead of the glossaries. 
- In some circumstances you could have more than one default glossary. This is fixed. 

## 1.0.0 - 2021-07-20

### Added

- Initial release
