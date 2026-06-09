# 🔐 Cybersecurity Squad — Week 2, Day 5 (Friday)
**Title:** Week 2 Review — Vulnerability Report, OWASP Top 10 Summary & CTF Challenge
**Progress:** 20% · Week 2 of 3-Month Bootcamp · Focus: Consolidation & Professional Reporting

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** TCM Security — How to Write a Professional Penetration Test Report https://www.youtube.com/watch?v=EOoBAq6z4os
- **YouTube:** David Bombal — OWASP Top 10 2021 Explained https://www.youtube.com/watch?v=1k0e1bVzBFs
- **PortSwigger:** Web Security Academy — All Labs Dashboard https://portswigger.net/web-security/all-labs

### 2. Required Technical Documentation
- **Primary:** OWASP Top 10 — 2021 Edition https://owasp.org/www-project-top-ten/
- **Secondary:** CVSS v3.1 Score Calculator https://www.first.org/cvss/calculator/3.1
- **Reference:** HackTricks — Web Application Pentesting https://book.hacktricks.xyz/pentesting-web/web-vulnerabilities-methodology

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Consolidate your Week 2 findings (XSS Day 3, SQLi Day 4) into a professional penetration test report. Map each finding to the OWASP Top 10 and assign a CVSS v3.1 score. Complete a bonus CTF challenge from PortSwigger.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch the TCM Security report-writing video in full — this is how professional pentesters communicate findings. Study the OWASP Top 10 2021 list — know the category for XSS (A03) and SQLi (A03). Read the CVSS scoring guide.

10:00 AM - 1:00 PM (Building):
Action 1 (Professional Report): Create week2_pentest_report.md with these sections: Executive Summary (2 paragraphs, non-technical), Scope & Methodology, Findings Table (ID, Title, OWASP Category, CVSS Score, Severity), Detailed Findings (one per vulnerability: description, evidence screenshot, CVSS breakdown, remediation steps), and Conclusion. This document must be professional enough to show to a client.
Action 2 (CVSS Scoring): Calculate CVSS v3.1 base scores for your XSS (Reflected) and SQLi (Login Bypass) findings. Document the Attack Vector, Privileges Required, User Interaction, and Impact scores. Show your working.
Action 3 (CTF Challenge): Complete one "Practitioner" level PortSwigger lab you haven't done yet. Options: SSRF, XXE, or OS command injection. Add it as a third finding in your report.

The Non-Negotiable Rules:
1. CVSS scores must be calculated — not guessed. Use the online calculator and show your values.
2. The Executive Summary must be written for a non-technical manager — no jargon.
3. Every finding must have a concrete, implementable remediation, not "patch the application."

1:00 PM - 1:30 PM (Hygiene): Spell-check the report. Ensure all screenshot filenames are descriptive (not "screenshot1.png"). Verify no real credentials or PII appear in any screenshots. Convert to PDF for the "client-ready" format.

1:30 PM - 2:00 PM (LinkedIn): "Week 2 complete at ProSensia Cyber squad — XSS, SQLi, and a new SSRF finding. A vulnerability without a CVSS score and a remediation plan is not a finding — it's a note. Professional security is about communication, not just exploitation."

---

## Section C: The Submission Protocol
1. **GitHub:** Push week2_pentest_report.md with commit: docs: week 2 professional pentest report
2. **Kanban:** Move ALL this week's cards to "Done"
3. **LinkedIn:** Post with #ProSensia #CyberSecurity #PenetrationTesting #OWASP
