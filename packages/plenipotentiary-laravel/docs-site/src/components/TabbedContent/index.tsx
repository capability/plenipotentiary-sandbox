import React, { useState } from "react";
import { Layers, Workflow, Boxes, Terminal, HelpCircle, MapPin } from "lucide-react";
import Architecture from "@site/src/components/Architecture";
import ApiFirstWorkflow from "@site/src/components/ApiFirstWorkflow";
import PatternInteractiveGuide from "@site/src/components/PatternInteractiveGuide";
import MakeScaffoldInteractive from "@site/src/components/MakeScaffoldInteractive";
import FAQs from "@site/src/components/FAQs";
import Roadmap from "@site/src/components/Roadmap";

type TabType = "architecture" | "workflow" | "patterns" | "scaffolding" | "faqs" | "roadmap";

interface Tab {
  id: TabType;
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  component: React.ComponentType;
}

const tabs: Tab[] = [
  {
    id: "architecture",
    label: "Architecture",
    icon: Layers,
    component: Architecture,
  },
  {
    id: "workflow",
    label: "Developer Workflow",
    icon: Workflow,
    component: ApiFirstWorkflow,
  },
  {
    id: "patterns",
    label: "Patterns",
    icon: Boxes,
    component: PatternInteractiveGuide,
  },
  {
    id: "scaffolding",
    label: "Scaffolding",
    icon: Terminal,
    component: MakeScaffoldInteractive,
  },
  {
    id: "faqs",
    label: "FAQs",
    icon: HelpCircle,
    component: FAQs,
  },
  {
    id: "roadmap",
    label: "Why/Roadmap",
    icon: MapPin,
    component: Roadmap,
  },
];

export default function TabbedContent() {
  const [activeTab, setActiveTab] = useState<TabType>("architecture");

  const currentTab = tabs.find((tab) => tab.id === activeTab) || tabs[0];
  const Component = currentTab.component;

  return (
    <div>
      {/* Mobile: Dropdown select - Hidden on desktop */}
      <div className="grid grid-cols-1 sm:hidden px-4 py-4">
        <select
          aria-label="Select a tab"
          value={activeTab}
          onChange={(e) => setActiveTab(e.target.value as TabType)}
          className="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-emerald-600"
        >
          {tabs.map((tab) => (
            <option key={tab.id} value={tab.id}>
              {tab.label}
            </option>
          ))}
        </select>
        <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" className="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end fill-gray-500">
          <path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clipRule="evenodd" fillRule="evenodd" />
        </svg>
      </div>

      {/* Desktop: Tabs - Hidden on mobile */}
      <div className="hidden sm:block bg-white">
        <div className="bg-white border-b-2 border-gray-200">
          <nav aria-label="Tabs" className="flex max-w-7xl mx-auto">
            {tabs.map((tab) => {
              const Icon = tab.icon;
              const isActive = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id)}
                  aria-current={isActive ? "page" : undefined}
                  style={{
                    borderLeft: 'none',
                    borderRight: 'none',
                    borderTop: 'none',
                    borderBottom: isActive ? '2px solid #3b82f6' : '2px solid transparent',
                    marginBottom: '-2px',
                  }}
                  className={`group inline-flex items-center justify-center px-4 py-4 text-sm font-medium flex-1 bg-white ${
                    isActive
                      ? "text-blue-600"
                      : "text-gray-500 hover:text-gray-700"
                  }`}
                  onMouseEnter={(e) => {
                    if (!isActive) {
                      e.currentTarget.style.borderBottom = '2px solid #d1d5db';
                    }
                  }}
                  onMouseLeave={(e) => {
                    if (!isActive) {
                      e.currentTarget.style.borderBottom = '2px solid transparent';
                    }
                  }}
                >
                  <Icon className={`mr-2 size-5 ${
                    isActive
                      ? "text-blue-500"
                      : "text-gray-400 group-hover:text-gray-500"
                  }`} />
                  <span>{tab.label}</span>
                </button>
              );
            })}
          </nav>
        </div>
      </div>

      {/* Tab Content */}
      <div>
        <Component />
      </div>
    </div>
  );
}
