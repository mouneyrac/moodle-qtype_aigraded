# Security Policy

Grade Confidence is an open-source (GPLv3) grading-*assurance* plugin for Moodle. For an AI tool that
handles student work, security and privacy are the product — not an afterthought.

## Reporting a vulnerability

Please report suspected vulnerabilities **privately** — not via a public issue or pull request:

- Contact the maintainer (Jerome) via Moodle.org: <https://moodle.org/user/profile.php?id=542994>

We aim to acknowledge within a few days and to fix confirmed issues promptly. This is an **alpha**,
maintained best-effort; there is no bug bounty. Please allow reasonable time for a fix before any public
disclosure. Responsible reports are welcomed and credited (with your consent).

## Scope

This repository (`qtype_aigraded`, the quiz question type) is reviewed together with its companions:

- `aiplacement_gradeconfidence` — engine
- `assignfeedback_gradeconfidence` — assignment adapter
- `qtype_aigraded` — quiz question type (this repo)

## How we test (and how you can verify)

- **Executable security suite** — `vendor/bin/phpunit --group security` asserts the access-control and
  attack-surface invariants on every CI run. The student-facing rendering is identical to a core Essay
  question — the assurance review is never shown to students.
- **Semgrep** static analysis (SAST for PHP) runs on every push and weekly — results appear in this
  repository's **Security** tab.
- A **threat-focused manual security review** (data theft, privilege escalation, abuse).
- **Zero third-party runtime dependencies** by design.

## Supported versions

The latest `main` is supported. Requires the `aiplacement_gradeconfidence` engine. Targets Moodle 5.1+,
PHP 8.3+.
