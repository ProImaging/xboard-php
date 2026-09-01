# Security policy

## Supported versions

| Version | Supported |
| ------- | --------- |
| 0.1.x   | Yes       |

## Reporting a vulnerability

**Please do not open public GitHub issues for security vulnerabilities.**

Email security concerns to your xBoard security contact or open a private advisory on GitHub if enabled for this repository. Include:

- Description and impact
- Steps to reproduce
- SDK version and environment (PHP version)

We aim to acknowledge reports within a few business days.

## API keys

- Never commit `xbk_*` keys
- Rotate keys if exposed
- Use test keys in development

The SDK sends the API key over HTTPS. You are responsible for secure storage and transport in your application.
