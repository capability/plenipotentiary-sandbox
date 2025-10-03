import React from 'react';
import CodeBlock from '@theme/CodeBlock';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

/**
 * MakeScaffoldSection (updated)
 *
 * Single‑column homepage section showing the pleni:make command and the
 * CURRENT folder structure you provided.
 */
export default function MakeScaffoldSection() {
  const cmdBasic = `php artisan pleni:make Google Ads Search Campaign`;

const cmdFull = `php artisan pleni:make Google Ads Search Campaign \\
  --with-actions \\
  --with-repository \\
  --with-commands \\
  --with-jobs \\
  --with-controller \\
  --with-requests \\
  --with-tests \\
  --with-migrations \\
  --with-factories \\
  --with-seeders`;

  const outputBasic = `Minimum Pattern:\n\nFolder Structure:\n│\n├── Pleni/Google/Ads/Contexts/Search/Campaign/\n│   │\n│   ├── Domain Side (Your App - Keep it predictable):\n│   │   ├── DTO/CampaignCanonicalDTO.php          # Your data model\n│   │   ├── Selector/CampaignSelector.php         # Lightweight targeting\n│   │   └── Gateway/CampaignApiCrudGateway.php    # Main entry point\n│   │\n│   └── Provider Side (Google Ads SDK - This is your service layer, provider specific logic here):\n│       └── Adapter/\n│           ├── CampaignApiCrudAdapter.php        # Google Ads CRUD SDK calls - Delegates to operations\n│           ├── CampaignCreate.php                # Work in one file for one operation. Easy to understand.\n│           ├── CampaignRead.php\n│           ├── CampaignReadMany.php\n│           ├── CampaignUpdate.php\n│           └── CampaignDelete.php\n│\n└──Pleni/Google/Ads/Shared/Auth/GoogleAdsSdkAuthStrategy.php`;
  const outputFull = `Generated Campaign Integration:\n\nFolder Structure:\n│\n├── Pleni/Google/Ads/Contexts/Search/Campaign/\n│   │\n│   ├── Domain Side (Your App - Keep it predictable):\n│   │   ├── DTO/CampaignCanonicalDTO.php          # Your data model\n│   │   ├── Selector/CampaignSelector.php         # Lightweight targeting\n│   │   ├── Gateway/CampaignApiCrudGateway.php    # Main entry point\n│   │   ├── Actions/\n│   │   │   ├── CampaignCreateAction.php          # Laravel Actions - use them, or don't\n│   │   │   ├── CampaignPauseAction.php\n│   │   │   └── CampaignResumeAction.php\n│   │   └── Repository/\n│   │       ├── EloquentCampaignRepository.php    # Persistence repository - Use it, or don't (or &darr;)\n│   │       └── MongoCampaignRepository.php       # Eloquent is not assumed. Nothing is assumed.\n│   │\n│   └── Provider Side (Google Ads SDK - This is your service layer, provider specific logic here):\n│       └── Adapter/\n│           ├── CampaignApiCrudAdapter.php        # Google Ads CRUD SDK calls - Delegates to operations\n│           ├── CampaignCreate.php                # Work in one file for one operation. Easy to understand.\n│           ├── CampaignRead.php\n│           ├── CampaignReadMany.php\n│           ├── CampaignUpdate.php\n│           └── CampaignDelete.php\n│\n├── Pleni/Google/Ads/Shared                       # Non-CRUD? Its Covered!\n│   ├── Rest/GoogleAdsRestGateway.php             # Provider-agnostic gateway powered by the Command Bus pattern\n│   └── Rest/GoogleAdsRestAdapter.php             # Saloon-powered REST adapter. We're not trying to reinvent the wheel.\n│   ├── Gateway/GoogleAdsRpcGateway.php           \n│   └── Adapter/GoogleAdsRpcAdapter.php           # Pro: Quick Exploratory Work Con: breaks the core REST principles\n│\n├── Pleni/Google/Ads/Shared/                      # Shared across contexts\n│   ├── Auth/GoogleAdsSdkAuthStrategy.php\n│   ├── Lookup/QueryBuilder.php\n│   └── Support/GoogleAdsErrorMapper.php\n│\n└── Generated Files:\n    ├── Commands/CreateCampaignCommand.php\n    ├── Controllers/CampaignController.php\n    └── Tests/ (Unit, Feature, Integration)\n\nReady to use:\n• php artisan campaign:create \"My Campaign\"\n• POST /api/campaigns (create)\n• POST /api/campaigns/{id}/pause`;

  return (
    <section className={styles.bg}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <header>
              <Heading as="h2" className="margin-bottom--md">What the Scaffold looks like - Resource based CRUD operations</Heading>
              <p className="margin-bottom--sm">
                Declare a <strong>Provider</strong>, <strong>Domain</strong>, <strong>Context</strong> and
                <strong> Resource</strong>. Plenipotentiary scaffolds the resource: canonical DTOs, operation-specific 
                adapters and tests you'll wire to your app and the provider SDK.
              </p>
              <ul className="margin-top--sm" style={{paddingLeft: '1.1rem'}}>
                <li><strong>Provider</strong>: the external platform (e.g., Google, eBay, Xero).</li>
                <li><strong>Domain</strong>: functional area inside the provider (e.g., Ads, Buy, Accounting).</li>
                <li><strong>Context</strong>: sub‑domain or API surface (e.g., Search, PMax, Invoices) - <a href="#faq:context">See FAQs below.</a></li>
                <li><strong>Resource</strong>: entity you work with (e.g., Campaign, Order, Invoice).</li>
              </ul>
            </header>
            <p className="margin-bottom--sm">
                <strong>Opinionated, not mandated:</strong> Outside the <strong>DTO → Adapter → Gateway</strong> pattern, 
                nothing is mandated: use Eloquent directly or any persistence via repositories. Add actions, commands, controllers and crons. Queue jobs or create a workflows... it's your call. 
                And because the Gateway is stable, Pleni can generate thin entrypoints whenever you want them utilising all the Laravel tools you already know and love.
              </p>
            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Make command - Basic generation</Heading>
            <CodeBlock language="bash">{cmdBasic}</CodeBlock>
            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Console output (basic CRUD)</Heading>
            <CodeBlock language="text">{outputBasic}</CodeBlock>
            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Make command - Full-featured generation</Heading>
            <CodeBlock language="bash">{cmdFull}</CodeBlock>
            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Console output (what the predictable gateway makes possible)</Heading>
            <CodeBlock language="text">{outputFull}</CodeBlock>
            <p>Don't worry about the file count - the Adapter folder is where you'd normally put your service layer code, but now it's organized by operation (Create, Update, Delete) with built-in validation, mapping, and error handling, so you only need to focus on the third-party API/SDK integration in each operation.</p>
          </div>
        </div>
      </div>
    </section>
  );
}
