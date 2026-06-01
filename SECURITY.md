# Security Policy

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security problems.

Email: **support@rodiumai.io** with subject `Security — laravel-sdk`.

Include steps to reproduce, impact, and any suggested fix.

## API keys

- Never commit `RODIUMAI_API_KEY` or `.env` files.
- Rotate keys immediately if they are exposed (chat, logs, CI, screenshots).
- Create keys only at [https://www.rodiumai.io/dashboard](https://www.rodiumai.io/dashboard).

## Supported versions

| Version | Supported |
| ------- | --------- |
| 0.1.x   | Yes       |
