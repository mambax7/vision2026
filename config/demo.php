<?php

declare(strict_types=1);

/**
 * Vision 2026 Demo Mode Configuration
 *
 * Enable demo mode to show sample articles without a database.
 * This is perfect for:
 * - Demonstrations and presentations
 * - Development before database is ready
 * - Showcasing the architecture
 *
 * To enable demo mode, set 'enabled' to true below,
 * or add ?demo=1 to any URL.
 */

return [
    'enabled' => false,  // Set to false to disable demo mode
];
