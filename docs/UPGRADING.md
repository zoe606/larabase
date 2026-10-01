# Upgrading

Larabase uses semantic versioning. Patch releases fix compatible behavior.
Minor releases add compatible capabilities. Major releases may require changes.

Each release includes a changelog, migration instructions when needed, and a
verification report. Read these before importing changes.

Create an application update branch. Compare starter-owned files and import
selected commits or apply equivalent patches. Preserve application branding,
dashboard, routes, configuration, and seed data. Record the adopted release and
commit plus any changes deliberately skipped.

Run focused tests and the complete application checks before merging the update.
Updates are deliberate and do not require a runtime link to the starter repository.
