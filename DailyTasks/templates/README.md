# ProSensia Daily Drop — Templates Folder

## Files in This Folder

| File | Purpose |
|------|---------|
| [PROCEDURE.md](PROCEDURE.md) | **Start here.** Full operating procedure — how the Daily Drop system works, how to fill templates, how to upload tasks to the portal, quality checklist |
| [base_template.md](base_template.md) | Universal skeleton all fields share — use this for new squads not yet covered |
| [ai_ml_template.md](ai_ml_template.md) | AI & Machine Learning Engineering squad |
| [full_stack_template.md](full_stack_template.md) | Full Stack Web Development squad (React/Next.js/TypeScript) |
| [cyber_template.md](cyber_template.md) | Cyber Security Task Force |
| [cpp_systems_template.md](cpp_systems_template.md) | Systems Engineering C++ squad |
| [qa_template.md](qa_template.md) | QA Engineering squad |

## Quick Start

1. Open the correct field template
2. Replace all `{{PLACEHOLDERS}}` — the Mentor Fill-In Guide at the bottom of each template explains each one
3. Delete the Mentor Fill-In Guide section before publishing
4. Assign the task on the portal: **Mentor → Assign Task → select Target Field + set Task Date**
5. Save PDF to `DailyTasks/Week{N}/Day{N}/Daily Drop - {Field} Day {N}.pdf`

## Folder Structure Convention

```
DailyTasks/
  Week1/
    Day1/   Daily Drop - AI&ML Day 1.pdf
            Daily Drop - Full Stack Day 1.pdf
            Daily Drop - Cyber Day 1.pdf
            Daily Drop - C++ Day 1.pdf
            Daily Drop - QA Day 1.pdf
    Day2/   ...
    Day3/   ...
    Day4/   ...
    Day5/   ...
  Week2/
    Day1/   ...
    ...
  templates/   ← You are here
```

## Important Portal Settings

- **Daily Unlock Time:** Configurable in Admin → Settings → "Daily Task Unlock Time"
- **Target Field:** Set per-task in Mentor → Assign Task → "Target Field" dropdown
- **Task Date:** Must be set to the correct date — interns only see tasks for today
