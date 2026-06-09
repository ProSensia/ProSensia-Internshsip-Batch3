# 💻 Full Stack Squad — Week 2, Day 4 (Thursday)
**Title:** Backend Integration — REST API with Next.js Route Handlers & Database
**Progress:** 15% · Week 2 of 3-Month Bootcamp · Focus: Full Stack Data Flow

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Fireship — Next.js App Router in 100 Seconds https://www.youtube.com/watch?v=gSSsZReIFRk
- **YouTube:** Josh tried coding — Build a Full Stack Next.js 14 App https://www.youtube.com/watch?v=6-X15CQHC8I
- **YouTube:** Web Dev Simplified — Learn Prisma in 60 Minutes https://www.youtube.com/watch?v=RebA5J-rlwg

### 2. Required Technical Documentation
- **Primary:** Next.js — Route Handlers https://nextjs.org/docs/app/building-your-application/routing/route-handlers
- **Secondary:** Prisma — Getting Started https://www.prisma.io/docs/getting-started/setup-prisma/start-from-scratch
- **Reference:** Zod — Schema Validation https://zod.dev/

### 3. Engineering Assets
- **Database:** SQLite (via Prisma) for local dev — zero configuration required
- **ORM:** Prisma (npm install prisma @prisma/client)
- **Validation:** Zod (npm install zod)
- **Continue:** Extend your React Query dashboard from Day 3

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Build a real backend with Next.js Route Handlers. Create a Prisma schema with a Post model, wire it to SQLite, and connect your Day 3 React Query hook to your own API instead of JSONPlaceholder. Replace mock data with real database reads and writes.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch all three videos. Understand the app/api/ directory structure, how route.ts files define GET/POST handlers, and why Prisma is better than raw SQL for type safety. Focus on the Zod video for input validation patterns.

10:00 AM - 1:00 PM (Building):
Action 1 (Database Setup): Run npx prisma init. Define a Post model in schema.prisma with: id Int @id @default(autoincrement()), title String, body String, createdAt DateTime @default(now()). Run npx prisma migrate dev --name init. Seed 5 posts with npx prisma db seed.
Action 2 (API Routes): Create app/api/posts/route.ts with GET (return all posts from Prisma) and POST (create new post). Create app/api/posts/[id]/route.ts with GET (single post) and DELETE. Use Zod to validate POST body: title min 3 chars, body min 10 chars.
Action 3 (Frontend Integration): Update your Day 3 useMetrics() hook to call /api/posts instead of JSONPlaceholder. The TypeScript interface must now match your Prisma Post type exactly (use Prisma generated types). Add a simple form to create a new post — it should call POST /api/posts and invalidate the React Query cache on success.

The Non-Negotiable Rules:
1. No direct database calls from React components — all DB access goes through Route Handlers.
2. All POST/PUT/DELETE endpoints must validate input with Zod before touching the database.
3. TypeScript must use the Prisma-generated types — no manual interface that duplicates the schema.

1:00 PM - 1:30 PM (Hygiene): Run npm run build — fix all TypeScript errors. Check that Prisma migrations are in the prisma/migrations/ folder and committed. Verify .env (with DATABASE_URL) is in .gitignore.

1:30 PM - 2:00 PM (LinkedIn): "Day 4 Full Stack at ProSensia — connected React to a real database via Next.js Route Handlers. End-to-end type safety with Prisma + Zod + TypeScript: the API literally can't receive data that doesn't match the schema."

---

## Section C: The Submission Protocol
1. **GitHub:** Push with commit: feat: add Prisma schema, route handlers, and DB-connected frontend
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #NextJS #Prisma #TypeScript #FullStack
