# ApiFirstWorkflow Component - Visual Guide

## Component Overview

The `ApiFirstWorkflow` component creates an **interactive, step-by-step journey** through Plenipotentiary's core philosophy: **Understanding Over Abstraction**.

---

## Visual Layout Structure

### 1. Header Section
```
┌──────────────────────────────────────────────────────────┐
│                  API-First Workflow                       │
│                                                           │
│  Learn the real API first. Codify your understanding.    │
│         Let tooling follow your spec.                    │
│                                                           │
│  ╔════════════════════════════════════════════════╗      │
│  ║ Core Principle: Understanding Over Abstraction ║      │
│  ╚════════════════════════════════════════════════╝      │
└──────────────────────────────────────────────────────────┘
```

**Elements:**
- **Title**: "API-First Workflow" (h2, centered)
- **Subtitle**: Gray text explaining the workflow
- **Principle Box**: Pill-shaped badge with gradient background

---

### 2. Progress Indicator Bar

This is the "1, 2, 3, 4, 5, 6 tabs" you're asking about.

#### **Desktop Layout (> 996px)**
```
┌────────────────────────────────────────────────────────────────┐
│    🔍      ━━━━━━━    📋      ━━━━━━━    ✅      ━━━━━━━     │
│   ( 1 )              ( 2 )              ( 3 )                   │
│                                                                 │
│    🚪      ━━━━━━━    🏗️      ━━━━━━━    🛡️                  │
│   ( 4 )              ( 5 )              ( 6 )                   │
└────────────────────────────────────────────────────────────────┘
```

**Actually renders as a single horizontal line:**
```
   🔍  ━━━  📋  ━━━  ✅  ━━━  🚪  ━━━  🏗️  ━━━  🛡️
   1        2        3        4        5        6
```

**Elements:**
- **6 Circular Dots** (70px diameter each)
  - Icon on top (emoji, 1.75rem)
  - Number below (0.75rem, bold)
  - Border: 3px solid
  - Flex column layout (icon above number)
  
- **Connecting Lines** (80px wide, 3px thick)
  - Horizontal bars between dots
  - Gray by default, green when step completed

**Visual States:**
1. **Default (Locked)**: 
   - Gray border (`var(--ifm-color-emphasis-300)`)
   - White/light background
   - Opacity: 0.4
   - Cursor: not-allowed

2. **Active (Current Step)**:
   - Blue border (`var(--ifm-color-primary)`)
   - Gradient background (light blue)
   - Scale: 1.2 (slightly larger)
   - Box shadow
   - Cursor: pointer

3. **Completed**:
   - Green border (`var(--ifm-color-success)`)
   - Light green background
   - Cursor: pointer
   - Connecting line turns green

#### **Mobile Layout (< 996px)**
```
┌──────────────────────────┐
│   🔍    📋    ✅          │
│   1     2     3           │
│                           │
│   🚪    🏗️    🛡️         │
│   4     5     6           │
└──────────────────────────┘
```

**Changes:**
- Dots wrap into multiple rows
- Connecting lines hidden (display: none)
- Dots slightly smaller (65px)
- Centered with gap spacing

---

### 3. Current Step Card

```
┌─────────────────────────────────────────────────────────────┐
│ ╔═══════════════════════════════════════════════════════╗   │
│ ║  🔍  Step 1 of 6                                      ║   │
│ ║      Start with the Real API                          ║   │
│ ║      Understanding Over Abstraction                   ║   │
│ ╚═══════════════════════════════════════════════════════╝   │
├─────────────────────────────────────────────────────────────┤
│  💡 Principle: Learn the provider SDK/API first. No      │
│                premature abstraction.                        │
│                                                              │
│  Open one file, one operation. Copy the provider's SDK      │
│  example almost verbatim. Make a real API call...           │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ // Code Example (PHP)                               │   │
│  │ public function perform(array $input): Result       │   │
│  │ {                                                   │   │
│  │     $campaign = new Campaign([...]);                │   │
│  │     ...                                             │   │
│  │ }                                                   │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────┬────────────────────────────┐  │
│  │ ✅ You understand the   │ ❌ Starting with           │  │
│  │    API call flow        │    abstractions before     │  │
│  │                         │    understanding the API   │  │
│  └─────────────────────────┴────────────────────────────┘  │
│                                                              │
│  [← Previous]              [Mark Complete & Continue →]     │
└─────────────────────────────────────────────────────────────┘
```

**Elements:**
- **Header** (blue/primary background)
  - Large icon (3rem)
  - Step badge ("Step 1 of 6")
  - Title
  - Subtitle

- **Principle Alert** (info blue)
  - 💡 icon + principle text
  
- **Description** (body text)
  
- **Code Block** (syntax highlighted PHP)

- **Outcome vs. Antipattern** (2-column grid)
  - Left: Green box with ✅
  - Right: Red box with ❌

- **Navigation Buttons**
  - Left: Previous (secondary, outline)
  - Right: Next/Complete (primary)

---

### 4. Comparison Card (After Completion)

