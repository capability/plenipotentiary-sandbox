# Pattern Decision Guide Component

An interactive React component for Docusaurus that helps developers choose the right pattern for their API integration.

## Visual Preview

```
┌─────────────────────────────────────────────────────────────────┐
│        Not Just Another Saloon Wrapper                          │
│   Opinionated patterns for different API types                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  What are you building?                                         │
├─────────────────────────────────────────────────────────────────┤
│  [🔍 Search/Query API] [📦 Resource Mgmt] [⚡ Quick Script] [...│
│                                                                  │
│  ✅ Recommended: Operation Pattern                              │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┬──────────────┬──────────────┬──────────────┐
│ CRUD Pattern │Operation Pat │Procedure Pat │ REST Pattern │
│ Resource...  │ Use Case...  │ Simple RPC   │ Dedicated... │
└──────────────┴──────────────┴──────────────┴──────────────┘
        (Tabs with active highlighting & hover effects)

┌─────────────────────────────────────────────────────────────────┐
│  Operation Pattern - Use Case Driven                            │
│  Use when: Non-CRUD operations like search, generate...         │
├──────────────────────────────┬──────────────────────────────────┤
│  Structure                   │  Feature Coverage                │
│                              │                                  │
│  Contexts/Default/           │  Type Safety     ████████ 100%  │
│    ├── Operations/           │  Validation      ████████ 100%  │
│    │   └── SearchItems/      │  Discoverability ████████ 100%  │
│    │       ├── Operation.php │  Ease of Setup   ██████   80%  │
│    │       ├── Gateway.php   │  Persistence     ██████   80%  │
│    │       ├── DTO.php       │  Idempotency     ████████ 100%  │
│    │       └── Result.php    │                                  │
│    └── Actions/              │  Real-World Examples:            │
│        └── SearchItems...    │  • eBay Browse Search            │
│                              │  • OpenAI Completions            │
│  Developer Usage             │  • Google Ads Reporting          │
│                              │  • Price Calculators             │
│  $dto = SearchItemsDTO...    │                                  │
│  $result = $action->handle() │                                  │
└──────────────────────────────┴──────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  🎯 Why Not Just Use Saloon?                                    │
├──────────────────────────────┬──────────────────────────────────┤
│  Saloon gives you:           │  Plenipotentiary adds:           │
│  • HTTP client abstraction   │  • Patterns (CRUD, Operation...) │
│  • Request/Response handling │  • Layers (Gateway vs Adapter)   │
│  • Authentication strategies │  • Contracts (DTOs, Result...)   │
│                              │  • Integration (Actions, Jobs)   │
│                              │  • Cross-cutting (Idempotency)   │
└──────────────────────────────┴──────────────────────────────────┘
```

## Features

### 1. Interactive Scenario Selector
- Click a scenario button
- Automatically recommends best pattern
- Visual feedback with animations

### 2. Pattern Tabs
- Switch between 4 patterns
- See structure, examples, features
- Smooth transitions

### 3. Visual Feature Comparison
- Animated progress bars
- Color-coded (green/yellow/red)
- Compare 6 key features

### 4. Clear Differentiation
- "Why Not Just Saloon?" section
- Shows architectural value
- Explains the layer model

## Usage

### In Homepage

```tsx
// docs-site/src/pages/index.tsx
import PatternDecisionGuide from '@site/src/components/PatternDecisionGuide';

export default function Home() {
  return (
    <>
      <HomepageHeader />
      <main>
        <PatternDecisionGuide />
        <HomepageFeatures />
      </main>
    </>
  );
}
```

### In MDX Page

```mdx
---
title: Introduction
---

import PatternDecisionGuide from '@site/src/components/PatternDecisionGuide';

# Getting Started

<PatternDecisionGuide />

## Next Steps
...
```

## Component Props

None required - fully self-contained with internal state management.

## State Management

```tsx
const [selectedPattern, setSelectedPattern] = useState<PatternType>('operation');
const [scenario, setScenario] = useState<string>('search');
```

- `selectedPattern` - Currently displayed pattern
- `scenario` - Selected use case scenario

## Patterns Included

### 1. CRUD Pattern
- **When:** Resource lifecycle management
- **Example:** Google Ads Campaigns, Stripe Customers
- **Features:** 100% type safety, validation, persistence

### 2. Operation Pattern ⭐
- **When:** Search, query, generate operations
- **Example:** eBay Browse, OpenAI Completions
- **Features:** 100% type safety, use case driven

### 3. Procedure Pattern
- **When:** Quick prototypes, admin tools
- **Example:** Scripts, one-off operations
- **Features:** Easy setup, minimal abstraction

### 4. REST Pattern
- **When:** Many endpoints, complex config
- **Example:** 50+ endpoint APIs
- **Features:** Per-endpoint type safety

## Styling

Uses Docusaurus/Infima classes + CSS Modules:

- `container`, `row`, `col` - Grid layout
- `card` - Pattern cards
- `alert` - Recommendations
- `button` - Interactive elements
- Custom modules for animations & bars

## Responsive Breakpoints

- **Mobile** (< 576px): Single column
- **Tablet** (< 996px): 2-column grids
- **Desktop** (> 996px): Full 4-column layout

## Key Message

> "Plenipotentiary provides opinionated patterns for different API types.
> Choose the right abstraction for your use case, not a one-size-fits-all wrapper."

**Saloon** = Transport Layer  
**Plenipotentiary** = Integration Architecture Layer

## Accessibility

- Semantic HTML
- Keyboard navigable
- Clear focus states
- ARIA labels (can be added)

## Browser Support

Works on all modern browsers that support:
- CSS Grid
- CSS Custom Properties (variables)
- ES6+ JavaScript

## Future Enhancements

- [ ] Add keyboard shortcuts
- [ ] Export pattern choice
- [ ] Print-friendly version
- [ ] Dark mode optimizations
- [ ] Animation preferences
- [ ] ARIA labels
- [ ] Pattern comparison table
- [ ] Share button
