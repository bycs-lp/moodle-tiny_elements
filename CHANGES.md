# Changelog for tiny_elements

## v1.0.1

* Management links can now be accessed directly in the admin tree (MBS-10269, #34)
* Names for components, categories, variants and flavors can now be filtered (#32). There is a new admin setting "tiny_elements/allowedfilters" where you can choose which filters should be applied (by default it's multilang2).
* Names for components, categories, variants and flavors can now have a length up to 1333 characters (#32)
* CSS code is not added during upgrade (MBS-10240, #33)
* Bugfix: Only variants of the chosen category are now exported (MBS-10239, #29)
* Bugfix: CSS cache is purged after renaming variants (MBS-10237, #28)
* Bugfix: Searching respects displaynames  (MBS-10253, #31)
