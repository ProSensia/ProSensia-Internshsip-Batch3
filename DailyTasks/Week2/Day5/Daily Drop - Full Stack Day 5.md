# 💻 Full Stack Squad — Week 2, Day 5 (Friday)
**Title:** Week 2 Review — Deploy to Vercel, Performance Audit & Demo
**Progress:** 20% · Week 2 of 3-Month Bootcamp · Focus: Deployment & Production Readiness

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Vercel — Deploy Next.js in 3 Minutes (Official) https://www.youtube.com/watch?v=2HBIzEx6IZA
- **YouTube:** Web Dev Simplified — Next.js Performance Optimisation https://www.youtube.com/watch?v=0aTRN9CSCY0
- **YouTube:** Fireship — Lighthouse 101 https://www.youtube.com/watch?v=NoRYn6gOtVo

### 2. Required Technical Documentation
- **Primary:** Vercel — Deploying Next.js https://vercel.com/docs/frameworks/nextjs
- **Secondary:** Next.js — Image Optimisation https://nextjs.org/docs/app/building-your-application/optimizing/images
- **Reference:** web.dev — Core Web Vitals https://web.dev/vitals/

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Deploy your Week 2 Next.js project to Vercel with a live public URL. Run a Lighthouse audit and fix the top 3 performance/accessibility issues. Write a deployment README and demo the live app.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Understand: how Vercel builds Next.js, the difference between Serverless Functions and Edge Functions for Route Handlers, and what LCP/CLS/FID mean in Lighthouse.

10:00 AM - 1:00 PM (Building):
Action 1 (Deploy): Create a Vercel account. Import your GitHub repository. Configure the build settings (should be auto-detected for Next.js). Set the DATABASE_URL environment variable to a production SQLite or Vercel Postgres free tier database. Deploy and confirm the live URL works.
Action 2 (Lighthouse Audit): Open Chrome DevTools → Lighthouse. Run Performance + Accessibility + Best Practices audit on your live deployment. Screenshot the scores. Fix the top 3 issues flagged: typically Image width/height missing, missing alt text, or large JS bundle. Re-run and confirm scores improved.
Action 3 (Polish): Add these production essentials if missing: favicon, metadata (title and description in layout.tsx using Next.js Metadata API), Open Graph image for social sharing, 404 not-found.tsx page, and a loading.tsx skeleton for the main data page.

The Non-Negotiable Rules:
1. The live deployment must work — a broken Vercel deploy is not a submission.
2. Lighthouse Performance score must be 70+ after your fixes.
3. All images on the app must use Next.js <Image> component — no raw <img> tags.

1:00 PM - 1:30 PM (Hygiene): Remove any console.log() statements. Ensure no API keys are in the deployed code. Run npm run build locally one final time. Tag the commit v0.1-week2 on GitHub.

1:30 PM - 2:00 PM (LinkedIn): "Week 2 complete at ProSensia — deployed a full-stack Next.js app to Vercel with Prisma database, React Query, and TypeScript. Lighthouse Performance: 82. The deploy URL is live. This is what 'working software' looks like."

---

## Section C: The Submission Protocol
1. **GitHub:** Tag commit v0.1-week2. Add live Vercel URL to repository About/description.
2. **Kanban:** Move ALL this week's cards to "Done"
3. **LinkedIn:** Post with live URL + Lighthouse screenshot + #ProSensia #NextJS #Vercel #FullStack