```
┌─────────────────────────────────────────────────────────────┐
│  API-First vs. Abstraction-First                            │
├─────────────────────────────┬───────────────────────────────┤
│  ✅ API-First               │  ❌ Abstraction-First         │
│  (Plenipotentiary)          │  (Magic)                      │
│                             │                               │
│  1. Copy real SDK example   │  1. Design perfect DTOs       │
│  2. Make real API call      │  2. Build universal mapper    │
│  3. Define INPUT_SPEC       │  3. Add all possible fields   │
│  4. Test until green        │  4. Hide validation           │
│  5. Gateway shows DTO       │  5. Abstract before learning  │
│  6. Scaffold from spec      │  6. Debug magic               │
│  7. Robustness layers on    │  7. Never understand API      │
│                             │                               │
│  Result: Understanding →    │  Result: Abstraction →        │
│          Contract →         │          Confusion →          │
│          Tooling            │          Debugging            │
└─────────────────────────────┴───────────────────────────────┘
```

---

### 5. Quick Reference (Collapsible)

```
┌─────────────────────────────────────────────────────────────┐
│  ▶ Quick Reference: All Steps                               │
└─────────────────────────────────────────────────────────────┘

When expanded:
┌─────────────────────────────────────────────────────────────┐
│  ▼ Quick Reference: All Steps                               │
├────────────────┬────────────────┬────────────────┬──────────┤
│      🔍        │      📋        │      ✅        │    🚪    │
│  Start with    │  Define        │  Test Until    │  Call    │
│  Real API      │  INPUT_SPEC    │  Green         │  Gateway │
├────────────────┼────────────────┴────────────────┴──────────┤
│      🏗️        │      🛡️        │                          │
│  Scaffold      │  Robustness    │                          │
│  to Spec       │  Online        │                          │
└────────────────┴────────────────┘
```

---

## Interaction Flow

### Step Progression
1. User lands on Step 1 (🔍)
2. Step 1 is **active** (blue, scaled up)
3. Steps 2-6 are **locked** (grayed out)
4. User reads Step 1, clicks "Mark Complete & Continue"
5. Step 1 becomes **completed** (green)
6. Step 2 becomes **active** (blue, scaled up)
7. Connecting line between 1 and 2 turns **green**
8. User can click Step 1 dot to go back
9. Process repeats through all 6 steps
10. After Step 6, "View Comparison" button appears
11. Comparison card slides in

### Visual Feedback
- **Hover**: Dot scales to 1.15x, shadow appears
- **Click**: Smooth transition to new step (0.4s slide-in animation)
- **Complete**: Green checkmark effect, line animates
- **Active**: Pulsing or highlighted state

---

## How the "1, 2, 3, 4, 5, 6" Should Look

### ✅ Correct Appearance:
Each progress dot should be a **well-spaced circle** with:
- **Icon centered on top** (emoji, clear and visible)
- **Small number centered below** (inside the circle)
- **Clear spacing** between icon and number
- **Visible borders** and backgrounds
- **Connected by horizontal lines** (desktop only)

```
     Emoji (large)
        
        1
     (small)
```

### ❌ Common Issues:
1. **Icon and number overlapping**: Both elements in same space
2. **Number too small**: Hard to read
3. **Dots too small**: Icons cramped
4. **Lines missing**: No visual connection between steps
5. **Poor spacing**: Dots touching or too far apart
6. **Unclear active state**: Can't tell which step you're on

---

## CSS Key Points

### Dot Structure
```css
.progressDot {
  width: 70px;              /* Sufficient space */
  height: 70px;
  display: flex;
  flex-direction: column;   /* Stack icon above number */
  align-items: center;
  justify-content: center;
  gap: 0;                   /* Tight but not overlapping */
  padding: 8px;             /* Internal spacing */
}
```

### Icon & Number Sizing
```css
.progressIcon {
  font-size: 1.75rem;       /* Large enough to see emoji */
  line-height: 1;
  margin-bottom: 2px;       /* Space below icon */
}

.progressNumber {
  font-size: 0.75rem;       /* Small but readable */
  font-weight: 700;         /* Bold for visibility */
  line-height: 1;
  color: var(--ifm-color-emphasis-800);  /* Dark enough */
}
```

### Connecting Lines
```css
.progressLine {
  width: 80px;              /* Visible connection */
  height: 3px;              /* Solid line */
  background: var(--ifm-color-emphasis-300);
  transition: background 0.3s ease;
}
```

---

## Debugging Checklist

If the "1, 2, 3, 4, 5, 6" look off, check:

1. ✅ **Are the circles large enough?** (70px)
2. ✅ **Is flex-direction: column working?** (Icon on top)
3. ✅ **Are the emojis rendering?** (Font support)
4. ✅ **Is the number visible?** (Not too small, good contrast)
5. ✅ **Are there connecting lines?** (Desktop only)
6. ✅ **Is spacing consistent?** (Gap between elements)
7. ✅ **Are hover states working?** (Scale effect)
8. ✅ **Is the active state clear?** (Different color/size)

---

## Summary

The ApiFirstWorkflow component is designed to be a **compelling, interactive learning experience**. The progress indicators (1-6) should feel like a clear **visual journey**, not cramped or confusing.

**Key Improvements Made:**
- Increased dot size: 60px → 70px
- Increased line width: 60px → 80px  
- Better icon sizing: 1.5rem → 1.75rem
- Clearer number styling: Added line-height, better color
- Improved spacing: Added margin-bottom to icon
- Max-width on progress bar for better centering
- Responsive adjustments for mobile

The result should be a **polished, professional, and intuitive** workflow component that guides users through the API-First philosophy step by step! 🚀
