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
  const cmd = `# Basic generation
php artisan pleni:make Google Ads Search Campaign

# Full-featured generation
php artisan pleni:make Google Ads Search Campaign \\
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

  const output = `Generated Campaign Integration:\n\nFolder Structure:\n│\n├── Pleni/Google/Ads/Contexts/Search/Campaign/\n│   │\n│   ├── Domain Side (Your App - Keep it predictable):\n│   │   ├── CampaignCanonicalDTO.php              # Your data model\n│   │   ├── CampaignSelector.php                  # Lightweight targeting\n│   │   ├── CampaignApiCrudGateway.php            # Main entry point\n│   │   ├── Actions/\n│   │   │   ├── CreateCampaignAction.php          # Laravel Actions - use them, or don't\n│   │   │   ├── PauseCampaignAction.php\n│   │   │   └── ResumeCampaignAction.php\n│   │   └── Repository/\n│   │       └── EloquentCampaignRepository.php    # Persistence repository - Use it, or don't\n│   │\n│   └── Provider Side (Google Ads SDK - This is your service layer, provider specific logic here):\n│       ├── Adapter/\n│       │   ├── CampaignApiCrudAdapter.php        # Google Ads CRUD SDK calls\n│       │   ├── Create/RequestMapper.php          # DTO → Google format\n│       │   ├── Update/RequestMapper.php\n│       │   └── Delete/RequestMapper.php\n│       └── Key/\n│           └── CampaignSelector.php\n│\n├── Pleni/Google/Ads/Contexts/Default/Endpoint    # Flexible API access for non-CRUD operations\n│   ├── Gateway/GoogleAdsApiEntrypointGateway.php # Provider-agnostic gateway for any Google Ads operation\n│   └── Adapter/GoogleAdsApiEntrypointAdapter.php # Google Ads SDK implementation\n│\n├── Pleni/Google/Ads/Shared/                      # Shared across contexts\n│   ├── Auth/GoogleAdsSdkAuthStrategy.php\n│   ├── Lookup/QueryBuilder.php\n│   └── Support/GoogleAdsErrorMapper.php\n│\n└── Generated Files:\n    ├── Commands/CreateCampaignCommand.php\n    ├── Controllers/CampaignController.php\n    └── Tests/ (Unit, Feature, Integration)\n\nReady to use:\n• php artisan campaign:create \"My Campaign\"\n• POST /api/campaigns (create)\n• POST /api/campaigns/{id}/pause`;

  return (
    <section className={styles.bg}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <header>
              <Heading as="h2" className="margin-bottom--md">Scaffold an Integration Surface - Resource based CRUD operations</Heading>
              <p className="margin-bottom--sm">
                Declare a <strong>Provider</strong>, <strong>Domain</strong>, <strong>Context</strong>, and
                <strong> Resource</strong> — Plenipotentiary generates the contracts, DTOs, adapters and tests
                you’ll wire into your app and provider SDK.
              </p>
              <ul className="margin-top--sm" style={{paddingLeft: '1.1rem'}}>
                <li><strong>Provider</strong>: the external platform (e.g., Google, eBay, Xero).</li>
                <li><strong>Domain</strong>: functional area inside the provider (e.g., Ads, Buy, Accounting).</li>
                <li><strong>Context</strong>: sub‑domain or API surface (e.g., Search, PMax, Invoices).</li>
                <li><strong>Resource</strong>: entity you work with (e.g., Campaign, Order, Invoice).</li>
              </ul>
            </header>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Make command</Heading>
            <CodeBlock language="bash">{cmd}</CodeBlock>

            <Heading as="h3" className="margin-top--lg margin-bottom--sm">Console output (typical)</Heading>
            <CodeBlock language="text">{output}</CodeBlock>
            <p>Don't worry about the file count - the Adapter folder is where you'd normally put your service layer code, but now it's organized by operation (Create, Update, Delete) with built-in validation, mapping, and error handling, so you only need to focus on the business logic in each RequestMapper.</p>
          </div>
        </div>
      </div>
    </section>
  );
}
