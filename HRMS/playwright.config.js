// @ts-check
const { defineConfig, devices } = require('@playwright/test');

/**
 * The app resolves tenants purely from the request Host header
 * (see app/Filters/TenantResolver.php) — there's no hosts-file entry for
 * *.hrms.test in this environment, so Chromium is launched with
 * --host-resolver-rules to map it to the local dev server without any
 * OS-level change.
 */
module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 20000,
  reporter: [['list']],
  use: {
    baseURL: 'http://abc.hrms.test:8082',
    screenshot: 'only-on-failure',
    launchOptions: {
      args: [
        '--host-resolver-rules=MAP abc.hrms.test 127.0.0.1',
        // navigator.geolocation is only available in "secure contexts" (https, or the literal
        // hostnames localhost/127.0.0.1) — abc.hrms.test doesn't qualify even though it resolves
        // to 127.0.0.1 above, since Chromium's check is on the hostname string, not the resolved
        // IP. This flag is Chromium's documented escape hatch for exactly this testing scenario.
        '--unsafely-treat-insecure-origin-as-secure=http://abc.hrms.test:8082',
      ],
    },
  },
  projects: [
    {
      name: 'desktop-1440',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } },
    },
    {
      name: 'mobile-390',
      use: { ...devices['Desktop Chrome'], viewport: { width: 390, height: 844 } },
    },
  ],
});
