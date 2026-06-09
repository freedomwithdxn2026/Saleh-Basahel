---
name: dxn-business-ops
description: Use when working on DXN-style direct-selling business operations, distributor/member workflows, product sales processes, order tracking, commission/bonus logic, training systems, CRM flows, WhatsApp sales follow-up, or back-office dashboards for a DXN business.
---

# DXN Business Ops

Treat DXN work as direct selling plus ecommerce operations: products, members/distributors, customers, orders, training, referrals, incentives, and repeat purchase follow-up.

## Operating Model

- Start by identifying the business actor: visitor, customer, member/distributor, team leader, admin, or support.
- Separate retail sales from member/distributor workflows.
- Keep customer purchase flows simple: product discovery, trust signal, contact/checkout, payment, delivery, follow-up.
- Keep distributor workflows measurable: lead source, signup status, sponsor/upline, first order, repeat order, training progress, sales volume, rank/status, payout readiness.
- Avoid inventing compensation rules. Ask for or inspect the official country-specific DXN plan before implementing percentages, ranks, bonuses, or payout conditions.
- Default to auditable records for orders, commissions, and member status changes.

## Data To Model

For DXN business apps, consider these core entities:

- Product: SKU, category, price, distributor price if applicable, stock, images, claims-safe description.
- Customer: name, contact, location, consent, purchase history, preferred language.
- Member/distributor: member ID, sponsor/upline, status, country, joined date, team, training progress.
- Order: order number, buyer type, items, quantities, pricing basis, payment status, fulfillment status.
- Lead: channel, owner, stage, notes, next follow-up date, conversion result.
- Commission/payout: source order, eligible member, calculation inputs, status, approval notes.
- Training/event: topic, date, attendees, recording/materials, follow-up tasks.

## Workflow

1. Inspect existing files, database schema, and business documents before designing behavior.
2. Map the user request to one actor and one measurable workflow.
3. Preserve local country rules and official DXN terminology already present in the project.
4. If building reports, include date range, actor scope, and source of truth.
5. If building automation, include a manual override path and a clear audit trail.

## Guardrails

- Do not promise guaranteed income, rank advancement, disease treatment, or medical outcomes.
- Do not use unverifiable product, income, or company ranking claims without a source.
- Treat member/customer contact details, order history, and payout data as sensitive.
- Flag missing legal/compliance review when workflows include health claims, income claims, payment, or personal data.
