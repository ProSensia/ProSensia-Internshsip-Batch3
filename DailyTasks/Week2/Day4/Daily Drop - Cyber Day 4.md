# 🔐 Cybersecurity Squad — Week 2, Day 4 (Thursday)
**Title:** SQL Injection — Detection, Exploitation & Parameterised Query Defence
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: Server-Side Injection Attacks

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** NetworkChuck — SQL Injection Attack (Explained) https://www.youtube.com/watch?v=ciNHn38EyRc
- **YouTube:** John Hammond — SQL Injection (CTF Walkthrough) https://www.youtube.com/watch?v=1nJgupaUPEQ
- **PortSwigger:** SQL Injection Learning Path https://portswigger.net/web-security/sql-injection

### 2. Required Technical Documentation
- **Primary:** OWASP SQL Injection Prevention Cheat Sheet https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html
- **Secondary:** PortSwigger SQLi Cheat Sheet https://portswigger.net/web-security/sql-injection/cheat-sheet
- **Reference:** OWASP Testing Guide — SQL Injection https://owasp.org/www-project-web-security-testing-guide/

### 3. Engineering Assets
- **Lab Platform:** PortSwigger Web Security Academy (free account)
- **Tool:** Burp Suite Community Edition (HTTP interception required)
- **Secondary Tool:** sqlmap (for comparison ONLY — see Non-Negotiable Rules)

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Understand and manually exploit three SQL injection vectors on PortSwigger labs. Write a remediation plan using parameterised queries. Understand the difference between first-order and second-order SQLi.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both videos and complete PortSwigger's SQLi learning path reading. Study the OWASP prevention cheatsheet. Understand: UNION-based extraction, blind SQLi (boolean/time-based), and the database comment syntax differences (Oracle, MySQL, MSSQL, PostgreSQL).

10:00 AM - 1:00 PM (Building):
Action 1 (Classic SQLi): Complete PortSwigger lab "SQL injection vulnerability in WHERE clause allowing retrieval of hidden data". Use Burp Suite to intercept the request. Document the vulnerable parameter and your UNION payload that extracts the hidden category filter.
Action 2 (Login Bypass): Complete "SQL injection vulnerability allowing login bypass". Demonstrate how ' OR 1=1 -- breaks authentication. Explain in your writeup why this works at the SQL level, not just "it bypasses the check."
Action 3 (Blind SQLi + Fix): Complete one blind SQLi lab (boolean-based or time-based). Then write a side-by-side comparison in sqli_findings.md: vulnerable PHP code vs. fixed code using PDO prepared statements. Show that parameterised queries make UNION injection structurally impossible.

The Non-Negotiable Rules:
1. All exploitation must be manual via Burp Suite — running sqlmap --dump on a lab is not learning.
2. Your writeup must explain the WHY at the SQL query level, not just "it worked."
3. Remediation code examples must use real parameterised queries (PDO/mysqli prepared statements) — "validate all inputs" is not a remediation.

1:00 PM - 1:30 PM (Hygiene): Organise findings in sqli_findings.md. Include: vulnerable query, exploitation payload, HTTP request screenshot, fixed parameterised query code. Commit to GitHub.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 Cyber at ProSensia — exploited SQL injection on 3 PortSwigger labs manually. Key insight: parameterised queries don't just sanitise — they structurally separate data from SQL syntax, making injection architecturally impossible."

---

## Section C: The Submission Protocol
1. **GitHub:** Push sqli_findings.md with commit: docs: SQL injection lab findings and parameterised query remediation
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #SQLInjection #WebSecurity #EthicalHacking
