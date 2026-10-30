# CLOZEE — Database Management Project (Planning Pack)

**Status: design draft; not an implemented website.**

CLOZEE is a responsive clothing marketplace for Burapha University students and people in Bangsaen. Buyers browse new and secondhand clothing, contact sellers about sizes, and choose local pickup or local delivery. Sellers manage their own shop and products. The first release is a PHP/MySQL web application; it does **not** dispatch couriers, track drivers, or process online payments.

## Start here

1. `docs/project-spec.md` — problem statement, audience, boundaries, milestones
2. `docs/functional-requirements.md` — functional/non-functional requirements and acceptance criteria
3. `docs/use-cases.md` — actors and main scenarios
4. `database/schema.sql` — **draft** MySQL/MariaDB relational schema (8 tables)
5. `docs/project-structure.md` — **proposed**, not yet implemented source structure
6. `docs/planning-context.md` — planning context and interpretation of course materials

## Classroom context

From provided course slide **Database-authen(1).pdf**, page 33 describes minimum project parts: problem/pain point, use case / ER diagram / schema, working MySQL CRUD, login/logout and authorization, GitHub/README/history, Angsila deployment. Page 34 shows a proposal milestone on **31 October 2026** and final submission on **10 November 2026**. Page 35 lists report sections. Verify these against any later instructor announcements.

## Security

This pack contains no usernames, passwords, or API keys. Do not paste or commit a Google Sheet containing User/Password columns. Credentials required for Angsila must be configured privately, not added to source control. Do not run schema against a shared or production database without review.

## What is not in this pack

No deployed application, no runnable authentication code, no seed data, and no official course-material PDFs. The SQL and structure are **planning drafts** for review before implementation.

## Final-round work authorization
The user has asked to prepare the full 10 November 2026 submission. Start with `docs/round2-build-brief.md`, then use `docs/final-submission-checklist.md` and `docs/final-report-outline.md` before claiming readiness. Deployment remains subject to authorized access and verification.
