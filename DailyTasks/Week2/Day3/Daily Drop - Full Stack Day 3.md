# 💻 Full Stack Squad — Week 2, Day 3 (Wednesday)
**Title:** React State Management & Real API Integration
**Progress:** 10% · Week 2 of 3-Month Bootcamp · Focus: State, Hooks & Data Fetching

---

## Section A: Today's Materials (The Synthesis Mandate)

### 1. Video Drills (Watch These First)
- **YouTube:** Codevolution — React Query Tutorial for Beginners https://www.youtube.com/watch?v=VtWkSCZX0Ec
- **YouTube:** Jack Herrington — useReducer + useContext (State Architecture) https://www.youtube.com/watch?v=kK_Wqx3RnHk
- **Scrimba:** Learn React — "useState hook 10:31" & "useEffect hook 14:09" https://v2.scrimba.com/learn-react-c0e

### 2. Required Technical Documentation
- **Primary:** TanStack Query (React Query) Official Docs https://tanstack.com/query/latest/docs/framework/react/overview
- **Secondary:** Next.js — Data Fetching with fetch() https://nextjs.org/docs/app/building-your-application/data-fetching
- **TypeScript:** TypeScript Generics for API Responses https://www.typescriptlang.org/docs/handbook/2/generics.html

### 3. Engineering Assets
- **Free API:** JSONPlaceholder REST API https://jsonplaceholder.typicode.com
- **Design Ref:** Continue from your Monday DashboardCard component

---

## Section B: The Execution Mandate (9:00 AM - 2:00 PM)

### 1. Execution Summary (TL;DR)
Objective: Extend your DashboardCard component to fetch and display live data from a public REST API using React Query, define strict TypeScript interfaces for all API responses, and implement loading/error states with proper UI feedback.

### 2. The Workflow:

9:00 AM - 10:00 AM (Learning): Watch both videos. Study the React Query quickstart. Understand staleTime, isLoading, isError pattern before writing any code.

10:00 AM - 1:00 PM (Building):
Action 1 (Setup/Init): Install @tanstack/react-query. Wrap your app in QueryClientProvider in layout.tsx. Set defaultOptions staleTime to 5 minutes.
Action 2 (Core Execution): Create a custom hook useMetrics() that calls JSONPlaceholder /posts?_limit=5. Define a TypeScript interface Post matching the API response shape exactly. Render results inside your existing DashboardCard.
Action 3 (Integration/UX): Implement three distinct UI states: a skeleton loader (CSS-only, no library), an error boundary with retry button, and the populated data card. All three must be visually distinct and styled with Tailwind.

The Non-Negotiable Rules:
1. Zero use of any type — every API response field must be explicitly typed.
2. No useEffect + fetch pattern allowed. React Query is the mandatory data layer.
3. Loading and error states must be fully styled — a plain "Loading..." text string is a failing submission.

1:00 PM - 1:30 PM (Hygiene): Run npm run build — fix every TypeScript compiler error. Run npm run lint. Confirm zero warnings before committing.

1:30 PM - 2:00 PM (LinkedIn): "Day 3 of Full Stack at ProSensia — just wired React Query into a live API. The staleTime pattern completely changed how I think about caching. No more useEffect data fetching spaghetti."

---

## Section C: The Submission Protocol
1. **GitHub:** Push with commit: feat: add React Query data fetching with TS interfaces
2. **Kanban:** Move to "Under Review"
3. **LinkedIn:** Post with #ProSensia #ReactQuery #TypeScript #NextJS
