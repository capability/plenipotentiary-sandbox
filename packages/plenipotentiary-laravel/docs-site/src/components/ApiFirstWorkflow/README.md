# API-First Workflow Component

An interactive, step-by-step walkthrough component that demonstrates Plenipotentiary's "Understanding Over Abstraction" philosophy.

## Visual Preview

```
┌─────────────────────────────────────────────────────────────────┐
│                   API-First Workflow                            │
│   Learn the real API first. Codify your understanding.          │
│             Core Principle: Understanding Over Abstraction      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  Progress: [🔍]━━━[📋]━━━[✅]━━━[🚪]━━━[🏗️]━━━[🛡️]           │
│             1     2     3     4     5     6                     │
│          (Interactive dots - click to navigate)                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  🔍  Step 1 of 6                                                │
│      Start with the Real API                                    │
│      Understanding Over Abstraction                             │
├─────────────────────────────────────────────────────────────────┤
│  💡 Principle: Learn the provider SDK/API first. No premature  │
│                abstraction.                                     │
│                                                                 │
│  Open one file, one operation. Copy the provider's SDK example │
│  almost verbatim. Make a real API call...                      │
│                                                                 │
│  [Code Example]                                                 │
│                                                                 │
│  ✅ You understand the API call flow                           │
│  ❌ Starting with abstractions before understanding the API    │
│                                                                 │
│  [← Previous]              [Mark Complete & Continue →]        │
└─────────────────────────────────────────────────────────────────┘

After completing all steps:

┌─────────────────────────────────────────────────────────────────┐
│  API-First vs. Abstraction-First                                │
├─────────────────────────┬───────────────────────────────────────┤
│  ✅ API-First           │  ❌ Abstraction-First                 │
│  (Plenipotentiary)      │  (Magic)                              │
│                         │                                       │
│  1. Copy real SDK       │  1. Design perfect DTOs               │
│  2. Make real API call  │  2. Build universal mapper            │
│  3. Define INPUT_SPEC   │  3. Add all possible fields           │
│  ...                    │  ...                                  │
│                         │                                       │
│  Result: Understanding  │  Result: Abstraction                  │
│  → Contract → Tooling   │  → Confusion → Debugging              │
└─────────────────────────┴───────────────────────────────────────┘
```

## Core Philosophy

### Understanding Over Abstraction

**The Problem:** Most frameworks abstract APIs before you understand them. You fight magic, debug black boxes, and never truly learn the provider's API.

**The Solution:** Start with the real API. Learn it. Then structure what you learned.

### The 6-Step Journey

1. **🔍 Start with Real API** - Copy SDK examples, make real calls
2. **📋 Define INPUT_SPEC** - Codify minimum needed fields
3. **✅ Test Until Green** - requestMapper + responseMapper + tests
4. **🚪 Call Through Gateway** - Stable boundary reveals DTO contract
5. **🏗️ Scaffold to Spec** - Generate from YOUR understanding
6. **🛡️ Robustness Online** - Cross-cutting concerns layer on

## Interactive Features

### 1. Progress Tracking
- **Visual dots** for each step (with icons!)
- Click to navigate between steps
- Completed steps marked with success color
- Locked steps (sequential unlocking)
- Active step highlighted and scaled

### 2. Step Completion Flow
- Mark step as complete to unlock next
- Track progress through workflow
- Navigate back to review steps

### 3. Code Examples
- Real, copy-pasteable code for each step
- Shows actual SDK usage → INPUT_SPEC → Tests → Gateway

### 4. Outcome vs. Antipattern
- Each step shows ✅ desired outcome
- Each step shows ❌ antipattern to avoid
- Learn what to do AND what not to do

### 5. Comparison View
- Unlocked after completing all steps
- Side-by-side: API-First vs. Abstraction-First
- Shows why understanding-first matters

### 6. Quick Reference
- Collapsible quick nav to all steps
- Grid layout with icons
- Jump to any step instantly

## Usage

### In Homepage

```tsx
import ApiFirstWorkflow from '@site/src/components/ApiFirstWorkflow';

export default function Home() {
  return (
    <>
      <HomepageHeader />
      <main>
        <ApiFirstWorkflow />
        <PatternDecisionGuide />
      </main>
    </>
  );
}
```

### In MDX Page

```mdx
---
title: Developer Workflow
---

import ApiFirstWorkflow from '@site/src/components/ApiFirstWorkflow';

# How to Build with Plenipotentiary

<ApiFirstWorkflow />
```

### In Tutorial Section

```mdx
---
title: Your First Integration
sidebar_position: 2
---

import ApiFirstWorkflow from '@site/src/components/ApiFirstWorkflow';

Follow this interactive workflow to build your first integration:

<ApiFirstWorkflow />
```

## Component State

```tsx
const [currentStep, setCurrentStep] = useState<WorkflowPhase>('understand');
const [completedSteps, setCompletedSteps] = useState<Set<WorkflowPhase>>(new Set());
const [showComparison, setShowComparison] = useState(false);
```

- `currentStep` - Currently displayed step
- `completedSteps` - Set of completed steps (enables progression)
- `showComparison` - Shows comparison card after completion

