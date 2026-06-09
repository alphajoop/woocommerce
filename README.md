# lomi. for WooCommerce

Accept WooCommerce payments with lomi. hosted checkout in XOF, USD, and EUR. Merchant revenue is credited in XOF.

## Overview

lomi. for WooCommerce connects your WooCommerce store to lomi. hosted checkout.

The plugin lets merchants accept customer payments in XOF, USD, and EUR while merchant revenue is credited in XOF. Customers are sent to a secure lomi. checkout session to complete payment, and WooCommerce order status is updated when the payment is confirmed.

## Current Payment Model

- Supported customer payment currencies: XOF, USD, EUR
- Merchant revenue credited in XOF
- Secure lomi. hosted checkout
- Test and live API key configuration
- Webhook-based payment confirmation
- WooCommerce refunds via `POST /refunds` when the order is linked to a lomi transaction
- WooCommerce Subscriptions: map products to lomi recurring `price_id` values for first payment
- Payouts and balance: lomi. dashboard only

## Important Notes

- This plugin does not support stores configured with currencies outside XOF, USD, and EUR unless the supported-currency filter is customized.
- Refunds from WooCommerce require `_lomi_transaction_id` on the order (set after payment). Otherwise process refunds from the lomi. dashboard.
- Saved cards and automatic subscription renewals are not charged in WooCommerce — recurring billing is handled by the lomi. subscription engine.
- WooCommerce Subscriptions can manage subscription products; link each subscription product to a lomi recurring price in the product editor.

## Requirements

- WordPress 6.2 or later
- WooCommerce 9.6 or later
- PHP 7.4 or later

## Installation

Download the latest release zip:

`https://github.com/lomiafrica/lomi./releases/latest/download/woo-lomi.zip`

1. In WordPress admin go to **Plugins → Add New → Upload Plugin** and upload `woo-lomi.zip`.
2. Activate the plugin.
3. Go to `WooCommerce > Settings > Payments`.
4. Select `lomi.`.
5. Enable the gateway and review the **Setup health** panel.
6. Add your lomi. test or live API keys and webhook signing secret.
7. Configure the webhook URL shown in the settings screen from your lomi. dashboard (`PAYMENT_SUCCEEDED` and `REFUND_COMPLETED` recommended).
8. Save changes.

### Building a release zip locally

From `apps/plugins/woo`:

```bash
npm run release
```

Produces `dist/woo-lomi-{version}.zip`. GitHub Actions publishes `woo-lomi.zip` on `woo-v*` tags.

## Configuration

### Main Settings

- Enable or disable lomi. on checkout.
- Set the checkout title customers see during payment.
- Set the checkout description customers see during payment.
- Enable test mode while testing.
- Add live and test API credentials.
- Add live and test webhook signing secrets.

### Webhooks

Configure the webhook URL shown in the lomi. settings screen from your lomi. dashboard.

The webhook signing secret in WooCommerce must match the secret configured for the webhook endpoint in lomi. Recommended events: `PAYMENT_SUCCEEDED`, `REFUND_COMPLETED`.

### WooCommerce Subscriptions

On each subscription product (or variation), set **lomi price ID** in the **lomi.** product data tab. Checkout is blocked until subscription products are linked. The first payment creates a lomi subscription; renewals are billed by lomi., not WooCommerce cron.

### Supported Currencies

The default supported store currencies are:

- XOF
- USD
- EUR

Stores using another currency will see the gateway disabled by default.

Developers can customize the supported currency list with the `woocommerce_lomi_supported_currencies` filter.

## FAQ

### Where do I download the plugin?

From GitHub Releases: `https://github.com/lomiafrica/lomi./releases/latest/download/woo-lomi.zip`

### Which currencies does this plugin support?

The plugin supports XOF, USD, and EUR by default.

### What currency is merchant revenue credited in?

Merchant revenue is credited in XOF.

### Does this plugin use hosted checkout?

Yes. Customers complete payment through lomi. hosted checkout.

### Are WooCommerce refunds supported?

Yes, when the order stores a lomi transaction ID after payment. Otherwise refund from the lomi. dashboard.

### Are saved cards supported?

No. Saved card payments are not available with lomi. hosted checkout in this plugin.

### Are automatic subscription renewals charged by WooCommerce?

No. Link subscription products to lomi recurring prices for the first payment. Recurring billing is handled by lomi.; WooCommerce renewal orders are placed on hold with an explanatory note.

### How do I test payments?

Enable test mode, enter your test API credentials and test webhook signing secret, then run checkout using a test order.

## Changelog

### 1.002.0

- GitHub Releases distribution (`woo-lomi.zip`, tag `woo-v*`)
- Setup health panel and `GET /me` connection test
- WooCommerce refunds via lomi. `POST /refunds`
- Persist lomi transaction, subscription, and price IDs on orders
- WooCommerce Subscriptions: lomi price ID product fields and checkout gating
- `REFUND_COMPLETED` webhook order notes

### 1.001.1

- Hosted checkout integration for XOF, USD, and EUR
- Webhook-based order confirmation
- WooCommerce Blocks checkout support
