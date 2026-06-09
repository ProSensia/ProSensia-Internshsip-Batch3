# 🔐 Cybersecurity Squad — Week 2, Day 3 (Wednesday)
**Title:** Cross-Site Scripting (XSS) — Reflected, Stored & DOM-Based Attacks
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: Client-Side Injection Attacks

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** NetworkChuck — XSS (Cross Site Scripting) — Explained https://www.youtube.com/watch?v=EoaDgUgS6QA
- **YouTube:** LiveOverflow — XSS on Bug Bounty Programs https://www.youtube.com/watch?v=lG7U3fuNw3A
- **PortSwigger:** XSS Learning Path (free) https://portswigger.net/web-security/cross-site-scripting

### 2. Required Technical Documentation
- **Primary:** OWASP XSS Prevention Cheat Sheet https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html
- **Secondary:** MDN — Content Security Policy (CSP) https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
- **Lab Platform:** PortSwigger Web Security Academy https://portswigger.net/web-security/all-labs

### 3. Engineering Assets
- **Lab Environment:** PortSwigger Academy (free account required — create now if not done)
- **Tool:** Burp Suite Community Edition (must be configured and running)

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Understand and manually exploit the three XSS attack vectors (Reflected, Stored, DOM-based) using PortSwigger labs. Write a technical remediation report for each vector. No automated scanner output accepted.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both videos. Complete PortSwigger's XSS learning path reading (theory sections only — save labs for building phase). Read the OWASP prevention cheatsheet in full.

10:00 AM - 1:00 PM (Building):
Action 1 (Reflected XSS): Complete PortSwigger Lab "Reflected XSS into HTML context with nothing encoded". Capture the HTTP request in Burp Suite. Document the vulnerable parameter and your payload.
Action 2 (Stored XSS): Complete "Stored XSS into HTML context with nothing encoded". Explain why stored XSS is more dangerous than reflected. Document the injection point (comment field, form field, etc.).
Action 3 (DOM XSS + CSP): Complete "DOM XSS in document.write sink using source location.search". Then write a 200-word remediation plan: how would you fix all three vulnerabilities using CSP headers and output encoding?

The Non-Negotiable Rules:
1. Automated scanner tools (OWASP ZAP auto-scan, nikto) are banned — all payloads must be crafted manually.
2. Every lab finding must include: vulnerable parameter, payload used, HTTP request screenshot, and remediation.
3. Your remediation report must cite OWASP prevention techniques — generic "sanitize inputs" is not acceptable.

1:00 PM - 1:30 PM (Hygiene): Organise all findings into a markdown report xss_findings.md. Commit to GitHub. Verify no credentials or tokens in the repo.

1:30 PM - 2:00 PM (LinkedIn): "Completed 3 XSS labs today at ProSensia — Reflected, Stored, and DOM-based. The DOM-based vector is the most dangerous because it never hits the server. CSP headers are the modern mitigation layer."

---

## Section C: The Submission Protocol
1. **GitHub:** Push xss_findings.md with commit: docs: XSS lab findings and remediations
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #CyberSecurity #XSS #EthicalHacking
