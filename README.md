## Flash-Sale Checkout Backend

### Assumptions & Invariants

- **IDs vs UUIDs**
  - Internal relations use integer IDs: `product_id`, `hold_id`, `order_id`.
  - Public API uses UUIDs only: URL params and request fields are `uuid`, `product_uuid`, `hold_uuid`, `order_uuid`.
  - All main tables (`products`, `holds`, `orders`, `payment_webhooks`) have a unique `uuid` column.

- **Status rules**
  - Holds use `HoldStatusEnum` (`active`, `expired`, `used`, `cancelled`) and are stored as strings.
  - Orders use `OrderStatusEnum` (`pending`, `paid`, `cancelled`) and are stored as strings.
  - A hold used to create an order is marked `used`.
  - Expiry job only changes `active` holds with `expires_at <= now()` to `expired`.

- **Inventory logic**
  - Available stock for a product is:
    - `stock - active, non-expired holds - paid orders`, never below 0.
  - Non-active or expired holds do not reduce availability.
  - Only `paid` orders reduce availability.

- **Payment webhooks**
  - `transaction_reference` is unique per webhook and used for idempotency.
  - `status=success` → order becomes `paid` (if not already).
  - `status=failure` → order becomes `cancelled`; if its hold is `used`, that hold becomes `cancelled`.
  - Already `paid` orders are never downgraded by later webhooks.

---

</p>
