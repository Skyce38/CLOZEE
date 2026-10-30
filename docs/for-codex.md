# How Codex should use the CLOZEE planning package

You previously reported that you could not find CLOZEE planning, schema or project structure in the workspace. These files were written in ChatGPT and are now supplied as **the initial design reference**, not previously existing files.

Read all files in this package in order:
1. README.md
2. docs/project-spec.md
3. docs/functional-requirements.md
4. docs/use-cases.md
5. database/schema.sql
6. docs/project-structure.md

Then examine the *separately supplied* course slides and assignments (if available):
- `Database-authen(1).pdf`
- `CRUD-and-VERSION-CONTROL(1).pdf`
- `Data-base-join(1).pdf`
- `campus_delivery_homework(1).sql`
- `Database_JOIN_Homework_Student(2).docx`
- Other ZIP/lab files only after checking their actual contents and relevance.

Separate **project requirements** from **class examples/homework**. The course example `campus_delivery_homework.sql` describes a food-delivery JOIN dataset; it is not CLOZEE's schema and should not be copied directly.

Create `docs/course-requirements.md` with the exact filenames/pages that support each course requirement. Review and report any conflicts between the classroom requirements and CLOZEE's proposed scope, schema or deployment assumptions. Do not invent missing instructions.

## Important privacy instruction

The Google Sheet that was described as having **User/Password** columns is **not required** to assess CLOZEE design. Do not copy, use, output, or commit any passwords or credentials from that sheet. Ask for a separate, sanitized connection configuration only during authorized deployment.

## Current authorization (Round 2)

The user has now authorized building the full CLOZEE coursework project for the 10 November 2026 final delivery. Read `docs/round2-build-brief.md` and proceed with local implementation in phases. Treat draft schema as unreviewed. **Do not deploy to Angsila or alter any production database without verifying permissions, environment and specific authorization.** Never copy or commit actual secrets.
