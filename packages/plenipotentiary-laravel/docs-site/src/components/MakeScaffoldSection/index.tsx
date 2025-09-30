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

  const output = `Generated Campaign Integration:\n\nFolder Structure:\n│\n├── Pleni/Google/Ads/Contexts/Search/Campaign/\n│   │\n│   ├── Domain Side (Your App - Keep it predictable):\n│   │   ├── DTO/CampaignCanonicalDTO.php          # Your data model\n│   │   ├── Selector/CampaignSelector.php         # Lightweight targeting\n│   │   ├── Gateway/CampaignApiCrudGateway.php    # Main entry point\n│   │   ├── Actions/\n│   │   │   ├── CreateCampaignAction.php          # Laravel Actions - use them, or don't\n│   │   │   ├── PauseCampaignAction.php\n│   │   │   └── ResumeCampaignAction.php\n│   │   └── Repository/\n│   │       └── EloquentCampaignRepository.php    # Persistence repository - Use it, or don't\n│   │\n│   └── Provider Side (Google Ads SDK - This is your service layer, provider specific logic here):\n│       └── Adapter/\n│           ├── CampaignApiCrudAdapter.php        # Google Ads CRUD SDK calls - Delegates to operations\n│           ├── CreateOperation.php               # Work in one file for one operation. Easy to understand.\n│           ├── ReadOperation.php\n│           ├── ReadManyOperation.php\n│           ├── UpdateOperation.php\n│           └── DeleteOperation.php\n│\n├── Pleni/Google/Ads/Contexts/Default/Endpoint    # Flexible API access for non-CRUD operations\n│   ├── Gateway/GoogleAdsApiEntrypointGateway.php # Provider-agnostic gateway. Not all ops should be abstracted!\n│   └── Adapter/GoogleAdsApiEntrypointAdapter.php # Google Ads SDK implementation\n│\n├── Pleni/Google/Ads/Shared/                      # Shared across contexts\n│   ├── Auth/GoogleAdsSdkAuthStrategy.php\n│   ├── Lookup/QueryBuilder.php\n│   └── Support/GoogleAdsErrorMapper.php\n│\n└── Generated Files:\n    ├── Commands/CreateCampaignCommand.php\n    ├── Controllers/CampaignController.php\n    └── Tests/ (Unit, Feature, Integration)\n\nReady to use:\n• php artisan campaign:create \"My Campaign\"\n• POST /api/campaigns (create)\n• POST /api/campaigns/{id}/pause`;

  return (
    <section className={styles.bg}>
      <div className="container">
        <div className="row">
          <div className="col col--12">
            <header>
              <Heading as="h2" className="margin-bottom--md">What the Scaffold looks like - Resource based CRUD operations</Heading>
              <p className="margin-bottom--sm">
                Declare a <strong>Provider</strong>, <strong>Domain</strong>, <strong>Context</strong> and
                <strong> Resource</strong>. Plenipotentiary scaffolds the resource: canonical DTOs, per-operation 
                adapters and tests you’ll wire to your app and the provider SDK.
              </p>
              <ul className="margin-top--sm" style={{paddingLeft: '1.1rem'}}>
                <li><strong>Provider</strong>: the external platform (e.g., Google, eBay, Xero).</li>
                <li><strong>Domain</strong>: functional area inside the provider (e.g., Ads, Buy, Accounting).</li>
                <li><strong>Context</strong>: sub‑domain or API surface (e.g., Search, PMax, Invoices) - See FAQs below.</li>
                <li><strong>Resource</strong>: entity you work with (e.g., Campaign, Order, Invoice).</li>
              </ul>
            </header>
            <p className="margin-bottom--sm">
                <strong>Opinionated, not mandated:</strong> Outside the <strong>DTO → Adapter → Gateway</strong> pattern, 
                nothing is mandated: use Eloquent or any persistence, repositories, actions, commands, and crons... it's your call. 
                And because the Gateway is stable, Pleni can generate thin entrypoints whenever you want them.
              </p>
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
