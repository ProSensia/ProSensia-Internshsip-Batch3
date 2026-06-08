# ProSensia Daily Drop — Full Operating Procedure
> **Classification: Internal — Mentor & Management Use Only**
> Last updated: June 2026 · Version 2.0

---

## What Is the Daily Drop?

The Daily Drop is ProSensia's core daily execution protocol. Every morning, interns receive a structured task sheet (the "Daily Drop Blueprint") tailored to their specific field (AI/ML, Full Stack, Cyber Security, C++ Systems, or QA Engineering). The blueprint replaces ad-hoc instruction and guarantees every intern works on the same domain-specific mandate simultaneously — no ambiguity, no time wasted.

The system is **not** a loose guide. It is an enforced sprint. The platform enforces:
- A **daily unlock time** (set by super admin in Portal Settings — default 9:00 AM)
- A **submission deadline** (2:00 PM daily)
- Two mandatory deliverables: **GitHub push** + **LinkedIn post**
- A **Kanban Board card** moved to "Under Review"
- An **Assignment Module** submission with live URLs

---

## The Standard Daily Drop Blueprint — Section Breakdown

Every Daily Drop PDF follows this exact three-section structure. Templates in this folder mirror this structure.

```
┌─────────────────────────────────────────────────────────────┐
│  HEADER — Squad name · Progress % · Current Week Focus      │
├─────────────────────────────────────────────────────────────┤
│  SECTION A — Today's Materials (The Synthesis Mandate)      │
│    1. Video Drills          ← Watch FIRST                   │
│    2. Technical Docs        ← Reference while building      │
│    3. Engineering Assets    ← Assets/tools/data to use      │
├─────────────────────────────────────────────────────────────┤
│  SECTION B — The Execution Mandate (Unlock Time – 2:00 PM)  │
│    1. TL;DR Objective       ← One paragraph summary         │
│    2. Time-Blocked Workflow ← 4 phases (see below)          │
│    3. Non-Negotiable Rules  ← Hard bans, zero tolerance     │
├─────────────────────────────────────────────────────────────┤
│  SECTION C — Submission Protocol (Due strictly by 2:00 PM)  │
│    Step 1: Kanban Board     ← Move card to "Under Review"   │
│    Step 2: Assignments      ← Paste GitHub + LinkedIn URLs  │
│    Operational Note:        ← Do NOT DM management          │
└─────────────────────────────────────────────────────────────┘
```

---

## Standard Time-Blocked Workflow

This block is fixed across all domains. Adjust the specific activities per field but keep the time buckets identical.

| Phase | Time Slot | Purpose |
|-------|-----------|---------|
| **Learning** | 9:00 AM – 10:00 AM | Watch assigned video drills. Read primary docs. No building. |
| **Building** | 10:00 AM – 1:00 PM | Three-action execution sequence (Setup → Core Logic → Integration) |
| **Hygiene** | 1:00 PM – 1:30 PM | Clean up, run compiler/tests, push to GitHub |
| **LinkedIn** | 1:30 PM – 2:00 PM | Draft and post technical LinkedIn update with ProSensia admit card link |

> **Rule:** The phases are not flexible. An intern who skips Learning and goes straight to Building will lack the context to complete the task correctly. The order is intentional.

---

## Progress Tracking Formula

Use this formula for the header progress percentage:

```
Progress % = (Days Completed / Total Bootcamp Days) × 100

Example: Week 1 Day 2 of a 20-session bootcamp = 1/20 = 5%
```

Update this number in each new Daily Drop header. Interns see it displayed on their character screen.

---

## Non-Negotiable Rules — Design Principles

Every Daily Drop must contain a "Non-Negotiable Rules" section with **at least two** hard bans. These bans serve a pedagogical purpose: they force interns to reason about the underlying concept rather than blindly copying a tool's output.

**Writing good non-negotiable rules:**
- The ban must be specific (not "don't cheat") — e.g., "You are forbidden from using SQLMap"
- State the **consequence** clearly: "Failure results in ticket rejection and penalized sprint velocity"
- Include an **interrogation standard** when AI tools are allowed — the intern must explain what the AI generated
- Balance bans with permissions: allow AI for scaffolding, ban it for core logic

**Examples from existing drops:**
- ML Ban (AI/ML Day 2): No Scikit-Learn/PyTorch/TensorFlow — today is EDA only
- Automated Tool Ban (Cyber Day 2): No SQLMap — payloads must be crafted manually
- Raw Pointer Ban (C++ Day 2): No `new`/`delete` keywords — only `make_unique`/`make_shared`
- Happy Path Ban (QA Day 2): Testing only valid inputs is automatic failure

---

## Submission Protocol — Standard Language

Copy this block verbatim into every Daily Drop. Update the URL descriptions for the specific deliverable:

```
Section C: Submission Protocol (Due strictly by 2:00 PM)

Step 1 (The Kanban Board):
Verify that this specific task card is shifted to the "Under Review" column on your team board.

Step 2 (The Assignments Module):
Navigate directly to today's entry in the platform's independent Assignment block.
Paste both [DELIVERABLE 1 URL description] and your live LinkedIn Post URL into the
designated submission form fields.

Operational Note:
All grading and technical feedback loops operate through this portal.
Do not DM Management your links.
```

