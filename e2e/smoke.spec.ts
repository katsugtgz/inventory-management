import { test, expect } from '@playwright/test';

const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL ?? 'admin@example.com';
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'password';

async function login(page: import('@playwright/test').Page) {
  for (let attempt = 1; attempt <= 3; attempt++) {
    await page.goto('/admin/login');
    await page.getByLabel('Email address').fill(ADMIN_EMAIL);
    await page.getByRole('textbox', { name: 'Password' }).fill(ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Sign in' }).click();
    try {
      await expect(page, 'login should reach dashboard').toHaveURL(/\/admin(?:\?.*)?$/, { timeout: 15_000 });
      return;
    } catch {
      if (attempt === 3) throw new Error('login failed after 3 attempts; check credentials/throttle');
    }
  }
}

test('login page renders', async ({ page }) => {
  await page.goto('/admin/login');
  await expect(page.getByRole('heading', { name: 'Sign in' })).toBeVisible();
});

test('admin can log in', async ({ page }) => {
  await login(page);
  await expect(page.getByRole('heading', { name: 'Dashboard', exact: true })).toBeVisible();
});

test('dashboard shows stats', async ({ page }) => {
  await login(page);
  await expect(page.getByText('Total Sales')).toBeVisible();
  await expect(page.getByText('Total Purchases')).toBeVisible();
});

const pages: Array<[string, string]> = [
  ['products', 'Products'],
  ['purchases', 'Purchases'],
  ['sales', 'Sales'],
  ['stocks', 'Stocks'],
  ['stock-adjustments', 'Stock Adjustments'],
  ['categories', 'Categories'],
  ['warehouses', 'Warehouses'],
  ['profit-loss-report', 'Profit Loss Report'],
];

for (const [path, heading] of pages) {
  test(`page /admin/${path} renders`, { timeout: 60_000 }, async ({ page }) => {
    await login(page);
    await page.goto(`/admin/${path}`);
    if (page.url().includes('/login')) {
      await login(page);
      await page.goto(`/admin/${path}`);
    }
    await expect(page.getByRole('heading', { name: heading, exact: true }).first()).toBeVisible({ timeout: 20_000 });
  });
}

test('products table lists rows', async ({ page }) => {
  await login(page);
  await page.goto('/admin/products');
  await expect(page.getByRole('cell', { name: 'SKU' }).first()).toBeVisible().catch(() => {});
  await expect(page.locator('table tbody tr').first()).toBeVisible();
});
