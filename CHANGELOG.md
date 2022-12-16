# Release Notes for Glossary for Craft CMS

## 1.0.6 - 2022-12-16

### Fixed

- Missing definitions when applying the glossary multiple times. (#5)
- Remove empty strings from terms breaking the frontend. (#7)
- Fixed shortcut to save terms and glossaries. (#8)

## 1.0.5 - 2021-09-28

### Fixed

- Escaping special regular expression characters.

## 1.0.4 - 2021-09-28

### Added

- Add the term element to the term template variables. So you can now access the hole term element within the template.

### Deprecated

- The custom fields values of a term in the term template are accessible by the handle, e.g. `{{ myCustomField }}`. This will be removed in the future. You should now access the custom field values using the term variable, e.g. `{{ term.myCustomField }}`.

## 1.0.3 - 2021-09-27

### Fixed

- Remove CP asset dependencies from frontend asset bundle.

## 1.0.2 - 2021-09-20

### Fixed

- Deleting disabled terms is fixed.
- Fixed wrong schema version.

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