## Step Data Structure

```tsx
interface Step {
  id: WorkflowPhase;
  number: number;
  title: string;
  subtitle: string;
  principle: string;         // Core principle of this step
  description: ReactNode;    // Explanation
  code: string;             // Code example
  outcome: string;          // Desired outcome (✅)
  antipattern: string;      // What to avoid (❌)
  icon: string;             // Emoji icon
}
```

## The 6 Steps in Detail

### Step 1: Start with Real API 🔍
- **Principle:** Learn the provider SDK first
- **Action:** Copy SDK example, make real call
- **Outcome:** Understand API flow
- **Antipattern:** Premature abstraction

### Step 2: Define INPUT_SPEC 📋
- **Principle:** Explicit contract
- **Action:** Codify minimum needed fields
- **Outcome:** Visible, auditable contract
- **Antipattern:** Hidden validation, magic

### Step 3: Test Until Green ✅
- **Principle:** Stay in one place
- **Action:** Build requestMapper + responseMapper
- **Outcome:** Operation tested, API understood
- **Antipattern:** Moving to gateway too early

### Step 4: Call Through Gateway 🚪
- **Principle:** Stable boundary emerges
- **Action:** Run through gateway (intentional fail)
- **Outcome:** Gateway reveals required DTO
- **Antipattern:** Premature DTO design

### Step 5: Scaffold to Spec 🏗️
- **Principle:** Tooling follows understanding
- **Action:** Generate DTO/Factory from INPUT_SPEC
- **Outcome:** Type-safe contracts from your spec
- **Antipattern:** Auto-generated bloat

### Step 6: Robustness Online 🛡️
- **Principle:** Progressive enhancement
- **Action:** Cross-cutting concerns activate
- **Outcome:** Idempotency, validation, queueing, logging
- **Antipattern:** Adding concerns while learning

## Key Messages

### Understanding Over Abstraction
> "Most frameworks hide the API behind abstractions. Plenipotentiary forces you to learn the API first, then provides structure for what you learned."

### Progressive Enhancement
> "You get robustness features when you need them, not when you're still learning the API."

### Explicit Contracts
> "INPUT_SPEC isn't magic. It's your explicit contract with the API, written while working with the real SDK."

### Tooling Follows Understanding
> "DTOs and Factories aren't guesses. They're derived from the INPUT_SPEC you wrote while learning the API."

## Styling Features

### Animations
- Slide-in animation for step cards
- Smooth transitions between steps
- Hover effects on progress dots
- Scale effect on active dot

### Color Coding
- **Success green** - Completed steps, outcomes
- **Danger red** - Antipatterns
- **Primary blue** - Active step, principles
- **Info blue** - Principle alerts

### Responsive Design
- **Desktop:** Full horizontal progress bar
- **Tablet:** Wrapped progress dots
- **Mobile:** Stacked layout, full-width buttons

### Accessibility
- Keyboard navigable (via buttons)
- Disabled state for locked steps
- Clear focus indicators
- Semantic HTML structure

## Comparison Chart

After completing all steps, users see:

```
API-First (Plenipotentiary)          Abstraction-First (Magic)
────────────────────────────────────────────────────────────
1. Copy real SDK example             1. Design perfect DTOs
2. Make real API call                2. Build universal mapper
3. Define INPUT_SPEC from learning   3. Add all possible fields
4. Test until green                  4. Hide validation in framework
5. Gateway shows required DTO        5. Abstract before understanding
6. Scaffold from YOUR spec           6. Debug magic when it breaks
7. Robustness layers on              7. Never really understand API

Result: Understanding → Contract     Result: Abstraction → Confusion
        → Tooling                            → Debugging
```

## Browser Support

Works on all modern browsers supporting:
- CSS Grid & Flexbox
- CSS Custom Properties
- ES6+ JavaScript
- React Hooks

## Future Enhancements

- [ ] Animated code transitions
- [ ] Save progress to localStorage
- [ ] Export workflow as PDF
- [ ] Video walkthrough links
- [ ] Interactive code playground
- [ ] Tooltip explanations
- [ ] Keyboard shortcuts (← → arrows)
- [ ] Progress percentage
- [ ] Estimated time per step
- [ ] Share progress link

## Why This Is Compelling

### 1. Interactive Learning
Users don't just read—they **experience** the workflow step by step.

### 2. Visual Progress
Progress bar with icons makes the journey tangible and motivating.

### 3. Sequential Unlocking
Can't skip ahead until understanding previous steps—reinforces learning order.

### 4. Clear Outcomes
Every step shows both what to achieve AND what to avoid.

### 5. Principle-Driven
Each step explicitly states its core principle—not just "how" but "why."

### 6. Real Code
Not pseudocode or abstractions—actual copy-pasteable PHP code.

### 7. Comparison Payoff
After completing workflow, users get validation of why this approach works.

### 8. Self-Documenting
The workflow IS the documentation—interactive, clear, and structured.

---

This component transforms the philosophical "API-First" principle into a **hands-on, interactive learning experience**! 🚀
