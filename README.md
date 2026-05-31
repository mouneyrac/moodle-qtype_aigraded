# Grade Confidence — AI-graded question type (`qtype_aigraded`)

A Moodle question type that behaves like an **essay** question for the student, and routes the response —
together with the question's rubric/criteria — through the **Grade Confidence** engine
(`aiplacement_gradeconfidence`) for grading **assurance**. The teacher still grades; the AI reviews for
consistency and flags material discrepancies with verbatim evidence. Stored under `sourcetype='quiz'`.

- Extends the core essay question behaviour (no third-party runtime dependency).
- Reuses the engine grader unchanged, so the same safety properties apply (blind review F2; verified
  evidence F3; the teacher↔AI diff computed in code F1).
- Students see a normal essay question; the assurance review is teacher-only.

## Project status — alpha, gauging interest

Part of an **alpha** project published to gauge real interest. Not yet piloted. If it reaches a stable
release it **may later be offered as a paid product on the
[Moodle Marketplace](https://marketplace.moodle.com/)** — selling maintenance, professional review, and
business support, **never** exclusivity over the code (see *License & reuse*). A paid edition reflects how
time-consuming ongoing maintenance, compliance upkeep across Moodle upgrades, and customer support are, and
may require a change in the maintainer situation.

## A note on compliance — please read

Grade Confidence is **designed to follow** sound privacy and EU AI Act practices, but it is **NOT
certified, audited, or conformity-assessed**, and we make **no legal compliance claim**. A formal EU AI Act
conformity assessment (~€30k–€80k) **has not been done**. It is built in good faith to *support* your own
compliance work — and we would hope it would pass an audit — but **you, the deployer, remain responsible**
for your compliance, DPAs, and any required assessment. Do not present anything here as proof of conformity.

## Requirements

- Moodle **5.1+** (developed against 5.2). **PHP 8.3+**.
- **Depends on** `aiplacement_gradeconfidence` (the engine), installed and configured with a working
  `core_ai` provider.

## Install

Place this repository's contents at `public/question/type/aigraded/` in your Moodle, then complete the
upgrade via **Site administration → Notifications**. Authors can then add **AI-graded** questions in the
question bank. (Distribution repo: `moodle-qtype_aigraded`.)

## Developing

```bash
vendor/bin/phpunit --testsuite qtype_aigraded_testsuite
vendor/bin/behat --tags @qtype_aigraded
```

The override surface over core essay is kept deliberately thin; see [`CONTRIBUTING.md`](CONTRIBUTING.md).

## License & reuse

GNU **GPL v3 or later** — free software you may use, study, modify, and **redistribute, including for
free**. A future paid edition cannot remove those rights for this code. Only the name "Grade Confidence"
(trademark) is outside the GPL: a redistribution must not imply endorsement or reuse the branding.
