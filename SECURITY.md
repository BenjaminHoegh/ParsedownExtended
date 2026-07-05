# Security Policy

Security reports are appreciated and will be handled with priority over regular feature requests and bug reports.

## Supported versions

Security fixes are generally considered for the latest released version of ParsedownExtended. Older versions may receive fixes when the change is low-risk and practical, but support is not guaranteed.

## Reporting a vulnerability

Please do not open a public issue for a suspected security vulnerability.

Use GitHub's private vulnerability reporting flow for this repository:

[Report a security issue](https://github.com/BenjaminHoegh/ParsedownExtended/security/advisories/new)

If private reporting is not available, open a minimal public issue asking for a private security contact, but do not include exploit details, payloads, or reproduction steps in the public issue.

Please include as much of the following as possible in the private report:

- ParsedownExtended version
- PHP version
- Parsedown and Parsedown Extra versions
- minimal Markdown input or PHP reproduction code
- expected output
- actual output
- potential impact
- whether the issue is already public

## Security scope

ParsedownExtended is a Markdown parser extension. It is not an HTML sanitizer.

By default, ParsedownExtended may allow raw HTML depending on configuration. Applications that render untrusted Markdown should review their `allow_raw_html` setting and apply an HTML sanitizer appropriate for their environment.

## Disclosure

Please allow reasonable time for investigation before public disclosure. If the report is valid, the maintainer will decide whether the fix should be released as a patch, documented as expected behavior, or treated as outside the project's scope.
