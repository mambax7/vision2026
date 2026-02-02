<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - About Page
 */

// XOOPS admin header
//require_once dirname(__DIR__, 3) . '/include/cp_header.php';
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

$GLOBALS['adminObject']->displayNavigation('about.php');

?>

<div class="vision2026-about">
    <h2>ℹ️ <?php echo _AM_VISION2026_ABOUT_TITLE; ?></h2>

    <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 1.5rem; margin: 2rem 0; border-radius: 0 8px 8px 0;">
        <h3>🏗️ Clean Architecture Reference Implementation</h3>
        <p><?php echo _AM_VISION2026_ABOUT_DESCRIPTION; ?></p>

        <h4 style="margin-top: 2rem;">Architecture Layers:</h4>
        <ul style="line-height: 1.8;">
            <li><strong>Domain Layer</strong> - Pure PHP business logic with entities, value objects, and domain events</li>
            <li><strong>Application Layer</strong> - Command/Query handlers orchestrating use cases</li>
            <li><strong>Infrastructure Layer</strong> - Database adapters, event dispatchers, service container</li>
            <li><strong>Presentation Layer</strong> - Controllers, templates, and REST APIs</li>
        </ul>

        <h4 style="margin-top: 2rem;">Key Patterns:</h4>
        <ul style="line-height: 1.8;">
            <li>🎯 <strong>Repository Pattern</strong> - Abstraction over data access</li>
            <li>⚡ <strong>Command/Handler Pattern</strong> - Explicit use case execution</li>
            <li>🎪 <strong>Domain Events</strong> - Decoupled side effects</li>
            <li>💎 <strong>Value Objects</strong> - Self-validating domain primitives</li>
            <li>🏭 <strong>Factory Methods</strong> - Controlled entity creation</li>
        </ul>

        <h4 style="margin-top: 2rem;">Module Information:</h4>
        <table style="width: 100%; margin-top: 1rem;">
            <tr>
                <td style="padding: 0.5rem;"><strong>Version:</strong></td>
                <td style="padding: 0.5rem;">1.0.0</td>
            </tr>
            <tr>
                <td style="padding: 0.5rem;"><strong>Author:</strong></td>
                <td style="padding: 0.5rem;">XOOPS Team</td>
            </tr>
            <tr>
                <td style="padding: 0.5rem;"><strong>License:</strong></td>
                <td style="padding: 0.5rem;">GPL-2.0+</td>
            </tr>
            <tr>
                <td style="padding: 0.5rem;"><strong>PHP Version:</strong></td>
                <td style="padding: 0.5rem;">8.1+</td>
            </tr>
        </table>

        <h4 style="margin-top: 2rem;">Documentation:</h4>
        <p>For detailed documentation, see:</p>
        <ul>
            <li><code>CLAUDE.md</code> - AI assistant guide</li>
            <li><code>README.md</code> - Module overview</li>
            <li><code>docs/WALKTHROUGH.md</code> - Request flow walkthrough</li>
            <li><code>docs/COMPARISON.md</code> - Traditional vs Vision 2026</li>
        </ul>
    </div>
</div>

<?php

require_once __DIR__ . '/admin_footer.php';
