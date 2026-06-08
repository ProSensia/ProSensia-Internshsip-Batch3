# 🧪 QA Engineering Squad: Progress Metrics

> **Current Progress:** {{PROGRESS_PCT}}% ({{COMPLETED_SLOTS}} out of {{TOTAL_SLOTS}} core bootcamp slots completed).
> **Current Focus:** Week {{WEEK_NUM}} Mandate → {{WEEK_MANDATE}}.

---

## 🗂️ The Daily Drop Blueprint

**Title:** QA Engineering Squad: Week {{WEEK_NUM}}, {{DAY_NAME}} — {{TASK_TITLE}}

---

## 📚 Section A: Today's Materials (The Synthesis Mandate)

> You must synthesize the information from all sources below to complete today's execution mandate. **Do not consult outside tutorials.**

### 1. Video Drills (Watch These First)

- **Scrimba Anchor 1:** {{SCRIMBA_1_COURSE}} — Module: "{{SCRIMBA_1_MODULE}}"
  *({{SCRIMBA_1_NOTE}})*

- **Scrimba Anchor 2:** {{SCRIMBA_2_COURSE}} — Module: "{{SCRIMBA_2_MODULE}}"
  *({{SCRIMBA_2_NOTE}})*

- **External Video 1:** YouTube: {{EXT_VIDEO_1}} *({{EXT_VIDEO_1_NOTE}})*

- **External Video 2:** YouTube: {{EXT_VIDEO_2}} *({{EXT_VIDEO_2_NOTE}})*

### 2. Required Technical Documentation

- **Primary Architecture Doc:** OWASP {{OWASP_DOC}} *(The industry benchmark for {{OWASP_PURPOSE}})*
- **Specific Syntax Doc:** {{SYNTAX_DOC}} *(Required for {{SYNTAX_PURPOSE}})*
- **Configuration Doc:** {{CONFIG_DOC}} *({{CONFIG_PURPOSE}})*

### 3. Engineering Assets

- **Requirements Asset:** {{REQUIREMENTS_ASSET}} *(The ground truth for all test cases — no test exists without a linked requirement)*
- **LLM Prompting Asset:** OpenAI Official Guide: Prompt Engineering
  *(Use this to generate edge-case permutations — then validate each case by hand)*
- **Reporting Asset:** Your live **Test Case Matrix (Google Sheets)** *(All findings must be logged here — not in a local file)*

---

## ⚡ Section B: The Execution Mandate ({{UNLOCK_TIME}} – 2:00 PM)

### 1. Execution Summary (TL;DR)

**Objective:** {{OBJECTIVE_PARAGRAPH}}

### 2. The Workflow

**{{UNLOCK_TIME}} – 10:00 AM (Learning)**
Watch the **{{PRIMARY_TOPIC}}** drills. Synthesize the **{{PRIMARY_DOC}}** documentation on **{{DOC_FOCUS}}**. Understand the theory of **{{THEORY_TOPIC}}** before designing a single test case.

**10:00 AM – 1:00 PM (Building)**

- **Action 1 (Setup & Organization):** Open your Test Case Matrix. {{SETUP_DETAIL}} Categorize all existing and new test cases into strict suites: **({{SUITE_NAMES}})**.

- **Action 2 ({{AI_OR_MANUAL}} Generation):** {{GENERATION_DETAIL}}
  You **must** include extreme boundary cases:
  - Arabic/RTL strings, emoji-only inputs, zero-width characters
  - 10,000+ character inputs in text fields
  - Negative integers and floating-point numbers in numeric fields
  - SQL injection strings (`' OR 1=1 --`, `'; DROP TABLE users; --`)
  - JavaScript injection strings (`<script>alert(1)</script>`)

- **Action 3 ({{VALIDATION_TYPE}}):** {{VALIDATION_DETAIL}} Map every test case to its corresponding PRD requirement using a traceability matrix. A test case with no linked requirement is invalid.

**The Non-Negotiable Rules:**

1. **The "Happy Path" Ban:** Testing only valid, expected inputs is an **automatic failure**. You must demonstrate **malicious creativity** — boundary violations, injection strings, and extreme permutations are the core of today's execution. Positive testing is table stakes; negative testing is the mandate.

2. **The AI Defense (Interrogation Standard):** If you use Copilot or ChatGPT to generate {{BAN_2_WHAT}}, you must be able to **mathematically explain** the logic. If a Technical Mentor asks what **{{INTERROGATION_QUESTION}}** and you cannot articulate the answer, your ticket fails and your sprint velocity is penalized.

**1:00 PM – 1:30 PM (Hygiene)**
Verify all edge cases are correctly linked to PRD requirements in your traceability matrix. Push your updated matrix to the **shared QA repository** on GitHub. Confirm the Google Sheets link is publicly accessible.

**1:30 PM – 2:00 PM (LinkedIn)**
Draft a technical summary of your execution. Detail **{{LINKEDIN_CONCEPT}}** and explain **why {{LINKEDIN_WHY}}** is a critical tool for Software Test Engineers. You **must** hyperlink your ProSensia admit card within the post text.

---

## 📋 Section C: Submission Protocol *(Due strictly by 2:00 PM)*

**Step 1 (The Kanban Board):**
Verify that this specific task card is shifted to the **"Under Review"** column on your team board.

**Step 2 (The Assignments Module):**
Navigate directly to today's entry in the platform's independent Assignment block. Paste both the **public link to your updated Test Case Matrix (Google Sheets)** and your **live LinkedIn Post URL** into the designated submission form fields.

> **Operational Note:** All grading and technical feedback loops operate through this portal. **Do not DM Management your links.**

---

## 🔧 Mentor Fill-In Guide (remove before publishing)

| Placeholder | Instructions |
|-------------|-------------|
| `{{WEEK_MANDATE}}` | e.g., "Edge-Case Generation, Boundary Value Analysis, Regex Validation" |
| `{{TASK_TITLE}}` | e.g., "AI Edge-Case Generation & Regex Data Validation" |
| `{{SCRIMBA_1_MODULE}}` | Specific Scrimba testing module |
| `{{OWASP_DOC}}` | e.g., "Data Validation Cheat Sheet" |
| `{{REQUIREMENTS_ASSET}}` | e.g., "Product Requirements Document (PRD) from Day 1" |
| `{{SUITE_NAMES}}` | e.g., "Authentication, Input Validation, Checkout, API Responses" |
| `{{AI_OR_MANUAL}}` | "AI Edge-Case" or "Manual Test Case" |
| `{{GENERATION_DETAIL}}` | Full description of how to generate cases |
| `{{VALIDATION_TYPE}}` | e.g., "Regex Construction" or "API Testing" |
| `{{VALIDATION_DETAIL}}` | What to validate and how |
| `{{BAN_2_WHAT}}` | e.g., "Regular Expressions" |
| `{{INTERROGATION_QUESTION}}` | e.g., "what the `^` or `$` symbols do in your regex" |
| `{{LINKEDIN_CONCEPT}}` | e.g., "Boundary Value Analysis" |
| `{{LINKEDIN_WHY}}` | e.g., "Regex" |
