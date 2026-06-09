# 🎨 Graphic Design & UI/UX Squad — Week 2, Day 4 (Thursday)
**Title:** Usability Testing, Iteration & Design Handoff Documentation
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: Design Validation & Developer Handoff

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** NNGroup — Usability Testing 101 https://www.youtube.com/watch?v=0YL0xoSmyZI
- **YouTube:** AJ&Smart — How to Run a UX Usability Test (5-second test, think-aloud) https://www.youtube.com/watch?v=0pv_TRKV8hc
- **YouTube:** Figma — Developer Handoff and Inspect Panel https://www.youtube.com/watch?v=B242nuM3y2s

### 2. Required Technical Documentation
- **Primary:** Nielsen Norman Group — 10 Usability Heuristics https://www.nngroup.com/articles/ten-usability-heuristics/
- **Secondary:** Figma — Design Tokens and Handoff https://help.figma.com/hc/en-us/articles/360040451373-Explore-auto-layout-properties
- **Reference:** Stark Figma Plugin — Accessibility Checker https://www.figma.com/community/plugin/732603254453395948/Stark

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Conduct a structured heuristic evaluation of your Day 3 hi-fi design using the 10 Nielsen Norman heuristics. Identify at least 3 usability issues, iterate the design to fix them, and prepare a complete developer handoff document.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Study all 10 Nielsen heuristics — not just the names but the failure modes. Examples: "visibility of system status" fails when a button doesn't have a loading state; "error prevention" fails when a destructive action has no confirmation.

10:00 AM - 1:00 PM (Building):
Action 1 (Heuristic Evaluation): Evaluate your Day 3 hi-fi design against all 10 Nielsen heuristics. For each of the 3 most severe issues you find: document the heuristic violated, describe the failure (what the user would experience), and rate severity 1-4 (1=cosmetic, 4=usability catastrophe). Record this in a usability_audit.md file.
Action 2 (Design Iteration): Fix all issues rated severity 3 or higher in Figma. Update your component library if the fix requires a component change. Take before/after screenshots for each fix. Annotate them with the heuristic and what changed.
Action 3 (Developer Handoff): Create a Figma "Handoff" page with: annotated specs for all interactive components (tap targets min 44px, colour contrast ratios), exported SVG icons, font specimen showing the complete type scale, spacing grid reference, and a component usage guide (do/don't examples for your 3 main components).

The Non-Negotiable Rules:
1. Heuristic evaluation must cover all 10 heuristics — not just the ones where you find problems.
2. All interactive elements must have minimum 44x44px tap target (WCAG AA touch requirement).
3. Developer handoff Figma page must have exact measurements — no component without annotated dimensions and spacing.

1:00 PM - 1:30 PM (Hygiene): Run the Stark accessibility plugin on all three screens. Fix any WCAG AA contrast failures. Confirm all layers are named. Export your handoff document as a PDF from Figma (File > Export PDF).

1:30 PM - 2:00 PM (LinkedIn): "Day 4 Design at ProSensia — ran a full heuristic evaluation against Nielsen's 10 principles. Found 3 severity-3 issues in my own hi-fi. Design is never done — iteration based on structured evaluation is what separates professional design from decoration."

---

## Section C: The Submission Protocol
1. **GitHub:** Push usability_audit.md, before/after screenshots with commit: docs: heuristic evaluation and design iteration
2. **Figma:** Ensure Handoff page is complete and share link
3. **Kanban:** Move to "Under Review"
4. **LinkedIn:** Post with #ProSensia #UXDesign #UsabilityTesting #HeuristicEvaluation
