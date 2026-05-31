# Contributing to Grade Confidence — AI-graded question type

Contributions are welcome. This is the **quiz question type** (`qtype_aigraded`); the surface-agnostic
grading logic lives in the engine (`aiplacement_gradeconfidence`), whose
[`CONTRIBUTING.md`](https://github.com/mouneyrac/moodle-aiplacement_gradeconfidence) holds the full ground
rules, coding standards, and licensing terms — please read it.

## In short

- **Isolation:** keep the override surface over the core essay question type **thin**; reuse the engine
  grader rather than re-implementing grading logic here.
- **Tests are not optional**; keep the suite green; a bug fix adds a regression test first.
- **Standards:** PSR-12/PSR-1 + Moodle style; type hints required; `moodle-cs` + `moodlecheck` clean;
  capability + `sesskey` checks where relevant; strings in `lang/`.

```bash
vendor/bin/phpunit --testsuite qtype_aigraded_testsuite
vendor/bin/behat --tags @qtype_aigraded
```

## Licensing & commercial intent

By contributing you agree your contribution is **GNU GPL v3 or later** — permanently free software anyone
may use, modify and **redistribute, including for free**. This is an **alpha** released to gauge interest; a
paid edition may later appear on the Moodle Marketplace to fund maintenance and support, but that never
changes the freedom of this code or your contributions.
