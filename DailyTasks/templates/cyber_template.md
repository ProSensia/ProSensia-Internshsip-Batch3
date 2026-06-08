# 🔐 Cyber Security Task Force: Progress Metrics

> **Current Progress:** {{PROGRESS_PCT}}% ({{COMPLETED_SLOTS}} out of {{TOTAL_SLOTS}} core bootcamp slots completed).
> **Current Focus:** Week {{WEEK_NUM}} Mandate → {{WEEK_MANDATE}}.

---

## 🗂️ The Daily Drop Blueprint

**Title:** Cyber Security Task Force: Week {{WEEK_NUM}}, {{DAY_NAME}} — {{TASK_TITLE}}

---

## 📚 Section A: Today's Materials (The Synthesis Mandate)

> You must synthesize the information from all sources below to complete today's execution mandate. **Do not consult outside tutorials.**

### 1. Video Drills (Watch These First)

- **Scrimba Anchor 1:** Learn Cybersecurity — Module: "{{SCRIMBA_MODULE}}"
  *({{SCRIMBA_NOTE}})*

- **External Video 1:** YouTube: {{EXT_VIDEO_1}} — {{EXT_VIDEO_1_NOTE}}

- **External Video 2:** YouTube: {{EXT_VIDEO_2}} — {{EXT_VIDEO_2_NOTE}}

### 2. Required Technical Documentation

- **Primary Architecture Doc:** OWASP {{OWASP_DOC}} *(The enterprise standard for {{OWASP_PURPOSE}})*
- **Specific Syntax/Rule Doc:** PortSwigger Web Security: {{PORTSWIGGER_DOC}} *(Required for {{PORTSWIGGER_PURPOSE}})*
- **Configuration Doc:** {{CONFIG_DOC}} *({{CONFIG_DOC_PURPOSE}})*

### 3. Engineering Assets

- **Vulnerable Target Asset:** {{VULNERABLE_ASSET}} *({{ASSET_NOTE}})*
- **LLM Prompting Asset:** OpenAI Official Guide: Prompt Engineering
  *(Use this to ask the LLM to explain the mechanics of a specific CVE or summarize remediation logic — never to bypass defenses)*
- **Reporting Asset:** Vulnerability Report Template (Google Docs) *(Use the exact structure from the template — do not invent your own format)*

---

## ⚡ Section B: The Execution Mandate ({{UNLOCK_TIME}} – 2:00 PM)

### 1. Execution Summary (TL;DR)

**Objective:** {{OBJECTIVE_PARAGRAPH}}

### 2. The Workflow

**{{UNLOCK_TIME}} – 10:00 AM (Learning)**
Watch the **{{PRIMARY_VIDEO_TOPIC}}** drills. Read the OWASP documentation to understand **why {{DEFENSE_CONCEPT}} alone is insufficient** compared to the correct architecture.

**10:00 AM – 1:00 PM (Building & Exploiting)**

- **Action 1 (Environment Setup):** {{ENV_SETUP_DETAIL}} Verify you can intercept and modify the target HTTP traffic.

- **Action 2 (Manual Exploitation):** {{EXPLOITATION_DETAIL}} Document every step — your PoC must be reproducible by a junior analyst following your report alone.

- **Action 3 (Forensic Reporting):** Draft a formal **Vulnerability Report** using the standard template. You must include:
  - **CVSS severity score** with justification
  - **Exact proof-of-concept** (request/response snippet or screenshot)
  - **Architectural fix** — the specific secure coding pattern required

**The Non-Negotiable Rules:**

1. **The Automated Tool Ban:** You are strictly **forbidden** from using automated exploitation tools (e.g., {{BANNED_TOOL}}). You must {{MANUAL_REQUIREMENT}}. This proves you understand the underlying mechanism — not just how to pull a trigger.

2. **The Remediation Logic Rule:** Finding the vulnerability is only half the job. Your report **cannot** just state "Fix the {{VULN_TYPE}}." You must provide the **exact secure coding pattern** (e.g., parameterized queries, Content Security Policy headers) required to patch the vulnerability. Theoretical findings without architectural remediation fail automatically.

**1:00 PM – 1:30 PM (Hygiene)**
Verify your PoC reproduction steps are **crystal clear** — another analyst must be able to reproduce the finding with zero ambiguity. Push your markdown-formatted Vulnerability Report to your internal Security repository on GitHub.

**1:30 PM – 2:00 PM (LinkedIn)**
Draft a technical summary detailing **{{LINKEDIN_TECHNICAL_TOPIC}}**. Explain the architectural difference between **{{WRONG_APPROACH}}** and **{{CORRECT_APPROACH}}**. You **must** hyperlink your ProSensia admit card within the post text.

---

## 📋 Section C: Submission Protocol *(Due strictly by 2:00 PM)*

**Step 1 (The Kanban Board):**
Verify that this specific task card is shifted to the **"Under Review"** column on your team board.

**Step 2 (The Assignments Module):**
Navigate directly to today's entry in the platform's independent Assignment block. Paste both the **link to your security repository report** and your **live LinkedIn Post URL** into the designated submission form fields.

> **Operational Note:** All grading and technical feedback loops operate through this portal. **Do not DM Management your links.**

---

## 🔧 Mentor Fill-In Guide (remove before publishing)

| Placeholder | Instructions |
|-------------|-------------|
| `{{WEEK_MANDATE}}` | e.g., "Application Layer Vulnerabilities & HTTP Manipulation" |
| `{{TASK_TITLE}}` | e.g., "Manual SQL Injection & Burp Suite Interception" |
| `{{SCRIMBA_MODULE}}` | Specific Scrimba Cybersecurity module name |
| `{{OWASP_DOC}}` | e.g., "SQL Injection Prevention Cheat Sheet" |
| `{{PORTSWIGGER_DOC}}` | e.g., "SQL Injection Cheat Sheet" |
| `{{VULNERABLE_ASSET}}` | e.g., "OWASP Juice Shop", "DVWA", "HackTheBox Machine" |
| `{{BANNED_TOOL}}` | e.g., "SQLMap", "Nikto", "Metasploit" |
| `{{MANUAL_REQUIREMENT}}` | What must be done manually instead |
| `{{VULN_TYPE}}` | e.g., "SQLi", "XSS", "CSRF" |
| `{{WRONG_APPROACH}}` | e.g., "simple input sanitization" |
| `{{CORRECT_APPROACH}}` | e.g., "parameterized queries / prepared statements" |
| `{{LINKEDIN_TECHNICAL_TOPIC}}` | What security concept to explain on LinkedIn |