---

## How to Create and Upload a New Daily Drop

### Step 1 — Choose the correct template
Open the appropriate field template from this folder:
- `ai_ml_template.md` → AI & Machine Learning squad
- `full_stack_template.md` → Full Stack (React/Next.js/TypeScript) squad
- `cyber_template.md` → Cyber Security squad
- `cpp_systems_template.md` → C++ Systems Engineering squad
- `qa_template.md` → QA Engineering squad

### Step 2 — Fill in the variables
Every template has `{{PLACEHOLDERS}}` in double-braces. Replace each one:

| Placeholder | What to put |
|-------------|-------------|
| `{{WEEK_NUM}}` | e.g., 1, 2, 3 |
| `{{DAY_NUM}}` | e.g., 1, 2, 3, 4, 5 |
| `{{DAY_NAME}}` | e.g., Monday, Tuesday |
| `{{DAY_ORDINAL}}` | e.g., 1st, 2nd, 3rd |
| `{{WEEK_MANDATE}}` | One-line description of this week's focus |
| `{{PROGRESS_PCT}}` | Calculated % (see formula above) |
| `{{COMPLETED_SLOTS}}` | Number of days done so far |
| `{{TOTAL_SLOTS}}` | Total bootcamp days (default: 20) |
| `{{TASK_TITLE}}` | Full descriptive task title |
| `{{VIDEO_1_PLATFORM}}` | Scrimba / YouTube |
| `{{VIDEO_1_TITLE}}` | Full module/video title |
| `{{VIDEO_1_NOTE}}` | Why interns should watch it |
| `{{DOC_1_TITLE}}` | Official docs title |
| `{{DOC_1_URL_DESC}}` | What to look for in those docs |
| `{{ASSET_1_TITLE}}` | Asset name (dataset, Figma file, etc.) |
| `{{OBJECTIVE}}` | TL;DR objective paragraph |
| `{{ACTION_1}}` | First building action (Setup/Init) |
| `{{ACTION_2}}` | Second building action (Core Logic) |
| `{{ACTION_3}}` | Third building action (Integration) |
| `{{BAN_1}}` | First non-negotiable rule + consequence |
| `{{BAN_2}}` | Second non-negotiable rule + consequence |
| `{{HYGIENE}}` | What to clean up and push |
| `{{LINKEDIN}}` | What to write about on LinkedIn |
| `{{SUBMISSION_URL_DESC}}` | Description of what URL to paste in Step 2 |

### Step 3 — Assign the task on the portal
1. Log into ProSensia as **mentor** or **super_admin**
2. Go to **Assign Task** (sidebar)
3. Fill in:
   - **Task Title** — same as `{{TASK_TITLE}}`
   - **Description** — paste the full Section B text
   - **Target Field** — select the correct domain (AI/ML, Full Stack, etc.)
   - **Video URL** — paste the primary video URL (Section A, Video 1)
   - **Task Date** — set to today or the target day
4. Click **Assign Task**

The portal will automatically:
- Show the task only to interns in that field
- Lock it behind the daily unlock timer (set in Portal Settings)
- Enable the character wizard story flow for that task
- Log all status changes to the Task Version Log

### Step 4 — Save the PDF
Export your completed template to PDF and save it following this naming convention:
```
DailyTasks/Week{N}/Day{N}/Daily Drop - {Field} Day {N}.pdf
```
Example: `DailyTasks/Week2/Day3/Daily Drop - Full Stack Day 3.pdf`

---

## Field Codes (for `target_field` dropdown)

| Field Code | Squad Name |
|------------|-----------|
| `AI/ML` | AI & Machine Learning Engineering |
| `Full Stack` | Full Stack Web Development |
| `Cyber` | Cyber Security Task Force |
| `C++` | Systems Engineering (C++) |
| `QA` | QA Engineering |
| `IoT` | Internet of Things |
| (blank) | All interns — cross-domain announcement |

---

## LinkedIn Post Requirements (Standard)

Every intern's LinkedIn post must include:
1. A technical summary of what they built/learned (not just "completed a task")
2. At least one specific concept explained (e.g., "why parameterized queries prevent SQLi")
3. A hyperlink to their **ProSensia admit card** within the post body
4. Relevant hashtags (auto-generated by the platform's Daily Social Post tool)

---

## Quality Checklist Before Publishing a Daily Drop

- [ ] Progress % is correct for this day
- [ ] Section A has at least 2 video links + 2 docs + 1 engineering asset
- [ ] Section B has all 4 time-blocked phases
- [ ] Building phase has exactly 3 sequential actions (Action 1 → 2 → 3)
- [ ] At least 2 Non-Negotiable Rules are defined
- [ ] LinkedIn step mentions ProSensia admit card
- [ ] Section C submission protocol is complete and correct
- [ ] PDF is saved to the correct folder path
- [ ] Task assigned on portal with correct `target_field`
- [ ] Task date set correctly on portal
