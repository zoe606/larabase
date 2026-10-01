# Security

The intended supported release line is 1.0.x after its initial publication.
Report vulnerabilities privately through GitHub's security advisory reporting
for this repository. Do not disclose exploit details in public issues before a fix.

Keep application keys, database passwords, SMTP credentials, and monitoring
credentials outside source control. Keep production debug mode disabled.
Use HTTPS and explicit allowed origins in production.

The documented demo administrator exists only in local and testing environments.
Production seeding creates no default user. Provision administrators explicitly
with unique credentials and keep dependency audits in release verification.
